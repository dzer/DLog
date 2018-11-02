<?php
use app\common\helpers\Common;

include(__DIR__ . '/../common/header.php')
?>
<?php include(__DIR__ . '/../common/nav.php') ?>
<div class="container-fluid theme-showcase" role="main">
    <div class="row">
        <div class="col-md-12">
            <h2>统计总览</h2>
            <table class="table table-condensed">
                <tbody>
                <tr style="font-weight: 600; font-size: 18px; text-align: center">
                    <td date_today="<?= $today_count ?>">总日志数：<?= $count ?></td>
                    <td>今日记录次数: <?= $statusData['count']?></td>
                    <td>今日平均执行时间: <?= sprintf('%.1f', ($statusData['count'] > 0 ? ($statusData['execTimeSum']/$statusData['count']) * 1000 : '')) ?> ms</td>
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
                        <?= Common::optionHtml($projects, 'project');?>
                    </select>
                </div>
                <div class="form-group" style="margin-left: 10px">
                    <label>时间：</label>
                    <input type="text" name="curr_time" class="form-control" placeholder="时间"
                           onclick="laydate({ istime: true, format: 'YYYY-MM-DD'})" value="<?= isset($_GET['curr_time']) ? $_GET['curr_time'] : ''?>">
                </div>

                <button type="submit" class="btn btn-default" style="margin-left: 10px">搜索</button>
                <a type="submit" href="<?= $base_url ?>" class="btn btn-default" style="margin-left: 10px">重置</a>
            </form>
        </div>
        <div class="col-md-12" style="margin-top: 15px">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>日志类型</th>
                    <th>记录次数</th>
                    <th>平均执行时间(ms)</th>
                    <th>ERROR</th>
                    <th>WARNING</th>
                    <th>NOTICE</th>
                    <th>http状态码(0)</th>
                    <th>http状态码(400)</th>
                    <th>http状态码(500)</th>
                </tr>
                </thead>
                <tbody>
                <?php
                if (isset($rs)) {
                    foreach ($rs as $v) {
                        ?>
                        <tr>
                            <td><?= isset($types[$v['_id']['type']]) ? $types[$v['_id']['type']] : ''?></td>
                            <td><?= $v['count']?></td>
                            <td><?= sprintf('%.1f', ($v['count'] > 0 ? ($v['execTime']/$v['count']) * 1000 : '')) ?> ms</td>
                            <td>
                                <a style="<?= $v['error'] > 0 ? 'color:#d9534f;font-weight:bold' : ''?>"
                                   href="/log/Index/just?project=<?= $_GET['project'] ?>&start_time=<?= $_GET['curr_time'] . ' 00:00:00'?>&end_time=<?= $_GET['curr_time'] . ' 23:59:59'?>&log_level=error&log_type=<?= $v['_id']['type']?>"><?= $v['error']?></a>
                            </td>
                            <td>
                                <a style="<?= $v['warning'] > 0 ? 'color:#d9534f;font-weight:bold' : ''?>"
                                   href="/log/Index/just?project=<?= $_GET['project'] ?>&start_time=<?= $_GET['curr_time'] . ' 00:00:00'?>&end_time=<?= $_GET['curr_time'] . ' 23:59:59'?>&log_level=warning&log_type=<?= $v['_id']['type']?>"><?= $v['warning']?></a>
                            </td>
                            <td>
                                <a style="<?= $v['notice'] > 0 ? 'color:#d9534f;font-weight:bold' : ''?>"
                                   href="/log/Index/just?project=<?= $_GET['project'] ?>&start_time=<?= $_GET['curr_time'] . ' 00:00:00'?>&end_time=<?= $_GET['curr_time'] . ' 23:59:59'?>&log_level=notice&log_type=<?= $v['_id']['type']?>"><?= $v['notice']?></a>
                            </td>
                            <td>
                                <a style="<?= $v['httpCode_0'] > 0 ? 'color:#d9534f;font-weight:bold' : ''?>"
                                   href="/log/Index/just?project=<?= $_GET['project'] ?>&start_time=<?= $_GET['curr_time'] . ' 00:00:00'?>&end_time=<?= $_GET['curr_time'] . ' 23:59:59'?>&responseCode=0&log_type=<?= $v['_id']['type']?>"><?= $v['httpCode_0']?></a>
                            </td>
                            <td>
                                <a style="<?= $v['httpCode_400'] > 0 ? 'color:#d9534f;font-weight:bold' : ''?>"
                                   href="/log/Index/just?project=<?= $_GET['project'] ?>&start_time=<?= $_GET['curr_time'] . ' 00:00:00'?>&end_time=<?= $_GET['curr_time'] . ' 23:59:59'?>&responseCode=400&log_type=<?= $v['_id']['type']?>"><?= $v['httpCode_400']?></a>
                            </td>
                            <td>
                                <a style="<?= $v['httpCode_500'] > 0 ? 'color:#d9534f;font-weight:bold' : ''?>"
                                   href="/log/Index/just?project=<?= $_GET['project'] ?>&start_time=<?= $_GET['curr_time'] . ' 00:00:00'?>&end_time=<?= $_GET['curr_time'] . ' 23:59:59'?>&responseCode=500&log_type=<?= $v['_id']['type']?>"><?= $v['httpCode_500']?></a>
                            </td>
                        </tr>
                        <?php
                    }
                }
                ?>
                </tbody>
            </table>
        </div>
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-4">
                    <div id="http" style="min-width:250px;height:250px;"></div>
                </div>
                <div class="col-md-4">
                    <div id="exec_time" style="min-width:250px;height:250px;"></div>
                </div>
                <div class="col-md-4" >
                    <h4>报警信息</h4>
                    <table style="height: 230px; overflow-y: scroll;display: block" class="table table-striped table-hover">
                        <tbody>
                        <?php
                            foreach ($forewarningList as $_list) {
                                ?>
                                <tr>
                                    <td>
                                        <?= $_list['msg']; ?>
                                    </td>
                                </tr>
                                <?php
                            }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
        <div class="col-md-12">
            <div id="container" style="min-width:350px;height:350px;"></div>
        </div>
    </div>
</div> <!-- /container -->
<script src="/static/log/js/highcharts.js"></script>
<script src="/static/log/js/exporting.js"></script>
<script src="/static/log/js/highcharts-zh_CN.js"></script>
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
                x: 100,
                verticalAlign: 'top',
                y: 50,
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
                    name: '错误消息',
                    type: 'column',
                    yAxis: 1,
                    color: '#d9534f',
                    data: <?= json_encode($countData['error']) ?>,
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
                    ['1s~5s', <?= intval($statusData['time_5000'])?>],
                    {
                        name: '5s+',
                        y: <?= intval($statusData['time_5000+'])?>,
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
