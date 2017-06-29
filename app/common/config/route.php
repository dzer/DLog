<?php
return array(
    'route' => array(
        'static' => array(
            'order' => array(
                'order\\Index\\index',
                array("test" => '123'),    //默认参数，可选项
            ),
        ),
        'dynamic' => array(
            '/^goods\/(\d+)$/iU' => array(                                  //匹配 /product/123 将被匹配
                'order\\Index\\index2',           //ctrl class
                array('id'),                //匹配参数                          //名为id的参数将被赋值 123
                'goods/{id}'             //格式化
            ),
        )
    )
);
