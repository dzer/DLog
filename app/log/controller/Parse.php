<?php

namespace app\log\controller;

use app\log\service\LogService;
use Mll\Controller;
use Mll\Db\Mongo;

class Parse extends Controller
{
    public function pull()
    {
        $num = isset($_GET['num']) ? intval($_GET['num']) : 1000;
        //获取缓存中日志数据并存储
        echo LogService::pullLogByMq($num);
    }
}