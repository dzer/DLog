<?php

namespace app\goods\controller;

use Mll\Cache;
use Mll\Controller;
use Mll\Curl\Curl;
use Mll\Mll;

class Index extends Controller
{
    public function index()
    {
        echo 'ss';
        $this->ss;
        Mll::app()->log->error('测试错误信息');
        Mll::app()->log->error('测试错误信息');
        echo 's';
        die;
        $rs = Mll::app()->rpc->get('order/Index/index', array(array('ss'=>'d')));
        //$rs2 = Mll::app()->rpc->post('order/Index/index2', array('r' => array('aad'=>'d22')));

       $curl = new Curl();
       $rs = $curl->get('http://mllphp.com/order/Index/index', array('ss'=>'d'), 1);

        /*$rs= $curl->post('http://mllphp.com/order/Index/index2?s2=1', array('r' => array('aad'=>'d22')));
        $rs = json_decode($rs);*/
        //$rs2 = json_decode($rs2);
        Cache::cut('code');
        Cache::set('test_1111', 'feawfe', 30);
        $cache = Cache::get('test_1111');


        return $this->json(array('error' => 0, 'message' => '成功', 'rs' => 2, 'rs2' => 2, 'cache' => $cache));
    }

    public function index2()
    {
        $this->ss;
        return $this->json(array('error' => 0, 'message' => '成功', 'rs' => 2, 'rs2' => 2));
    }
}