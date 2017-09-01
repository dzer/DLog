<?php

namespace app\log\controller;

use app\log\service\LogService;
use Mll\Common\Common;
use Mll\Controller;
use Mll\Db\Mongo;
use Mll\Mll;

class Index extends Controller
{
    public function index()
    {
        $curr_time = Mll::app()->request->get('curr_time', date('Y-m-d'));
        $log_type = Mll::app()->request->get('log_type');
        $_GET['curr_time'] = $curr_time;
        $where = [];
        $mongo = new Mongo();
        $collection = $mongo->setDBName('system_log')->selectCollection('log');
        $where['type']['$in'] = [LOG_TYPE_FINISH, LOG_TYPE_CURL, LOG_TYPE_RPC, LOG_TYPE_RULE];
        if (!empty($log_type)) {
            $where['type'] = $log_type;
        }
        $count = $collection->count($where);
        if (!empty($curr_time)) {
            $where['time']['$gte'] = $curr_time . ' 00:00:00';
        }
        if (!empty($curr_time)) {
            $where['time']['$lte'] = $curr_time . ' 23:59:59';
        }

        $where['date']['$ne'] = null;
        $statusArr = [
            'aggregate' => 'log',
            'pipeline' => [
                [
                    '$project' => [
                        'type' => 1,
                        'time' => 1,
                        'date' => [
                            '$dateToString' => ['format' => '%Y-%m-%d', 'date' => '$date']
                        ],
                        'execTime' => '$content.execTime',
                        'execTime_200' => [
                            '$cond' => [
                                'if' => ['$and' => [
                                    ['$gt' => ['$content.execTime', 0]], ['$lte' => ['$content.execTime', 0.2]]
                                ]],
                                'then' => 1,
                                'else' => 0
                            ]
                        ],
                        'execTime_500' => [
                            '$cond' => [
                                'if' => ['$and' => [
                                    ['$gt' => ['$content.execTime', 0.2]], ['$lte' => ['$content.execTime', 0.5]]
                                ]],
                                'then' => 1,
                                'else' => 0
                            ]
                        ],
                        'execTime_1000' => [
                            '$cond' => [
                                'if' => ['$and' => [
                                    ['$gt' => ['$content.execTime', 0.5]], ['$lte' => ['$content.execTime', 1]]
                                ]],
                                'then' => 1,
                                'else' => 0
                            ]
                        ],
                        'execTime_1000+' => [
                            '$cond' => [
                                'if' => ['$and' => [
                                    ['$gt' => ['$content.execTime', 1]]
                                ]],
                                'then' => 1,
                                'else' => 0
                            ]
                        ],
                        'httpCode_200' => [
                            '$cond' => [
                                'if' => ['$and' => [
                                    ['$gte' => ['$content.responseCode', 200]], ['$lte' => ['$content.responseCode', 220]]
                                ]],
                                'then' => 1,
                                'else' => 0
                            ]
                        ],
                        'httpCode_300' => [
                            '$cond' => [
                                'if' => ['$and' => [
                                    ['$gte' => ['$content.responseCode', 300]], ['$lte' => ['$content.responseCode', 320]]
                                ]],
                                'then' => 1,
                                'else' => 0
                            ]
                        ],
                        'httpCode_400' => [
                            '$cond' => [
                                'if' => ['$and' => [
                                    ['$gte' => ['$content.responseCode', 400]], ['$lte' => ['$content.responseCode', 420]]
                                ]],
                                'then' => 1,
                                'else' => 0
                            ]
                        ],
                        'httpCode_500' => [
                            '$cond' => [
                                'if' => ['$and' => [
                                    ['$gte' => ['$content.responseCode', 500]], ['$lte' => ['$content.responseCode', 520]]
                                ]],
                                'then' => 1,
                                'else' => 0
                            ]
                        ],
                    ]
                ],
                ['$match' => $where],
                [
                    '$group' => [
                        '_id' => [
                            'date' => '$date',
                        ],
                        'count' => ['$sum' => 1],
                        'time' => ['$avg' => '$execTime'],
                        'time_200' => ['$sum' => '$execTime_200'],
                        'time_500' => ['$sum' => '$execTime_500'],
                        'time_1000' => ['$sum' => '$execTime_1000'],
                        'time_1000+' => ['$sum' => '$execTime_1000+'],
                        'code_200' => ['$sum' => '$httpCode_200'],
                        'code_300' => ['$sum' => '$httpCode_300'],
                        'code_400' => ['$sum' => '$httpCode_400'],
                        'code_500' => ['$sum' => '$httpCode_500'],
                    ]
                ],
            ]
        ];

        $status_rs = $collection->executeCommand($statusArr);
        unset($statusArr);
        $status_rs = Common::objectToArray($status_rs);

        $countArr = [
            'aggregate' => 'log',
            'pipeline' => [
                [
                    '$project' => [
                        'item' => 1,
                        'url' => '$content.url',
                        'type' => 1,
                        'time' => 1,
                        'date' => [
                            '$dateToString' => ['format' => '%Y-%m-%d %H', 'date' => '$date']
                        ],
                        'execTime' => '$content.execTime',
                        'httpCode_200' => [
                            '$cond' => [
                                'if' => ['$and' => [
                                    ['$eq' => ['$content.responseCode', 200]]
                                ]],
                                'then' => 1,
                                'else' => 0
                            ]
                        ],
                        'responseCode' => '$content.responseCode',
                    ]
                ],
                ['$match' => $where],
                [
                    '$group' => [
                        '_id' => [
                            'date' => '$date',
                        ],
                        'count' => ['$sum' => 1],
                        'time' => ['$avg' => '$execTime'],
                        'code_200' => ['$sum' => '$httpCode_200'],
                        'min_time' => ['$min' => '$execTime'],
                        'max_time' => ['$max' => '$execTime'],
                    ]
                ],
                ['$sort' => ['date' => -1]],
            ]
        ];
        $mongo = new Mongo();
        $collection = $mongo->setDBName('system_log')->selectCollection('log');
        $count_rs = $collection->executeCommand($countArr);
        unset($countArr);
        $count_rs = Common::objectToArray($count_rs);
        $countData = array();
        if (!empty($count_rs[0]['result'])) {
            foreach ($count_rs[0]['result'] as $_count) {
                $hour = intval(date('H',strtotime($_count['_id']['date'] . ':00')));
                $time[$hour] = $_count['time'];
                $success[$hour] = $_count['code_200'];
                $fail[$hour] = $_count['count'] - $success[$hour];
            }
        }

        for ($i = 0; $i < 24; $i++) {
            $countData['count_time'][] =  str_pad($i, 2, '0', STR_PAD_LEFT) . ':00';
            $countData['time'][$i] = isset($time[$i]) ? floatval(sprintf('%.2f', ($time[$i] * 1000))) : 0;
            $countData['success'][$i] = isset($success[$i]) ? $success[$i] : 0;
            $countData['fail'][$i] = isset($fail[$i]) ? $fail[$i] : 0;
        }

        return $this->render('index', [
            'countData' => $countData,
            'statusData' => isset($status_rs[0]['result'][0]) ? $status_rs[0]['result'][0] : null,
            'count' => intval($count),
            'base_url' => '/' . Mll::app()->request->getModule()
                . '/' . Mll::app()->request->getController() . '/' . Mll::app()->request->getAction()
        ]);
    }

    /**
     * 最近访问
     */
    public function just()
    {
        //获取缓存中日志数据并存储
        LogService::pullLogByFile(1000);

        $start_time = Mll::app()->request->get('start_time', date('Y-m-d 00:00:00', strtotime('-2 days')));
        $end_time = Mll::app()->request->get('end_time', date('Y-m-d') . ' 23:59:59');
        $request_url = Mll::app()->request->get('request_url');
        $log_level = Mll::app()->request->get('log_level');
        $log_type = Mll::app()->request->get('log_type');
        $request_id = Mll::app()->request->get('request_id');
        $page = Mll::app()->request->get('page', 1, 'intval');
        $page_size = Mll::app()->request->get('limit', 20, 'intval');
        $_GET['start_time'] = $start_time;
        $_GET['end_time'] = $end_time;

        $where = [];
        if (!empty($start_time)) {
            $where['time']['$gte'] = $start_time;
        }
        if (!empty($end_time)) {
            $where['time']['$lte'] = $end_time;
        }
        if (!empty($request_url)) {
            $where['content.url']['$regex'] = preg_quote(trim($request_url));
        }
        if (!empty($log_level)) {
            $where['level'] = $log_level;
        }
        if (!empty($log_type)) {
            $where['type'] = $log_type;
        } else {
            $where['type']['$in'] = [LOG_TYPE_FINISH, LOG_TYPE_CURL, LOG_TYPE_RPC, LOG_TYPE_RULE];
        }
        if (!empty($request_id)) {
            $where['requestId'] = $request_id;
        }

        $mongo = new Mongo();
        $collection = $mongo->setDBName('system_log')->selectCollection('log');
        $count = $collection->count($where);
        //计算分页
        $page_count = ceil($count / $page_size);

        $rs = $collection->find($where, ['time' => -1], ($page - 1) * $page_size, $page_size);
        $rs = Common::objectToArray($rs);

        return $this->render('just', [
            'rs' => $rs,
            'page' => [
                'page' => $page,
                'page_size' => $page_size,
                'page_count' => $page_count,
            ],
            'base_url' => '/' . Mll::app()->request->getModule()
                . '/' . Mll::app()->request->getController() . '/' . Mll::app()->request->getAction()
        ]);
    }

    /**
     * 日志跟踪
     */
    public function trace()
    {
        $requestId = Mll::app()->request->get('request_id');

        $mongo = new Mongo();
        $collection = $mongo->setDBName('system_log')->selectCollection('log');
        $rs = $collection->find(['requestId' => $requestId]);

        $rs = Common::objectToArray($rs);
        if (empty($rs)) {
            throw new \Exception('跟踪日志不存在');
        }

        //traceId排序
        $rs = LogService::traceLogVersionSort($rs);

        $mainRequest = reset($rs);
        return $this->render('trace', [
            'info' => json_encode($rs),
            'rs' => $rs,
            'main' => $mainRequest
        ]);
    }

    /**
     * 性能排行
     */
    public function rank()
    {
        $start_time = Mll::app()->request->get('start_time', date('Y-m-d 00:00:00'));
        $end_time = Mll::app()->request->get('end_time', date('Y-m-d') . ' 23:59:59');
        $request_url = Mll::app()->request->get('request_url');
        $log_type = Mll::app()->request->get('log_type');
        //$call_sort = Mll::app()->request->get('call_sort', 1);
        $page = Mll::app()->request->get('page', 1, 'intval');
        $page_size = Mll::app()->request->get('limit', 10, 'intval');
        $_GET['start_time'] = $start_time;
        $_GET['end_time'] = $end_time;

        $where = [];
        if (!empty($start_time)) {
            $where['time']['$gte'] = $start_time;
        }
        if (!empty($end_time)) {
            $where['time']['$lte'] = $end_time;
        }
        if (!empty($request_url)) {
            $where['content.url']['$regex'] = preg_quote(trim($request_url));
        }
        if (!empty($log_type)) {
            $where['type'] = $log_type;
        } else {
            $where['type']['$in'] = [LOG_TYPE_FINISH, LOG_TYPE_CURL, LOG_TYPE_RPC, LOG_TYPE_RULE];
        }

        $mongo = new Mongo();
        $collection = $mongo->setDBName('system_log')->selectCollection('log');
        $comArr = [
            'aggregate' => 'log',
            'pipeline' => [
                [
                    '$project' => [
                        'item' => 1,
                        'url' => '$content.url',
                        'type' => 1,
                        'time' => 1,
                        'execTime' => '$content.execTime',
                        'execTime_200' => [
                            '$cond' => [
                                'if' => ['$and' => [
                                    ['$gt' => ['$content.execTime', 0]], ['$lte' => ['$content.execTime', 0.2]]
                                ]],
                                'then' => 1,
                                'else' => 0
                            ]
                        ],
                        'execTime_500' => [
                            '$cond' => [
                                'if' => ['$and' => [
                                    ['$gt' => ['$content.execTime', 0.2]], ['$lte' => ['$content.execTime', 0.5]]
                                ]],
                                'then' => 1,
                                'else' => 0
                            ]
                        ],
                        'execTime_1000' => [
                            '$cond' => [
                                'if' => ['$and' => [
                                    ['$gt' => ['$content.execTime', 0.5]], ['$lte' => ['$content.execTime', 1]]
                                ]],
                                'then' => 1,
                                'else' => 0
                            ]
                        ],
                        'execTime_1000+' => [
                            '$cond' => [
                                'if' => ['$and' => [
                                    ['$gt' => ['$content.execTime', 1]]
                                ]],
                                'then' => 1,
                                'else' => 0
                            ]
                        ],
                        'httpCode_200' => [
                            '$cond' => [
                                'if' => ['$and' => [
                                    ['$gte' => ['$content.responseCode', 200]], ['$lte' => ['$content.responseCode', 220]]
                                ]],
                                'then' => 1,
                                'else' => 0
                            ]
                        ],
                        'httpCode_300' => [
                            '$cond' => [
                                'if' => ['$and' => [
                                    ['$gte' => ['$content.responseCode', 300]], ['$lte' => ['$content.responseCode', 320]]
                                ]],
                                'then' => 1,
                                'else' => 0
                            ]
                        ],
                        'httpCode_400' => [
                            '$cond' => [
                                'if' => ['$and' => [
                                    ['$gte' => ['$content.responseCode', 400]], ['$lte' => ['$content.responseCode', 420]]
                                ]],
                                'then' => 1,
                                'else' => 0
                            ]
                        ],
                        'httpCode_500' => [
                            '$cond' => [
                                'if' => ['$and' => [
                                    ['$gte' => ['$content.responseCode', 500]], ['$lte' => ['$content.responseCode', 520]]
                                ]],
                                'then' => 1,
                                'else' => 0
                            ]
                        ],
                        'responseCode' => '$content.responseCode',
                    ]
                ],
                ['$match' => $where],
                [
                    '$group' => [
                        '_id' => [
                            'url' => '$url',
                        ],
                        'count' => ['$sum' => 1],
                        'type' => ['$first' => '$type'],
                        'time' => ['$avg' => '$execTime'],
                        'time_200' => ['$sum' => '$execTime_200'],
                        'time_500' => ['$sum' => '$execTime_500'],
                        'time_1000' => ['$sum' => '$execTime_1000'],
                        'time_1000+' => ['$sum' => '$execTime_1000+'],
                        'code_200' => ['$sum' => '$httpCode_200'],
                        'code_300' => ['$sum' => '$httpCode_300'],
                        'code_400' => ['$sum' => '$httpCode_400'],
                        'code_500' => ['$sum' => '$httpCode_500'],
                        'http_code' => ['$addToSet' => '$responseCode'],
                        'min_time' => ['$min' => '$execTime'],
                        'max_time' => ['$max' => '$execTime'],
                    ]
                ],
                ['$sort' => ['count' => -1]],
            ],
        ];
        $rs = $collection->executeCommand($comArr);
        unset($comArr);
        $rs = Common::objectToArray($rs);
        return $this->render('rank', [
            'rs' => isset($rs[0]['result']) ? $rs[0]['result'] : null,
            'page' => [
                'page' => 1,
                'page_size' => 1,
                'page_count' => 1,
            ],
            'base_url' => '/' . Mll::app()->request->getModule()
                . '/' . Mll::app()->request->getController() . '/' . Mll::app()->request->getAction()
        ]);
    }
}