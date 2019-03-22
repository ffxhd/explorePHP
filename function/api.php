<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/13
 * Time: 10:57
 */
function isDebugApi()
{
    $debug = getItemFromArray($_GET,'debug','');
    return $debug === 'true' || $debug === '1';
}

function creatApiData($errorCode,$msg,$data = [])
{
    return  [
        'errorCode' => $errorCode,
        'msg'=>$msg,
        'data'=>$data
    ];
}

use \must\DB;
function outputApiData($data, $debugMsg = '')
{
    if( true === IS_LOCAL)
    {
        $data['sqlArr'] = DB::fetchSqlArr();
    }
    global $config;
    $ajaxStr = json_encode($data,JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if( true === $_SERVER['IS_AJAX'] )
    {
        echo $ajaxStr;
        return true;
    }
    echo $ajaxStr;
    return true;
    //say('$_SERVER[\'x-real-ip\']',$_SERVER['x-real-ip']);
    //say('$config[\'local_hostOnly_ip\'] ',$config['local_hostOnly_ip'] );
    /*if( $_SERVER['x-real-ip'] === $config['local_hostOnly_ip'] && false === $_SERVER['IS_AJAX'] )
    {
        $str = json_encode($data,JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
        echo '<pre style="font-size:22px">'.$str . '</pre>';
    }
    else//true === $_SERVER['IS_AJAX']//本意是仅限ajax
    {
        echo $ajaxStr;
    }*/
}
