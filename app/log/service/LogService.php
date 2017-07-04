<?php

namespace app\log\service;

use app\common\helpers\Common;
use Mll\Common\MemcacheQueue;
use Mll\Db\Mongo;
use Mll\Mll;

class LogService
{

    /**
     * 从队列取出日志并存储
     *
     * @param int $num 日志条数
     * @return array|bool
     */
    public static function pullLog($num = 1000)
    {
        $config = Mll::app()->config->get('log.cache');

        $queue = new MemcacheQueue($config['cache_server'], $config['queue_name'], $config['expire']);
        $logs = $queue->get($num);
        //分析日志并存储
        if (!empty($logs)) {
            $logArr = [];
            foreach ($logs as $log) {
                if (!empty($log)) {
                    $logArr = array_merge($logArr, json_decode($log, true));
                }
            }
            $mongo = new Mongo();

            $mongo->setDBName('system_log')
                ->selectCollection('log')
                ->batchInsert($logArr);
        }
        return true;
    }

    /**
     * 跟踪日志按版本排序
     *
     * @param array $traceLog 跟踪日志
     * @return array|bool
     */
    public static function traceLogVersionSort($traceLog)
    {
        if (empty($traceLog)) {
            return false;
        }
        $traceIds = [];
        foreach ($traceLog as $v) {
            $traceIds[] = $v['content']['traceId'];
        }
        $version_sort = array_flip(Common::version_sort($traceIds));
        foreach ($traceLog as &$v) {
            if (isset($version_sort[$v['content']['traceId']])) {
                $order[] = $version_sort[$v['content']['traceId']];
            }
        }
        array_multisort($order, SORT_ASC, $traceLog);

        return $traceLog;
    }
}