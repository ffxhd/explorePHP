<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/11/11
 * Time: 21:37
 */
const ROOT = __DIR__;
require ROOT.'/function/function_debug.php';
require ROOT.'/must/Autoload.php';
//
$shareTable = new \Swoole\Table(8);
$shareTable->column('fd',\Swoole\Table::TYPE_INT);
$shareTable->column('userName',\Swoole\Table::TYPE_STRING,20);
$shareTable->create();
//创建websocket服务器对象，监听0.0.0.0:9502端口
$server = new  \Swoole\WebSocket\Server("0.0.0.0", 9502);
$callbackObj = new \serverCallBack\WebSocketServer();
$server->on('open', [ $callbackObj, 'onOpen' ]);
$server->on('message', [ $callbackObj, 'onMessage' ]);
$server->on('close', [ $callbackObj, 'onClose' ]);
$server->start();