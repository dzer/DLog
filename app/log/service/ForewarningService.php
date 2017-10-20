<?php

namespace app\log\service;

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
                'number' => 50, //次数
            ],
            'sendType' => [
                'wechat' => [
                    'all',
                    //'18581855415',
                    //'shenke',
                    //'18681232162'
                ],
            ],
        ],
        [
            'time' => 5,    //统计时间最近几分钟
            'where' => [    //筛选条件
                //'level' => 'error',
                'type' => 'REQUEST',
                //'execTime' => '',
                'project' => 'mll'
            ],
            'count' => [
                'number' => 50, //次数
                'avg' => 1 //平均时间单位秒
            ],
            'sendType' => [
                'wechat' => [
                    'all',
                    //'18581855415',
                    //'shenke',
                    //'18681232162'
                ],
                /*'email' => [
                    'dongxu2@meilele.com'
                ],
                'sms' => [
                    '18581855415'
                ]*/
            ],
        ],
        [
            'time' => 5,    //统计时间最近几分钟
            'where' => [    //筛选条件
                'type' => 'REQUEST',
                'responseCode' => 500
            ],
            'count' => [
                'number' => 10, //次数
                //'avg' => 1 //平均时间单位秒
            ],
            'sendType' => [
                'wechat' => [
                    'all',
                ],
            ],
        ],
        [
            'time' => 5,    //统计时间最近几分钟
            'where' => [    //筛选条件
                'type' => 'CURL',
                'responseCode' => 500
            ],
            'count' => [
                'number' => 10, //次数
                //'avg' => 1 //平均时间单位秒
            ],
            'sendType' => [
                'wechat' => [
                    'all',
                ],
            ],
        ],
        [
            'time' => 5,    //统计时间最近几分钟
            'where' => [    //筛选条件
                'level' => 'error',
                'type' => 'MEMCACHE',
            ],
            'count' => [
                'number' => 10, //次数
            ],
            'sendType' => [
                'wechat' => [
                    'all',
                ],
            ],
        ],
        [
            'time' => 5,    //统计时间最近几分钟
            'where' => [    //筛选条件
                'level' => 'error',
                'type' => 'MYSQL',
            ],
            'count' => [
                'number' => 10, //次数
                //'avg' => 1 //平均时间单位秒
            ],
            'sendType' => [
                'wechat' => [
                    'all',
                ],
            ],
        ],
        [
            'time' => 5,    //统计时间最近几分钟
            'where' => [    //筛选条件
                'level' => 'error',
                'type' => 'RULE',
            ],
            'count' => [
                'number' => 50, //次数
            ],
            'sendType' => [
                'wechat' => [
                    'all',
                ],
            ],
        ],
        [
            'time' => 10,    //统计时间最近几分钟
            'where' => [    //筛选条件
                'type' => 'RULE',
            ],
            'count' => [
                'number' => 200,
                'avg' => 3 //平均时间单位秒
            ],
            'sendType' => [
                'wechat' => [
                    'all',
                ],
            ],
        ],
    ];

    /**
     * 统计
     */
    public function count()
    {
        $model = new LogModel();
        $rs_msg = [];
        foreach ($this->scheme as $k => $scheme) {
            $where = $scheme['where'];
            $start_time = date('Y-m-d H:i:s', (time() - $scheme['time'] * 60));
            $end_time = date('Y-m-d H:i:s');

            if (isset($scheme['time']) && $scheme['time'] > 0) {
                $where['time']['$gt'] = $start_time;
            }
            if (isset($where['execTime']) && $where['execTime'] > 0) {
                $where['execTime']['$gt'] = $where['execTime'];
            }
            $db = 'system_log_' . date('m_d');
            //统计日志
            $rs = $model->countByForewarning($db, $where);
            $rs_msg[$k]['where'] = $scheme;
            $rs_msg[$k]['rs'] = $rs;

            if (!empty($rs[0]['result'][0])) {
                $result = $rs[0]['result'][0];
                if (isset($scheme['count']['number']) && $scheme['count']['number'] > 0
                    && $result['count'] >= $scheme['count']['number'] && empty($scheme['count']['avg'])
                ) {
                    //创建消息
                    $msg = $this->createMsg($start_time, $end_time, $scheme['where'], $result, 'number');
                    $this->msg[] = [
                        'msg' => $msg,
                        'sendType' => $scheme['sendType']
                    ];
                }
                if (isset($scheme['count']['avg']) && $scheme['count']['avg'] > 0
                    && $result['time'] > $scheme['count']['avg'] && $result['count'] >= $scheme['count']['number']
                ) {
                    $msg = $this->createMsg($start_time, $end_time, $scheme['where'], $result, 'avg');
                    $rs_msg[$k]['msg'] = $msg;
                    $this->msg[] = [
                        'msg' => $msg,
                        'sendType' => $scheme['sendType']
                    ];
                }
            }
        }
        //发送消息
        $this->sendMsg();
        echo json_encode($rs_msg);
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
                        //echo $msg['msg'];
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
                if ($_user == 'all') {
                    Mll::app()->curl->get($url . '?content=' . urlencode($msg));
                } else {
                    Mll::app()->curl->get($url . '?content=' . urlencode($msg) . '&wxzh=' . $_user);
                }
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
                . round($count['time'],2) . ' 秒，请及时处理。';
        }
        return $msg;
    }

}