<?php

namespace app\log\service;

use app\log\model\LogCountHourModel;
use app\log\model\LogModel;
use Mll\Mll;

class ForewarningService
{
    private $msg;

    /**
     * 报警条件
     * @var array
     */
    private $scheme = [
        [
            'time' => 5,    //统计时间最近几分钟
            'where' => [    //筛选条件
                'level' => 'error',
                //'type' => 'REQUEST',
                //'execTime' => '',
                //'project' => ''
            ],
            'count' => [
                'number' => '10', //次数
            ],
            'sendType' => [
                'wechat' => [
                    '18581855415',
                    'shenke',
                    '18681232162'
                ],
            ],
        ],
        [
            'time' => 5,    //统计时间最近几分钟
            'where' => [    //筛选条件
                //'level' => 'error',
                'type' => 'REQUEST',
                //'execTime' => '',
                //'project' => ''
            ],
            'count' => [
                //'number' => '20', //次数
                'avg' => '2' //平均时间单位秒
            ],
            'sendType' => [
                'wechat' => [
                    '18581855415',
                    'shenke',
                    '18681232162'
                ],
                /*'email' => [
                    'dongxu2@meilele.com'
                ],
                'sms' => [
                    '18581855415'
                ]*/
            ],
        ],
    ];

    /**
     * 统计
     */
    public function count()
    {
        $model = new LogModel();
        foreach ($this->scheme as $scheme) {
            $where = $scheme['where'];
            $start_time = date('Y-m-d H:i:s', (time() - $scheme['time'] * 60));
            $end_time = date('Y-m-d H:i:s');

            if ($scheme['time'] > 0) {
                $where['time']['$gt'] = $start_time;
            }
            if ($where['execTime'] > 0) {
                $where['execTime']['$gt'] = $where['execTime'];
            }
            //统计日志
            $rs = $model->countByForewarning($where);

            if (!empty($rs[0]['result'][0])) {
                $result = $rs[0]['result'][0];
                if (isset($scheme['count']['number']) && $scheme['count']['number'] > 0
                    && $result['count'] > $scheme['count']['number']
                ) {
                    //创建消息
                    $msg = $this->createMsg($start_time, $end_time, $scheme['where'], $result['count'], 'number');
                    $this->msg[] = [
                        'msg' => $msg,
                        'sendType' => $scheme['sendType']
                    ];
                }
                if (isset($scheme['count']['avg']) && $scheme['count']['avg'] > 0
                    && $result['time'] > $scheme['count']['avg']
                ) {
                    $msg = $this->createMsg($start_time, $end_time, $scheme['where'], $result['time'], 'avg');
                    $this->msg[] = [
                        'msg' => $msg,
                        'sendType' => $scheme['sendType']
                    ];
                }
            }
        }
        //发送消息
        $this->sendMsg();
        echo 'success';
    }

    /**
     * 发送消息
     */
    private function sendMsg()
    {
        if (!empty($this->msg)) {
            foreach ($this->msg as $msg) {
                foreach ($msg['sendType'] as $type => $user) {
                    if ($type == 'wechat') {
                        echo $msg['msg'];
                        $this->sendWechat($msg['msg'], $user);
                    }
                }
            }
        }
    }

    /**
     * 发送微信消息
     *
     * @param $msg
     * @param $user
     */
    private function sendWechat($msg, $user)
    {
        $url = Mll::app()->config->params('wechat_url');
        $user = is_string($user) ? array($user) : $user;
        if (is_array($user)) {
            foreach ($user as $_user) {
                //echo $url . '?content=' . urlencode($msg) . '&wxzh=' . $_user;
                $rs = Mll::app()->curl->get($url . '?content=' . urlencode($msg) . '&wxzh=' . $_user);
                echo $rs;
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
        $msg = "DLOG日志报警：{$start_time}~{$end_time}";
        if (!empty($where['project'])) {
            $msg .= '，项目： ' . $where['project'];
        }
        if (!empty($where['type'])) {
            $msg .= '，日志类型为 ' . $where['type'];
        }
        if (!empty($where['level'])) {
            $msg .= '，错误级别为 ' . $where['level'];
        }
        if (!empty($where['execTime'])) {
            $msg .= '，执行时间大于 ' . $where['execTime'] . ' 秒';
        }
        if ($type == 'number') {
            $msg .= ' 的日志数量超过 ' . $count . ' 次，请及时处理。';
        }
        if ($type == 'avg') {
            $msg .= ' 的平均执行时间为 ' . $count . ' 秒，请及时处理。';
        }
        return $msg;
    }

}