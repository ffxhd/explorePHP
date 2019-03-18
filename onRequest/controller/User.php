<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/13
 * Time: 10:46
 */

namespace onRequest\controller;
use must\DB;
use  \onRequest\core\upload;
use  \onRequest\core\dir;
use \must\PC;
class User
{
    public static $avatarTempDir = 'temp_uploads';
    public static $avatarFormalDir = '/uploads';
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
        $extraData = [
            'session_id'=>$_SERVER['session_id']
        ];
        $data = array_merge($data,$extraData);
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
        $formalDir = self::$avatarFormalDir;
        $avatarField = "concat('onRequest/public',`avatar`) as `avatar`";
        $sql = "select `user_name`,{$avatarField},`register_time`,`age`,`sex` 
from `user` where `id` = {$userId}";
        $info = DB::findOne($sql);
        $isSuccess = false === empty($info) ;
        $resMsg = true === $isSuccess ? '成功':'失败';
        $data = creatApiData(0,"获取用户信息{$resMsg}", $info);
        outputApiData($data);
    }

    public function uploadAvatar()
    {
        /*say('$_FILES[\'avatar\']',$_FILES['avatar']);
        return;*/
        /*go(function(){
            echo '正在处理..';
            sleep(2);
            echo '正在处理2..';
            sleep(2);
            echo '正在处理3..';
        });*/
        /*PC::outputResponse('正在处理..') ;
        sleep(5);
        PC::outputResponse('正在处理2..') ;
        sleep(5);
        PC::outputResponse('正在处理3..') ;
        return;*/
        //say('server',$_SERVER);
        if( false === self::isHaveLogin())
        {
            $data = creatApiData(1,"未登录...");
            return  outputApiData($data);
        }
        $files = getItemFromArray($_FILES,'avatar',[]);
        if( true === empty($files))
        {
            $data = creatApiData(1,'没有上传文件');
            return outputApiData($data);
        }
        $path = OnRequestPath.'/public/'.self::$avatarTempDir;
        if( false === is_dir($path))
        {
            dir::createFolder($path);
        }
        $uploadedFiles = upload::uploadFile($files,$path);
        foreach ($uploadedFiles as $seq => $file)
        {
            $uploadedFiles[$seq] = $file;
        }
        $noticeArray = upload::$noticeArray;
        $apiData = [
            'path'=>'/onRequest/public/'.self::$avatarTempDir.'/',
            'successFiles' => $uploadedFiles,
            'noticeArr' => $noticeArray
        ];
        $data = creatApiData(0,'上传头像成功',$apiData);
        outputApiData($data);
    }

    public function modifyUserInfo()
    {
        if( false === self::isHaveLogin())
        {
            $data = creatApiData(1,"未登录...");
            return  outputApiData($data);
        }
        $userId = $_SESSION['userInfo']['id'];
        $avatar = getWashedData($_POST,'avatar','');
        $age = getWashedData($_POST,'age',null);
        $sex = getWashedData($_POST,'sex',null);
        $formalDir = self::$avatarFormalDir;
        $avatarForDb = "{$formalDir}/{$avatar}";
        $arr = [
            'age'=>$age,
            'sex'=>$sex,
        ];
        $isExistAvatar = $avatar !== '';
        if( true === $isExistAvatar)
        {
            $arr['avatar'] = $avatarForDb;
        }
        $where = "`id`={$userId}";
        $result = DB::update('user',$arr,$where);
        $isSuccess = $result > 0 ;
        if( true === $isSuccess)//移动头像图片到正式的目录
        {
            if( true === $isExistAvatar)
            {
                $tempDir = self::$avatarTempDir;
                $file = OnRequestPath."/public/{$tempDir}/{$avatar}";
                if( true === file_exists($file))
                {
                    rename($file, OnRequestPath."/public/{$avatarForDb}");
                }
            }
        }
        $resMsg = true === $isSuccess ? '成功':'失败';
        $errorCode = true === $isSuccess ? 0 : 1000;
        $data = creatApiData($errorCode,'修改资料'.$resMsg);
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
