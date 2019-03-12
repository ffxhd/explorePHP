<?php
$config = array(
    'host'=> true ===IS_LOCAL ? 'test.swoole.local' : '139.180.147.165',
    'favicon_ico'=>ROOT.'/favicon.ico',
    'session'=>[
        'name'=>'PHPSESSID',
        'expireTime'=>  60 * 3,//session的过期时间(秒）,
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
        'password'=>'u1804-redis-psw'
    ]

    //================================================


);

