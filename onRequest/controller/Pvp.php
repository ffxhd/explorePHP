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
        $debug = getItemFromArray($_GET,'debug','');
        $this->isDebug = $debug === 'true' || $debug === '1';
    }

    protected function outputData($data,$debugMsg = '')
    {
        if( true === $_SERVER['IS_AJAX'] )//=== $_SERVER['IS_AJAX']
        {
            echo json_encode($data,JSON_UNESCAPED_UNICODE );
            return true;
        }
        if( false === $this->isDebug)
        {
            echo '<pre style="font-size:22px">'.
                json_encode($data,JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ).
                '</pre>';
        }
        else
        {
            say($debugMsg,$data);
        }
    }

    public function getPvpTabs()
    {
        $obj = new  PvpSpider();
        $data = $obj->getPvpTabs();
        $this->outputData($data,__FUNCTION__.'--选项卡');
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
        $this->outputData($data,__FUNCTION__.'--英雄--搜索单选框');
    }

    public function updateHeroSearchRadiosForcibly()
    {
        $obj = new  PvpSpider();
    }

    //===============================================================

    protected function heroesWhere($rawSubmit)
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

    public function getHeroesList()
    {
        /*say('$_REQUEST',$_REQUEST);
        say('$_GET',$_GET);
        say('$_POST',$_POST);*/
        $where = $this->heroesWhere($_REQUEST);
        /*say('$where',$where);
        stop();*/
        $obj = new  PvpSpider();
        $data = $obj->getHeroesList($where);
        if( false === $_SERVER['IS_AJAX'] && true === $this->isDebug )
        {
            say('sql',DB::fetchSqlArr());
            $L  = count($data);
            say('$data的个数：',$L);
            $cNameArr = array_column($data,'cname');
            sort($cNameArr,SORT_STRING );
            say('cName',$cNameArr);
        }
        $this->outputData($data,__FUNCTION__.'--所有英雄');
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
        $this->outputData($data,__FUNCTION__.'-道具--搜索单选框');
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
        /*$arr = [
            'a的值', true,'b的值','b'
        ];
        say(...extract($arr));
        stop();*/
        /*say('$_REQUEST',$_REQUEST);
        say('$_GET',$_GET);
        say('$_POST',$_POST);*/
        $where = $this->itemWhere($_REQUEST);
        /*say('$where',$where);
        stop();*/
        $obj = new  PvpSpider();
        $data = $obj->getItemList($where);
        if( false === $_SERVER['IS_AJAX'] && true === $this->isDebug )
        {
            say('sql',DB::fetchSqlArr());
            $L  = count($data);
            say('$data的个数：',$L);
            $cNameArr = array_column($data,'item_name');
            sort($cNameArr,SORT_STRING );
            say('cName',$cNameArr);
        }
        $this->outputData($data,__FUNCTION__.'--所有道具');
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
        $this->outputData($data,__FUNCTION__.'--所有召唤师技能');
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
