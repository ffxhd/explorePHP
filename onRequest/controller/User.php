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
use onRequest\spiderModel\Like;
use onRequest\spiderModel\PvpDb as pvpModel;
use onRequest\controller\Pvp;
use onRequest\spiderModel\VideoModel;
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

    public static function isHaveLogin()
    {
        //say('$_SESSION',$_SESSION);
        $userInfo = getItemFromArray($_SESSION,'userInfo',[]);
        return  false === empty($userInfo);
    }

    public static function  getUserId()
    {
        return  $_SESSION['userInfo']['id'];
    }

    public function  likeTheHeroOrNot()
    {
        if( false === User::isHaveLogin())
        {
            $data = creatApiData(1,"未登录...");
            return  outputApiData($data);
        }
        //
        $eName = getItemFromArray($_GET,'eName',0);
        if( $eName < 1)
        {
            $data = creatApiData(1,'点赞或者取消点赞，用参数eName指定英雄的id');
            return outputApiData($data);
        }
        $eName = intval($eName);
        //
        $like = getItemFromArray($_GET,'like',0);
        $like = intval($like);
        if( $like === 0)
        {
            $data = creatApiData(1,'like参数为0，不知是要点赞还是取消点赞...');
            return outputApiData($data);
        }
        $userId = User::getUserId();
        $obj = new Like();
        $operation = $like > 0 ? '点赞':'取消点赞';
        $apiData = $obj->likeTheHeroOrNot($eName,$like,$userId);
        //
        $msg = is_string($apiData)? $apiData : "{$operation}成功";
        $data = creatApiData(0,$msg, $apiData);
        return outputApiData($data);
    }

    public function  likeTheMovieOrNot()
    {
        if( false === User::isHaveLogin())
        {
            $data = creatApiData(1,"未登录...");
            return  outputApiData($data);
        }
        //
        $vid = getItemFromArray($_GET,'vid',0);
        if( $vid < 1)
        {
            $data = creatApiData(1,'点赞或者取消点赞，用参数vid指定视频的id');
            return outputApiData($data);
        }
        $vid = intval($vid);
        //
        $like = getItemFromArray($_GET,'like',0);
        $like = intval($like);
        if( $like === 0)
        {
            $data = creatApiData(1,'like参数为0，不知是要点赞还是取消点赞...');
            return outputApiData($data);
        }
        $userId = User::getUserId();
        $obj = new Like();
        $operation = $like > 0 ? '点赞':'取消点赞';
        $apiData = $obj->likeTheMovieOrNot($vid,$like,$userId);
        //
        $msg = is_string($apiData)? $apiData : "{$operation}成功";
        $data = creatApiData(0,$msg, $apiData);
        return outputApiData($data);
    }

    public function getMyHeroList()
    {
        if( false === self::isHaveLogin())
        {
            $data = creatApiData(1,"未登录...");
            return  outputApiData($data);
        }
        $userId = User::getUserId();
        //
        /*$field = 'hero_id';
        $sql = "select `{$field}` from `user_like_hero`  where `user_id` = {$userId}";
        $list = DB::findAll($sql);
        $idArr = array_column($list,$field);
        $ids = implode(',',$idArr);
        $userWhere = "`ename` in ({$ids})";
        $baseWhere = Pvp::heroesWhere($_REQUEST);
        $where = $baseWhere === '' ?  $userWhere : " {$userWhere} and ";*/
        $where = '';
        //
        $p = getItemFromArray($_GET,'p',1);
        $p = intval($p);
        $pageSize = getItemFromArray($_GET,'pageSize',10);
        $pageSize = intval($pageSize);
        $obj = new  pvpModel();
        $apiData = $obj->getHeroesList($where,$p,$pageSize,'',$userId,'inner');
        $isSuccess = false === empty($apiData['list']) ;
        $resMsg = true === $isSuccess ? '成功':'失败';
        $data = creatApiData(0,"获取英雄列表数据{$resMsg}", $apiData);
        return outputApiData($data,__FUNCTION__.'--所有英雄');
    }

    public function getMyVideoList()
    {
        $p = getItemFromArray($_GET,'p',1);
        $p = intval($p);
        $pageSize = getItemFromArray($_GET,'pageSize',10);
        $pageSize = intval($pageSize);
        //
        $userId = User::getUserId();
        $where = '';
        $obj = new  VideoModel();
        $apiData = $obj->getAllByPage($where,$p,$pageSize,'',$userId,'inner');
        $isSuccess = false === empty($apiData['list']) ;
        $resMsg = true === $isSuccess ? '成功':'失败';
        $data = creatApiData(0,"获取列表数据{$resMsg}", $apiData);
        return outputApiData($data,__FUNCTION__.'--所有视频');
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
        $sql = "select `user_name`,{$avatarField},`register_time`,`age`,`sex`,
       `nickname`,`signature` from `user` where `id` = {$userId}";
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
        $nickname = getWashedData($_POST,'nickname',null);
        $signature = getWashedData($_POST,'signature',null);
        $formalDir = self::$avatarFormalDir;
        $avatarForDb = "{$formalDir}/{$avatar}";
        $arr = [];
        if( $age!== null)
        {
            $arr['age'] = $age;
        }
        if( $sex!== null)
        {
            $arr['sex'] = $sex;
        }
        if( $nickname!== null)
        {
            $arr['nickname'] = $nickname;
        }
        if( $signature!== null)
        {
            $arr['signature'] = $signature;
        }
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
