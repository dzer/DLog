<?php

namespace app\log\controller;

use app\log\service\LogService;
use Mll\Cache;
use Mll\Common\Common;
use Mll\Controller;
use Mll\Db\Mongo;
use Mll\Mll;

class Index extends Controller
{
    public function __construct()
    {
        if ((isset($_GET['admin']) && $_GET['admin'] == '2253dsag23&^') || (isset($_SESSION['admin']) && $_SESSION['admin'] == 1) ) {
            $_SESSION['admin'] = 1;
        } else {
            exit('没有权限');
        }
    }

    public function index()
    {
        $curr_time = Mll::app()->request->get('curr_time', date('Y-m-d'));
        $log_type = Mll::app()->request->get('log_type');
        $_GET['curr_time'] = $curr_time;
        $where = [];
        $mongo = new Mongo();
        $collection = $mongo->selectCollection('log');
        $where['type'] = LOG_TYPE_FINISH;
        $_GET['log_type'] = $where['type'];
        if (!empty($log_type)) {
            $where['type'] = $log_type;
        }
        Cache::cut('file');
        $count = Cache::get('log_count');
        if (!$count) {
            $count = $collection->count();
            Cache::set('log_count', $count, 1800);
        }

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
        $cache_key = 'log_status_rs_' . md5(serialize($where));
        $status_rs = Cache::get($cache_key);
        if ($status_rs === false) {
            $status_rs = $collection->executeCommand($statusArr);
            $status_rs = Common::objectToArray($status_rs);
            Cache::set($cache_key, json_encode($status_rs), 1800);
        } else {
            $status_rs = json_decode($status_rs, true);
        }
        unset($statusArr);

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


        $cache_key = 'log_count_rs_' . md5(serialize($where));
        $count_rs = Cache::get($cache_key);
        if ($count_rs === false) {
            $mongo = new Mongo();
            $collection = $mongo->selectCollection('log');
            $count_rs = $collection->executeCommand($countArr);
            $count_rs = Common::objectToArray($count_rs);
            Cache::set($cache_key, json_encode($count_rs), 1800);
        } else {
            $count_rs = json_decode($count_rs, true);
        }
        unset($countArr);

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
        $start_time = Mll::app()->request->get('start_time', date('Y-m-d 00:00:00'));
        $end_time = Mll::app()->request->get('end_time', date('Y-m-d') . ' 23:59:59');
        $request_url = Mll::app()->request->get('request_url');
        $log_level = Mll::app()->request->get('log_level');
        $log_type = Mll::app()->request->get('log_type');
        $responseCode = Mll::app()->request->get('responseCode');
        $request_id = Mll::app()->request->get('request_id');
        $execTime = Mll::app()->request->get('execTime');
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
        if (!empty($execTime)) {
            switch ($execTime) {
                case '200':
                    $where['content.execTime']['$lte'] = 0.2;
                    break;
                case '500':
                    $where['content.execTime']['$gt'] = 0.2;
                    $where['content.execTime']['$lte'] = 0.5;
                    break;
                case '1000':
                    $where['content.execTime']['$gt'] = 0.5;
                    $where['content.execTime']['$lte'] = 1;
                    break;
                case '1000+':
                    $where['content.execTime']['$gt'] = 1;
                    break;
            }
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
            $where['type']['$in'] = [LOG_TYPE_FINISH, LOG_TYPE_CURL, LOG_TYPE_RPC];
        }
        if (!empty($request_id)) {
            $where['requestId'] = $request_id;
        }
        if (is_numeric($responseCode)) {
            if ($responseCode > 0) {
                $where['content.responseCode']['$gte'] = intval($responseCode);
                $where['content.responseCode']['$lte'] = intval($responseCode) + 20;
            } else {
                $where['content.responseCode']['$eq'] = $responseCode;
            }
        }

        $mongo = new Mongo();
        $collection = $mongo->selectCollection('log');
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
                'count' => $count
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
        $collection = $mongo->selectCollection('log');
        $rs = $collection->find(['requestId' => $requestId]);
        $rs = Common::objectToArray($rs);

        if (empty($rs)) {
            throw new \Exception('跟踪日志不存在');
        }
        $xhprof_dir = ROOT_PATH . '/runtime/xhprof' . DS . date('Ymd');
        if (!is_dir($xhprof_dir)) {
            @mkdir($xhprof_dir, 0777, true);
        }
        //traceId排序
        $rs = LogService::traceLogVersionSort($rs);
        $mainRequest = reset($rs);
        return $this->render('trace', [
            'info' => json_encode($rs),
            'rs' => $rs,
            'xhprof_dir' => $xhprof_dir,
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
            $where['type']['$in'] = [LOG_TYPE_FINISH, LOG_TYPE_CURL, LOG_TYPE_RPC];
        }

        $mongo = new Mongo();
        $collection = $mongo->selectCollection('log');
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
                ['$sort' => ['time' => -1]],
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