<?php include(__DIR__ . '/../common/header.php') ?>
<?php include(__DIR__ . '/../common/nav.php') ?>
<div class="container-fluid theme-showcase" role="main">
    <div class="row">
        <div class="col-md-12">
            <h2>统计总览</h2>
            <table class="table table-condensed">
                <tbody>
                <tr style="font-weight: 600; font-size: 18px; text-align: center">
                    <td date_today="<?= $today_count ?>">总日志数：<?= $count ?></td>
                    <td>今日调用次数: <?= $statusData['count']?></td>
                    <td>今日平均执行时间: <?= sprintf('%.1f', $statusData['time'] * 1000)?> ms</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <form class="form-inline" action="<?= $base_url ?>">
                <div class="form-group" style="margin-left: 10px">
                    <label>项目：</label>
                    <select name="project" class="form-control">
                        <option <?= isset($_GET['project']) && $_GET['project'] == 'help' ? 'selected="selected"' : ''?> value="help">HELP</option>
                        <option <?= isset($_GET['project']) && $_GET['project'] == 'mll' ? 'selected="selected"' : ''?> value="mll">MLL</option>
                        <option <?= isset($_GET['project']) && $_GET['project'] == 'common' ? 'selected="selected"' : ''?> value="common">COMMON</option>
                    </select>
                </div>
                <div class="form-group" style="margin-left: 10px">
                    <label>时间：</label>
                    <input type="text" name="curr_time" class="form-control" placeholder="时间"
                           onclick="laydate({ istime: true, format: 'YYYY-MM-DD'})" value="<?= isset($_GET['curr_time']) ? $_GET['curr_time'] : ''?>">
                </div>
                <div class="form-group" style="margin-left: 10px">
                    <label>日志类型：</label>
                    <select name="log_type" class="form-control">
                        <option value="">请选择</option>
                        <option <?= isset($_GET['log_type']) && $_GET['log_type'] == 'RULE' ? 'selected="selected"' : '' ?>
                                value="RULE">规则
                        </option>
                       <!-- <option <?/*= isset($_GET['log_type']) && $_GET['log_type'] == 'RPC' ? 'selected="selected"' : '' */?>
                                value="RPC">RPC
                        </option>-->
                        <option <?= isset($_GET['log_type']) && $_GET['log_type'] == 'REQUEST' ? 'selected="selected"' : '' ?>
                                value="REQUEST">请求
                        </option>
                        <option <?= isset($_GET['log_type']) && $_GET['log_type'] == 'CURL' ? 'selected="selected"' : '' ?>
                                value="CURL">接口
                        </option>
                    </select>
                </div>
                <button type="submit" class="btn btn-default" style="margin-left: 10px">搜索</button>
                <a type="submit" href="<?= $base_url ?>" class="btn btn-default" style="margin-left: 10px">重置</a>
            </form>
        </div>
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-6">
                    <div id="http" style="min-width:250px;height:250px;"></div>
                </div>
                <div class="col-md-6">
                    <div id="exec_time" style="min-width:250px;height:250px;"></div>
                </div>
            </div>

        </div>
        <div class="col-md-12">
            <div id="container" style="min-width:350px;height:350px;"></div>
        </div>
    </div>
</div> <!-- /container -->
<script src="https://img.hcharts.cn/highcharts/highcharts.js"></script>
<script src="https://img.hcharts.cn/highcharts/modules/exporting.js"></script>
<script src="https://img.hcharts.cn/highcharts-plugins/highcharts-zh_CN.js"></script>
<script>
    $(function () {
        $('#container').highcharts({
            chart: {
                zoomType: 'xy'
            },
            title: {
                text: '响应时间和调用次数'
            },
            /*subtitle: {
             text: '数据来源: WorldClimate.com'
             },*/
            xAxis: [{
                categories: <?= json_encode($countData['count_time']) ?>,
                crosshair: true
            }],
            yAxis: [{ // Primary yAxis
                labels: {
                    format: '{value} ms',
                    style: {
                        color: Highcharts.getOptions().colors[1]
                    }
                },
                title: {
                    text: '响应时间',
                    style: {
                        color: Highcharts.getOptions().colors[1]
                    }
                }
            }, { // Secondary yAxis
                title: {
                    text: '调用次数',
                    style: {
                        color: Highcharts.getOptions().colors[0]
                    }
                },
                labels: {
                    format: '{value} ',
                    style: {
                        color: Highcharts.getOptions().colors[0]
                    }
                },
                opposite: true
            }],
            tooltip: {
                pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b><br/>',
                shared: true
            },
            plotOptions: {
                column: {
                    stacking: 'normal',
                    dataLabels: {
                        //enabled: true,
                        color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white',
                        style: {
                            textShadow: '0 0 3px black'
                        }
                    }
                }
            },
            legend: {
                layout: 'vertical',
                align: 'left',
                x: 120,
                verticalAlign: 'top',
                y: 100,
                floating: true,
                backgroundColor: (Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF'
            },
            series: [{
                name: '成功次数',
                type: 'column',
                yAxis: 1,
                color: '#79DD1B',
                data: <?= json_encode($countData['success']) ?>,
                tooltip: {
                    valueSuffix: ' '
                }
            },
                {
                    name: '失败次数',
                    type: 'column',
                    yAxis: 1,
                    color: '#FF9326',
                    data: <?= json_encode($countData['fail']) ?>,
                    tooltip: {
                        valueSuffix: ' '
                    }
                },
                {
                    name: '响应时间',
                    type: 'spline',
                    data: <?= json_encode($countData['time']) ?>,
                    tooltip: {
                        valueSuffix: ' ms'
                    }
                }],
            credits: {
                enabled: false // 禁用版权信息
            }
        });
    });
    $(function () {
        // Radialize the colors
        Highcharts.getOptions().colors = Highcharts.map(Highcharts.getOptions().colors, function (color) {
            return {
                radialGradient: {cx: 0.5, cy: 0.3, r: 0.7},
                stops: [
                    [0, color],
                    [1, Highcharts.Color(color).brighten(-0.3).get('rgb')] // darken
                ]
            };
        });
        // 构建图表
        $('#http').highcharts({
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false
            },
            title: {
                text: 'HTTP响应状态码'
            },
            tooltip: {
                pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                        style: {
                            color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                        },
                        connectorColor: 'silver'
                    }
                }
            },
            series: [{
                type: 'pie',
                name: '状态码占比图',
                data: [
                    {
                        name: '200',
                        y: <?= intval($statusData['code_200'])?>,
                        sliced: true
                    },
                    ['300',    <?= intval($statusData['code_300'])?>],
                    ['400',    <?= intval($statusData['code_400'])?>],
                    ['500',     <?= intval($statusData['code_500'])?>],
                    {
                        name: '其他',
                        selected: true
                    }
                ]
            }],
            credits: {
                enabled: false // 禁用版权信息
            }
        });
        $('#exec_time').highcharts({
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false
            },
            title: {
                text: '执行时间分布图'
            },
            tooltip: {
                pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                        style: {
                            color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                        },
                        connectorColor: 'silver'
                    }
                }
            },
            series: [{
                type: 'pie',
                name: '状态码占比',
                data: [
                    {
                        name: '0~200ms',
                        y: <?= intval($statusData['time_200'])?>,
                        sliced: true
                    },
                    ['200~500ms', <?= intval($statusData['time_500'])?>],
                    ['500~1000ms', <?= intval($statusData['time_1000'])?>],
                    {
                        name: '1000ms+',
                        y: <?= intval($statusData['time_1000+'])?>,
                        selected: true
                    }
                ]
            }],
            credits: {
                enabled: false // 禁用版权信息
            }
        });
    });
</script>
<?php include(__DIR__ . '/../common/footer.php') ?>
