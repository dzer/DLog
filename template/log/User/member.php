<?php include(__DIR__ . '/../common/header.php') ?>
<?php include(__DIR__ . '/../common/nav.php') ?>
<div class="container theme-showcase" role="main">
    <div class="page-header">
        <div style="float: right;line-height: 33px;"><a href="/log/User/add">添加成员</a></div>
        <h2>成员列表</h2>
    </div>
    <div class="row">
        <div class="col-md-12">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>邮箱</th>
                    <th>手机号</th>
                    <th>角色</th>
                    <th>创建时间</th>
                    <th>操作</th>
                </tr>
                </thead>
                <tbody>
                <?php
                if (!empty($members)) {
                    foreach ($members as $member) {
                        ?>
                        <tr>
                            <td><?= $member['email'] ?></td>
                            <td><?= $member['phone'] ?></td>
                            <td><?= \app\log\model\UserModel::getRoles($member['role']) ?></td>
                            <td><?= date('Y-m-d H:i:s', $member['createTime']) ?></td>
                            <td><a href="/log/User/update?email=<?= urlencode($member['email']) ?>">修改</a> | <a
                                        href="/log/User/delete?email=<?= urlencode($member['email']) ?>">删除</a></td>
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
