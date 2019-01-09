<?php
const ROOT = __DIR__;
require ROOT . '/must/index_common.php';
define('IS_LOCAL', strpos($config['host'],'local') !== false);
$isCLi = isRunInCLI();
if(false === $isCLi )
{
    say('不是cli');
    return false;
}

if( true === IS_LOCAL && false === function_exists('inotify_init'))
{
    echo '未发现inotify扩展( 安装及使用详见 http://php.net/inotify )---然鹅，',
         '本地开发，为了提升开发体验，需要自动热加载onRequest目录中的文件，'.
        '需要利用此扩展监听onRequest目录'.PHP_EOL,PHP_EOL;
}
//$sessionManager = new \session\SessionManager();
/*$sessionManager->prefix = 'swooleTest';
$_SESSION['swoole_userName'] = [
    'nickname'=>'飞飞小坏蛋',
    'role'=>'超级管理员',
    'power' =>[1,2,3,4]
];
exit;*/
//
/*Http\Server对Http协议的支持并不完整，建议仅作为应用服务器。并且在前端增加Nginx作为代理
 */
$server = new  \Swoole\Http\Server($config['host'], 9501);
$server->set([
    'document_root' => ROOT.'/public',
    'enable_static_handler' => true,
]);
//
/*if( true === IS_LOCAL )
{
    require ROOT.'/listenFile_1.php';
}*/
$callbackObj = new \serverCallBack\HttpServer();
$server->on('WorkerStart', [ $callbackObj, 'onWorkerStart' ]);
$server->on('WorkerError', [ $callbackObj, 'onWorkerError' ]);
$server->on('Connect', [ $callbackObj, 'onConnect' ]);
$server->on('Close', [ $callbackObj, 'onClose' ]);
$server->on('Request', [ $callbackObj, 'onRequest' ]);
$server->start();