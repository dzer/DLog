<?php

namespace app\log\controller;

use app\log\service\LogService;
use Mll\Controller;
use Mll\Db\Mongo;
use Mll\Mll;

class Index extends Controller
{
    public function index()
    {
        return $this->render('index', ['data' => 'hehehhehe']);
    }

    public function beforeAction()
    {

        return true;
    }

    /**
     * 最近访问
     */
    public function just()
    {
        //获取缓存中日志数据
        $logs = LogService::pullLog(1000);
        //分析日志并存储
        if (!empty($logs)) {
            $logArr = [];
            foreach ($logs as $log) {
                $logArr = array_merge($logArr, json_decode($log, true));
            }
            $mongo = new Mongo();

            $mongo->setDBName('system_log')
                ->selectCollection('log')
                ->batchInsert($logArr);
        }

        $mongo = new Mongo();
        $collection = $mongo->setDBName('system_log')->selectCollection('log');
        $rs = $collection->find(['type' => LOG_TYPE_FINISH], ['time' => -1], 0, 20);

        return $this->render('just', [
           'rs' => $rs
        ]);
    }

    /**
     * 日志跟踪
     */
    public function trace()
    {
        $requestId = Mll::app()->request->get('request_id');

        $mongo = new Mongo();
        $collection = $mongo->setDBName('system_log')->selectCollection('log');
        $rs = $collection->find(['requestId' => $requestId], ['content.traceId' => 1]);

        $mainRequest = reset($rs);
        return $this->render('trace', [
            'info' => json_encode($rs),
            'rs' => $rs,
            'main' => $mainRequest
        ]);
    }
}