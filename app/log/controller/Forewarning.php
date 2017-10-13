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
        $service = new ForewarningService();
        $service->count();
    }
}