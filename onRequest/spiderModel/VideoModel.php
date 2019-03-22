<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/19
 * Time: 19:34
 */

namespace onRequest\spiderModel;
use must\DB;
use onRequest\core\page;

class VideoModel
{
    public $videoConfig = [
        'table' =>'honor_movies',
        'fieldsArr'=>[
            'vid' => 's',
            'title' => 's',
            'link' => 's',
            'img_src' => 's',
            'detail_url' => 's',
            'author' => 's',
            'duration' => 's',
            'counts' => 's',
            'upload_time' => 's',
            'introduction' => 's',
            'like_count' => 's',
            'game_name' => 's',
            'author_count' => 's',
            'author_src' => 's',
        ],
        /*'imgField'=>"concat('https://game.gtimg.cn/images/yxzj/img201606/heroimg/'".
            ",`ename`,'/',`ename`,'.jpg') as `hero_avatar`",*/
    ];

    public function searchSql($where,$otherField = '')
    {
        $where = '' === $where ? '': "where {$where}";
        $sqlBase = $this->searchFieldsNoWhere($otherField);
        return "{$sqlBase} {$where}";
    }

    protected function searchFieldsNoWhere($otherField = '')
    {
        $table = $this->videoConfig['table'];
        $fieldsArr = $this->videoConfig['fieldsArr'];
        $fields = joinFieldsToSelect($fieldsArr);
        //$imgField = $this->heroConfig['imgField'];
        return "select {$fields} {$otherField} from `{$table}`";//,{$imgField}
    }

    public function getAllByPage($where,$p,$pageSize,$orderBy='',$userId=null,$joinType='left')
    {
        $table = $this->videoConfig['table'];
        $userId = $userId === null ? 'null' : $userId;
        $whereCount = '' === $where ? '': "where {$where}";
        $rowsField = 'totalRows';
        $join = " as a  {$joinType} join 
        (select `movie_id` from `user_like_movie` where `user_id` = {$userId}) as b
         on a.`vid` = b.`movie_id` ";
        $sqlCount = "select count(`vid`) as `{$rowsField}` from `{$table}` {$join} {$whereCount}";
        $totalRows = DB::findResultFromTheInfo($sqlCount,$rowsField);
        //say('$totalRows',$totalRows);
        $totalPage = page::getTotalPage($totalRows,$pageSize);
        //say('$totalPage',$totalPage);
        $offset = page::getOffsetByPage($p,$totalPage,$pageSize);
        //say('$offset',$offset);
        //
        $otherField = ",if(b.`movie_id`=a.`vid`,1,0) as `is_like`";
        $sqlPart = $this->searchFieldsNoWhere($otherField);
        $orderBy = $orderBy === ''? '' : $orderBy;
        $where = '' === $where ? '': "where {$where}";
        $sql = "{$sqlPart} {$join} {$where} {$orderBy} limit {$offset},{$pageSize}";
        $list = DB::findAll($sql);
        $data = [
            'list' => $list,
//                'p' => $p,
//                'pageSize' => $pageSize,
            'totalPage' => $totalPage,
            'totalRows' => intval($totalRows),
        ];
        if( true === IS_LOCAL)
        {
            $data['sqlCount'] = $sqlCount;
            $data['sql'] = $sql;
        }
        return  $data;
    }

    public function getRandom($pageSize)
    {
        $table = $this->videoConfig['table'];
        $where = "id >= (SELECT floor(RAND() * (SELECT MAX(id) FROM `{$table}`)))";
        $p = 1;
        return $this->getAllByPage($where,$p,$pageSize);
    }

    public function getMostHotVideos($pageSize,$where = '')
    {
        $orderBy = 'order by  `counts` desc';
        $sqlPart = $this->searchSql($where);
        $sql = "{$sqlPart} {$orderBy} limit 0,{$pageSize}";
        $data = DB::findAll($sql);
        if( true === IS_LOCAL)
        {
            $data['sql'] = $sql;
        }
        return $data;
    }

    public function getInfoWithRelatedVideos($id,$userId=null)
    {
        $userId = $userId === null ? 'null' : $userId;
        $table = $this->videoConfig['table'];
        $sqlArr = [];
        $fieldsArr = $this->videoConfig['fieldsArr'];
        $fields = joinFieldsToSelect($fieldsArr);
        //$sqlArr['info'] = "select {$fields}from  `{$table}` where `vid` = {$id}";
        $sqlArr['info'] = "select {$fields}, if(b.`movie_id` = a.`vid`,1,0) as is_like
from  `{$table}` as a 
left join ( select movie_id from user_like_movie where user_id = {$userId} ) as b
 on a.vid = b.movie_id  where a.`vid` = {$id}";
        $data['info'] = DB::findOne($sqlArr['info']);
        $data['info']['is_like'] = $data['info']['is_like'] === '1';

        $type =  $data['info'] ['game_name'];
        $table = 'related_movie';
        $sqlArr['related'] = "select {$fields}from  `{$table}` where `game_name` = '{$type}'";
        $data['related'] = DB::findAll($sqlArr['related']);
        //
        //$data = DB::multiFind($sqlArr,false);
        $data['sql'] = DB::fetchSqlArr();
        return $data;
    }
}
