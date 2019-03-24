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
    $ip = $_SERVER['x-real-ip'];
    if( true === IS_LOCAL)
    {
        $data['sqlArr'] = DB::fetchSqlArr();
        $data['ip'] = $ip;
    }
    DB::cleanSqlArr();
    global $config;
    if( true ===in_array($ip,$config['developer_host_ip_arr']))
    {
        if( false === $_SERVER['IS_AJAX'] )
        {
            $str = json_encode($data,JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT );
            echo '<pre style="font-size:22px">'.$str . '</pre>';
            return true;
        }
    }
    $ajaxStr = json_encode($data,JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    echo $ajaxStr;
    return true;
}
