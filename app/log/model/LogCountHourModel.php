<?php

namespace app\log\model;

use Mll\Mll;
use Mll\Model;
use Mll\Cache;
use Mll\Db\Mongo;
use Mll\Common\Common;

class LogCountHourModel extends Model
{
    /**
     * 按条件统计数量
     *
     * @param string $db
     * @param string $field
     * @param array $where
     * @param string $group
     * @return int
     */
    public function sumField($db, $field, array $where, $group = null)
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
        $mongoConfig = Mll::app()->config->get('db.mongo');
        $mongoConfig['database'] = $db;
        $mongo = new Mongo($mongoConfig);
        $rs = Common::objectToArray($mongo->executeCommand($statusArr));
        return isset($rs[0]['result'][0]['count']) ? $rs[0]['result'][0]['count'] : 0;
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
        $project = isset($where['project']) ? $where['project'] : '';
        $type = isset($where['type']) ? $where['type'] : '';
        $cache_key = 'log2_status_rs_' . $project . '_' . $curr_time . '_' . $type;
        $status_rs = Cache::get($cache_key);
        if ($status_rs === false) {
            $mongoConfig = Mll::app()->config->get('db.mongo');
            $mongoConfig['database'] = 'system_log';
            $mongo = new Mongo($mongoConfig);
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
     * @param string $curr_time
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
                        'code_200' => ['$sum' => '$httpCode_200'],
                        'code_300' => ['$sum' => '$httpCode_300']
                    ]
                ],
                ['$sort' => ['date' => -1]],
            ]
        ];
        $project = isset($where['project']) ? $where['project'] : '';
        $type = isset($where['type']) ? $where['type'] : '';
        $cache_key = 'log2_count_rs_' . $project . '_' . $curr_time . '_' . $type;
        $countData = Cache::get($cache_key);
        if ($countData === false) {
            $mongoConfig = Mll::app()->config->get('db.mongo');
            $mongoConfig['database'] = 'system_log';
            $mongo = new Mongo($mongoConfig);
            $count_rs = $mongo->executeCommand($countArr);
            $count_rs = Common::objectToArray($count_rs);
            if (!empty($count_rs[0]['result'])) {
                foreach ($count_rs[0]['result'] as $_count) {
                    $hour = intval($_count['_id']['date']);
                    $time[$hour] = $_count['timeSum'] / $_count['count'];
                    $success[$hour] = $_count['code_200'] + $_count['code_300'];
                    $fail[$hour] = $_count['count'] - $success[$hour];
                    $error[$hour] = $_count['error'];
                }
            }
            $countData = [];
            for ($i = 0; $i < 24; $i++) {
                $countData['count_time'][$i] = str_pad($i, 2, '0', STR_PAD_LEFT) . ':00';
                $countData['time'][$i] = isset($time[$i]) ? floatval(sprintf('%.2f', ($time[$i] * 1000))) : 0;
                $countData['success'][$i] = isset($success[$i]) ? $success[$i] : 0;
                $countData['fail'][$i] = isset($fail[$i]) ? $fail[$i] : 0;
                $countData['error'][$i] = isset($error[$i]) ? $error[$i] : 0;
            }

            Cache::set($cache_key, json_encode($countData), $expire);
        } else {
            $countData = json_decode($countData, true);
        }
        return $countData;
    }

    public function countError($where, $expire = 600, $curr_time)
    {
        if (isset($where['type'])) {
            unset($where['type']);
        }
        $countArr = [
            'aggregate' => 'log_count_hour',
            'pipeline' => [
                [
                    '$project' => [
                        'project' => 1,
                        'type' => 1,
                        'level_error' => 1,
                        'level_warning' => 1,
                        'level_notice' => 1,
                        'level_info' => 1,
                        'date' => 1,
                        'count' => 1
                    ]
                ],
                ['$match' => $where],
                [
                    '$group' => [
                        '_id' => [
                            'type' => '$type',
                        ],
                        'count' => ['$sum' => '$count'],
                        'error' => ['$sum' => '$level_error'],
                        'warning' => ['$sum' => '$level_warning'],
                        'notice' => ['$sum' => '$level_notice'],
                        'info' => ['$sum' => '$level_info'],
                    ]
                ],
                ['$sort' => ['count' => -1]],
            ]
        ];
        $project = isset($where['project']) ? $where['project'] : '';
        $cache_key = 'log2_count_error_rs_' . $project . '_' . $curr_time;
        $count_rs = Cache::get($cache_key);
        if ($count_rs === false) {
            $mongoConfig = Mll::app()->config->get('db.mongo');
            $mongoConfig['database'] = 'system_log';
            $mongo = new Mongo($mongoConfig);
            $count_rs = $mongo->executeCommand($countArr);
            $count_rs = Common::objectToArray($count_rs);
            Cache::set($cache_key, json_encode($count_rs), $expire);
        } else {
            $count_rs = json_decode($count_rs, true);
        }

        return $count_rs;
    }

    public function countByType($where, $expire = 600, $curr_time)
    {
        if (isset($where['type'])) {
            unset($where['type']);
        }
        $countArr = [
            'aggregate' => 'log_count_hour',
            'pipeline' => [
                ['$match' => $where],
                [
                    '$group' => [
                        '_id' => [
                            'type' => '$type',
                        ],
                        'count' => ['$sum' => '$count'],
                        'execTime' => ['$sum' => '$execTime'],
                        'info' => ['$sum' => '$level_info'],
                        'warning' => ['$sum' => '$level_warning'],
                        'error' => ['$sum' => '$level_error'],
                        'notice' => ['$sum' => '$level_notice'],
                        'httpCode_0' => ['$sum' => '$httpCode_0'],
                        'httpCode_200' => ['$sum' => '$httpCode_200'],
                        'httpCode_300' => ['$sum' => '$httpCode_300'],
                        'httpCode_400' => ['$sum' => '$httpCode_400'],
                        'httpCode_500' => ['$sum' => '$httpCode_500'],
                        'execTime_200' => ['$sum' => '$execTime_200'],
                        'execTime_500' => ['$sum' => '$execTime_500'],
                        'execTime_1000' => ['$sum' => '$execTime_1000'],
                        'execTime_5000' => ['$sum' => '$execTime_5000'],
                        'execTime_5000+' => ['$sum' => '$execTime_5000+'],
                    ]
                ],
                ['$sort' => ['count' => -1]],
            ]
        ];
        $project = isset($where['project']) ? $where['project'] : '';
        $cache_key = 'log2_count_type_rs_' . $project . '_' . $curr_time;
        $count_rs = Cache::get($cache_key);
        if (1||$count_rs === false) {
            $mongoConfig = Mll::app()->config->get('db.mongo');
            $mongoConfig['database'] = 'system_log';
            $mongo = new Mongo($mongoConfig);
            $count_rs = $mongo->executeCommand($countArr);
            $count_rs = Common::objectToArray($count_rs);
            Cache::set($cache_key, json_encode($count_rs), $expire);
        } else {
            $count_rs = json_decode($count_rs, true);
        }

        return $count_rs;
    }

}