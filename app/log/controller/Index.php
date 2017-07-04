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

    /**
     * 最近访问
     */
    public function just()
    {
        //获取缓存中日志数据并存储
       LogService::pullLog(1000);

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
        $rs = $collection->find(['requestId' => $requestId]);

        if (empty($rs)) {
            throw new \Exception('跟踪日志不存在');
        }
        //traceId排序
        $rs = LogService::traceLogVersionSort($rs);
        $mainRequest = reset($rs);

        return $this->render('trace', [
            'info' => json_encode($rs),
            'rs' => $rs,
            'main' => $mainRequest
        ]);
    }
}