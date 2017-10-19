<?php

namespace app\log\controller;

use app\log\service\LogService;
use Mll\Controller;
use Mll\Db\Mongo;
use Mll\Mll;

/**
 * 日志收集
 *
 * @package app\log\controller
 * @author Xu Dong <d20053140@gmail.com>
 * @since 1.0
 */
class Parse extends Controller
{
    public function pull()
    {
        $num = isset($_GET['num']) ? intval($_GET['num']) : 60000;
        //获取缓存中日志数据并存储
        for ($i = intval($num / 20000); $i > 0; $i--) {
            echo LogService::pullLogByMq(20000) . '<br>';
        }
    }
}