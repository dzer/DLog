<?php
/**
 * 项目配置
 */
return [
    //是否开启调试模式
    'app_debug' => false,
    //时区
    'time_zone' => 'Asia/Shanghai',
    'view_mode' => 'php',

    'params' => [
        'rule_url' => 'sssss',
    ],

    'request' => [
        'driver' => 'http',
        'http' => [
            // 默认模块名
            'default_module' => 'goods',
            // 禁止访问模块
            'deny_module_list' => ['common'],
            // 默认控制器名
            'default_controller' => 'Index',
            // 默认操作名
            'default_action' => 'index',
            //兼容pathInfo
            'path_info_var' => 'r',
            // 表单请求类型伪装变量
            'var_method' => '_method',
            //缓存服务器
            'cache_server' => 'code',
            //请求唯一key
            'request_id_key' => 'x-request-id',
            //请求时间key
            'request_time_key' => 'x-request-time',
        ],
        'rpc' => [
            //缓存服务器
            'cache_server' => 'code',
            //请求唯一key
            'request_id_key' => 'x-request-id',
            //请求时间key
            'request_time_key' => 'x-request-time',
        ]
    ],

    'exception' => [
        'template' => null,//错误提示模板
        'exception_handle' => '\Mll\Exception\Handle',//错误处理
        'show_error_msg' => true, //是否只显示错误信息
        'error_message' => '系统繁忙,请稍后再试', //错误提示
    ],
    'log' => [
        'driver' => 'cache',
        'file' => [
            'time_format' => 'Y-m-d H:i:s', //ISO 8601 格式的日期
            'file_size' => 2097152, //文件大小
            'path' => '/runtime/log', //报错日志路径
            'level' => 'all', //默认所有，或者逗号隔开warning,error
            'separator' => ' | ', //分隔符
            'suffix' => '.log', //日志文件后缀
        ],
        'cache' => [
            'time_format' => 'Y-m-d H:i:s', //格式的日期
            'cache_server' => 'code',   //缓存服务器
            'queue_name' => 'mll_log_queue', //缓存队列名
            'expire' => 600,    //过期时间
            'level' => 'all', //默认所有，或者逗号隔开warning,error
        ]
    ],
    'xhprof' => [
        'enable' => true,
        'path' => '/extend/xhprof',
    ],
    'rpc' => [
        'driver' => 'yar',
        'yar' => [
            'host' => 'http://mllphp.com/rpc.php',
            'connect_timeout' => 1,    //连接超时
            'timeout' => 2,    //响应超时
        ],
    ],
    'cache' => [
        'default' =>[
            'driver' => 'memcache',
            'default' => [
                'host' => 'memcache.meilele.com ',
                'port' => 11211,
                'prefix' => '',
                'persistent' => true,
                'expire'     => 0,
                'timeout'    => 0, // 超时时间（单位：毫秒）
            ]
        ],
        'code' => [
            'driver' => 'memcache',
            'host' => '192.168.31.169',
            'port' => 11211
        ],
        'file' => [
            'driver' => 'file',
            'file' => [
                'expire'        => 0,
                'cache_subdir'  => true,
                'prefix'        => '',
                'path'          => '/runtime/temp/cache',
                'data_compress' => false,
            ]
        ],
    ],
    'session' => [
        'driver' => 'memcache',
        'auto_start' => 1,
        'prefix' => '',
        'var_session_id' => '',
        //'name' => '',
        'path' => '',
        'domain' => '',
        'expire' => '',
        'use_cookies' => '',
        'cache_limiter' => '',
        'cache_expire' => '',
        'driver_config' => [
            'host'         => 'memcache.meilele.com', // memcache主机
            'port'         => 11211, // memcache端口
            'expire'       => 3600, // session有效期
            'timeout'      => 0, // 连接超时时间（单位：毫秒）
            'persistent'   => true, // 长连接
            'session_name' => '', // memcache key前缀
        ]
    ]

];