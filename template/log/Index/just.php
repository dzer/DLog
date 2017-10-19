<?php include(__DIR__ . '/../common/header.php') ?>
<?php include(__DIR__ . '/../common/nav.php') ?>
<div class="container-fluid theme-showcase" role="main">
    <div class="page-header">
        <h2>最近访问</h2>
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
                <div class="form-group" style="margin: 10px 10px 0 0">
                    <label>时间范围：</label>
                    <input type="text" name="start_time" class="form-control" placeholder="开始时间"
                           onclick="laydate({ istime: true, format: 'YYYY-MM-DD hh:mm:ss'})" value="<?= isset($_GET['start_time']) ? $_GET['start_time'] : ''?>">
                    <input type="text" name="end_time" class="form-control" placeholder="结束时间"
                           onclick="laydate({ istime: true, format: 'YYYY-MM-DD hh:mm:ss'})" value="<?= isset($_GET['end_time']) ? $_GET['end_time'] : ''?>">
                </div>
                <div class="form-group" style="margin: 10px 10px 0 0">
                    <label>请求地址：</label>
                    <input type="text" name="request_url" style="width: 200px;" class="form-control" value="<?= isset($_GET['request_url']) ? $_GET['request_url'] : ''?>" placeholder="url">
                </div>
                <div class="form-group" style="margin: 10px 10px 0 0">
                    <label>请求ID：</label>
                    <input type="text" name="request_id" style="width: 150px;" class="form-control" value="<?= isset($_GET['request_id']) ? $_GET['request_id'] : ''?>" placeholder="requestId">
                </div>
                <div class="form-group" style="margin: 10px 10px 0 0">
                    <label>日志级别：</label>
                    <select name="log_level" class="form-control">
                        <option value="">请选择</option>
                        <option <?= isset($_GET['log_level']) && $_GET['log_level'] == 'info' ? 'selected="selected"' : ''?> value="info">info</option>
                        <option <?= isset($_GET['log_level']) && $_GET['log_level'] == 'error' ? 'selected="selected"' : ''?> value="error">error</option>
                        <option <?= isset($_GET['log_level']) && $_GET['log_level'] == 'warning' ? 'selected="selected"' : ''?> value="warning">warning</option>
                        <option <?= isset($_GET['log_level']) && $_GET['log_level'] == 'notice' ? 'selected="selected"' : ''?> value="notice">notice</option>
                    </select>
                </div>
                <div class="form-group" style="margin: 10px 10px 0 0">
                    <label>日志类型：</label>
                    <select name="log_type" class="form-control">
                        <option value="">请选择</option>
                        <?= \app\common\helpers\Common::optionHtml($types, 'log_type');?>
                    </select>
                </div>
                <div class="form-group" style="margin: 10px 10px 0 0">
                    <label>执行时间：</label>
                    <select name="execTime" class="form-control">
                        <option value="">请选择</option>
                        <option <?= isset($_GET['execTime']) && $_GET['execTime'] == '200' ? 'selected="selected"' : ''?> value="200">0~200ms</option>
                        <option <?= isset($_GET['execTime']) && $_GET['execTime'] == '500' ? 'selected="selected"' : ''?> value="500">200~500ms</option>
                        <option <?= isset($_GET['execTime']) && $_GET['execTime'] == '1000' ? 'selected="selected"' : ''?> value="1000">500ms+</option>
                        <option <?= isset($_GET['execTime']) && $_GET['execTime'] == '1000+' ? 'selected="selected"' : ''?> value="1000+">1000ms+</option>
                    </select>
                </div>
                <div class="form-group" style="margin: 10px 10px 0 0">
                    <label>http状态码：</label>
                    <select name="responseCode" class="form-control">
                        <option value="">请选择</option>
                        <option <?= isset($_GET['responseCode']) && $_GET['responseCode'] == '200' ? 'selected="selected"' : ''?> value="200">200</option>
                        <option <?= isset($_GET['responseCode']) && $_GET['responseCode'] == '400' ? 'selected="selected"' : ''?> value="400">400</option>
                        <option <?= isset($_GET['responseCode']) && $_GET['responseCode'] == '500' ? 'selected="selected"' : ''?> value="500">500</option>
                        <option <?= isset($_GET['responseCode']) && $_GET['responseCode'] == '0' ? 'selected="selected"' : ''?> value="0">0</option>
                    </select>
                </div>
                <div class="form-group" style="margin: 10px 10px 0 0">
                    <label>服务器：</label>
                    <select name="server" class="form-control">
                        <option value="">请选择</option>
                        <?= \app\common\helpers\Common::optionHtml($servers, 'server');?>
                    </select>
                </div>
                <div class="form-group" style="margin: 10px 10px 0 0">
                    <label>排序：</label>
                    <select name="sort" class="form-control">
                        <option value="">请选择</option>
                        <option <?= isset($_GET['sort']) && $_GET['sort'] == 'execTime' ? 'selected="selected"' : ''?> value="execTime">执行时间</option>
                        <option <?= isset($_GET['sort']) && $_GET['sort'] == 'responseCode' ? 'selected="selected"' : ''?> value="responseCode">状态码</option>
                        <option <?= isset($_GET['sort']) && $_GET['sort'] == 'useMemory' ? 'selected="selected"' : ''?> value="useMemory">占用内存</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-default" style="margin: 10px 10px 0 0">搜索</button>
                <a type="submit" href="<?= $base_url ?>" class="btn btn-default" style="margin: 10px 10px 0 0">重置</a>
            </form>
        </div>
        <div class="col-md-12" style="margin-top: 5px">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th style="width: 180px">时间</th>
                    <th>URL</th>
                    <th>类型</th>
                    <th style="width: 110px">状态码</th>
                    <th style="width: 110px">响应时间(ms)</th>
                    <th style="width: 100px">内存占用</th>
                    <th style="width: 100px">日志级别</th>
                    <th style="width: 80px">Server</th>
                </tr>
                </thead>
                <tbody>
                <?php
                if (!empty($rs)) {
                    foreach ($rs as $log) {
                        $is_danger = 0;
                        if ($log['content']['execTime'] > 0.5 || $log['level'] == 'error'
                            || (isset($log['content']['responseCode']) && $log['content']['responseCode'] != 200 && $log['content']['responseCode'] != 302)) {
                            $is_danger = 1;
                        }
                        $time_danger = $log['content']['execTime'] > 0.5 ? 1 : 0;

                        $method_style = isset($log['content']['method']) && $log['content']['method'] == 'POST' ? 'primary' : 'info';

                        ?>
                        <tr class="<?= $is_danger ? 'danger' : '' ?>">
                            <td><?= $log['time'] ?></td>
                            <td>
                                <div>
                                    <span class="label label-<?= $method_style ?>"><?= isset($log['content']['method']) ? $log['content']['method'] : ''?></span>
                                    <a class="line" target="_blank" style="<?= $log['content']['traceId'] == 0 ? 'font-weight:bold' : ''?>" href="/log/Index/trace?request_id=<?= $log['requestId'] ?>&time=<?= urlencode($log['time'])?>"><?= $log['content']['url'] ?></a>
                                </div>
                                <?php if (!empty($log['content']['errorMessage'])) {?>
                                <span class="text-danger" style="display: block; margin: 5px; word-wrap:break-word; word-break:break-all; ">
                                    <?= $log['content']['errorMessage']?>
                                </span>
                                <?php }?>
                            </td>
                            <td><?= $log['type']?></td>
                            <td class="<?= isset($log['content']['responseCode']) && $log['content']['responseCode'] >= 200 && $log['content']['responseCode'] < 400 ? 'text-success' : 'text-danger'?>"><?= isset($log['content']['responseCode']) ? $log['content']['responseCode'] : ''?></td>
                            <td>
                                    <span class="label label-<?= $time_danger ? 'danger' : 'success' ?>">
                                        <?= sprintf('%.2f', ($log['content']['execTime'] * 1000)) ?>
                                    </span>
                            </td>
                            <td>
                                <?php
                                if (!empty($log['content']['useMemory'])) {
                                    echo \Mll\Common\Common::convert(intval($log['content']['useMemory']));
                                }
                                ?>
                            </td>
                            <td><?= strtoupper($log['level']) ?></td>
                            <td><?= isset($log['server']) ? $log['server'] : ''?></td>
                        </tr>
                        <?php
                    }
                }
                ?>
                </tbody>
            </table>
            <nav aria-label="Page navigation" class="pull-right">
                <div class="pagination" style="line-height: 34px; float: left; margin-right: 10px;"> 当前第 <?= $page['page']?>页</div>
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
