<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017-10-16
 * Time: 11:53
 */

namespace onRequest\controller;
//use \core\DB;
use  \onRequest\core\upload;
class Index{
    public function index()
    {
        include OnRequestDir.'/public/index.html';
       say('hello, i am swoole index from Index ctrl
<br/>');
    }

    /*public function coroutine()
    {

    }*/

    public function upload()
    {
        say('$_FILES',$_FILES);
        upload::uploadFile($_FILES['thePictures'],OnRequestDir.'/public/uploads');
        $noticeArray = upload::$noticeArray;
        $msg = upload::$mes;
        say('$noticeArray',$noticeArray,'$msg',$msg);
    }

    public function login()
    {
        $_SESSION['user'] = [
            'name' => $_POST['userName'],
        ];
        $arr = [
            'isAjax'=> $_SERVER['IS_AJAX']  ? 'yes' : 'no',
            'status'=>1,
            'data'=>$_POST,
            'msg'=> 'done for ajax',
        ];
        $s = get_defined_vars();
        say('say done for ajax',$s);
        say('$arr',$arr,'login-$_SESSION',$_SESSION);
       // echo json_encode($arr,JSON_UNESCAPED_UNICODE);
    }

    public function getSessionInfo()
    {
        say('sessionUserInfo', $_SESSION);
    }

}