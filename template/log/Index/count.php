<?php include(__DIR__ . '/../common/header.php') ?>
<?php include(__DIR__ . '/../common/nav.php') ?>
    <div class="container-fluid theme-showcase" role="main">
        <div class="page-header">
            <h2>日志统计</h2>
        </div>
        <div class="row">
            <div class="col-md-12">
                <form class="form-inline" action="<?= $base_url ?>">
                    <div class="form-group" style="margin-left: 10px">
                        <label>项目：</label>
                        <select name="project" class="form-control">
                            <?= \app\common\helpers\Common::optionHtml($projects, 'project');?>
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
                        <th>WARNING</th>
                        <th>ERROR</th>
                        <th>NOTICE</th>
                        <th>http状态码(0)</th>
                        <th>http状态码(400)</th>
                        <th>http状态码(500)</th>
                        <th>执行时间(%)</th>
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
                                <td>
                                    <ul class="table-ul">
                                        <?php
                                        $time_arr = array(
                                            '0~200' => '200',
                                            '200~500' => '500',
                                            '500~1000' => '1000',
                                            '1000~5000' => '5000',
                                            '5000+' => '5000+'
                                        );
                                        foreach ($time_arr as $k => $_time) {
                                            $time = $v['execTime_' . $_time] / $v['count'] * 100;
                                            if ($time > 0) {
                                                echo '<li>' . $k .'ms: ' . sprintf('%.1f', $time) . '%</li>';
                                            }
                                        }
                                        ?>
                                    </ul>
                                </td>
                            </tr>
                            <?php
                        }
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div> <!-- /container -->
<?php include(__DIR__ . '/../common/footer.php') ?>