<?php

namespace app\log\service;

use app\common\helpers\Common;
use Mll\Common\Amqp;
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
            $mongo->selectCollection('log')
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
                    $orig_date = new \DateTime($log['time']);
                    $log['date'] = new \MongoDB\BSON\UTCDateTime(($orig_date->getTimestamp() + 8 * 3600) * 1000);
                    $logArr[] = $log;
                }
            }
            unset($logs);
            $mongo = new Mongo();
            $mongo->selectCollection('log')->batchInsert($logArr);
        }
        return true;
    }

    /**
     * 从MQ取出日志并存储
     *
     * @param int $num 日志条数
     * @return array|bool
     */
    public static function pullLogByMq($num = 2000)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(240);
        $num = min(10000, $num);

        $mq = new Amqp(Mll::app()->config->get('mq.rabbit'));
        while ($num--) {
            $msg = $mq->getMessage('QUEUE_PHP_LOG');
            if (empty($msg)) {
                break;
            }
            $logs[] = $msg;
        }
        //分析日志并存储
        $insertNum = $updateNum = 0;
        if (!empty($logs)) {
            $logArr = self::countLogByHour($logs);
            unset($logs);

            $mongo = new Mongo();
            $insertNum = $mongo->selectCollection('log')->batchInsert($logArr['log']);
            if ($insertNum == 0) {
                Mll::app()->log->warning(
                    date('Y-m-d H:i:s') . 'mongo 插入失败' . count($logArr['log']) . '条！'
                );
            }
            if (!empty($logArr['countHour'])) {
                Mll::app()->log->debug($logArr['countHour']);
                $mongo->selectCollection('log_count_hour');
                foreach ($logArr['countHour'] as $k => $v) {
                    $where = explode('#', $k);

                    $updateNum = $mongo->update(
                        ['date' => $where[0], 'hour' => $where[1], 'project' => $where[2], 'type' => $where[3]],
                        [
                            '$inc' => $v['inc'],
                            '$set' => $v['set']
                        ],
                        ['upsert' => true]
                    );
                }
            }
        }
        return 'inc:' . $insertNum . ', update:' . $updateNum;
    }

    public static function countLogByHour($logs)
    {
        $logArr = $countHour = [];
        foreach ($logs as $log) {
            $log = json_decode($log, true);
            if (!empty($log)) {
                $orig_date = new \DateTime($log['time']);
                if (!isset($log['content']['url'])) {
                    $log['content']['url'] = '?';
                }
                $log['content']['url'] = strlen($log['content']['url']) > 1000 ?
                    substr($log['content']['url'], 0, 1000) : $log['content']['url'];
                $log['date'] = new \MongoDB\BSON\UTCDateTime(($orig_date->getTimestamp() + 8 * 3600) * 1000);

                $date = date('Y-m-d', intval($log['microtime']));
                $hour = date('H', intval($log['microtime']));
                $key = "{$date}#{$hour}#{$log['project']}#{$log['type']}";
                $countHour[$key]['set'] = [
                    'date' => $date,
                    'hour' => $hour,
                    'project' => $log['project'],
                    'type' => $log['type']
                ];
                $countHour[$key]['inc']['count'] += 1;
                $countHour[$key]['inc']['execTime'] += $log['content']['execTime'];
                $countHour[$key]['inc']['level_' . $log['level']] += 1;
                if (is_numeric($log['content']['responseCode']) && $log['content']['responseCode'] == 0) {
                    $countHour[$key]['inc']['httpCode_0'] += 1;
                } elseif ($log['content']['responseCode'] >= 200 && $log['content']['responseCode'] < 300) {
                    $countHour[$key]['inc']['httpCode_200'] += 1;
                } elseif ($log['content']['responseCode'] >= 300 && $log['content']['responseCode'] < 400) {
                    $countHour[$key]['inc']['httpCode_300'] += 1;
                } elseif ($log['content']['responseCode'] >= 400 && $log['content']['responseCode'] < 500) {
                    $countHour[$key]['inc']['httpCode_400'] += 1;
                } elseif ($log['content']['responseCode'] >= 500 && $log['content']['responseCode'] < 600) {
                    $countHour[$key]['inc']['httpCode_500'] += 1;
                }

                if ($log['content']['execTime'] <= 0.2) {
                    $countHour[$key]['inc']['execTime_200'] += 1;
                } elseif ($log['content']['execTime'] > 0.2 && $log['content']['execTime'] <= 0.5) {
                    $countHour[$key]['inc']['execTime_500'] += 1;
                } elseif ($log['content']['execTime'] > 0.5 && $log['content']['execTime'] <= 1) {
                    $countHour[$key]['inc']['execTime_1000'] += 1;
                } elseif ($log['content']['execTime'] > 1 && $log['content']['execTime'] <= 5) {
                    $countHour[$key]['inc']['execTime_5000'] += 1;
                } else {
                    $countHour[$key]['inc']['execTime_5000+'] += 1;
                }
                $logArr[] = $log;
            }
        }
        return ['log' => $logArr, 'countHour' => $countHour];
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