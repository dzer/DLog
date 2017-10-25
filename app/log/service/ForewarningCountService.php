<?php

namespace app\log\service;

use app\log\model\LogModel;
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
                        'sendType' => $scheme['sendType']
                    ];
                }
                if (isset($scheme['count']['avg']) && $scheme['count']['avg'] > 0
                    && $result['time'] > $scheme['count']['avg'] && $result['count'] >= $scheme['count']['number']
                ) {
                    $msg = $this->createMsg($start_time, $end_time, $scheme['where'], $result, 'avg');
                    $msgArr[] = [
                        'time' => date('Y-m-d H:i:s'),
                        'msg' => $msg,
                        'sendType' => $scheme['sendType']
                    ];
                }
            }
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
        $rs = $model->countByServer($db, ['time' => ['$gt' => date('Y-m-d H:i:s', time() - 300)]]);
        $servers = [];
        if (!empty($rs[0]['result'])) {
            foreach ($rs[0]['result'] as $_list) {
                $servers[] = $_list['_id']['server'];
            }
        }
        $diff_servers = array_diff($model->servers, $servers);

        if (!empty($diff_servers)) {
            return [
                'time' => date('Y-m-d H:i:s'),
                'msg' => 'DLOG日志报警：近5分钟，' . implode('，', $diff_servers) . ' 没有日志记录！！！',
                'sendType' => $config['sendType']
            ];
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

        $msg = "DLOG日志报警：{$start_time}~{$end_time}";
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