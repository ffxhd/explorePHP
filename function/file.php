<?php
/**
 * Created by PhpStorm.
 * User: 飞飞小坏蛋
 * Date: 2017/7/12
 * Time: 14:37
 */

//-----------文件----------------------------------------------------------------------

function getFileLastModifiedTime( $file )
{
    return filemtime( $file );
}

function getVersionNumber( $filePartStr )
{
    $file = $_SERVER['DOCUMENT_ROOT'].$filePartStr;
    $dateTime = file_exists($file) ? getFileLastModifiedTime($file) : '未知版本时间';
    return date('H:i:s.Y-m-d',$dateTime);
}

function fileWith_v( $filePartStr )
{
    $file = $_SERVER['DOCUMENT_ROOT'].$filePartStr;
    $dateTime = file_exists($file) ? getFileLastModifiedTime($file) : 'file_v';
    echo $filePartStr.'?v='.date('H:i:s.Y-m-d',$dateTime);
}

function html_pic_root()
{
    $scheme =   $_SERVER['REQUEST_SCHEME'];
    $serverName =   $_SERVER['SERVER_NAME'];
    return "{$scheme}://$serverName/";
}

//删除文件
function deleteFiles( $files )
{
    $root = $_SERVER['DOCUMENT_ROOT'];
    $files = is_array($files)? $files :array( $files );
    foreach( $files as $k =>$file )
    {
        if(!$file)
        {
            continue;
        }
        $file =  $root.'/'.$file;
        if( file_exists( $file))
        {
            unlink( $file );
        }
    }
}

