#!/usr/bin/env bash
echo 正在安装
composer update
echo 正在创建数据文件
mkdir ./storge
echo 正在创建数据库
awk 'BEGIN { cmd="cp -i ./database.db ./storge/."; print "n" |cmd; }'