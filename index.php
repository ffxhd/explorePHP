<?php
session_start();
header('Content-type:text/html;charset=utf-8');
define('ROOT',$_SERVER['DOCUMENT_ROOT']);
require ROOT . '/must/index_common.php';
define('IS_LOCAL', strpos($_SERVER['SERVER_NAME'],'local') !== false);
require ROOT . '/must/config.php';
isAjaxOrNot($_SERVER);
try{
    \must\PC::run($config);
}
catch (\Error $e)
{
    $errFile = $e->getFile();
    $errLine = $e->getLine();
    $errMsg = $e->getMessage();
    $errTrace = $e->getTrace();
    $errCode = $e->getCode();
    /*$previous = $e->getPrevious();
    say($previous,'$previous');*/
    throw_phpError($errCode,$errMsg, $errFile,$errLine,$errTrace);
}
