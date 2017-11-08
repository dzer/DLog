<?php include(__DIR__ . '/../common/header.php') ?>
<?php include(__DIR__ . '/../common/nav.php') ?>
    <div class="container-fluid theme-showcase" role="main">
        <div class="page-header">
            <h2>性能排行</h2>
        </div>
        <div class="row">
            <div class="col-md-12">
                <form class="form-inline" action="<?= $base_url ?>">
                    <div class="form-group" style="margin: 10px 10px 0 0">
                        <label>项目：</label>
                        <select name="project" class="form-control">
                            <?= \app\common\helpers\Common::optionHtml($projects, 'project');?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>时间范围：</label>
                        <input type="text" name="start_time" class="form-control" placeholder="开始时间"
                               onclick="laydate({ istime: true, format: 'YYYY-MM-DD'})" value="<?= isset($_GET['start_time']) ? $_GET['start_time'] : ''?>">
                        <input type="text" name="end_time" class="form-control" placeholder="结束时间"
                               onclick="laydate({ istime: true, format: 'YYYY-MM-DD'})" value="<?= isset($_GET['end_time']) ? $_GET['end_time'] : ''?>">
                    </div>
                    <div class="form-group" style="margin-left: 10px">
                        <label>请求地址：</label>
                        <input type="text" name="request_url" style="width: 200px;" class="form-control" value="<?= isset($_GET['request_url']) ? $_GET['request_url'] : ''?>" placeholder="url">
                    </div>
                    <div class="form-group" style="margin-left: 10px">
                        <label>日志类型：</label>
                        <select name="log_type" class="form-control">
                            <option value="">请选择</option>
                            <?= \app\common\helpers\Common::optionHtml($types, 'log_type');?>
                        </select>
                    </div>
                    <div class="form-group" style="margin-left: 10px">
                        <label>日志级别：</label>
                        <select name="log_level" class="form-control">
                            <option value="">请选择</option>
                            <option <?= isset($_GET['log_level']) && $_GET['log_level'] == 'info' ? 'selected="selected"' : ''?> value="info">info</option>
                            <option <?= isset($_GET['log_level']) && $_GET['log_level'] == 'error' ? 'selected="selected"' : ''?> value="error">error</option>
                            <option <?= isset($_GET['log_level']) && $_GET['log_level'] == 'warning' ? 'selected="selected"' : ''?> value="warning">warning</option>
                            <option <?= isset($_GET['log_level']) && $_GET['log_level'] == 'notice' ? 'selected="selected"' : ''?> value="notice">notice</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-left: 10px">
                        <label>执行时间：</label>
                        <select name="execTime" class="form-control">
                            <option value="0">请选择</option>
                            <option <?= isset($_GET['execTime']) && $_GET['execTime'] == '0.5' ? 'selected="selected"' : ''?> value="0.5">大于500ms</option>
                            <option <?= isset($_GET['execTime']) && $_GET['execTime'] == '1' ? 'selected="selected"' : ''?> value="1">大于1s</option>
                            <option <?= isset($_GET['execTime']) && $_GET['execTime'] == '5' ? 'selected="selected"' : ''?> value="5">大于5s</option>
                            <option <?= isset($_GET['execTime']) && $_GET['execTime'] == '10' ? 'selected="selected"' : ''?> value="10">大于10s</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-left: 10px">
                        <label>排序：</label>
                        <select name="sort" class="form-control">
                            <option value="">请选择</option>
                            <option <?= isset($_GET['sort']) && $_GET['sort'] == 'time' ? 'selected="selected"' : ''?> value="time">执行时间</option>
                            <option <?= isset($_GET['sort']) && $_GET['sort'] == 'count' ? 'selected="selected"' : ''?> value="count">调用次数</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-default" style="margin-left: 10px">搜索</button>
                    <a type="submit" href="<?= $base_url ?>" class="btn btn-default" style="margin-left: 10px">重置</a>
                </form>
            </div>

            <div class="col-md-12" style="margin-top: 15px">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th style="max-width: 50%">URL</th>
                        <th style="max-width: 50%">client</th>
                        <th>调用次数</th>
                        <th>类型</th>
                        <th style="width: 110px">平均响应时间(ms)</th>
                        <th style="width: 110px">最大响应时间(ms)</th>
                        <th style="width: 110px">最小响应时间(ms)</th>
                        <th style="width: 150px">响应时间(%)</th>
                        <th style="width: 150px">http状态码(%)</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    if (!empty($rs)) {
                        foreach ($rs as $log) {
                            $is_danger = 0;
                            if ($log['time'] > 0.5) {
                                $is_danger = 1;
                            }
                            $time_danger = $log['time'] > 0.5 ? 1 : 0;
                            ?>
                            <tr class="<?= $is_danger ? 'danger' : '' ?>">
                                <td>
                                    <div>
                                        <a class="line" target="_blank" href="/log/Index/just?project=<?= $_GET['project'] ?>&log_level=<?= $_GET['log_level']?>&log_type=<?= $_GET['log_type']?>&start_time=<?= urlencode($_GET['start_time'])?>&end_time=<?= urlencode($_GET['end_time'])?>&request_url=<?= urlencode($log['_id']['url'])?>"><?= $log['_id']['url'] ?></a>
                                    </div>
                                </td>
                                <td><?= $log['client'] ?></td>
                                <td><?= $log['count'] ?></td>
                                <td><?= $log['type'] ?></td>
                                <td>
                                    <span class="label label-<?= $time_danger ? 'danger' : 'success' ?>">
                                        <?= sprintf('%.2f', ($log['time'] * 1000)) ?>
                                    </span>
                                </td>
                                <td style="<?= $log['max_time'] > 0.5 ? 'color:red' : '' ?>"><?= sprintf('%.2f', ($log['max_time'] * 1000)) ?></td>
                                <td><?= sprintf('%.2f', ($log['min_time'] * 1000)) ?></td>
                                <td>
                                    <ul class="table-ul">
                                        <?php
                                            $time_arr = array(
                                                    '0~200' => '200',
                                                    '200~500' => '500',
                                                    '500~1000' => '1000',
                                                    '1000+' => '1000+'
                                            );
                                            foreach ($time_arr as $k => $_time) {
                                                 $time = $log['time_' . $_time] / $log['count'] * 100;
                                                if ($time > 0) {
                                                    echo '<li>' . $k .'ms: ' . sprintf('%.1f', $time) . '%</li>';
                                                }
                                            }
                                        ?>
                                    </ul>
                                </td>
                                <td>
                                    <ul class="table-ul">
                                        <?php
                                        $code_arr = array(
                                            '200' => '200',
                                            '300' => '300',
                                            '400' => '400',
                                            '500' => '500'
                                        );
                                        foreach ($code_arr as $k => $_code) {
                                            $code = $log['code_' . $_code] / $log['count'] * 100;
                                            if ($code > 0) {
                                                echo '<li>' . $k .': ' . sprintf('%.1f', $code) . '%</li>';
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
                <nav aria-label="Page navigation" class="pull-right">
                    <div class="pagination" style="line-height: 34px; float: left; margin-right: 10px;">总计 <?= $page['count']?>个调用次数</div>
                    <ul class="pagination">
                        <?php
                        if (!empty($page)) {
                            if ($page['page'] > 1) {
                                $_GET['page'] = $page['page'] - 1;
                                ?>
                                <li>
                                    <a href="<?= $base_url . '?' . http_build_query($_GET);?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                                <?php
                            }
                            $_GET['page'] = $page['page'] + 1;
                            ?>
                            <li>
                                <a href="<?= $base_url . '?' . http_build_query($_GET);?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                            <?php
                        }
                        ?>
                    </ul>
                </nav>
            </div>
        </div>
    </div> <!-- /container -->
<?php include(__DIR__ . '/../common/footer.php') ?>