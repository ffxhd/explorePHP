<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/21
 * Time: 16:48
 */

namespace onRequest\spiderModel;
use must\DB;
use onRequest\spiderModel\VideoModel;
class Like
{
    protected function likeItOrNot($itsId, $like, $userId, $likeTable, $whereIdField,
                                   $likeField,$userLikeTable)
    {
        $isAdd = $like > 0;
        //like_count总数增加
        $change = true === $isAdd ? '+1' : '-1';
        $vToSet = true == $isAdd ? "`like_count` {$change}" :
            "if(like_count = 0, 0,like_count-1) ";
        $sql = "update `{$likeTable}` set `like_count` = {$vToSet} where `{$whereIdField}` = {$itsId}";
        DB::query($sql);
        //say('$result_hero',$result_hero);
        $affectedRows = DB::getAffectedRows();
        if( $affectedRows === 0)
        {
            //return  $operation.'失败：该英雄不存在';
            return  true;
        }
        //插入记录或者删除记录
        if( true === $isAdd)
        {
            $result_user = DB::insert($userLikeTable,[
                'user_id'=>$userId,
                $likeField=>$itsId
            ]);
        }
        else
        {
            $result_user = DB::delete($userLikeTable,[
                "`user_id` = {$userId}",
                "`{$likeField}` = {$itsId}"
            ]);
        }
        return $result_user;
    }

    public function likeTheHeroOrNot($itsId,$like,$userId)
    {
        $table = 'honor_main';
        $whereIdField = 'ename';
        $likeField = 'hero_id';
        $userLikeTable = 'user_like_hero';
        return $this->likeItOrNot($itsId, $like, $userId, $table,
            $whereIdField, $likeField,$userLikeTable);
    }

    public function likeTheMovieOrNot($itsId, $like, $userId)
    {
        $obj = new VideoModel();
        $table = $obj->videoConfig['table'];
        $whereIdField = 'vid';
        $likeField = 'movie_id';
        $userLikeTable = 'user_like_movie';
        return $this->likeItOrNot($itsId, $like, $userId, $table,
            $whereIdField, $likeField,$userLikeTable);
    }


}
