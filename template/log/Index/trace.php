<?php include(__DIR__ . '/../common/header.php') ?>
<?php include(__DIR__ . '/../common/nav.php') ?>
    <div class="container-fluid theme-showcase" role="main">
        <div class="row">
            <div class="col-md-12">
                <h3>请求信息</h3>
                <table class="table table-condensed">
                    <tbody>
                    <tr style="font-weight: bolder">
                        <td>请求时间：<?= $main['time'] ?></td>
                        <td>执行时间: <?= sprintf('%.4f', ($main['content']['execTime'] * 1000)) ?> ms</td>
                        <td>内存占用: <?= \Mll\Common\Common::convert(intval($main['content']['useMemory'])) ?></td>
                        <td>REQUEST_ID: <?= $main['requestId'] ?></td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="page-header">
            <h1>日志回放</h1>
        </div>
        <div class="row">
            <div class="col-md-12">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th width="600px">URL</th>
                        <th>traceId</th>
                        <th>TYPE</th>
                        <th>日志级别</th>
                        <th>响应(ms)</th>
                        <th>内存占用</th>
                        <th>Server</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    if (!empty($rs)) {
                        foreach ($rs as $log) {
                            $is_danger = 0;
                            if ((isset($log['content']['execTime']) && $log['content']['execTime'] > 0.5)
                                || $log['level'] == 'error'
                            ) {
                                $is_danger = 1;
                            }
                            $count = 0;
                            if (isset($log['content']['traceId'])) {
                                $count = substr_count($log['content']['traceId'], '.') * 2;
                            }

                            $time_danger = isset($log['content']['execTime']) && $log['content']['execTime'] > 0.5 ? 1 : 0;
                            ?>
                            <tr class="<?= $is_danger ? 'danger' : '' ?>">
                                <td style="padding-left: <?= ($count * 20) ?>px">
                                    <div>
                                    <span class="label label-primary"><?= isset($log['method']) ? $log['method'] : '' ?></span>
                                    <a href="#"><?= isset($log['content']['url']) ? $log['content']['url'] : $log['message'] ?></a>
                                    </div>
                                    <?php if (!empty($log['content']['errorMessage'])) {?>
                                        <span class="text-danger" style="display: block; margin: 5px; word-wrap:break-word; word-break:break-all; ">
                                        <?= $log['content']['errorMessage']?>
                                        </span>
                                    <?php }?>
                                </td>
                                <td><?= isset($log['content']['traceId']) ? $log['content']['traceId'] : '' ?></td>
                                <td><?= $log['type'] ?></td>
                                <td><?= $log['level'] ?></td>
                                <td>
                                    <?php
                                    if (isset($log['content']['execTime'])) {
                                        ?>
                                        <span class="label label-<?= $time_danger ? 'danger' : 'success' ?>">
                                        <?= sprintf('%.4f', ($log['content']['execTime'] * 1000)) ?>
                                    </span>
                                    <?php } ?>
                                </td>
                                <td>
                                    <?php
                                        if (!empty($log['content']['useMemory'])) {
                                           echo \Mll\Common\Common::convert(intval($log['content']['useMemory']));
                                        }
                                    ?>
                                    </td>
                                <td><?= isset($log['server']) ? $log['server'] : '' ?></td>
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