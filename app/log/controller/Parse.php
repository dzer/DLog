<?php

namespace app\log\controller;

use app\log\service\LogService;
use Mll\Controller;
use Mll\Db\Mongo;

class Parse extends Controller
{
    public function pull()
    {
        //获取缓存中日志数据
        $logs = LogService::pullLog(10);
        //分析日志并存储
        if (!empty($logs)) {
            $logArr = [];
            foreach ($logs as $log) {
                $logArr = array_merge($logArr, json_decode($log, true));
            }
            $mongo = new Mongo();

            $rs = $mongo->setDBName('system_log')
                ->selectCollection('log')
                ->batchInsert($logArr);
        }

        var_dump($rs);
        //return $this->render('index', ['data' => 'hehehhehe']);
    }

    public function show()
    {

            $mongo = new Mongo();

            $rs = $mongo->setDBName('system_log')
                ->selectCollection('log')
                ->find();

        var_dump($rs);
        //return $this->render('index', ['data' => 'hehehhehe']);
    }
}