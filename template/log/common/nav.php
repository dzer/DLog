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
                <li class="<?= $action == 'index' ? 'active' : ''?>"><a href="/log/Index/index">仪表盘</a></li>
                <li class="<?= $action == 'just' ? 'active' : ''?>"><a href="/log/Index/just">最近运行</a></li>
                <li class="<?= $action == 'rank' ? 'active' : ''?>"><a href="/log/Index/rank">性能排行</a></li>
                <!--<li><a href="#">异常统计</a></li>-->
                <!--<li><a href="#contact">Contact</a></li>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true"
                       aria-expanded="false">Dropdown <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                        <li><a href="#">Action</a></li>
                        <li><a href="#">Another action</a></li>
                        <li><a href="#">Something else here</a></li>
                        <li role="separator" class="divider"></li>
                        <li class="dropdown-header">Nav header</li>
                        <li><a href="#">Separated link</a></li>
                        <li><a href="#">One more separated link</a></li>
                    </ul>
                </li>-->
            </ul>
        </div><!--/.nav-collapse -->
    </div>
</nav>