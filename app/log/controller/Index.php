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

        return $this->render('index', ['data' => 'hehehhehe']);
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
                ['$sort' => ['number' => -1]],
            ],

        ];
        $rs = $collection->executeCommand($comArr);
        $rs = Common::objectToArray($rs);

        return $this->render('rank', [
            'rs' => $rs[0]['result'],
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