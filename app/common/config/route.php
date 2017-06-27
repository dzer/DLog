<?php
return array(
    'route' => array(
        'static' => array(
            'sd/rr' => array(
                'goods\\Index\\index',
                array("callurl" => 'http://zphp.com'),    //默认参数，可选项
            ),
        ),
        'dynamic' => array(
            '/^product\/(\d+)$/iU' => array(                                  //匹配 /product/123 将被匹配
                'goods\\Index\\index',           //ctrl class
                array('id'),                //匹配参数                          //名为id的参数将被赋值 123
                'product/{id}'             //格式化
            ),
        )
    )
);
