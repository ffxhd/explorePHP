<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/22
 * Time: 9:34
 */

namespace onRequest\controller;
use onRequest\controller\User;


class Comment
{
    public function addCommentForMovie()
    {
        if( false === User::isHaveLogin())
        {
            $data = creatApiData(1,"未登录...");
            return  outputApiData($data);
        }
        //say('欢迎添加评论');
        $data = creatApiData(0,'评论成功',[]);
        return outputApiData($data);
    }
}
