# 介绍

这是一个基于[Hyperf](https://github.com/hyperf/hyperf)开发的客服聊天demo.

# 环境要求

建议使用Docker运行，[hyperf\hyperf](https://hub.docker.com/r/hyperf/hyperf)有现成的环境。

自己的环境需要满足以下配置：
 - PHP >= 7.2
 - Swoole PHP extension >= 4.4，and Disabled `Short Name`
 - OpenSSL PHP extension
 - JSON PHP extension
 - PDO PHP extension （If you need to use MySQL Client）
 - Redis PHP extension （If you need to use Redis Client）
 - Protobuf PHP extension （If you need to use gRPC Server of Client）

# 安装

## 获取代码：
```
git clone git@github.com:xinxinyue/xinyue-im.git
```

##安装组件
```
composer install
```

##导入数据库
导入im.sql文件，使用时只有一个msg表是存储聊天记录的，user和admin都可以替换为自己的

##环境变量
复制.env.example 为.env并修改配置项，需要配置redis，客服用户绑定关系、登录状态等都是redis存储。

#启动
项目根目录执行：
```
php bin/hyperf.php start
```
将会监听两个端口：9501是http服务负责登录等，9502是websocket服务。


#演示
此项目是学习使用，并没有用到生产环境中，仅供参考，另外提供前端[demo](https://github.com/xinxinyue/vue-cs-chat)演示

##演示效果
###用户登录

###用户界面

###管理员登录

###管理员界面
