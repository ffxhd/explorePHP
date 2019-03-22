<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/22
 * Time: 9:34
 */

namespace onRequest\controller;
use onRequest\controller\User;
use onRequest\spiderModel\Comment as CommentModel;
use must\DB;
class Comment
{
    public function addCommentForMovie()
    {
        if( false === User::isHaveLogin())
        {
            $data = creatApiData(1,"未登录...");
            return  outputApiData($data);
        }
        $userId = User::getUserId();
        $source = $_POST;
        $content = getWashedData($source,'content','');
        if( $content === '')
        {
            $data = creatApiData(1,"评论内容不能为空");
            return  outputApiData($data);
        }
        $movieId = getWashedData($source,'movieId',0);
        $movieId = intval($movieId);
        if( $movieId === 0)
        {
            $data = creatApiData(1,"视频编号vid不能为空");
            return  outputApiData($data);
        }
        $commentId_BeRelied = getWashedData($source,'commentId',0);
        $commentId_BeRelied = intval($commentId_BeRelied);
        $arr = [
            'user_id'=>$userId,
            'movie_id'=>$movieId,
            'content' =>$content
        ];
        if( $commentId_BeRelied > 0)
        {
            $arr['reply_this_id'] = $commentId_BeRelied;
        }
        $result = DB::insert('user_comment_movie',$arr);
        $isSuccess = $result > 0;
        //say('欢迎添加评论');
        $describe = true === $isSuccess ? '成功':'失败';
        $errCode = true === $isSuccess ? 0 : 1;
        $data = creatApiData($errCode,"评论{$describe}",[]);
        return outputApiData($data);
    }

    public function commentListOfMovie()
    {
        $source = $_GET;
        $movieId = getWashedData($source,'movieId',0);
        $movieId = intval($movieId);
        if( $movieId === 0)
        {
            $data = creatApiData(1,"视频编号vid不能为空");
            return  outputApiData($data);
        }
        $p = getItemFromArray($_GET,'p',1);
        $p = intval($p);
        $pageSize = getItemFromArray($_GET,'pageSize',10);
        $pageSize = intval($pageSize);
        $obj = new CommentModel();
        $apiData = $obj->commentList_movie($p,$pageSize,$movieId);
        $isSuccess = false === empty($apiData['list']) ;
        $resMsg = true === $isSuccess ? '成功':'失败';
        $data = creatApiData(0,"获取列表数据{$resMsg}", $apiData);
        return outputApiData($data);
    }
}
