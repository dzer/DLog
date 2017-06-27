<?php

namespace app\order\controller;

use Mll\Controller;
use Mll\Mll;

class Index extends Controller
{
    public function index()
    {
        $rs = Mll::app()->rpc->get('goods/Index/index2', array(array('ss'=>'d')));
        return $this->json(array('get' => $_GET));
    }

    public function index2()
    {
        //sleep(1);
        return $this->json(array('post' => $_POST));
    }
}