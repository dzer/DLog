<?php include(__DIR__ . '/../common/header.php') ?>
<?php include(__DIR__ . '/../common/nav.php') ?>
<div class="container-fluid theme-showcase" role="main">
    <div class="page-header">
        <h2>最近访问</h2>
    </div>
    <div class="row">
        <div class="col-md-12">
            <form class="form-inline">
                <div class="form-group">
                    <label>时间范围：</label>
                    <input type="text" class="form-control" placeholder="开始时间"
                           onclick="laydate({ istime: true, format: 'YYYY-MM-DD hh:mm:ss'})">
                    <input type="text" class="form-control" placeholder="结束时间"
                           onclick="laydate({ istime: true, format: 'YYYY-MM-DD hh:mm:ss'})">
                </div>
                <div class="form-group" style="margin-left: 10px">
                    <label for="exampleInputEmail2">请求地址：</label>
                    <input type="email" class="form-control" id="exampleInputEmail2" placeholder="url">
                </div>
                <div class="form-group" style="margin-left: 10px">
                    <label for="exampleInputEmail2">日志级别：</label>
                    <select class="form-control">
                        <option>all</option>
                        <option>info</option>
                        <option>error</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-default" style="margin-left: 10px">搜索</button>
            </form>
        </div>
        <div class="col-md-12" style="margin-top: 15px">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th style="width: 180px">时间</th>
                    <th>URL</th>
                    <th style="width: 110px">响应时间(ms)</th>
                    <th style="width: 100px">日志级别</th>
                    <th style="width: 80px">Server</th>
                </tr>
                </thead>
                <tbody>
                <?php
                if (!empty($rs)) {
                    foreach ($rs as $log) {
                        $is_danger = 0;
                        if ($log['content']['execTime'] > 0.5 || $log['level'] == 'error') {
                            $is_danger = 1;
                        }
                        $time_danger = $log['content']['execTime'] > 0.5 ? 1 : 0;

                        ?>
                        <tr class="<?= $is_danger ? 'danger' : '' ?>">
                            <td><?= $log['time'] ?></td>
                            <td>
                                <div>
                                <span class="label label-primary"><?= $log['content']['method']?></span>
                                <a target="_blank" href="/log/Index/trace?request_id=<?= $log['requestId'] ?>"><?= $log['content']['url'] ?></a>
                                </div>
                                <?php if (!empty($log['content']['errorMessage'])) {?>
                                <span class="text-danger" style="display: block; margin: 5px; word-wrap:break-word; word-break:break-all; ">
                                    <?= $log['content']['errorMessage']?>
                                </span>
                                <?php }?>
                            </td>
                            <td>
                                    <span class="label label-<?= $time_danger ? 'danger' : 'success' ?>">
                                        <?= sprintf('%.4f', ($log['content']['execTime'] * 1000)) ?>
                                    </span>
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
                <ul class="pagination">
                    <li>
                        <a href="#" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    <li><a href="#">1</a></li>
                    <li><a href="#">2</a></li>
                    <li><a href="#">3</a></li>
                    <li><a href="#">4</a></li>
                    <li><a href="#">5</a></li>
                    <li>
                        <a href="#" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div> <!-- /container -->
<?php include(__DIR__ . '/../common/footer.php') ?>
