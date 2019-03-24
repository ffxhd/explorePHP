<?php
$config = array(
    'host'=> true ===IS_LOCAL ? 'test.swoole.local' : '139.180.147.165',//test.swoole.local
    'developer_host_ip_arr'=>[
        '192.168.56.1'
    ],
    'favicon_ico'=>ROOT.'/favicon.ico',
    'session'=>[
        'name'=>'PHPSESSID',
        'expireTime'=>  60 * 60 * 4,//session的过期时间(秒）,
        'redis_db'=> 0,
    ],
    //
    'defaultAction'=>[
        'method'=>'index',
        'controller'=>'Index'
    ],

    //必需
    'db_config'=>array(
        'host'   =>'127.0.0.1',
        'user'   =>'ffxhd',
        'password'    =>'mysql_remote=ffxhd2018',
        'databaseName'   => true ===IS_LOCAL ? 'pvp':'honor_of_kings',
        'charset'=>'utf8',
        'class' => '\\onRequest\\core\\db\\MySQLiOOP'
    ),
    'redis'=>[
        'host'=>'127.0.0.1',
        'port'=>6379,
        'password'=>true ===IS_LOCAL ?'u1804-redis-psw':'u1810-redis-psw'
    ],

    //================================================
    'errorLevelExplain'=>[
        E_DEPRECATED => 'Deprecated',
        E_NOTICE => 'Notice',
        E_WARNING => 'Warning',
        E_STRICT => 'Strict',
        E_ERROR => 'Fatal Error',
        E_PARSE => '语法解析错误',
        E_CORE_ERROR => 'PHP初始化启动过程中发生致命错误',
        E_CORE_WARNING => 'PHP初始化启动过程中发生的警告 (非致命错误)',
    ]

);

