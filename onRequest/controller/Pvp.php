<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/5
 * Time: 17:34
 */

namespace onRequest\controller;
use onRequest\spiderModel\PvpSpider;
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
        $obj = new  PvpSpider();
        $data = $obj->getPvpTabs();
        outputApiData($data,__FUNCTION__.'--选项卡');
    }

    //===============================================================

    public function getHeroSearchRadios()
    {
        $obj = new  PvpSpider();
        $data = $obj->getHeroSearchRadios();
        if( false === $_SERVER['IS_AJAX'] && true === $this->isDebug )
        {
            say('sql', DB::fetchSqlArr());
        }
        outputApiData($data,__FUNCTION__.'--英雄--搜索单选框');
    }

    public function updateHeroSearchRadiosForcibly()
    {
        $obj = new  PvpSpider();
    }

    //===============================================================

    protected function heroesWhere($rawSubmit)
    {
        /*$type = getItemFromArray($rawSubmit,'complex',null);
        if( $type !== null )
        {
            $type = $type === 'novice_recommendation' ? '11' : '10';
           return "`pay_type` = {$type}";
        }*/
        /*$heroTypeArr = [
            'all'=> 0,//全部
            'tank'=> 3,//坦克
            'warrior'=>1,//战士
            'assassin'=> 4,//刺客
            'mage'=>2,//法师
            'shooter'=> 5,//射手
            'aid'=>6,//辅助
        ];*/
        $type = getItemFromArray($rawSubmit,'hero_type',null);
        /*if( $type !== null )
        {
            $type = getItemFromArray($heroTypeArr,$type,0);
            return " `hero_type` = {$type} or (`hero_type2` = {$type}  and `pay_type` is null)";
        }*/
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
        $obj = new  PvpSpider();
        $apiData = $obj->getHeroesList($where,$p,$pageSize);
        $isSuccess = false === empty($apiData['list']) ;
        $resMsg = true === $isSuccess ? '成功':'失败';
        $data = creatApiData(0,"获取英雄列表数据{$resMsg}", $apiData);
        return outputApiData($data,__FUNCTION__.'--所有英雄');
    }

    public function updateHeroesListForcibly()
    {
        $obj = new  PvpSpider();
    }

    //===============================================================
    //===============================================================

    public function getItemSearchRadios()
    {
        $obj = new  PvpSpider();
        $data = $obj->getItemSearchRadios();
        if( false === $_SERVER['IS_AJAX'] && true === $this->isDebug )
        {
            say('sql', DB::fetchSqlArr());
        }
        outputApiData($data,__FUNCTION__.'-道具--搜索单选框');
    }

    public function updateItemSearchRadiosForcibly()
    {
        $obj = new  PvpSpider();
    }

    //===============================================================

    protected function itemWhere($rawSubmit)
    {
        $type = getItemFromArray($rawSubmit,'complex',null);
        if( $type !== null )
        {
            $type = $type === 'novice_recommendation' ? '11' : '10';
            return "`pay_type` = {$type}";
        }
        $heroTypeArr = [
            'all'=> 0,//全部
            'tank'=> 3,//坦克
            'warrior'=>1,//战士
            'assassin'=> 4,//刺客
            'mage'=>2,//法师
            'shooter'=> 5,//射手
            'aid'=>6,//辅助
        ];
        $type = getItemFromArray($rawSubmit,'hero_type',null);
        if( $type !== null )
        {
            $type = getItemFromArray($heroTypeArr,$type,0);
            return "`hero_type2` = {$type} OR `hero_type` = {$type} ";
        }
        return '';
    }

    public function getItemList()
    {
        $where = $this->itemWhere($_REQUEST);
        $p = getItemFromArray($_GET,'p',1);
        $p = intval($p);
        $pageSize = getItemFromArray($_GET,'pageSize',10);
        $pageSize = intval($pageSize);
        $obj = new  PvpSpider();
        $apiData = $obj->getItemList($where,$p,$pageSize);
        $isSuccess = false === empty($apiData['list']) ;
        $resMsg = true === $isSuccess ? '成功':'失败';
        $data = creatApiData(0,"获取列表数据{$resMsg}", $apiData);
        return outputApiData($data,__FUNCTION__.'--所有道具');
    }

    public function updateItemListForcibly()
    {
        $obj = new  PvpSpider();
    }

    //===============================================================

    public function getSummonerList()
    {
        $obj = new  PvpSpider();
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
