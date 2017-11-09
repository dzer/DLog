<?php
use app\common\helpers\Common;

include(__DIR__ . '/../common/header.php')
?>
<?php include(__DIR__ . '/../common/nav.php') ?>
<style>
    body,input{
    /*background : rgb(51, 51, 51)*/
    }
    #calendar{
        position:relative;
        left:-27px;
        top:7px;
    }
</style>

<link rel="stylesheet" href="/static/log/css/daterangepicker.css" type="text/css" />
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
                    时间维度:
                    <select name="time-level" class="form-control">
                        <option value="hour" <?= $_g['time-level']=='hour' ? 'selected' : ''; ?> >小时</option>
                        <option value="day" <?= $_g['time-level']=='day' ? 'selected' : ''; ?> >天</option>
                    </select>


                    <input class="time-input form-control fc-clear" id="time-range" size="25" type="text" name="time_range" value="<?=$_g['time_range']?>" >

                    <span id="calendar" class="glyphicon glyphicon-calendar form-group-btn input-icon input-icon-md"  style="font-size:21px;color: blue"></span>

                    <span class="add-on"><i class="icon-th "></i></span>
<!--                    日:-->
<!--                    <input class="time-input form-control" id="time-input-day" size="2" type="text" name="time-day" value="" >-->
<!--                    <span class="add-on"><i class="icon-th"></i></span>-->
                </div>
                <!-- <div class="form-group" style="margin-left: 10px">
                    <label>时间：</label>
                    <input type="text" name="curr_time" class="form-control" placeholder="时间"
                           onclick="laydate({ istime: true, format: 'YYYY-MM-DD'})" value="<?/*= isset($_GET['curr_time']) ? $_GET['curr_time'] : ''*/?>">
                </div>-->
<!--                <div class="form-group" style="margin-left: 10px">-->
<!--                    <label>日志类型：</label>-->
<!--                    <select name="log_type" class="form-control">-->
<!--                        <option value="">请选择</option>-->
<!--                        --><?//= Common::optionHtml($types, 'log_type');?>
<!--                    </select>-->
<!--                </div>-->


                <button type="submit" class="btn btn-success" style="margin-left: 10px">搜索</button>
                <a type="submit" href="<?= $base_url ?>" class="btn btn-danger" style="margin-left: 10px">重置</a>
            </form>
        </div>
        <div style="height:20px"></div>
        <br />
        <div class="col-md-12">

            <div class="row">
                <div id="request" style="width: 100%;height:400%"></div>
                <br />
                <div id="curl" style="width: 100%;height:400%"></div>
                <br />
                <div id="rule" style="width: 100%;height:400%"></div>
                <div id="exec-time" style="width: 100%;height:200px;"></div>
                <div id="error" style="width: 100%;height:200px;"></div>
                <div id="warning" style="width: 100%;height:200px;"></div>
                <div id="notice" style="width: 100%;height:200px;"></div>
                <div id="httpCode_400" style="width: 100%;height:200px;"></div>
                <div id="httpCode_500" style="width: 100%;height:200px;"></div>
            </div>

        </div>
        <div class="col-md-12">
            <div id="container" style="min-width:350px;height:350px;"></div>
        </div>
    </div>
</div> <!-- /container -->

<script src="http://www.bootcss.com/p/bootstrap-datetimepicker/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"></script>
<script src="/static/log/js/moment.js"></script>
<script src="/static/log/js/daterangepicker.js"></script>
<script type="text/javascript">

    $(function() {
        $('#calendar').daterangepicker({
            applyClass : 'btn-sm btn-success',
            cancelClass : 'btn-sm btn-default',
            locale: {
                applyLabel: '确认',
                cancelLabel: '取消',
                fromLabel : '起始时间',
                toLabel : '结束时间',
                customRangeLabel : '自定义',
                firstDay : 1
            },
            ranges : {
                //'最近1小时': [moment().subtract('hours',1), moment()],
                '今日': [moment().startOf('day'), moment()],
                '昨日': [moment().subtract('days', 1).startOf('day'), moment().subtract('days', 1).endOf('day')],
                '最近7日': [moment().subtract('days', 6), moment()],
                '最近30日': [moment().subtract('days', 29), moment()],
                '本月': [moment().startOf("month"),moment().endOf("month")],
                '上个月': [moment().subtract(1,"month").startOf("month"),moment().subtract(1,"month").endOf("month")]
            },
            opens : 'right',    // 日期选择框的弹出位置
            separator : ' 至 ',
            showWeekNumbers : true,     // 是否显示第几周
//            timePicker: true,
//            timePickerIncrement : 60, // 时间的增量，单位为分钟
//            timePicker12Hour : false, // 是否使用12小时制来显示时间
            maxDate : moment(),           // 最大时间
            format: 'YYYY-MM-DD HH:mm'

        }, function(start, end, label) { // 格式化日期显示框
            $('#time-range').val(start.format('YYYY-MM-DD') + ' - ' + end.format('YYYY-MM-DD'));
        }).next().on('click', function(){
                $(this).prev().focus();
            });
    });



</script>

<script src="http://echarts.baidu.com/dist/echarts.min.js"></script>
<script src="http://echarts.baidu.com/asset/theme/dark.js"></script>
<script src="http://echarts.baidu.com/asset/theme/macarons.js"></script>
<script src="http://echarts.baidu.com/asset/theme/vintage.js"></script>

<script>

    var timeData = <?=json_encode($data['REQUEST']['time'])?>;
    timeData = timeData.map(function (str) {
        return str.replace('2017-', '');
    });

    option = {
        title: {
            text: 'REQUEST',
            subtext: '',
            x: 'center',
            color: 'red'
        },
        tooltip: {
            trigger: 'axis',
            formatter: function (params) {
                return params[0].name + '<br/>'
                    + params[0].seriesName + ' : ' + params[0].value + ' <br/>';
            },
            axisPointer: {
                animation: false
            }
        },
        legend: {
            data:['请求量','错误'],
            x: 'left'
        },
        dataZoom: [
            {
                show: true,
                realtime: true,
                start: 0,
                end: 100,
                xAxisIndex: [0, 1]
            },
            {
                type: 'inside',
                realtime: true,
                start: 0,
                end: 100,
                xAxisIndex: [0, 1]
            }
        ],
        grid: [{
            left: 50,
            right: 50,
            height: '35%'
        }, {
            left: 50,
            right: 50,
            top: '57%',
            height: '35%'
        }],
        xAxis : [
            {
                type : 'category',
                boundaryGap : false,
                axisLine: {
                    onZero: true
                },
                data: timeData
            },
            {
                gridIndex: 1,
                type : 'category',
                boundaryGap : false,
                axisLine: {onZero: true},
                data: timeData,
                position: 'top'
            }
        ],
        yAxis : [
            {
                name : '请求量',
                type : 'value'
                //max : 5000
            },
            {
                gridIndex: 1,
                name : '错误',
                type : 'value',
                inverse: true
            }
        ],
        series : [
            {
                name:'请求量',
                type:'line',
                symbolSize: 8,
                hoverAnimation: false,
                data:   <?=json_encode($data['REQUEST']['count'])?>

            },
            {
                name:'错误',
                type:'line',
                xAxisIndex: 1,
                yAxisIndex: 1,
                symbolSize: 8,
                hoverAnimation: false,
                data: <?=json_encode($data['REQUEST']['error'])?>

            }
        ]
    };
    var myChart = echarts.init(document.getElementById('request'),'macarons');
    myChart.setOption(option);


    //curl

    var timeData = <?=json_encode($data['CURL']['time'])?>;
    timeData = timeData.map(function (str) {
        return str.replace('2017-', '');
    });

    option = {
        title: {
            text: 'CURL',
            subtext: '',
            x: 'center',
            color: 'red'
        },
        tooltip: {
            trigger: 'axis',
            formatter: function (params) {
                return params[0].name + '<br/>'
                    + params[0].seriesName + ' : ' + params[0].value + ' <br/>';
            },
            axisPointer: {
                animation: false
            }
        },
        legend: {
            data:['请求量','错误'],
            x: 'left'
        },
        dataZoom: [
            {
                show: true,
                realtime: true,
                start: 0,
                end: 100,
                xAxisIndex: [0, 1]
            },
            {
                type: 'inside',
                realtime: true,
                start: 0,
                end: 100,
                xAxisIndex: [0, 1]
            }
        ],
        grid: [{
            left: 50,
            right: 50,
            height: '35%'
        }, {
            left: 50,
            right: 50,
            top: '57%',
            height: '35%'
        }],
        xAxis : [
            {
                type : 'category',
                boundaryGap : false,
                axisLine: {
                    onZero: true
                },
                data: timeData
            },
            {
                gridIndex: 1,
                type : 'category',
                boundaryGap : false,
                axisLine: {onZero: true},
                data: timeData,
                position: 'top'
            }
        ],
        yAxis : [
            {
                name : '请求量',
                type : 'value'
                //max : 5000
            },
            {
                gridIndex: 1,
                name : '错误',
                type : 'value',
                inverse: true
            }
        ],
        series : [
            {
                name:'请求量',
                type:'line',
                symbolSize: 8,
                hoverAnimation: false,
                data:   <?=json_encode($data['CURL']['count'])?>

            },
            {
                name:'错误',
                type:'line',
                xAxisIndex: 1,
                yAxisIndex: 1,
                symbolSize: 8,
                hoverAnimation: false,
                data: <?=json_encode($data['CURL']['error'])?>

            }
        ]
    };
    myChart = echarts.init(document.getElementById('curl'),'macarons');
    myChart.setOption(option);

    //rule
    var timeData = <?=json_encode($data['RULE']['time'])?>;
    timeData = timeData.map(function (str) {
        return str.replace('2017-', '');
    });

    option = {
        title: {
            text: 'RULE',
            subtext: '',
            x: 'center',
            color: 'red'
        },
        tooltip: {
            trigger: 'axis',
            formatter: function (params) {
                return params[0].name + '<br/>'
                    + params[0].seriesName + ' : ' + params[0].value + ' <br/>';
            },
            axisPointer: {
                animation: false
            }
        },
        legend: {
            data:['请求量','错误'],
            x: 'left'
        },
        dataZoom: [
            {
                show: true,
                realtime: true,
                start: 0,
                end: 100,
                xAxisIndex: [0, 1]
            },
            {
                type: 'inside',
                realtime: true,
                start: 0,
                end: 100,
                xAxisIndex: [0, 1]
            }
        ],
        grid: [{
            left: 50,
            right: 50,
            height: '35%'
        }, {
            left: 50,
            right: 50,
            top: '57%',
            height: '35%'
        }],
        xAxis : [
            {
                type : 'category',
                boundaryGap : false,
                axisLine: {
                    onZero: true
                },
                data: timeData
            },
            {
                gridIndex: 1,
                type : 'category',
                boundaryGap : false,
                axisLine: {onZero: true},
                data: timeData,
                position: 'top'
            }
        ],
        yAxis : [
            {
                name : '请求量',
                type : 'value'
                //max : 5000
            },
            {
                gridIndex: 1,
                name : '错误',
                type : 'value',
                inverse: true
            }
        ],
        series : [
            {
                name:'请求量',
                type:'line',
                symbolSize: 8,
                hoverAnimation: false,
                data:   <?=json_encode($data['RULE']['count'])?>

            },
            {
                name:'错误',
                type:'line',
                xAxisIndex: 1,
                yAxisIndex: 1,
                symbolSize: 8,
                hoverAnimation: false,
                data: <?=json_encode($data['RULE']['error'])?>

            }
        ]
    };
    myChart = echarts.init(document.getElementById('rule'),'macarons');
    myChart.setOption(option);
</script>
<?php include(__DIR__ . '/../common/footer.php') ?>
