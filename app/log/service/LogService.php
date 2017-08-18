<?php

namespace app\log\service;

use app\common\helpers\Common;
use Mll\Cache;
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
    public static function pullLogByCache($num = 1000)
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
     * 从文件取出日志并存储
     *
     * @param int $num 日志条数
     * @return array|bool
     */
    public static function pullLogByFile($num = 1000)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(240);
        $num = min(5000, $num);
        $path = Mll::app()->config->params('service_log_path') . '/' . date('Ym') . '/' . date('d') . '.log';
        $logs = [];
        if (file_exists($path)) {
            $logs = self::readFile($path, $num);
        }
        //分析日志并存储
        if (!empty($logs)) {
            $logArr = [];
            foreach ($logs as $log) {
                $log = json_decode($log, true);
                if (!empty($log)) {
                    $logArr[] = $log;
                }
            }
            unset($logs);
            $mongo = new Mongo();
            $mongo->setDBName('system_log')->selectCollection('log')->batchInsert($logArr);
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
            if (isset($v['content']['traceId'])) {
                $traceIds[] = $v['content']['traceId'];
            }
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

    /**
     * 获取日志文件
     *
     * @param $filename
     * @param $line
     * @return array|bool
     */
    public static function readFile($filename, $line)
    {
        if (!$fp = fopen($filename, 'r')) {
            return false;
        }
        $lines = array();
        // 获取文件读取位置
        Cache::cut('file');
        $today = date('d');
        $readLocation = Cache::get('readLocation_' . $today, 0);
        fseek($fp, $readLocation);
        while ($line > 0 && ($buffer = fgets($fp, 40960)) !== false) {
            $lines[] = $buffer;
            $line--;
        }

        // 记录日志文件读取位置
        Cache::set('readLocation_' . $today, ftell($fp), 3600 * 24);
        fclose($fp);
        return $lines;
    }
}