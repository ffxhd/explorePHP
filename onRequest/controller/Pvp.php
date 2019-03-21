<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/5
 * Time: 17:34
 */

namespace onRequest\controller;
use onRequest\spiderModel\PvpDb as pvpModel;
use must\DB;
class Pvp
{
    protected  $isDebug = true;
    public function __construct()
    {
        $this->isDebug = isDebugApi();
    }

    public function getPvpTabs()
    {
        $obj = new  pvpModel();
        $data = $obj->getPvpTabs();
        outputApiData($data,__FUNCTION__.'--选项卡');
    }

    //===============================================================

    public function getHeroSearchRadios()
    {
        $obj = new  pvpModel();
        $data = $obj->getHeroSearchRadios();
        if( false === $_SERVER['IS_AJAX'] && true === $this->isDebug )
        {
            say('sql', DB::fetchSqlArr());
        }
        outputApiData($data,__FUNCTION__.'--英雄--搜索单选框');
    }

    public function updateHeroSearchRadiosForcibly()
    {
        $obj = new  pvpModel();
    }

    //===============================================================

    protected static function where_heroName($name)
    {
        return "`cname` like '%{$name}%'";
    }

    public static function heroesWhere($rawSubmit)
    {
        $name = getWashedData($rawSubmit,'hero_name','');
        if($name !== '')
        {
            return self::where_heroName($name);
        }
        $type = getItemFromArray($rawSubmit,'hero_type',null);
        $type = intval($type);
        switch (true)
        {
            case $type > 0 && $type < 7:
                return " `hero_type` = {$type} or (`hero_type2` = {$type}  and `pay_type` is null)";
            case $type === 7://新手推荐
                return "`pay_type` = 11";
            case $type === 8://本周免费
                return "`pay_type` = 10";
            default:
        }
        return '';
    }

    public function getHeroesList()
    {
        $where = $this->heroesWhere($_REQUEST);
        $p = getItemFromArray($_GET,'p',1);
        $p = intval($p);
        $pageSize = getItemFromArray($_GET,'pageSize',10);
        $pageSize = intval($pageSize);
        $obj = new  pvpModel();
        $apiData = $obj->getHeroesList($where,$p,$pageSize);
        $isSuccess = false === empty($apiData['list']) ;
        $resMsg = true === $isSuccess ? '成功':'失败';
        $data = creatApiData(0,"获取英雄列表数据{$resMsg}", $apiData);
        return outputApiData($data,__FUNCTION__.'--所有英雄');
    }

    public function updateHeroesListForcibly()
    {
        $obj = new  pvpModel();
    }

    //===============================================================
    //===============================================================

    public function getItemSearchRadios()
    {
        $obj = new  pvpModel();
        $data = $obj->getItemSearchRadios();
        if( false === $_SERVER['IS_AJAX'] && true === $this->isDebug )
        {
            say('sql', DB::fetchSqlArr());
        }
        outputApiData($data,__FUNCTION__.'-道具--搜索单选框');
    }

    public function updateItemSearchRadiosForcibly()
    {
        $obj = new  pvpModel();
    }

    //===============================================================

    protected function where_ItemName_0($name)
    {
        return "`item_name` like '%{$name}%'";
    }

    protected function where_ItemName_1($name)
    {
        return "`itemnamezwm_cd` like '%{$name}%'";
    }

    protected function itemWhere($rawSubmit,$isRegularMode)
    {
        $nameField = true === $isRegularMode ? 'item_name': 'itemnamezwm_cd';
        $name = getWashedData($rawSubmit,'item_name','');
        if($name !== '')
        {
            return "`{$nameField}` like '%{$name}%'";
        }
        //
        $type = getItemFromArray($rawSubmit,'type',0);
        $type = intval($type);
        $typeField = true === $isRegularMode ? 'item_type':'itemtypezbfl_30';
        return $type === 0 ? '': "`{$typeField}` = {$type}";
    }

    public function getItemList()
    {
        $obj = new  pvpModel();
        $p = getItemFromArray($_GET,'p',1);
        $p = intval($p);
        $pageSize = getItemFromArray($_GET,'pageSize',10);
        $pageSize = intval($pageSize);
        //
        $parentType = getItemFromArray($_GET,'parent_type',0);
        $parentType = intval($parentType);
        $isRegularMode = $parentType === 0;
        $where = $this->itemWhere($_REQUEST,$isRegularMode);
        if(true === $isRegularMode )
        {
            $apiData = $obj->getItemList($where,$p,$pageSize);
        }
        else
        {
            $apiData = $obj->getBorderBreakOutItemList($where,$p,$pageSize);
        }
        $isSuccess = false === empty($apiData['list']) ;
        $resMsg = true === $isSuccess ? '成功':'失败';
        $data = creatApiData(0,"获取列表数据{$resMsg}", $apiData);
        return outputApiData($data,__FUNCTION__.'--所有道具');
    }

    public function updateItemListForcibly()
    {
        $obj = new  pvpModel();
    }

    //===============================================================

    protected function where_summonerName($name)
    {
        return "`summoner_name` like '%{$name}%'";
    }

    public function getSummonerList()
    {
        $obj = new  pvpModel();
        $data = $obj->getSummonerList();
        if( false === $_SERVER['IS_AJAX'] && true === $this->isDebug )
        {
            say('sql',DB::fetchSqlArr());
            $L  = count($data);
            say('$data的个数：',$L);
            $cNameArr = array_column($data,'summoner_name');
            sort($cNameArr,SORT_STRING );
            say('cName',$cNameArr);
        }
        outputApiData($data,__FUNCTION__.'--所有召唤师技能');
    }

    //===============================================================

    public function searchAll()
    {
        $keyword = getWashedData($_REQUEST,'keyword','');
        if( $keyword === '')
        {
            $data = creatApiData(1,'需要关键字');
            return outputApiData($data);
        }
        $obj = new  pvpModel();
        $sqlArr = [];
        $where = $this->where_heroName($keyword);
        $sqlArr['hero'] = $obj->searchHeroSql($where);
        //
        $where = $this->where_ItemName_0($keyword);
        $sqlArr['item_0'] = $obj->searchItemSql($where);
        //
        $where = $this->where_ItemName_1($keyword);
        $sqlArr['item_1'] = $obj->searchBorderBreakOutItemSql($where);
        //
        $where = $this->where_summonerName($keyword);
        $sqlArr['summoner'] = $obj->searchSummonerSql($where);
        //
        $apiData = DB::multiFind($sqlArr,true);
        $apiData['item_0'] = $obj->washDesOfItemList($apiData['item_0'] );
        //say('$apiData[\'item_1\']',$apiData['item_1']);
        $apiData['item_1'] = $obj->washDesOfBorderBreakOutItemList($apiData['item_1'] );
        //
        $data = creatApiData(0,'获取数据成功',$apiData);
        return outputApiData($data);
    }

    //===============================================================

    public function test()
    {
        $testObj = new \onRequest\spiderModel\TestQL();
        //$testObj->triggerChrome_node_fail();
        //$testObj->getAllHeroes_node_fail();
        $testObj->getAllHeroesJson();
        //$testObj->testListHtml();
        /*$testObj->testHtml_3();
        $testObj->testHtml_2();
        $testObj->testHtml_1();*/
    }
}
