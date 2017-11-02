<?php
use app\common\helpers\Common;

include(__DIR__ . '/../common/header.php')
?>
<?php include(__DIR__ . '/../common/nav.php') ?>


<link href="http://www.bootcss.com/p/bootstrap-datetimepicker/bootstrap-datetimepicker/css/datetimepicker.css" rel="stylesheet">
<div class="container-fluid theme-showcase" role="main">
    <div class="row">
        <div class="col-md-12">
            <form class="form-inline" action="<?= $base_url ?>">
                <div class="form-group" style="margin-left: 10px">
                    <label>项目：</label>
                    <select name="project" class="form-control">
                        <?= Common::optionHtml($projects, 'project');?>
                    </select>
                </div>

                <div class="form-group" style="margin-left: 10px">
                    年:
                    <input class="time-input form-control" id="time-input-year" size="4" type="text" name="time-year" value="<?= $_g['time-year']?>" >
                    <span class="add-on"><i class="icon-th"></i></span>
                    月:
                    <input class="time-input form-control" id="time-input-month" size="2" type="text" name="time-month" value="<?= $_g['time-month']?>" >
                    <span class="add-on"><i class="icon-th"></i></span>
                    日:
                    <input class="time-input form-control" id="time-input-day" size="2" type="text" name="time-day" value="<?= $_g['time-day']?>" >
                    <span class="add-on"><i class="icon-th"></i></span>

                    <a href="#" id="tip" class="btn btn-large btn-success" title="TIPS" data-placement="bottom"  data-toggle="popover" title="" data-content="选择年:横轴为月;选择年月：横轴为天;选择年月日:横轴为小时" data-original-title="A Title">时间搜索提示</a>

                </div>
               <!-- <div class="form-group" style="margin-left: 10px">
                    <label>时间：</label>
                    <input type="text" name="curr_time" class="form-control" placeholder="时间"
                           onclick="laydate({ istime: true, format: 'YYYY-MM-DD'})" value="<?/*= isset($_GET['curr_time']) ? $_GET['curr_time'] : ''*/?>">
                </div>-->
                <div class="form-group" style="margin-left: 10px">
                    <label>日志类型：</label>
                    <select name="log_type" class="form-control">
                        <option value="">请选择</option>
                        <?= Common::optionHtml($types, 'log_type');?>
                    </select>
                </div>


                <button id="search" type="submit" class="btn btn-success" style="margin-left: 10px">搜索</button>
                <a type="submit" href="<?= $base_url ?>" class="btn btn-danger" style="margin-left: 10px">重置</a>
            </form>
        </div>
        <br />
        <br />
        <br />
        <div class="col-md-12">




            <div class="row">
                <div id="exec-time" style="width: 100%;height:200px;"></div>
                <div id="error" style="width: 100%;height:200px;"></div>
                <div id="warning" style="width: 100%;height:200px;"></div>
                <div id="notice" style="width: 100%;height:200px;"></div>
                <div id="httpCode_500" style="width: 100%;height:200px;"></div>
                <div id="httpCode_400" style="width: 100%;height:200px;"></div>
                <div id="execTime_5000" style="width: 100%;height:200px;"></div>
                <div id="execTime_1000" style="width: 100%;height:200px;"></div>
                <div id="execTime_500" style="width: 100%;height:200px;"></div>
            </div>

        </div>
        <div class="col-md-12">
            <div id="container" style="min-width:350px;height:350px;"></div>
        </div>
    </div>
</div> <!-- /container -->

<script src="http://www.bootcss.com/p/bootstrap-datetimepicker/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<link rel="stylesheet" href="http://cdn.static.runoob.com/libs/bootstrap/3.3.7/css/bootstrap.min.css">
<script src="http://cdn.static.runoob.com/libs/bootstrap/3.3.7/js/bootstrap.min.js"></script>

<script type="text/javascript">
    $('#tip').popover({
        trigger : 'hover'
    });

    $("#time-input-year").datetimepicker({
        todayBtn:  1,
        autoclose: 1,
        language: 'zh-CN',
        format: "yyyy",
        startView: 4, //这里就设置了默认视图为年视图
        minView: 4 //设置最小视图为年视图
    });

    $("#time-input-month").datetimepicker({
        todayBtn:  1,
        autoclose: 1,
        language: 'zh-CN',
        format: "mm",
        startView: 3, //这里就设置了默认视图为年视图
        minView: 3 //设置最小视图为年视图
    });
    $("#time-input-day").datetimepicker({
        todayBtn:  1,
        autoclose: 1,
        todayHighlight: 1,
        language:  'zh-CN',
        format: "dd",
        startView: 2, //这里就设置了默认视图为年视图
        minView: 2 //设置最小视图为年视图
    });

</script>

<script src="http://echarts.baidu.com/build/dist/echarts.js"></script>

<script>
    // 路径配置
    require.config({
        paths: {
            echarts: 'http://echarts.baidu.com/build/dist'
        }
    });

    require(
        [
            'echarts',
            'echarts/chart/line',   // 按需加载所需图表，如需动态类型切换功能，别忘了同时加载相应图表
            'echarts/chart/bar'
        ],
        function (ec) {
            var myChart = ec.init(document.getElementById('exec-time'));
            var   option = {
                title: {
                    text: '平均执行时间',
                    textStyle : {
                        fontSize: 16,
                        fontWeight: 'bolder',
                        color: '#5cb85c'
                    }
                },
                tooltip : {
                    trigger: 'axis'
                },
                legend: {
                    data:<?= json_encode($lenData['execTime']); ?>
                },
                toolbox: {
                    show : true,
                    feature : {
                        mark : {show: true},
                        dataView : {show: true, readOnly: false},
                        magicType : {show: true, type: ['line', 'bar', 'stack', 'tiled']},
                        restore : {show: true},
                        saveAsImage : {show: true}
                    }
                },
                calculable : true,
                xAxis : [
                    {
                        type : 'category',
                        boundaryGap : false,
                        data : <?= json_encode($x); ?>
                    }
                ],
                yAxis : [
                    {
                        type : 'value'
                    }
                ],
                series : <?= json_encode($data['execTime']); ?>
            };
            myChart.setOption(option);
        }
    );

    require(
        [
            'echarts',
            'echarts/chart/line',   // 按需加载所需图表，如需动态类型切换功能，别忘了同时加载相应图表
            'echarts/chart/bar'
        ],
        function (ec) {
            var myChart = ec.init(document.getElementById('error'));
            var   option = {
                title: {
                    text: 'ERROR',
                    textStyle : {
                        fontSize: 16,
                        fontWeight: 'bolder',
                        color: '#DC143C'
                    }
                },
                tooltip : {
                    trigger: 'axis'
                },
                legend: {
                    data:<?= json_encode($lenData['error']); ?>
                },
                toolbox: {
                    show : true,
                    feature : {
                        mark : {show: true},
                        dataView : {show: true, readOnly: false},
                        magicType : {show: true, type: ['line', 'bar', 'stack', 'tiled']},
                        restore : {show: true},
                        saveAsImage : {show: true}
                    }
                },
                calculable : true,
                xAxis : [
                    {
                        type : 'category',
                        boundaryGap : false,
                        data : <?= json_encode($x); ?>
                    }
                ],
                yAxis : [
                    {
                        type : 'value'
                    }
                ],
                series : <?= json_encode($data['error']); ?>
            };
            myChart.setOption(option);
        }
    );
    require(
        [
            'echarts',
            'echarts/chart/line',   // 按需加载所需图表，如需动态类型切换功能，别忘了同时加载相应图表
            'echarts/chart/bar'
        ],
        function (ec) {
            var myChart = ec.init(document.getElementById('warning'));
            var   option = {
                title: {
                    text: 'WARNING',
                    textStyle : {
                        fontSize: 16,
                        fontWeight: 'bolder',
                        color: '#FF6633'
                    }
                },
                tooltip : {
                    trigger: 'axis'
                },
                legend: {
                    data:<?= json_encode($lenData['warning']); ?>
                },
                toolbox: {
                    show : true,
                    feature : {
                        mark : {show: true},
                        dataView : {show: true, readOnly: false},
                        magicType : {show: true, type: ['line', 'bar', 'stack', 'tiled']},
                        restore : {show: true},
                        saveAsImage : {show: true}
                    }
                },
                calculable : true,
                xAxis : [
                    {
                        type : 'category',
                        boundaryGap : false,
                        data : <?= json_encode($x); ?>
                    }
                ],
                yAxis : [
                    {
                        type : 'value'
                    }
                ],
                series : <?= json_encode($data['warning']); ?>
            };
            myChart.setOption(option);
        }
    );
    require(
        [
            'echarts',
            'echarts/chart/line',   // 按需加载所需图表，如需动态类型切换功能，别忘了同时加载相应图表
            'echarts/chart/bar'
        ],
        function (ec) {
            var myChart = ec.init(document.getElementById('notice'));
            var   option = {
                title: {
                    text: 'NOTICE',
                    textStyle : {
                        fontSize: 16,
                        fontWeight: 'bolder',
                        color: '#FF6633'
                    }
                },
                tooltip : {
                    trigger: 'axis'
                },
                legend: {
                    data:<?= json_encode($lenData['notice']); ?>
                },
                toolbox: {
                    show : true,
                    feature : {
                        mark : {show: true},
                        dataView : {show: true, readOnly: false},
                        magicType : {show: true, type: ['line', 'bar', 'stack', 'tiled']},
                        restore : {show: true},
                        saveAsImage : {show: true}
                    }
                },
                calculable : true,
                xAxis : [
                    {
                        type : 'category',
                        boundaryGap : false,
                        data : <?= json_encode($x); ?>
                    }
                ],
                yAxis : [
                    {
                        type : 'value'
                    }
                ],
                series : <?= json_encode($data['notice']); ?>
            };
            myChart.setOption(option);
        }
    );
    //httpCode_500
    require(
        [
            'echarts',
            'echarts/chart/line',   // 按需加载所需图表，如需动态类型切换功能，别忘了同时加载相应图表
            'echarts/chart/bar'
        ],
        function (ec) {
            var myChart = ec.init(document.getElementById('httpCode_500'));
            var   option = {
                title: {
                    text: 'HTTP_500',
                    textStyle : {
                        fontSize: 16,
                        fontWeight: 'bolder',
                        color: '#DC143C'
                    }
                },
                tooltip : {
                    trigger: 'axis'
                },
                legend: {
                    data:<?= json_encode($lenData['httpCode_500']); ?>
                },
                toolbox: {
                    show : true,
                    feature : {
                        mark : {show: true},
                        dataView : {show: true, readOnly: false},
                        magicType : {show: true, type: ['line', 'bar', 'stack', 'tiled']},
                        restore : {show: true},
                        saveAsImage : {show: true}
                    }
                },
                calculable : true,
                xAxis : [
                    {
                        type : 'category',
                        boundaryGap : false,
                        data : <?= json_encode($x); ?>
                    }
                ],
                yAxis : [
                    {
                        type : 'value'
                    }
                ],
                series : <?= json_encode($data['httpCode_500']); ?>
            };
            myChart.setOption(option);
        }
    );
    //httpCode_400
    require(
        [
            'echarts',
            'echarts/chart/line',   // 按需加载所需图表，如需动态类型切换功能，别忘了同时加载相应图表
            'echarts/chart/bar'
        ],
        function (ec) {
            var myChart = ec.init(document.getElementById('httpCode_400'));
            var   option = {
                title: {
                    text: 'HTTP_400',
                    textStyle : {
                        fontSize: 16,
                        fontWeight: 'bolder',
                        color: '#DC143C'
                    }
                },
                tooltip : {
                    trigger: 'axis'
                },
                legend: {
                    data:<?= json_encode($lenData['httpCode_400']); ?>
                },
                toolbox: {
                    show : true,
                    feature : {
                        mark : {show: true},
                        dataView : {show: true, readOnly: false},
                        magicType : {show: true, type: ['line', 'bar', 'stack', 'tiled']},
                        restore : {show: true},
                        saveAsImage : {show: true}
                    }
                },
                calculable : true,
                xAxis : [
                    {
                        type : 'category',
                        boundaryGap : false,
                        data : <?= json_encode($x); ?>
                    }
                ],
                yAxis : [
                    {
                        type : 'value'
                    }
                ],
                series : <?= json_encode($data['httpCode_400']); ?>
            };
            myChart.setOption(option);
        }
    );
    //execTime_500
    require(
        [
            'echarts',
            'echarts/chart/line',   // 按需加载所需图表，如需动态类型切换功能，别忘了同时加载相应图表
            'echarts/chart/bar'
        ],
        function (ec) {
            var myChart = ec.init(document.getElementById('execTime_500'));
            var   option = {
                title: {
                    text: 'EXECTIME_500',
                    textStyle : {
                        fontSize: 16,
                        fontWeight: 'bolder',
                        color: '#FF6633'
                    }
                },
                tooltip : {
                    trigger: 'axis'
                },
                legend: {
                    data:<?= json_encode($lenData['execTime_500']); ?>
                },
                toolbox: {
                    show : true,
                    feature : {
                        mark : {show: true},
                        dataView : {show: true, readOnly: false},
                        magicType : {show: true, type: ['line', 'bar', 'stack', 'tiled']},
                        restore : {show: true},
                        saveAsImage : {show: true}
                    }
                },
                calculable : true,
                xAxis : [
                    {
                        type : 'category',
                        boundaryGap : false,
                        data : <?= json_encode($x); ?>
                    }
                ],
                yAxis : [
                    {
                        type : 'value'
                    }
                ],
                series : <?= json_encode($data['execTime_500']); ?>
            };
            myChart.setOption(option);
        }
    );
    //execTime_1000
    require(
        [
            'echarts',
            'echarts/chart/line',   // 按需加载所需图表，如需动态类型切换功能，别忘了同时加载相应图表
            'echarts/chart/bar'
        ],
        function (ec) {
            var myChart = ec.init(document.getElementById('execTime_1000'));
            var   option = {
                title: {
                    text: 'EXECTIME_1000',
                    textStyle : {
                        fontSize: 16,
                        fontWeight: 'bolder',
                        color: '#DC143C'
                    }
                },
                tooltip : {
                    trigger: 'axis'
                },
                legend: {
                    data:<?= json_encode($lenData['execTime_1000']); ?>
                },
                toolbox: {
                    show : true,
                    feature : {
                        mark : {show: true},
                        dataView : {show: true, readOnly: false},
                        magicType : {show: true, type: ['line', 'bar', 'stack', 'tiled']},
                        restore : {show: true},
                        saveAsImage : {show: true}
                    }
                },
                calculable : true,
                xAxis : [
                    {
                        type : 'category',
                        boundaryGap : false,
                        data : <?= json_encode($x); ?>
                    }
                ],
                yAxis : [
                    {
                        type : 'value'
                    }
                ],
                series : <?= json_encode($data['execTime_1000']); ?>
            };
            myChart.setOption(option);
        }
    );
    //execTime_5000
    require(
        [
            'echarts',
            'echarts/chart/line',   // 按需加载所需图表，如需动态类型切换功能，别忘了同时加载相应图表
            'echarts/chart/bar'
        ],
        function (ec) {
            var myChart = ec.init(document.getElementById('execTime_5000'));
            var   option = {
                title: {
                    text: 'EXECTIME_5000',
                    textStyle : {
                        fontSize: 16,
                        fontWeight: 'bolder',
                        color: '#DC143C'
                    }
                },
                tooltip : {
                    trigger: 'axis'
                },
                legend: {
                    data:<?= json_encode($lenData['execTime_5000']); ?>
                },
                toolbox: {
                    show : true,
                    feature : {
                        mark : {show: true},
                        dataView : {show: true, readOnly: false},
                        magicType : {show: true, type: ['line', 'bar', 'stack', 'tiled']},
                        restore : {show: true},
                        saveAsImage : {show: true}
                    }
                },
                calculable : true,
                xAxis : [
                    {
                        type : 'category',
                        boundaryGap : false,
                        data : <?= json_encode($x); ?>
                    }
                ],
                yAxis : [
                    {
                        type : 'value'
                    }
                ],
                series : <?= json_encode($data['execTime_5000']); ?>
            };
            myChart.setOption(option);
        }
    );

    $("body").keydown(function() {
        if (event.keyCode == "13") {//keyCode=13是回车键
            $('#search').click();
        }
    });

</script>
<?php include(__DIR__ . '/../common/footer.php') ?>
