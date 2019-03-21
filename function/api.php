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
    if( true === $_SERVER['IS_AJAX'] )//=== $_SERVER['IS_AJAX']
    {
        echo json_encode($data,JSON_UNESCAPED_UNICODE );
        return;
    }
    if( false === isDebugApi() )
    {
        echo '<pre style="font-size:22px">'.
            json_encode($data,JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ).
            '</pre>';
    }
    else
    {
        say($debugMsg,$data);
    }
}
