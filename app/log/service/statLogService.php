<?php

namespace app\log\service;

use app\common\helpers\Common;
use Mll\Cache;
use Mll\Common\MemcacheQueue;
use Mll\Db\Mongo;
use Mll\Mll;
class statLogService
{

    /**
     * 统计时间段的日志并存储
     */
    public function statLog()
    {
        $time = Mll::app()->request->get('time', date('Y-m-d'));
        //$call_sort = Mll::app()->request->get('call_sort', 1);
        $page = Mll::app()->request->get('page', 1, 'intval');
        $page_size = Mll::app()->request->get('limit', 10, 'intval');

        $where = [];
        if (!empty($time)) {
            $_GET['time'] = $time;
            $where['time']['$gte'] = $time;
            $where['time']['$lte'] = $time . '23:59:59';
        }


        $where['type']['$in'] = [LOG_TYPE_FINISH, LOG_TYPE_CURL, LOG_TYPE_RPC, LOG_TYPE_RULE];

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
                                    ['$gte' => ['$content.responseCode', 200]], ['$lte' => ['$content.execTime', 220]]
                                ]],
                                'then' => 1,
                                'else' => 0
                            ]
                        ],
                        'httpCode_300' => [
                            '$cond' => [
                                'if' => ['$and' => [
                                    ['$gte' => ['$content.responseCode', 300]], ['$lte' => ['$content.execTime', 320]]
                                ]],
                                'then' => 1,
                                'else' => 0
                            ]
                        ],
                        'httpCode_400' => [
                            '$cond' => [
                                'if' => ['$and' => [
                                    ['$gte' => ['$content.responseCode', 400]], ['$lte' => ['$content.execTime', 420]]
                                ]],
                                'then' => 1,
                                'else' => 0
                            ]
                        ],
                        'httpCode_500' => [
                            '$cond' => [
                                'if' => ['$and' => [
                                    ['$gte' => ['$content.responseCode', 500]], ['$lte' => ['$content.execTime', 520]]
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