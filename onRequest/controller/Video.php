<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/19
 * Time: 19:29
 */

namespace onRequest\controller;

use onRequest\spiderModel\VideoModel;
class Video
{
    public function getList()
    {
        $isHot = getWashedData($_REQUEST,'is_hot',0);
        $p = getItemFromArray($_GET,'p',1);
        $p = intval($p);
        $pageSize = getItemFromArray($_GET,'pageSize',10);
        $pageSize = intval($pageSize);
        //
        $where = '';
        $obj = new  VideoModel();
        $isHot = $isHot > 0;
        if( false === $isHot )
        {
            $apiData = $obj->getAllByPage($where,$p,$pageSize);
        }
        else
        {
            $apiData = $obj->getRandom($pageSize);
        }
        $isSuccess = false === empty($apiData['list']) ;
        $resMsg = true === $isSuccess ? '成功':'失败';
        $data = creatApiData(0,"获取列表数据{$resMsg}", $apiData);
        return outputApiData($data,__FUNCTION__.'--所有视频');
    }

    public function getInfoWithRelatedVideos()
    {
        $id = getItemFromArray($_GET,'id',1);
        $id = intval($id);
        $obj = new  VideoModel();
        $apiData = $obj->getInfoWithRelatedVideos($id);
        $isSuccess = false === empty($apiData['info']) ;
        $resMsg = true === $isSuccess ? '成功':'失败';
        $data = creatApiData(0,"获取列表数据{$resMsg}", $apiData);
        return outputApiData($data,__FUNCTION__.'--所有');
    }
}
