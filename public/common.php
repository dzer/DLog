<?php
if (isset($argv)) {
    $_GET['r'] = $argv[1];
    // 加载框架引导文件
    require __DIR__ . '/../vendor/dzer/mll-framework/main.php';
}
