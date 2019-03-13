<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/13
 * Time: 10:46
 */

namespace onRequest\controller;

use must\DB;
class User
{
    public function  login()
    {
        $userName = $this->getUserNameFromSubmit();
        if( true === is_array($userName))
        {
            return  outputApiData($userName);
        }
        $password = $this->getPasswordFromSubmit();
        if( true === is_array($password))
        {
            return  outputApiData($password);
        }
        $pswField = 'password';
        $sql = "select `id`,`user_name`,`{$pswField}`,`avatar`,`register_time` 
from `user` where `user_name` = '{$userName}'";
        $info = DB::findOne($sql);
        if( true === empty($info))
        {
            $data = creatApiData(1,"此用户不存在...");
            return  outputApiData($data);
        }
        if( $password !== $info[$pswField])
        {
            $data = creatApiData(1,"密码不正确...");
            return  outputApiData($data);
        }
        unset($info[$pswField]);
        $_SESSION['userInfo'] = $info;
        $data = creatApiData(0,"登录成功", $info);
        outputApiData($data);
    }

    protected static function isHaveLogin()
    {
        //say('$_SESSION',$_SESSION);
        $userInfo = getItemFromArray($_SESSION,'userInfo',[]);
        return  false === empty($userInfo);
    }

    public function getUserInfo()
    {
        //say('self::isHaveLogin()',self::isHaveLogin());
        if( false === self::isHaveLogin())
        {
            $data = creatApiData(1,"未登录...");
            return  outputApiData($data);
        }
        $userId = $_SESSION['userInfo']['id'];
        $sql = "select `user_name`,`avatar`,`register_time` 
from `user` where `id` = {$userId}";
        $info = DB::findOne($sql);
        $isSuccess = false === empty($info) ;
        $resMsg = true === $isSuccess ? '成功':'失败';
        $data = creatApiData(0,"获取用户信息{$resMsg}", $info);
        outputApiData($data);
    }

    protected function getUserNameFromSubmit()
    {
        $userName = getItemFromArray($_POST,'userName','');
        if( $userName === '')
        {
            return creatApiData(1,'用户名不能为空');
        }
        return  addSlashesOrNot($userName);
    }

    protected function getPasswordFromSubmit()
    {
        $password = getItemFromArray($_POST,'password','');
        if( $password === '')
        {
            return creatApiData(2,'密码不能为空');
        }
        return addSlashesOrNot($password);
    }

    public function register()
    {
        $userName = $this->getUserNameFromSubmit();
        if( true === is_array($userName))
        {
            return  outputApiData($userName);
        }
        $password = $this->getPasswordFromSubmit();
        if( true === is_array($password))
        {
            return  outputApiData($password);
        }
        $isUnique = $this->isUniqueUserNameLogic($userName);
        if( false === $isUnique)
        {
            $data = creatApiData(999,"用户名【{$userName}】已经被注册了...");
            return  outputApiData($data);
        }
        $arr = [
            'user_name'=>$userName,
            'password'=>$password,
            'register_time'=>date('Y-m-d H:i:s',$_SERVER['REQUEST_TIME']),
        ];
        $result= DB::insert('user',$arr);
        $isSuccess = $result > 0 ;
        $resMsg = true === $isSuccess ? '成功':'失败';
        $errorCode = true === $isSuccess ? 0 : 1000;
        $data = creatApiData($errorCode,'注册'.$resMsg);
        outputApiData($data);
    }

    protected function isUniqueUserNameLogic($userName)
    {
        $sql = "select `user_name` from `user` where `user_name` = '{$userName}'";
        $info = DB::findOne($sql);
        return true === empty($info);
    }

    public function isUniqueUserName()
    {
        $userName = $this->getUserNameFromSubmit();
        if( true === is_array($userName))
        {
            return  outputApiData($userName);
        }
        $isUnique = $this->isUniqueUserNameLogic($userName);
        $resMsg = true === $isUnique ? '可用':'已存在...';
        $errorCode = true === $isUnique ? 0 : 1000;
        $data = creatApiData($errorCode,"用户名【{$userName}】{$resMsg}");
        outputApiData($data);
    }
}