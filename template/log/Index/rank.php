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
                            <option <?= isset($_GET['project']) && $_GET['project'] == 'help' ? 'selected="selected"' : ''?> value="help">HELP</option>
                            <option <?= isset($_GET['project']) && $_GET['project'] == 'mll' ? 'selected="selected"' : ''?> value="mll">MLL</option>
                            <option <?= isset($_GET['project']) && $_GET['project'] == 'common' ? 'selected="selected"' : ''?> value="common">COMMON</option>
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
                            <option <?= isset($_GET['log_type']) && $_GET['log_type'] == 'RULE' ? 'selected="selected"' : ''?> value="RULE">规则</option>
                            <option <?= isset($_GET['log_type']) && $_GET['log_type'] == 'RPC' ? 'selected="selected"' : ''?> value="RPC">RPC</option>
                            <option <?= isset($_GET['log_type']) && $_GET['log_type'] == 'REQUEST' ? 'selected="selected"' : ''?> value="REQUEST">请求</option>
                            <option <?= isset($_GET['log_type']) && $_GET['log_type'] == 'CURL' ? 'selected="selected"' : ''?> value="CURL">接口</option>
                            <option <?= isset($_GET['log_type']) && $_GET['log_type'] == 'MYSQL' ? 'selected="selected"' : ''?> value="MYSQL">MYSQL</option>
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
                                        <a class="line" target="_blank" href="/log/Index/just?start_time=<?= urlencode($_GET['start_time'])?>&end_time=<?= urlencode($_GET['end_time'])?>&request_url=<?= urlencode($log['_id']['url'])?>"><?= $log['_id']['url'] ?></a>
                                    </div>
                                </td>
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
                    <div class="pagination" style="line-height: 34px; float: left; margin-right: 10px;">总计 <?= $page['count']?>个记录 分为 <?= $page['page_count']?>页 当前第 <?= $page['page']?>页</div>
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
                            for ($i = max(1, $page['page'] - 5); $i <= min($page['page'] + 5, $page['page_count']); $i++) {
                                $_GET['page'] = $i;
                                ?>
                                <li <?= $page['page'] == $i ? 'class="active"' : ''?>><a href="<?= $base_url . '?' . http_build_query($_GET);?>"><?= $i ?></a></li>
                                <?php
                            }
                            if ($page['page'] < $page['page_count']) {
                                $_GET['page'] = $page['page'] + 1;
                                ?>
                                <li>
                                    <a href="<?= $base_url . '?' . http_build_query($_GET);?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                                <?php
                            }
                        }
                        ?>
                    </ul>
                </nav>
            </div>
        </div>
    </div> <!-- /container -->
<?php include(__DIR__ . '/../common/footer.php') ?>