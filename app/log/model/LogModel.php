<?php

namespace app\log\model;

use Mll\Mll;
use Mll\Model;
use Mll\Cache;
use Mll\Db\Mongo;
use Mll\Common\Common;

class LogModel extends Model
{

    public $projects = [
        'all' => '所有项目',
        'common' => 'COMMON',
        'mll' => 'MLL',
        'zx' => 'ZX',
        'help' => 'HELP',
        'store' => 'STORE',
        'seller' => 'SELLER',
        'wap' => 'WAP',
        'factory' => 'FACTORY',
        'erp' => 'ERP',
        'ipad' => 'IPAD',
        'third_api' => 'THIRD_API',
        'logi' => 'LOGI',
        'supply' => 'SUPPLY',
    ];

    public $types = [
        'REQUEST' => '请求',
        'CURL' => '接口',
        'RULE' => '规则',
        'MYSQL' => 'MYSQL',
        'MQ' => 'MQ',
        'MEMCACHE' => '缓存',
        'SYSTEM' => '系统',
    ];

    public $servers = [
        'web_php_04' => 'web_php_04',
        'web_php_05' => 'web_php_05',
        'web_php_07' => 'web_php_07',
        'web_php_08' => 'web_php_08',
        'web_php_13' => 'web_php_13',
        'web_php_16' => 'web_php_16',
    ];

    public function getProjects($db, $expire = 86400)
    {
        $countArr = [
            'aggregate' => 'log_count_hour',
            'pipeline' => [
                [
                    '$project' => [
                        'project' => 1,
                        'date' => 1,
                        'count' => 1
                    ]
                ],
                ['$match' => ['date' => ['$gte' => date('Y-m-d', strtotime('-3 day'))]]],
                [
                    '$group' => [
                        '_id' => [
                            'project' => '$project',
                        ],
                        'count' => ['$sum' => '$count'],
                    ]
                ],
                ['$sort' => ['count' => -1]],
            ]
        ];

        $cache_key = 'log_count_projects';
        Cache::cut('file');
        $project_rs = Cache::get($cache_key);
        if ($project_rs === false) {
            $mongoConfig = Mll::app()->config->get('db.mongo');
            $mongoConfig['database'] = $db;
            $mongo = new Mongo($mongoConfig);
            $projects = ['all' => '所有项目'];
            $project_rs = $mongo->executeCommand($countArr);
            $project_rs = Common::objectToArray($project_rs);
            if (isset($project_rs[0]['result'])) {
                foreach ($project_rs[0]['result'] as $_project) {
                    $projects[$_project['_id']['project']] = strtoupper($_project['_id']['project']);
                }
            }
            Cache::set($cache_key, json_encode($projects), $expire);
        } else {
            $projects = json_decode($project_rs, true);
        }
        return $projects;
    }

    /**
     * 按状态统计
     *
     * @param array $where
     * @param int $expire
     * @param string $curr_time
     * @return array|mixed|object
     */
    public function countStatus(array $where, $expire = 1800, $curr_time)
    {
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
        Cache::cut('file');
        $cache_key = 'log_status_rs_' . $where['project'] . '_' . $curr_time . '_' .$where['type'];
        $status_rs = Cache::get($cache_key);
        if ($status_rs === false) {
            $mongo = new Mongo();
            $collection = $mongo->selectCollection('log');
            $status_rs = $collection->executeCommand($statusArr);
            $status_rs = Common::objectToArray($status_rs);
            Cache::set($cache_key, json_encode($status_rs), $expire);
        } else {
            $status_rs = json_decode($status_rs, true);
        }
        return $status_rs;
    }

    /**
     * 按时间段统计
     *
     * @param array $where
     * @param int $expire
     * @param string $curr_time
     * @return array|mixed|object
     */
    public function count(array $where, $expire = 1800, $curr_time)
    {
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
        $count_rs = Cache::get($cache_key);
        if ($count_rs === false) {
            $mongo = new Mongo();
            $collection = $mongo->selectCollection('log');
            $count_rs = $collection->executeCommand($countArr);
            $count_rs = Common::objectToArray($count_rs);
            Cache::set($cache_key, json_encode($count_rs), $expire);
        } else {
            $count_rs = json_decode($count_rs, true);
        }
        return $count_rs;
    }

    /**
     * 按执行时间统计
     *
     * @param string $db 数据库名称
     * @param array $where 条件
     * @param integer $page 页码
     * @param integer $page_size 条数
     * @param string $sort 排序
     * @return array|object
     */
    function countRank($db, array $where, $page, $page_size, $sort = 'time')
    {
        $comArr = [
            'aggregate' => 'log',
            'pipeline' => [
                [
                    '$project' => [
                        'item' => 1,
                        'url' => '$content.url',
                        'type' => 1,
                        'time' => 1,
                        'project' => 1,
                        'level' => 1,
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
                ['$sort' => [$sort => -1]],
                ['$skip' => ($page - 1) * $page_size],
                ['$limit' => $page_size]
            ],
        ];
        $mongoConfig = Mll::app()->config->get('db.mongo');
        $mongoConfig['database'] = $db;
        $mongo = new Mongo($mongoConfig);
        $collection = $mongo->selectCollection('log');
        return $collection->executeCommand($comArr);
    }

    public function countError($db, $where, $expire = 600, $curr_time)
    {
        if (isset($where['type'])) {
            unset($where['type']);
        }
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
                    ]
                ],
                ['$match' => $where],
                [
                    '$group' => [
                        '_id' => [
                            'type' => '$type',
                        ],
                        'count' => ['$sum' => 1],
                        'error' => ['$sum' => '$error'],
                    ]
                ],
                ['$sort' => ['count' => -1]],
            ]
        ];

        $cache_key = 'log_count_error_rs_' . $where['project'] . '_' . $curr_time;
        $count_rs = Cache::get($cache_key);
        if ($count_rs === false) {
            $mongoConfig = Mll::app()->config->get('db.mongo');
            $mongoConfig['database'] = $db;
            $mongo = new Mongo($mongoConfig);
            $collection = $mongo->selectCollection('log');
            $count_rs = $collection->executeCommand($countArr);
            $count_rs = Common::objectToArray($count_rs);
            Cache::set($cache_key, json_encode($count_rs), $expire);
        } else {
            $count_rs = json_decode($count_rs, true);
        }
        return $count_rs;
    }

    /**
     * 统计最近几分钟的日志
     *
     * @param string $db
     * @param array $where
     * @return array|mixed|object
     */
    public function countByForewarning($db, array $where)
    {
        if (isset($where['responseCode'])) {
            if ($where['responseCode'] > 0) {
                $where['content.responseCode']['$gte'] = intval($where['responseCode']);
                $where['content.responseCode']['$lte'] = intval($where['responseCode']) + 20;
            } else {
                $where['content.responseCode']['$eq'] = intval($where['responseCode']);
            }
            unset($where['responseCode']);
        }
        $countArr = [
            'aggregate' => 'log',
            'pipeline' => [
                [
                    '$project' => [
                        'project' => 1,
                        'type' => 1,
                        'level' => 1,
                        'time' => 1,
                        'content.responseCode' => 1,
                        'execTime' => '$content.execTime',
                    ]
                ],
                ['$match' => $where],
                [
                    '$group' => [
                        '_id' => null,
                        'count' => ['$sum' => 1],
                        'time' => ['$avg' => '$execTime'],
                    ]
                ],
            ]
        ];

        $mongoConfig = Mll::app()->config->get('db.mongo');
        $mongoConfig['database'] = $db;
        $mongo = new Mongo($mongoConfig);
        return Common::objectToArray($mongo->executeCommand($countArr));
    }

    public function countNum($db, $where)
    {
        $countArr = [
            'aggregate' => 'log',
            'pipeline' => [
                [
                    '$project' => [
                        'url' => '$content.url',
                        'type' => 1,
                        'time' => 1,
                        'project' => 1,
                        'execTime' => '$content.execTime',
                        'responseCode' => '$content.responseCode',
                        'requestId' => 1,
                        'level' => 1
                    ]
                ],
                ['$match' => $where],
                [
                    '$group' => [
                        '_id' => null,
                        'count' => ['$sum' => 1],
                    ]
                ],
            ]
        ];

        $mongoConfig = Mll::app()->config->get('db.mongo');
        $mongoConfig['database'] = $db;
        $mongo = new Mongo($mongoConfig);
        $count_rs = $mongo->executeCommand($countArr);

        $count_rs = Common::objectToArray($count_rs);
        return isset($count_rs[0]['result'][0]['count']) ? $count_rs[0]['result'][0]['count'] : null;
    }


    /**
     * 统计各个服务器的日志数量
     *
     * @param string $db
     * @param array $where
     * @return array|mixed|object
     */
    public function countByServer($db, array $where)
    {
        $countArr = [
            'aggregate' => 'log',
            'pipeline' => [
                [
                    '$project' => [
                        'time' => 1,
                        'server' => 1,
                    ]
                ],
                ['$match' => $where],
                [
                    '$group' => [
                        '_id' => [
                            'server' => '$server',
                        ],
                        'count' => ['$sum' => 1],
                    ]
                ],
            ]
        ];
        $mongoConfig = Mll::app()->config->get('db.mongo');
        $mongoConfig['database'] = $db;
        $mongo = new Mongo($mongoConfig);
        return Common::objectToArray($mongo->executeCommand($countArr));
    }
}