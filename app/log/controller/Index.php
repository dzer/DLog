<?php

namespace app\log\controller;

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
            'rs' => $rs,
            'main' => $mainRequest
        ]);
    }
}