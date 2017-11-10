<?php include(__DIR__ . '/../common/header.php') ?>
<?php include(__DIR__ . '/../common/nav-online.php') ?>
    <div class="container-fluid theme-showcase" role="main">
        <div class="row">
            <div class="col-md-12">
                <h3>请求信息</h3>
                <table class="table table-condensed">
                    <tbody>
                    <tr style="font-weight: bolder">
                        <td>请求时间：<?= $main['time'] ?></td>
                        <td>执行时间: <?= sprintf('%.4f', (isset($main['content']['execTime']) ? $main['content']['execTime'] * 1000 : '')) ?> ms</td>
                        <td>内存占用: <?= \Mll\Common\Common::convert(intval(isset($main['content']['useMemory']) ? $main['content']['useMemory'] : '')) ?></td>
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
                        <th width="50%">URL</th>
                        <th>traceId</th>
                        <th>TYPE</th>
                        <th>状态码</th>
                        <th>日志级别</th>
                        <th>响应(ms)</th>
                        <th>内存占用</th>
                        <th>Server</th>
                        <th>xhprof</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    if (!empty($rs)) {

                        if (function_exists('xhprof_enable')){
                            $xhprof_path = \Mll\Mll::app()->config->get('xhprof.path');
                            require(ROOT_PATH . $xhprof_path . DS . 'xhprof_lib' . DS . 'utils' . DS . 'xhprof_lib.php');
                            require(ROOT_PATH . $xhprof_path . DS . 'xhprof_lib' . DS . 'utils' . DS . 'xhprof_runs.php');
                        }
                        foreach ($rs as $k => $log) {
                            $is_danger = '';
                            if ((isset($log['content']['execTime']) && $log['content']['execTime'] > 0.5)
                                || $log['level'] == 'error'
                            ) {
                                $is_danger = 'danger';
                            } elseif ($log['level'] == 'warning' || $log['level'] == 'debug') {
                                $is_danger = 'warning';
                            } elseif ($log['level'] == 'notice') {
                                $is_danger = 'info';
                            }

                            $count = 0;
                            if (isset($log['content']['traceId'])) {
                                $count = substr_count($log['content']['traceId'], '.') * 2;
                            }
                            $run_id = '';
                            if (!empty($log['content']['xhprof']) && function_exists('xhprof_enable')){
                                $xhprof_runs = new XHProfRuns_Default($xhprof_dir);
                                $run_id = $xhprof_runs->save_run($log['content']['xhprof'], "xhprof_foo");
                            }

                            $time_danger = isset($log['content']['execTime']) && $log['content']['execTime'] > 0.5 ? 1 : 0;

                            $method_style = isset($log['content']['method']) && $log['content']['method'] == 'POST' ? 'primary' : 'info';
                            ?>
                            <tr class="<?= $is_danger ?>">
                                <td style="padding-left: <?= ($count * 20) ?>px">
                                    <div>
                                    <span class="label label-<?= $method_style ?>"><?= isset($log['content']['method']) ? $log['content']['method'] : '' ?></span>
                                    <a href="#" class="line" onclick="showData('<?= $k ?>')"><?= isset($log['content']['url']) ? $log['content']['url'] : $log['message'] ?></a>
                                    </div>
                                    <?php if (!empty($log['content']['errorMessage'])) {?>
                                        <span class="text-danger" style="display: block; margin: 5px; word-wrap:break-word; word-break:break-all; ">
                                        <?= $log['content']['errorMessage']?>
                                        </span>
                                    <?php }?>
                                </td>
                                <td><?= isset($log['content']['traceId']) ? $log['content']['traceId'] : '' ?></td>
                                <td><?= $log['type'] ?></td>
                                <td class="<?= isset($log['content']['responseCode']) && $log['content']['responseCode'] == 200 ? 'text-success' : 'text-danger'?>"><?= isset($log['content']['responseCode']) ? $log['content']['responseCode'] : ''?></td>
                                <td><?= $log['level'] ?></td>
                                <td>
                                    <?php
                                    if (isset($log['content']['execTime'])) {
                                        ?>
                                        <span class="label label-<?= $time_danger ? 'danger' : 'success' ?>">
                                        <?= sprintf('%.2f', ($log['content']['execTime'] * 1000)) ?>
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
                                <td>
                                    <?php
                                        if (!empty($run_id)) {
                                            echo "<a target='_blank' href='/xhprof.php?run={$run_id}&source=xhprof_foo'>性能分析</a>";
                                        }
                                    ?>
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
    <!-- Modal -->
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">请求信息</h4>
                </div>
                <div class="modal-body">
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th width="100px;">参数名</th>
                            <th>值</th>
                        </tr>
                        </thead>
                        <tbody id="request-content">

                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        var rs = <?= $info ?>;
        function showData(id) {
            var str = '<tr><td>Time</td> <td>' + rs[id]['time'] + '</td></tr>'
                +'<tr><td>URL</td><td style="word-break: break-all">' + rs[id]['content']['url'] + '</td></tr><tr>'
                +'<tr><td>Method</td><td>' + rs[id]['content']['method'] + '</td></tr><tr>'
                +'<tr><td>请求参数</td><td><pre>' + formatJson(JSON.stringify(rs[id]['content']['requestParams'])) + '</pre></td></tr><tr>'
                +'<tr><td>超时时间</td><td>' + rs[id]['content']['timeout'] + 's</td></tr><tr>'
                +'<tr><td>错误消息</td><td>' + rs[id]['content']['errorMessage'] + '</td></tr><tr>'
                +'<tr><td>日志</td><td><pre>' + formatJson(JSON.stringify(rs[id])) + '</pre></td></tr><tr>';
            $('#request-content').html(str);
            $('#myModal').modal();
        }
    </script>
<?php include(__DIR__ . '/../common/footer.php') ?>