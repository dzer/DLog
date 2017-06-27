<?php

namespace app\log\service;

use Mll\Common\MemcacheQueue;
use Mll\Mll;

class LogService
{

    /**
     * 从队列取出日志
     *
     * @param int $num 日志条数
     * @return array|bool
     */
    public static function pullLog($num = 500)
    {
        $config = Mll::app()->config->get('log.cache');

        $queue = new MemcacheQueue($config['cache_server'], $config['queue_name'], $config['expire']);
        return $queue->get($num);
    }
}