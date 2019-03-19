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
    protected $videoConfig = [
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
    public function searchHeroSql($where)
    {
        $table = $this->videoConfig['table'];
        $fieldsArr = $this->videoConfig['fieldsArr'];
        $fields = joinFieldsToSelect($fieldsArr);
        //$imgField = $this->heroConfig['imgField'];
        $where = '' === $where ? '': "where {$where}";
        return "select {$fields}from `{$table}` {$where}";//,{$imgField}
    }

    public function getAllByPage($where,$p,$pageSize)
    {
        $table = $this->videoConfig['table'];
        $whereCount = '' === $where ? '': "where {$where}";
        $rowsField = 'totalRows';
        $sqlCount = "select count(`id`) as `{$rowsField}` from `{$table}` {$whereCount}";
        $totalRows = DB::findResultFromTheInfo($sqlCount,$rowsField);
        //say('$totalRows',$totalRows);
        $totalPage = page::getTotalPage($totalRows,$pageSize);
        //say('$totalPage',$totalPage);
        $offset = page::getOffsetByPage($p,$totalPage,$pageSize);
        //say('$offset',$offset);
        //
        $sqlPart = $this->searchHeroSql($where);
        $sql = "{$sqlPart} limit {$offset},{$pageSize}";
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

    public function getInfoWithRelatedVideos($id)
    {
        $table = $this->videoConfig['table'];
        $sqlArr = [];
        $fieldsArr = $this->videoConfig['fieldsArr'];
        $fields = joinFieldsToSelect($fieldsArr);
        $sqlArr['info'] = "select {$fields}from  `{$table}` where `vid` = {$id}";
        $data['info'] = DB::findOne($sqlArr['info']);

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
