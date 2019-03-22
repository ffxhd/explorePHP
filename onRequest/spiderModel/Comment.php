<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/22
 * Time: 16:07
 */

namespace onRequest\spiderModel;

use must\DB;
use onRequest\core\page;
class Comment
{
    public static $table = 'user_comment_movie';
    public function commentList_movie($p,$pageSize,$movieId)
    {
        $sqlBody = "from `user` as u 
inner join `user_comment_movie` as c on u.`id` = c.`user_id` 
where c.`movie_id` = {$movieId}";
        $rowsField = 'totalRows';
        $sqlCount = "select count(c.`id`) as `{$rowsField}` {$sqlBody}";
        $totalRows = DB::findResultFromTheInfo($sqlCount,$rowsField);
        $totalPage = page::getTotalPage($totalRows,$pageSize);
        $offset = page::getOffsetByPage($p,$totalPage,$pageSize);
        //
        $sql = "select u.`user_name`,c.`content` {$sqlBody} limit {$offset},{$pageSize}";
        $list = DB::findAll($sql);
        $data = [
            'list' => $list,
            'p' => $p,
            'pageSize' => $pageSize,
            'totalPage' => $totalPage,
            'totalRows' => intval($totalRows),
        ];
        return  $data;
    }
}
