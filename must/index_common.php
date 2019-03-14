<?php
date_default_timezone_set('PRC');//PRC，中华人民共和国，People's Republic of China
$get_magic_quotes_gpc_result = get_magic_quotes_gpc();
const OnRequestPath = ROOT.'/onRequest';
require ROOT . '/must/Autoload.php';
require ROOT . '/vendor/autoload.php';
$functionsPath = ROOT.'/function';
$arr = scandir($functionsPath);
unset($arr[0],$arr[1]);
foreach($arr as $item)
{
    require $functionsPath.'/'.$item;
}
unset($arr,$functionsPath);

//检查扩展
if( false === class_exists('Redis'))
{
    echo '未发现redis扩展---然鹅，管理 $_SESSION 需要连接 redis',
    '（ $_SESSION的原生行为在swoole的http server中不起作用，',
    '所以需要接管session ）',PHP_EOL,PHP_EOL;
}
