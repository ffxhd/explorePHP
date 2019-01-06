<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/5
 * Time: 16:36
 */
require '/home/ffxhd/samba_share/swooleNoob/function/function_debug.php';
$waitTime = [
    'order_desk'=>3000,//ms
    'order_dishes'=>4500,
    'order'=>2000,
];
//-----------------------------------------------------
say('fpm无yield方式--点菜数据保存环节开始');
$startTime = microtime(true);
//
say("即将插入【订单表】");
sleep($waitTime['order']/1000);
say("插入【订单表】完成");
//
say('即将插入【订单-餐桌表】');
sleep($waitTime['order_desk']/1000);
say('插入【订单-餐桌表】完成');
//
$mean = '菜品';
say("即将插入【订单-{$mean}表】");
sleep($waitTime['order_dishes']/1000);
say("插入【订单-{$mean}表】完成");
//
$endTime = microtime(true);
$consumeTime = $endTime - $startTime;
say('点菜数据保存环节结束'." 耗时：".$consumeTime);

//-----------------------------------------------------

say('swoole方式--点菜数据保存环节开始');
$startTime = microtime(true);

say("swoole--即将插入【订单表】");
sleep($waitTime['order']/1000);
say("swoole--插入【订单表】完成");
go(function(){
    global $waitTime;
    say('swoole--即将插入【订单-餐桌表】');
    \Swoole\Coroutine::sleep($waitTime['order_desk']/1000);
    say('swoole--插入【订单-餐桌表】完成');
});
go(function()
{
    //
    global $waitTime;
    $mean = '菜品';
    say("swoole--即将插入【订单-{$mean}表】");
    \Swoole\Coroutine::sleep($waitTime['order_dishes']/1000);
    say("swoole--插入【订单-{$mean}表】完成");
    global $startTime;
    $endTime = microtime(true);
    $consumeTime = $endTime - $startTime;
    say('swoole--点菜数据保存环节结束'." 耗时：".$consumeTime);
    //
});
