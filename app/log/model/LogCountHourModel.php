<?php

namespace app\log\model;

use Mll\Model;
use Mll\Cache;
use Mll\Db\Mongo;
use Mll\Common\Common;

class LogCountHourModel extends Model
{
    /**
     * 按条件统计数量
     *
     * @param string $field
     * @param array $where
     * @param string $group
     * @return array|mixed|object
     */
    public function sumField($field, array $where, $group = null)
    {
        $statusArr = [
            'aggregate' => 'log_count_hour',
            'pipeline' => [
                ['$match' => $where],
                [
                    '$group' => [
                        '_id' => [
                            'project' => $group,
                        ],
                        'count' => ['$sum' => $field],
                    ]
                ],
            ]
        ];

        $mongo = new Mongo();
        $rs = Common::objectToArray($mongo->executeCommand($statusArr));
        return isset($rs[0]['result'][0]) ? $rs[0]['result'][0] : null;
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
            'aggregate' => 'log_count_hour',
            'pipeline' => [
                ['$match' => $where],
                [
                    '$group' => [
                        '_id' => [
                            'date' => '$date',
                        ],
                        'count' => ['$sum' => '$count'],
                        'execTimeSum' => ['$sum' => '$execTime'],
                        'time_200' => ['$sum' => '$execTime_200'],
                        'time_500' => ['$sum' => '$execTime_500'],
                        'time_1000' => ['$sum' => '$execTime_1000'],
                        'time_5000' => ['$sum' => '$execTime_5000'],
                        'time_5000+' => ['$sum' => '$execTime_5000+'],
                        'code_0' => ['$sum' => '$httpCode_0'],
                        'code_200' => ['$sum' => '$httpCode_200'],
                        'code_300' => ['$sum' => '$httpCode_300'],
                        'code_400' => ['$sum' => '$httpCode_400'],
                        'code_500' => ['$sum' => '$httpCode_500'],
                    ]
                ],
            ]
        ];
        Cache::cut('file');
        $cache_key = 'log_status_rs_' . $where['project'] . '_' . $curr_time . '_' . $where['type'];
        $status_rs = Cache::get($cache_key);
        if ($status_rs === false) {
            $mongo = new Mongo();
            $status_rs = $mongo->executeCommand($statusArr);
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
     * @return array|mixed|object
     */
    public function countByHour(array $where, $expire = 1800, $curr_time)
    {
        $countArr = [
            'aggregate' => 'log_count_hour',
            'pipeline' => [
                ['$match' => $where],
                [
                    '$group' => [
                        '_id' => [
                            'date' => '$hour',
                        ],
                        'count' => ['$sum' => '$count'],
                        'error' => ['$sum' => '$level_error'],
                        'timeSum' => ['$sum' => '$execTime'],
                        'code_200' => ['$sum' => '$httpCode_200']
                    ]
                ],
                ['$sort' => ['date' => -1]],
            ]
        ];
        $cache_key = 'log_count_rs_' . $where['project'] . '_' . $curr_time . '_' . $where['type'];
        $count_rs = Cache::get($cache_key);
        if ($count_rs === false) {
            $mongo = new Mongo();
            $count_rs = $mongo->executeCommand($countArr);
            $count_rs = Common::objectToArray($count_rs);
            if (!empty($count_rs[0]['result'])) {
                foreach ($count_rs[0]['result'] as $_count) {
                    $hour = intval($_count['_id']['date']);
                    $time[$hour] = $_count['timeSum'] / $_count['count'];
                    $success[$hour] = $_count['code_200'];
                    $fail[$hour] = $_count['count'] - $success[$hour];
                    $error[$hour] = $_count['error'];
                }
            }
            for ($i = 0; $i < 24; $i++) {
                $countData['count_time'][] = str_pad($i, 2, '0', STR_PAD_LEFT) . ':00';
                $countData['time'][$i] = isset($time[$i]) ? floatval(sprintf('%.2f', ($time[$i] * 1000))) : 0;
                $countData['success'][$i] = isset($success[$i]) ? $success[$i] : 0;
                $countData['fail'][$i] = isset($fail[$i]) ? $fail[$i] : 0;
                $countData['error'][$i] = isset($error[$i]) ? $error[$i] : 0;
            }

            Cache::set($cache_key, json_encode($count_rs), $expire);
        } else {
            $count_rs = json_decode($count_rs, true);
        }
        return $count_rs;
    }

    /**
     * 按执行时间统计
     *
     * @param array $where 条件
     * @param integer $page 页码
     * @param integer $page_size 条数
     * @return array|object
     */
    function countRank(array $where, $page, $page_size)
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
                ['$sort' => ['time' => -1]],
                ['$skip' => ($page - 1) * $page_size],
                ['$limit' => $page_size]
            ],
        ];
        $mongo = new Mongo();
        $collection = $mongo->selectCollection('log');
        return $collection->executeCommand($comArr);
    }

    public function countError($where, $expire = 600, $curr_time)
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
}