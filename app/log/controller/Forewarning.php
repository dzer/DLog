<?php

namespace app\log\controller;

use app\log\service\ForewarningService;
use app\log\service\LogService;
use Mll\Controller;

/**
 * 预警
 *
 * @package app\log\controller
 * @author Xu Dong <d20053140@gmail.com>
 * @since 1.0
 */
class Forewarning extends Controller
{
    /**
     * 设置
     *
     */
    public function setting()
    {

    }

    /**
     * 预警统计并将预警消息存入mongo
     *
     */
    public function count()
    {
        //日志报警
        $rs = ForewarningService::start();
        echo json_encode($rs);

        //发送消息
        ForewarningService::sendMsg();

        //创建当天DB索引和删除几天前的日志DB
        LogService::checkCurrDb();
    }
}