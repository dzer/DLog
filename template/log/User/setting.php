<?php include(__DIR__ . '/../common/header.php') ?>
<?php include(__DIR__ . '/../common/nav.php') ?>
<div class="container theme-showcase" role="main">
    <div class="page-header">
        <h2>个人设置</h2>
    </div>
    <div class="row">
        <div class="col-md-12">
            <form class="form-horizontal" enctype="multipart/form-data" method="POST">
                <div class="form-group">
                    <label for="email" class="col-md-2 control-label">邮箱</label>
                    <div class="col-md-5">
                        <input type="email" readonly disabled name="email" value="<?= $info['email'] ?>" required class="form-control" id="email" placeholder="美乐乐企业邮箱">
                    </div>
                </div>
                <div class="form-group">
                    <label for="password" class="col-md-2 control-label">密码</label>
                    <div class="col-md-5">
                        <input type="password" name="password" class="form-control" id="password" placeholder="不修改时请置空">
                    </div>
                </div>
                <div class="form-group">
                    <label for="repeat_password" class="col-md-2 control-label">确认密码</label>
                    <div class="col-md-5">
                        <input type="password" name="repeat_password" class="form-control" id="repeat_password" placeholder="不修改时请置空">
                    </div>
                </div>
                <div class="form-group">
                    <label for="phone" class="col-md-2 control-label">手机号</label>
                    <div class="col-md-5">
                        <input type="tel" name="phone" value="<?= $info['phone'] ?>" class="form-control" id="phone" placeholder="phone">
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-5">
                        <?php if (!empty($errorMsg)) { ?>
                            <div class="alert alert-danger alert-dismissible" role="alert">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                                            aria-hidden="true">&times;</span></button>
                                <?= $errorMsg ?>
                            </div>
                        <?php } ?>
                        <?php if (!empty($successMsg)) { ?>
                            <div class="alert alert-success alert-dismissible" role="alert">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                                            aria-hidden="true">&times;</span></button>
                                <?= $successMsg ?>
                            </div>
                        <?php } ?>
                        <button type="submit" class="btn btn-default">保存</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div> <!-- /container -->
<?php include(__DIR__ . '/../common/footer.php') ?>
