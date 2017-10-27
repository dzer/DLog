<?php

namespace app\log\service;

use app\common\helpers\Common;
use app\log\model\LogModel;
use Mll\Common\Amqp;
use Mll\Db\Mongo;
use Mll\Mll;

class ForewarningCountService
{

    /**
     * 校验mongo链接
     *
     * @param $config
     * @return bool|array
     */
    public function checkMongoConnect($config)
    {
        if (empty($config) || empty($config['enable'])) {
            return false;
        }
        if (!empty($config['where']['config'])) {
            $mongoConfig = $config['where']['config'];
        } else {
            $mongoConfig = Mll::app()->config->get('db.mongo');
            $mongoConfig['database'] = 'system_log_' . date('m_d');
        }
        try {
            new Mongo($mongoConfig);
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            return [
                'time' => date('Y-m-d H:i:s'),
                'msg' => 'DLOG日志报警：mongo链接失败，' . $msg,
                'sendType' => $config['sendType']
            ];
        }
        return true;
    }

    /**
     * 统计
     *
     * @param $config
     * @return bool|array
     */
    public function checkLogCount($config)
    {
        if (empty($config) || empty($config['enable']) || empty($config['where'])) {
            return false;
        }
        $model = new LogModel();
        $msgArr = [];
        $db = 'system_log_' . date('m_d');
        foreach ($config['where'] as $k => $scheme) {
            $where = $scheme['where'];
            $start_time = date('Y-m-d H:i:s', (time() - $scheme['time'] * 60));
            $end_time = date('Y-m-d H:i:s');

            if (isset($scheme['time']) && $scheme['time'] > 0) {
                $where['time']['$gt'] = $start_time;
            }
            if (isset($where['execTime']) && $where['execTime'] > 0) {
                $where['execTime']['$gt'] = $where['execTime'];
            }
            //统计日志
            $rs = $model->countByForewarning($db, $where);
            $rsArr[] = $rs;
            if (!empty($rs[0]['result'][0])) {
                $result = $rs[0]['result'][0];
                if (isset($scheme['count']['number']) && $scheme['count']['number'] > 0
                    && $result['count'] >= $scheme['count']['number'] && empty($scheme['count']['avg'])
                ) {
                    //创建消息
                    $msg = $this->createMsg($start_time, $end_time, $scheme['where'], $result, 'number');
                    $msgArr[] = [
                        'time' => date('Y-m-d H:i:s'),
                        'msg' => $msg,
                        'sendType' => $scheme['sendType'],
                        'rs' => $result,
                    ];
                }
                if (isset($scheme['count']['avg']) && $scheme['count']['avg'] > 0
                    && $result['time'] > $scheme['count']['avg'] && $result['count'] >= $scheme['count']['number']
                ) {
                    $msg = $this->createMsg($start_time, $end_time, $scheme['where'], $result, 'avg');
                    $msgArr[] = [
                        'time' => date('Y-m-d H:i:s'),
                        'msg' => $msg,
                        'sendType' => $scheme['sendType'],
                        'rs' => $result,
                    ];
                }
            }
            Mll::app()->log->debug('预警统计！', $rsArr);
        }

        return $msgArr;
    }

    /**
     * checkLogRecord
     *
     * @param $config
     * @return bool|array
     */
    public function checkLogRecord($config)
    {
        if (empty($config) || empty($config['enable'])) {
            return false;
        }
        $model = new LogModel();
        $db = 'system_log_' . date('m_d');
        $rs = $model->countByServer($db, ['time' => ['$gt' => date('Y-m-d H:i:s', time() - ($config['time'] * 60))]]);
        $servers = [];
        if (!empty($rs[0]['result'])) {
            foreach ($rs[0]['result'] as $_list) {
                $servers[] = $_list['_id']['server'];
            }
        }
        $diff_servers = array_diff($model->servers, $servers);

        if (!empty($diff_servers)) {
            //判断mq是否链接成功
            $msgNum = 0;
            try {
                $mq = new Amqp(Mll::app()->config->get('mq.rabbit'));
                $msgNum = $mq->countMessage('QUEUE_PHP_LOG', 'EXCHANGE_PHP_LOG');
            } catch (\Exception $e) {
                $mq_msg = $e->getMessage();
            }
            $mq_msg = !empty($mq_msg) ? 'mq错误：' . $mq_msg : 'mq未处理数量：' . $msgNum;
            return [
                'time' => date('Y-m-d H:i:s'),
                'msg' => 'DLOG日志报警：近' . $config['time'] . '分钟，' . implode('，', $diff_servers)
                    . ' 没有日志记录！！！' . $mq_msg,
                'sendType' => $config['sendType']
            ];
        }
    }

    /**
     * 校验接口
     *
     * @param $config
     * @return bool|array
     */
    public function checkUrl($config)
    {
        if (empty($config) || empty($config['enable']) || empty($config['where']['level']) || empty($config['where']['url'])) {
            return false;
        }
        $mongoConfig = Mll::app()->config->get('db.mongo');
        $mongoConfig['database'] = 'system_log_' . date('m_d');
        $mongo = new Mongo($mongoConfig);
        $start_time = date('Y-m-d H:i:s', time() - ($config['time'] * 60));
        $end_time = date('Y-m-d H:i:s');
        $where = [
            'time' => ['$gte' => $start_time, '$lte' => $end_time],
            'level' => $config['where']['level'],
            'content.url' => ['$regex' => preg_quote($config['where']['url'])],
        ];
        $mongo->selectCollection('log');
        $rs = \Mll\Common\Common::objectToArray($mongo->find($where, [], 0, 10));
        if (isset($rs[0])) {
            $count = count($rs);
            $error_msg = [];
            foreach ($rs as $v) {
                $error_msg[] = isset($v['content']['errorMessage']) ? $v['content']['errorMessage'] : '';
            }
            $error_msg = array_count_values($error_msg);    //统计数组元素出现的次数
            $error_msg = array_flip($error_msg);    //键名与值进行对调
            krsort($error_msg);    //按数组的索引值降序排列
            if (count($error_msg) > 0 && isset($config['count']['number']) && $count >= $config['count']['number']) {
                return [
                    'time' => date('Y-m-d H:i:s'),
                    'msg' => "DLOG日志报警：{$start_time}~" . date('H:i:s', strtotime($end_time))
                        . '，' . $config['msg'] . '，数量超过' . $count . '次，errorMsg：' . array_shift($error_msg),
                    'sendType' => $config['sendType']
                ];
            }

        }

    }

    /**
     * 创建消息
     *
     * @param string $start_time 开始时间
     * @param string $end_time 结束时间
     * @param array $where
     * @param string|int $count
     * @param string $type
     * @return mixed
     */
    private function createMsg($start_time, $end_time, $where, $count, $type)
    {
        $model = new LogModel();

        $msg = "DLOG日志报警：{$start_time}~" . date('H:i:s', strtotime($end_time));
        if (!empty($where['project'])) {
            $msg .= '，项目： ' . $where['project'];
        }
        if (!empty($where['type'])) {
            $msg .= '，日志类型为 ' . $model->types[$where['type']];
        }
        if (!empty($where['level'])) {
            $msg .= '，错误级别为 ' . $where['level'];
        }
        if (!empty($where['responseCode'])) {
            $msg .= '，http状态码为 ' . $where['responseCode'];
        }
        if (!empty($where['execTime'])) {
            $msg .= '，执行时间大于 ' . $where['execTime'] . ' 秒';
        }
        if ($type == 'number') {
            $msg .= ' 的日志数量超过 ' . $count['count'] . ' 次，请及时处理。';
        }
        if ($type == 'avg') {
            $msg .= ' 的日志数量为 ' . $count['count'] . ' 次，平均执行时间为 '
                . round($count['time'], 2) . ' 秒，请及时处理。';
        }
        return $msg;
    }
}