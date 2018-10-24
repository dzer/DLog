<?php include(__DIR__ . '/../common/header.php') ?>
<?php include(__DIR__ . '/../common/nav.php') ?>
<div class="container-fluid theme-showcase" role="main">
    <div class="page-header">
        <h2>缓存统计</h2>
    </div>
    <div class="row">
        <div class="col-md-12">
            <form class="form-inline" action="<?= $base_url ?>">
                <div class="form-group" style="margin: 10px 10px 0 0">
                    <label>时间：</label>
                    <input type="text" name="curr_time" class="form-control" placeholder="时间"
                           onclick="laydate({ istime: true, format: 'YYYY-MM-DD'})" value="<?= isset($_GET['curr_time']) ? $_GET['curr_time'] : ''?>">
                </div>
                <div class="form-group" style="margin: 10px 10px 0 0">
                    <label>缓存服务器：</label>
                    <select name="host" class="form-control">
                        <?= \app\common\helpers\Common::optionHtml($hosts, 'host');?>
                    </select>
                </div>
                <div class="form-group" style="margin: 10px 10px 0 0">
                    <label>缓存key：</label>
                    <input type="text" name="key" style="width: 200px;" class="form-control" value="<?= isset($_GET['key']) ? $_GET['key'] : ''?>" placeholder="cache_key">
                </div>
                <div class="form-group" style="margin: 10px 10px 0 0">
                    <label>排序：</label>
                    <select name="sort" class="form-control">
                        <option value="">请选择</option>
                        <option <?= isset($_GET['sort']) && $_GET['sort'] == 'count' ? 'selected="selected"' : ''?> value="count">记录总数</option>
                        <option <?= isset($_GET['sort']) && $_GET['sort'] == 'get' ? 'selected="selected"' : ''?> value="get">get总数</option>
                        <option <?= isset($_GET['sort']) && $_GET['sort'] == 'set' ? 'selected="selected"' : ''?> value="set">set总数</option>
                        <option <?= isset($_GET['sort']) && $_GET['sort'] == 'get_fail' ? 'selected="selected"' : ''?> value="get_fail">get失败数</option>
                        <option <?= isset($_GET['sort']) && $_GET['sort'] == 'set_fail' ? 'selected="selected"' : ''?> value="set_fail">set失败数</option>
                        <option <?= isset($_GET['sort']) && $_GET['sort'] == 'length' ? 'selected="selected"' : ''?> value="length">长度</option>
                        <option <?= isset($_GET['sort']) && $_GET['sort'] == 'expire' ? 'selected="selected"' : ''?> value="expire">过期时间</option>
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
                    <th width="100px">时间</th>
                    <th width="400px">key</th>
                    <th width="180px">host</th>
                    <th width="60px">长度</th>
                    <th width="80px">过期时间</th>
                    <th width="60px">get</th>
                    <th width="80px">get失败</th>
                    <th width="60px">set</th>
                    <th width="80px">set失败</th>
                    <th width="80px">记录次数</th>
                    <th>url</th>
                </tr>
                </thead>
                <tbody>
                <?php
                if (!empty($rs)) {
                    foreach ($rs as $log) {
                        $set_danger = $log['set_fail'] > 0 ? 1 : 0;
                        $get_danger = $log['get_fail'] > 50 ? 1 : 0;
                        ?>
                        <tr class="<?= ($set_danger || $get_danger) ? 'danger' : '' ?>">
                            <td><?= $log['date'] ?></td>
                            <td><span style="word-break: break-all;"><?= $log['key'] ?></span></td>
                            <td><?= $log['host'] ?></td>
                            <td><?= $log['length']?></td>
                            <td><?= $log['expire']?></td>
                            <td><?= $log['get']?></td>
                            <td><?= $log['get_fail']?></td>
                            <td><?= $log['set']?></td>
                            <td><?= $log['set_fail']?></td>
                            <td><?= $log['count']?></td>
                            <td><span style="word-break: break-all;"><?= $log['request_uri']?></span></td>
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