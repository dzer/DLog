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
        <br />
        <div class="col-md-12">

            <div class="row">
                <div id="request-time" style="width: 100%;height:300px"></div>
                <div id="request-error" style="width: 100%;height:300%"></div>
                <div id="request-count" style="width: 100%;height:300%"></div>

                <div id="user-time" style="width: 100%;height:300px"></div>
                <div id="user-error" style="width: 100%;height:300%"></div>
                <div id="user-count" style="width: 100%;height:300%"></div>

                <div id="rule-time" style="width: 100%;height:300px"></div>
                <div id="rule-error" style="width: 100%;height:300%"></div>
                <div id="rule-count" style="width: 100%;height:300%"></div>

                <div id="request" style="width: 100%;height:400%"></div>
                <br />
                <div id="curl" style="width: 100%;height:400%"></div>
                <br />
                <div id="rule" style="width: 100%;height:400%"></div>
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
    var grid_left = 70

    var myChart = echarts.init(document.getElementById('request-time'),'macarons');
    var data = <?= json_encode($single['REQUEST']['exec_time'])?>;
    myChart.setOption(option = {
        title: {
            text: '请求-平均时间',
            textStyle:{
                fontSize:15
            }
        },
        tooltip: {
            trigger: 'axis'
        },
        xAxis: {
            data: data.map(function (item) {
                return item[0];
            })
        },
        yAxis: {
            splitLine: {
                show: false
            }
        },
        toolbox: {
            left: 'center',
            feature: {
                dataZoom: {
                    yAxisIndex: 'none'
                },
                restore: {},
                saveAsImage: {}
            }
        },
        dataZoom: [{
            //startValue: '2014-06-01'
//            left: 30, //左边的距离
//            right: 40,//右边的距离
            bottom: 80//右边的距离
        }, {
            type: 'inside',
//            realtime: true,
            start: 30,
            end: 70,
            xAxisIndex: [0]
        }],
        visualMap: {
            top: 10,
            right: 10,
            pieces: [{
                gt: 0,
                lte: 500,
                color: '#096'
            }, {
                gt: 500,
                lte: 1000,
                color: '#ffde33'
            }, {
                gt: 1000,
                lte: 1500,
                color: '#ff9933'
            }, {
                gt: 1500,
                lte: 2000,
                color: '#cc0033'
            }, {
                gt: 2000,
                lte: 3000,
                color: '#660099'
            }, {
                gt: 3000,
                color: '#7e0023'
            }],
            outOfRange: {
                color: '#999'
            }
        },
        grid: [{
            left: grid_left,
            right: 150,
            top: '20%',
            height: '35%'
        }],
        series: {
            name: '平均执行时间',
            type: 'line',
            data: data.map(function (item) {
                return item[1];
            }),
            markLine: {
                silent: true,
                data: [{
                    yAxis: 500
                }, {
                    yAxis: 1000
                }, {
                    yAxis: 1500
                }, {
                    yAxis: 2000
                }, {
                    yAxis: 2500
                }, {
                    yAxis: 3000
                }]
            }
        }
    });

    var myChart = echarts.init(document.getElementById('request-count'),'macarons');
    var data = <?= json_encode($single['REQUEST']['count'])?>;
    myChart.setOption(option = {
        title: {
            text: '请求-数量',
            textStyle:{
                fontSize:15
            }
        },
        tooltip: {
            trigger: 'axis'
        },
        xAxis: {
            data: data.map(function (item) {
                return item[0];
            })
        },
        yAxis: {
            splitLine: {
                show: false
            }
        },
        toolbox: {
            left: 'center',
            feature: {
                dataZoom: {
                    yAxisIndex: 'none'
                },
                restore: {},
                saveAsImage: {}
            }
        },
        dataZoom: [{
            //startValue: '2014-06-01'
//            left: 30, //左边的距离
//            right: 40,//右边的距离
            bottom: 80//右边的距离
        }, {
            type: 'inside',
//            realtime: true,
            start: 30,
            end: 70,
            xAxisIndex: [0]
        }],
//        visualMap: {
//            top: 10,
//            right: 10,
//            pieces: [{
//                gt: 0,
//                lte: 500,
//                color: '#096'
//            }, {
//                gt: 500,
//                lte: 1000,
//                color: '#ffde33'
//            }, {
//                gt: 1000,
//                lte: 1500,
//                color: '#ff9933'
//            }, {
//                gt: 1500,
//                lte: 2000,
//                color: '#cc0033'
//            }, {
//                gt: 2000,
//                lte: 3000,
//                color: '#660099'
//            }, {
//                gt: 3000,
//                color: '#7e0023'
//            }],
//            outOfRange: {
//                color: '#999'
//            }
//        },
        grid: [{
            left: grid_left,
            right: 150,
            top: '20%',
            height: '35%'
        }],
        series: {
            name: '请求量',
            type: 'line',
            data: data.map(function (item) {
                return item[1];
            }),
//            markLine: {
//                silent: true,
//                data: [{
//                    yAxis: 500
//                }, {
//                    yAxis: 1000
//                }, {
//                    yAxis: 1500
//                }, {
//                    yAxis: 2000
//                }, {
//                    yAxis: 2500
//                }, {
//                    yAxis: 3000
//                }]
//            }
        }
    });

    var myChart = echarts.init(document.getElementById('request-error'),'macarons');
    var data = <?= json_encode($single['REQUEST']['error'])?>;
    myChart.setOption(option = {
        title: {
            text: '请求-ERROR',
            textStyle:{
                fontSize:15
            }
        },
        tooltip: {
            trigger: 'axis'
        },
        xAxis: {
            data: data.map(function (item) {
                return item[0];
            })
        },
        yAxis: {
            splitLine: {
                show: false
            }
        },
        toolbox: {
            left: 'center',
            feature: {
                dataZoom: {
                    yAxisIndex: 'none'
                },
                restore: {},
                saveAsImage: {}
            }
        },
        dataZoom: [{
            //startValue: '2014-06-01'
//            left: 30, //左边的距离
//            right: 40,//右边的距离
            bottom: 80//右边的距离
        }, {
            type: 'inside',
//            realtime: true,
            start: 30,
            end: 70,
            xAxisIndex: [0]
        }],
        visualMap: {
            top: 10,
            right: 10,
            pieces: [{
                gt: 0,
                lte: 50,
                color: '#096'
            }, {
                gt: 50,
                lte: 100,
                color: '#ffde33'
            }, {
                gt: 100,
                lte: 150,
                color: '#ff9933'
            }, {
                gt: 150,
                lte: 200,
                color: '#cc0033'
            }, {
                gt: 200,
                lte: 300,
                color: '#660099'
            }, {
                gt: 300,
                color: '#7e0023'
            }],
            outOfRange: {
                color: '#999'
            }
        },
        grid: [{
            left: grid_left,
            right: 150,
            top: '20%',
            height: '35%'
        }],
        series: {
            name: '错误量',
            type: 'line',
            data: data.map(function (item) {
                return item[1];
            }),
            markLine: {
                silent: true,
                data: [{
                    yAxis: 50
                }, {
                    yAxis: 100
                }, {
                    yAxis: 150
                }, {
                    yAxis: 200
                }, {
                    yAxis: 250
                }, {
                    yAxis: 300
                }]
            }
        }
    });

</script>

<script>
    var grid_left = 70

    var myChart = echarts.init(document.getElementById('user-time'),'macarons');
    var data = <?= json_encode($single['USER']['exec_time'])?>;
    myChart.setOption(option = {
        title: {
            text: 'PC请求-平均时间',
            textStyle:{
                fontSize:15
            }
        },
        tooltip: {
            trigger: 'axis'
        },
        xAxis: {
            data: data.map(function (item) {
                return item[0];
            })
        },
        yAxis: {
            splitLine: {
                show: false
            }
        },
        toolbox: {
            left: 'center',
            feature: {
                dataZoom: {
                    yAxisIndex: 'none'
                },
                restore: {},
                saveAsImage: {}
            }
        },
        dataZoom: [{
            //startValue: '2014-06-01'
//            left: 30, //左边的距离
//            right: 40,//右边的距离
            bottom: 80//右边的距离
        }, {
            type: 'inside',
//            realtime: true,
            start: 30,
            end: 70,
            xAxisIndex: [0]
        }],
        visualMap: {
            top: 10,
            right: 10,
            pieces: [{
                gt: 0,
                lte: 200,
                color: '#096'
            }, {
                gt: 200,
                lte: 400,
                color: '#ffde33'
            }, {
                gt: 400,
                lte: 600,
                color: '#ff9933'
            }, {
                gt: 600,
                lte: 1000,
                color: '#cc0033'
            }, {
                gt: 1000,
                lte: 1200,
                color: '#660099'
            }, {
                gt: 1200,
                color: '#7e0023'
            }],
            outOfRange: {
                color: '#999'
            }
        },
        grid: [{
            left: grid_left,
            right: 150,
            top: '20%',
            height: '35%'
        }],
        series: {
            name: '平均执行时间',
            type: 'line',
            data: data.map(function (item) {
                return item[1];
            }),
            markLine: {
                silent: true,
                data: [{
                    yAxis: 200
                }, {
                    yAxis: 400
                }, {
                    yAxis: 600
                }, {
                    yAxis: 800
                }, {
                    yAxis: 1000
                }, {
                    yAxis: 1200
                }]
            }
        }
    });

    var myChart = echarts.init(document.getElementById('user-count'),'macarons');
    var data = <?= json_encode($single['USER']['count'])?>;
    myChart.setOption(option = {
        title: {
            text: 'PC请求-数量',
            textStyle:{
                fontSize:15
            }
        },
        tooltip: {
            trigger: 'axis'
        },
        xAxis: {
            data: data.map(function (item) {
                return item[0];
            })
        },
        yAxis: {
            splitLine: {
                show: false
            }
        },
        toolbox: {
            left: 'center',
            feature: {
                dataZoom: {
                    yAxisIndex: 'none'
                },
                restore: {},
                saveAsImage: {}
            }
        },
        dataZoom: [{
            //startValue: '2014-06-01'
//            left: 30, //左边的距离
//            right: 40,//右边的距离
            bottom: 80//右边的距离
        }, {
            type: 'inside',
//            realtime: true,
            start: 30,
            end: 70,
            xAxisIndex: [0]
        }],
//        visualMap: {
//            top: 10,
//            right: 10,
//            pieces: [{
//                gt: 0,
//                lte: 500,
//                color: '#096'
//            }, {
//                gt: 500,
//                lte: 1000,
//                color: '#ffde33'
//            }, {
//                gt: 1000,
//                lte: 1500,
//                color: '#ff9933'
//            }, {
//                gt: 1500,
//                lte: 2000,
//                color: '#cc0033'
//            }, {
//                gt: 2000,
//                lte: 3000,
//                color: '#660099'
//            }, {
//                gt: 3000,
//                color: '#7e0023'
//            }],
//            outOfRange: {
//                color: '#999'
//            }
//        },
        grid: [{
            left: grid_left,
            right: 150,
            top: '20%',
            height: '35%'
        }],
        series: {
            name: '请求量',
            type: 'line',
            data: data.map(function (item) {
                return item[1];
            }),
//            markLine: {
//                silent: true,
//                data: [{
//                    yAxis: 500
//                }, {
//                    yAxis: 1000
//                }, {
//                    yAxis: 1500
//                }, {
//                    yAxis: 2000
//                }, {
//                    yAxis: 2500
//                }, {
//                    yAxis: 3000
//                }]
//            }
        }
    });

    var myChart = echarts.init(document.getElementById('user-error'),'macarons');
    var data = <?= json_encode($single['USER']['error'])?>;
    myChart.setOption(option = {
        title: {
            text: 'PC请求-ERROR',
            textStyle:{
                fontSize:15
            }
        },
        tooltip: {
            trigger: 'axis'
        },
        xAxis: {
            data: data.map(function (item) {
                return item[0];
            })
        },
        yAxis: {
            splitLine: {
                show: false
            }
        },
        toolbox: {
            left: 'center',
            feature: {
                dataZoom: {
                    yAxisIndex: 'none'
                },
                restore: {},
                saveAsImage: {}
            }
        },
        dataZoom: [{
            //startValue: '2014-06-01'
//            left: 30, //左边的距离
//            right: 40,//右边的距离
            bottom: 80//右边的距离
        }, {
            type: 'inside',
//            realtime: true,
            start: 30,
            end: 70,
            xAxisIndex: [0]
        }],
        visualMap: {
            top: 10,
            right: 10,
            pieces: [{
                gt: 0,
                lte: 50,
                color: '#096'
            }, {
                gt: 50,
                lte: 100,
                color: '#ffde33'
            }, {
                gt: 100,
                lte: 150,
                color: '#ff9933'
            }, {
                gt: 150,
                lte: 200,
                color: '#cc0033'
            }, {
                gt: 200,
                lte: 300,
                color: '#660099'
            }, {
                gt: 300,
                color: '#7e0023'
            }],
            outOfRange: {
                color: '#999'
            }
        },
        grid: [{
            left: grid_left,
            right: 150,
            top: '20%',
            height: '35%'
        }],
        series: {
            name: '错误量',
            type: 'line',
            data: data.map(function (item) {
                return item[1];
            }),
            markLine: {
                silent: true,
                data: [{
                    yAxis: 50
                }, {
                    yAxis: 100
                }, {
                    yAxis: 150
                }, {
                    yAxis: 200
                }, {
                    yAxis: 250
                }, {
                    yAxis: 300
                }]
            }
        }
    });

</script>


<script>
    //rule

    var myChart = echarts.init(document.getElementById('rule-time'),'macarons');
    var data = <?= json_encode($single['RULE']['exec_time'])?>;
    myChart.setOption(option = {
        title: {
            text: '规则-平均时间',
            textStyle:{
                fontSize:15
            }
        },
        tooltip: {
            trigger: 'axis'
        },
        xAxis: {
            data: data.map(function (item) {
                return item[0];
            })
        },
        yAxis: {
            splitLine: {
                show: false
            }
        },
        toolbox: {
            left: 'center',
            feature: {
                dataZoom: {
                    yAxisIndex: 'none'
                },
                restore: {},
                saveAsImage: {}
            }
        },
        dataZoom: [{
            //startValue: '2014-06-01'
//            left: 30, //左边的距离
//            right: 40,//右边的距离
            bottom: 80//右边的距离
        }, {
            type: 'inside',
//            realtime: true,
            start: 30,
            end: 70,
            xAxisIndex: [0]
        }],
        visualMap: {
            top: 10,
            right: 10,
            pieces: [{
                gt: 0,
                lte: 500,
                color: '#096'
            }, {
                gt: 500,
                lte: 1000,
                color: '#ffde33'
            }, {
                gt: 1000,
                lte: 1500,
                color: '#ff9933'
            }, {
                gt: 1500,
                lte: 2000,
                color: '#cc0033'
            }, {
                gt: 2000,
                lte: 3000,
                color: '#660099'
            }, {
                gt: 3000,
                color: '#7e0023'
            }],
            outOfRange: {
                color: '#999'
            }
        },
        grid: [{
            left: grid_left,
            right: 150,
            top: '20%',
            height: '35%'
        }],
        series: {
            name: '平均执行时间',
            type: 'line',
            data: data.map(function (item) {
                return item[1];
            }),
            markLine: {
                silent: true,
                data: [{
                    yAxis: 500
                }, {
                    yAxis: 1000
                }, {
                    yAxis: 1500
                }, {
                    yAxis: 2000
                }, {
                    yAxis: 2500
                }, {
                    yAxis: 3000
                }]
            }
        }
    });

    var myChart = echarts.init(document.getElementById('rule-count'),'macarons');
    var data = <?= json_encode($single['RULE']['count'])?>;
    myChart.setOption(option = {
        title: {
            text: '规则-数量',
            textStyle:{
                fontSize:15
            }
        },
        tooltip: {
            trigger: 'axis'
        },
        xAxis: {
            data: data.map(function (item) {
                return item[0];
            })
        },
        yAxis: {
            splitLine: {
                show: false
            }
        },
        toolbox: {
            left: 'center',
            feature: {
                dataZoom: {
                    yAxisIndex: 'none'
                },
                restore: {},
                saveAsImage: {}
            }
        },
        dataZoom: [{
            //startValue: '2014-06-01'
//            left: 30, //左边的距离
//            right: 40,//右边的距离
            bottom: 80//右边的距离
        }, {
            type: 'inside',
//            realtime: true,
            start: 30,
            end: 70,
            xAxisIndex: [0]
        }],
//        visualMap: {
//            top: 10,
//            right: 10,
//            pieces: [{
//                gt: 0,
//                lte: 500,
//                color: '#096'
//            }, {
//                gt: 500,
//                lte: 1000,
//                color: '#ffde33'
//            }, {
//                gt: 1000,
//                lte: 1500,
//                color: '#ff9933'
//            }, {
//                gt: 1500,
//                lte: 2000,
//                color: '#cc0033'
//            }, {
//                gt: 2000,
//                lte: 3000,
//                color: '#660099'
//            }, {
//                gt: 3000,
//                color: '#7e0023'
//            }],
//            outOfRange: {
//                color: '#999'
//            }
//        },
        grid: [{
            left: grid_left,
            right: 150,
            top: '20%',
            height: '35%'
        }],
        series: {
            name: '请求量',
            type: 'line',
            data: data.map(function (item) {
                return item[1];
            }),
//            markLine: {
//                silent: true,
//                data: [{
//                    yAxis: 500
//                }, {
//                    yAxis: 1000
//                }, {
//                    yAxis: 1500
//                }, {
//                    yAxis: 2000
//                }, {
//                    yAxis: 2500
//                }, {
//                    yAxis: 3000
//                }]
//            }
        }
    });

    var myChart = echarts.init(document.getElementById('rule-error'),'macarons');
    var data = <?= json_encode($single['RULE']['error'])?>;
    myChart.setOption(option = {
        title: {
            text: '规则-ERROR',
            textStyle:{
                fontSize:15
            }
        },
        tooltip: {
            trigger: 'axis'
        },
        xAxis: {
            data: data.map(function (item) {
                return item[0];
            })
        },
        yAxis: {
            splitLine: {
                show: false
            }
        },
        toolbox: {
            left: 'center',
            feature: {
                dataZoom: {
                    yAxisIndex: 'none'
                },
                restore: {},
                saveAsImage: {}
            }
        },
        dataZoom: [{
            //startValue: '2014-06-01'
//            left: 30, //左边的距离
//            right: 40,//右边的距离
            bottom: 80//右边的距离
        }, {
            type: 'inside',
//            realtime: true,
            start: 30,
            end: 70,
            xAxisIndex: [0]
        }],
        visualMap: {
            top: 10,
            right: 10,
            pieces: [{
                gt: 0,
                lte: 500,
                color: '#096'
            }, {
                gt: 500,
                lte: 1000,
                color: '#ffde33'
            }, {
                gt: 1000,
                lte: 1500,
                color: '#ff9933'
            }, {
                gt: 1500,
                lte: 2000,
                color: '#cc0033'
            }, {
                gt: 2000,
                lte: 3000,
                color: '#660099'
            }, {
                gt: 3000,
                color: '#7e0023'
            }],
            outOfRange: {
                color: '#999'
            }
        },
        grid: [{
            left: grid_left,
            right: 150,
            top: '20%',
            height: '35%'
        }],
        series: {
            name: '错误量',
            type: 'line',
            data: data.map(function (item) {
                return item[1];
            }),
            markLine: {
                silent: true,
                data: [{
                    yAxis: 500
                }, {
                    yAxis: 1000
                }, {
                    yAxis: 1500
                }, {
                    yAxis: 2000
                }, {
                    yAxis: 2500
                }, {
                    yAxis: 3000
                }]
            }
        }
    });
</script>

<script>

</script>
<?php include(__DIR__ . '/../common/footer.php') ?>
