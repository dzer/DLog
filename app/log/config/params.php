<?php
return [
    'params' => [
        'service_log_path' => '/usr/local/nginx/html/mllphp/runtime/log',
        'wechat_url' => 'http://ywweixin.meilele.com/send_weixin2/',
        'forewarning' => [
            [
                'action' => 'ForewarningCountService::checkMongoConnect',
                'enable' => false,
                'where' => '',
                'sendType' => [
                    'wechat' => [
                        '18581855415',
                        '18681232162'
                    ],
                ]
            ],
            [
                'action' => 'ForewarningCountService::checkLogRecord',
                'enable' => false,
                'time' => 5,
                'where' => '',
                'sendType' => [
                    'wechat' => [
                        '18581855415',
                        '18681232162'
                    ],
                ]
            ],
            [
                'action' => 'ForewarningCountService::checkUrl',
                'enable' => true,
                'time' => 2,
                'where' => ['level' => 'info', 'url' => 'meilele.com'],
                'count' => [
                    'number' => 1, //次数
                ],
                'msg' => '易宝查询接口出现错误',
                'sendType' => [
                    'wechat' => [
                        '18581855415',
                    ],
                ]
            ],
            [
                'action' => 'ForewarningCountService::checkLogCount',
                'enable' => false,
                'time' => 5,
                'where' => [
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
                ],
            ]
        ]
    ]
];