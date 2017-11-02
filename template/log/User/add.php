<?php include(__DIR__ . '/../common/header.php') ?>
<?php include(__DIR__ . '/../common/nav.php') ?>
<div class="container theme-showcase" role="main">
    <div class="page-header">
        <div style="float: right;line-height: 33px;"><a href="/log/User/member">成员列表</a></div>
        <h2>添加成员</h2>
    </div>
    <div class="row">
        <div class="col-md-12">
            <form class="form-horizontal" enctype="multipart/form-data" method="POST">
                <div class="form-group">
                    <label for="email" class="col-md-2 control-label">邮箱</label>
                    <div class="col-md-5">
                        <input type="email" name="email" required class="form-control" id="email" placeholder="美乐乐企业邮箱">
                    </div>
                </div>
                <div class="form-group">
                    <label for="password" class="col-md-2 control-label">密码</label>
                    <div class="col-md-5">
                        <input type="password" name="password" required class="form-control" id="password" placeholder="Password">
                    </div>
                </div>
                <div class="form-group">
                    <label for="repeat_password" class="col-md-2 control-label">确认密码</label>
                    <div class="col-md-5">
                        <input type="password" name="repeat_password" required class="form-control" id="repeat_password" placeholder="Password">
                    </div>
                </div>
                <div class="form-group">
                    <label for="phone" class="col-md-2 control-label">手机号</label>
                    <div class="col-md-5">
                        <input type="tel" name="phone" class="form-control" id="phone" placeholder="phone">
                    </div>
                </div>
                <div class="form-group">
                    <label for="role" class="col-md-2 control-label">角色</label>
                    <div class="col-md-5">
                        <div class="radio">
                            <label><input type="radio" name="role" value="manager"> 管理员</label>
                            <label><input type="radio" name="role" checked="checked" value="member"> 普通成员</label>
                            <div class="label label-info">管理员才能查看请求参数</div>
                        </div>
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
                        <button type="submit" class="btn btn-default">保存</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div> <!-- /container -->
<?php include(__DIR__ . '/../common/footer.php') ?>
