<?php

namespace app\log\controller;

use app\log\service\LogService;
use Mll\Controller;
use Mll\Db\Mongo;
use Mll\Mll;
use Mll\Cache;
use Mll\Common\Common;

class Parse extends Controller
{
    public function pull()
    {
        $num = isset($_GET['num']) ? intval($_GET['num']) : 10000;
        //获取缓存中日志数据并存储
        for ($i = intval($num / 2000); $i > 0; $i--) {
            echo LogService::pullLogByMq(2000) . '<br>';
        }
    }

    public function count()
    {
        echo 'ok';
        set_time_limit(600);
        if (function_exists("fastcgi_finish_request")) {
            fastcgi_finish_request();
        }

        $curr_time = Mll::app()->request->get('curr_time', date('Y-m-d'));
        $log_type = Mll::app()->request->get('log_type', LOG_TYPE_FINISH);
        $project = Mll::app()->request->get('project', 'help');
        $where = [];
        $mongo = new Mongo();
        $collection = $mongo->selectCollection('log');
        if (!empty($log_type)) {
            $where['type'] = $log_type;
        }
        if (!empty($project)) {
            $where['project'] = $project;
        }
        Cache::cut('file');
        $cache_key = 'log_count_time';
        $count_time = Cache::get($cache_key);
        if (!($count_time === false || time() - $count_time > 600)) {
            return false;
        }
        Cache::set($cache_key, time(), 600);

        $cache_key = 'log_count_' . date('d');
        $count = Cache::get($cache_key);
        if (isset($_GET['count']) || $count === false) {
            $count = $mongo->count(['microtime' => ['$lt' => strtotime(date('Y-m-d 0:0:0'))]]);
            Cache::set($cache_key, $count, 0);
        }
        if (!empty($curr_time)) {
            $where['time']['$gte'] = $curr_time . ' 00:00:00';
            $where['time']['$lte'] = $curr_time . ' 23:59:59';
        }

        $statusArr = [
            'aggregate' => 'log',
            'pipeline' => [
                [
                    '$project' => [
                        'project' => 1,
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
        $cache_key = 'log_status_rs_' . $where['project'] . '_' . $curr_time . '_' .$where['type'];
        $status_rs = $collection->executeCommand($statusArr);
        $status_rs = Common::objectToArray($status_rs);
        Cache::set($cache_key, json_encode($status_rs), 1800);

        $countArr = [
            'aggregate' => 'log',
            'pipeline' => [
                [
                    '$project' => [
                        'project' => 1,
                        'type' => 1,
                        'error' => [
                            '$cond' => [
                                'if' => ['$eq' => ['$level', 'error']],
                                'then' => 1,
                                'else' => 0
                            ]
                        ],
                        'time' => 1,
                        'date' => [
                            '$dateToString' => ['format' => '%Y-%m-%d %H', 'date' => '$date']
                        ],
                        'execTime' => '$content.execTime',
                        'httpCode_200' => [
                            '$cond' => [
                                'if' => ['$and' => [
                                    ['$gte' => ['$content.responseCode', 200]], ['$lte' => ['$content.responseCode', 320]]
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
                        'error' => ['$sum' => '$error'],
                        'time' => ['$avg' => '$execTime'],
                        'code_200' => ['$sum' => '$httpCode_200'],
                    ]
                ],
                ['$sort' => ['date' => -1]],
            ]
        ];

        $cache_key = 'log_count_rs_' . $where['project'] . '_' . $curr_time . '_' .$where['type'];
        $count_rs = $collection->executeCommand($countArr);
        $count_rs = Common::objectToArray($count_rs);
        Cache::set($cache_key, json_encode($count_rs), 1800);

        return true;
    }

}