<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar"
                    aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="#">DLog调试性能分析系统</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
            <ul class="nav navbar-nav">
                <?php
                $action = \Mll\Mll::app()->request->getAction();

                ?>
<!--                <li class="--><?//= $action == 'index' ? 'active' : ''?><!--"><a href="/log/Index/index">仪表盘</a></li>-->
                <li class="<?= $action == 'just' ? 'active' : ''?>"><a href="/log/History/just">历史数据</a></li>
<!--                <li class="--><?//= $action == 'rank' ? 'active' : ''?><!--"><a href="/log/Index/rank">性能排行</a></li>-->
<!--                <li class="--><?//= $action == 'count' ? 'active' : ''?><!--"><a href="/log/Index/count">日志统计</a></li>-->
<!--                <li class="--><?//= $action == 'statistics' ? 'active' : ''?><!--"><a href="/log/Index/statistics">图表</a></li>-->
                <!--<li><a href="#">异常统计</a></li>-->
            </ul>
            <ul class="nav navbar-nav navbar-right">
                <li class="dropdown navbar-right">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true"
                       aria-expanded="false"><strong><?= $_SESSION['userInfo']['email'] ?? '' ?></strong> <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <li><a href="/log/User/setting">个人设置</a></li>
                        <?php
                            if (isset($_SESSION['userInfo']['role']) && $_SESSION['userInfo']['role'] == 'admin') {
                                echo '<li><a href="/log/User/member">成员列表</a></li>';
                            }
                        ?>
                        <li role="separator" class="divider"></li>
                       <!-- <li class="dropdown-header">Nav header</li>-->
                        <li><a href="/log/User/logout">退出登录</a></li>
                    </ul>
                </li>
            </ul>
        </div><!--/.nav-collapse -->
    </div>
</nav>