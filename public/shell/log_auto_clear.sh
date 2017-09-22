#!/bin/bash
#权限
if [ `id -u` -ne 0 ];then
  echo "请使用root权限!"
  exit 1
fi
#锁文件path
log_name=/usr/local/nginx/html/DLog/runtime/log/log_lock.log
if [ ! -f $log_name ];then
date +%s > $log_name
fi

#超时时间差(秒)
time_more=120
#文件查找标识
file_name_mark=common
#php查找标识
php_mark=bin/php
#php路径
php_bin=/usr/local/php/bin/php
#log脚本文件
log_php_file=/usr/local/nginx/html/DLog/public/common.php
#路由
argv=log/Parse/pull
#判断是否超时
function checkTime {
  local log_time=`cat $log_name`
  local now=`date +%s`
  local diff=$[$now - $log_time]
  if [ $diff -gt $time_more ];then
    echo 1
  else
    echo 0
  fi
}

#清理所有readLog进程(理论上一个)
function killSome {
  `ps aux | grep -v grep | grep -v vim | grep -v vi | grep -v cat | grep $php_mark | grep $file_name_mark | gawk '{print $2}' | xargs -n1 kill -9`
  date +%s > $log_name
}

#main
if [ `checkTime` -eq 1 ];then
  killSome
fi 

$php_bin $log_php_file $argv &

