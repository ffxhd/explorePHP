<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/6
 * Time: 11:00
 */

namespace onRequest\spiderModel;
use QL\QueryList;
use must\DB;
class PvpSpider
{
    protected $urlBase = 'https://pvp.qq.com/web201605';
    protected $jsonFilePath = ROOT.'/onRequest/public/pvp';
    //
    protected $heroQL;
    protected $heroListUrl = '/herolist.shtml';
    //
    protected $itemListUrl = '/item.shtml';
    protected $itemQL;
    //
    protected function initHero()
    {
        $this->heroListUrl = $this->urlBase.$this->heroListUrl;
        $this->heroQL = QueryList::get($this->heroListUrl)
            ->encoding('utf-8','gbk');
    }

    public function grabHeroesJson():string
    {
        $url = $this->urlBase.'/js/herolist.json';
        $ql = QueryList::get($url);
        return $ql->removeHead()->getHtml();
    }

    public function StuffedHeroJSONIntoDB($list,$table,$fieldsArr)
    {
        $fields = joinFieldsToSelect($fieldsArr);
        $L = count($fieldsArr);
        $qArr =  array_fill(0, $L, '?');
        $qStr = implode(',',$qArr);
        $preSql="INSERT INTO `{$table}`({$fields}) VALUES({$qStr})";
        $stmt = DB::getStmt($preSql);
        $sArr = array_values($fieldsArr);
        $sStr = implode('',$sArr);
        $dbFieldsArr  = array_keys($fieldsArr);
        $vToBind = array_fill(0, $L,'v');
        $stmt->bind_param($sStr, $vToBind[0], $vToBind[1], $vToBind[2],$vToBind[3],
            $vToBind[4], $vToBind[5],$vToBind[6],$vToBind[7]);
        // 设置参数并执行
        foreach ($list as $key => $item)
        {
            foreach ($vToBind as $seq => $value)
            {
                $field = $dbFieldsArr[$seq];
                $vToBind[$seq] = getItemFromArray($item,$field,null);
            }
            $stmt->execute();
        }
    }

    public function saveHeroesJsonToFile($str):int
    {
        $path = $this->jsonFilePath;
        $file = $path.'/download_heroList.json';
        if(false === file_exists($file))
        {
            touch($file);
        }
        return file_put_contents($file,$str);
    }

    //=======================================================================================

    public function getHeroSearchRadios():array
    {
        $ruleField_1 =  'data_p_type';
        $ruleField_2 =  'data_type';
        $rules = array(
            'radio_text' => array('ul.types-ms  label','text'),
            $ruleField_1 => array('ul.types-ms  li','data-ptype'),
            $ruleField_2 => array('ul.types-ms  li','data-type'),
        );
        $table = 'hero_search_radios';
        //
        $fieldsArr = array_keys($rules);
        $fields = joinFieldsToSelect($fieldsArr);
        unset($fieldsArr);
        //
        $sql = "select `id`,{$fields} from `{$table}`";
        $data =  DB::findAll($sql);
        if( false === empty($data) /*&& false === IS_LOCAL*/)
        {
            return  $data;
        }
        $data = $this->grabHeroSearchRadios($rules);
        //清空表,从1开始
        DB::resetTable($table);
        //
        $this->StuffedHeroSearchRadiosIntoDB($data,$fields,$table);
        return $data;
    }

    public function grabHeroSearchRadios($rules):array
    {
        $this->initHero();
        return  $this->heroQL->rules($rules)->queryData();
    }

    public function StuffedHeroSearchRadiosIntoDB($list,$fields,$table)
    {
        $fieldsArr = [
            'radio_text' => 's',
            'data_p_type' => 'i',
            'data_type' => 'i',
        ];
        $L = count($fieldsArr);
        $qArr =  array_fill(0, $L, '?');
        $qStr = implode(',',$qArr);
        $sArr = array_values($fieldsArr);
        $sStr = implode('',$sArr);
        $preSql="INSERT INTO `{$table}`({$fields}) VALUES({$qStr})";
        $stmt = DB::getStmt($preSql);
        $stmt->bind_param($sStr, $radio_text, $data_p_type, $data_type);
        // 设置参数并执行
        foreach ($list as $key => $item)
        {
            $radio_text = getItemFromArray($item,'radio_text',null);
            $data_p_type = getItemFromArray($item,'data_p_type',null);
            $data_type = getItemFromArray($item,'data_type',null);
            $stmt->execute();
        }
    }

    //======================================================================================

    /**
     * @return array
     */
    public function getPvpTabs():array
    {
        $this->initHero();
        $field = 'tabText';
        $rules = array(
            $field  => array('ul.herolist-nav  a','text'),
        );
        $data = $this->heroQL->rules($rules)->query()->getData()->flatten()->all();
        //return array_column($data,$field);
        return $data;
    }

//======================================================================================
//=======================================================================================

    protected function initItem()
    {
        $this->itemListUrl = $this->urlBase.$this->itemListUrl;
        $this->itemQL = QueryList::get($this->itemListUrl)
            ->encoding('utf-8','gbk');
    }

    public function getItemSearchRadios():array
    {
        $ruleField_1 =  'data_parent_type';
        $ruleField_2 =  'data_type';
        $rules = array(
            'radio_text' => array('ul.types-ms  label','text'),
            $ruleField_1 => array('ul.types-ms  li','data-parent-type'),
            $ruleField_2 => array('ul.types-ms  li','data-type'),
        );
        $table = 'item_radios';
        //
        $fieldsArr = array_keys($rules);
        $fields = joinFieldsToSelect($fieldsArr);
        unset($fieldsArr);
        //
        $sql = "select `id`,{$fields} from `{$table}`";
        $data =  DB::findAll($sql);
        if( false === empty($data) /*&& false === IS_LOCAL*/)
        {
            $idealData = [];
            foreach($data as $key => $item)
            {
                $pType = $item[$ruleField_1];
                //say('$pType',$pType);
                if( null === $pType)
                {
                    $type = $item[$ruleField_2];
                    unset($item[$ruleField_1]);
                    $idealData[$type] = $item;
                    $idealData[$type]['children'] = [];
                }
                else
                {
                    $idealData[$pType]['children'][] = $item;
                }
            }
            return  $idealData;
        }
        $list = $this->grabItemSearchRadios($rules);
        //清空表,从1开始
        DB::resetTable($table);
        //
        $this->StuffedItemSearchRadiosIntoDB($list,$fields,$table);
        return $data;
    }

    public function grabItemSearchRadios($rules):array
    {
        $this->initItem();
        return  $this->itemQL->rules($rules)->queryData();
    }

    public function StuffedItemSearchRadiosIntoDB($list,$fields,$table)
    {
        $fieldsArr = [
            'radio_text' => 's',
            'data_parent_type' => 'i',
            'data_type' => 'i',
        ];
        $L = count($fieldsArr);
        $qArr =  array_fill(0, $L, '?');
        $qStr = implode(',',$qArr);
        $sArr = array_values($fieldsArr);
        $sStr = implode('',$sArr);
        $preSql="INSERT INTO  `{$table}` ( {$fields} ) VALUES ( {$qStr} )";
        $stmt = DB::getStmt($preSql);
        $stmt->bind_param($sStr, $radio_text, $data_parent_type, $data_type);
        // 设置参数并执行
        foreach ($list as $key => $item)
        {
            $radio_text = getItemFromArray($item,'radio_text',null);
            $data_parent_type = getItemFromArray($item,'data_parent_type',null);
            $data_type = getItemFromArray($item,'data_type',null);
            $stmt->execute();
        }
    }

    //===========局内道具====常规模式===================================================

    public function grabItemJson():string
    {
        $url = $this->urlBase.'/js/item.json';
        $ql = QueryList::get($url);
        return $ql->removeHead()->getHtml();
    }

    public function StuffedItemJSONIntoDB($list,$table,$fieldsArr)
    {
        $fields = joinFieldsToSelect($fieldsArr);
        $L = count($fieldsArr);
        $qArr =  array_fill(0, $L, '?');
        $qStr = implode(',',$qArr);
        $preSql="INSERT INTO `{$table}`({$fields}) VALUES({$qStr})";
        $stmt = DB::getStmt($preSql);
        $dbFieldsArr  = array_keys($fieldsArr);
        $sArr = array_values($fieldsArr);
        $sStr = implode('',$sArr);
        $vToBind = array_fill(0, $L,'v');
        $stmt->bind_param($sStr, $vToBind[0], $vToBind[1], $vToBind[2],$vToBind[3],
            $vToBind[4], $vToBind[5],$vToBind[6]);
        // 设置参数并执行
        foreach ($list as $key => $item)
        {
            foreach ($vToBind as $seq => $value)
            {
                $field = $dbFieldsArr[$seq];
                $vToBind[$seq] = getItemFromArray($item,$field,null);
            }
            $stmt->execute();
        }
    }

    public function saveItemJsonToFile($str):int
    {
        $path = $this->jsonFilePath;
        $file = $path.'/download_itemList.json';
        if(false === file_exists($file))
        {
            touch($file);
        }
        return file_put_contents($file,$str);
    }

//============局内道具====边境突围模式=================================================

    public function grabItemBorderBreakOutJson():string
    {
        $url = 'https://pvp.qq.com/zlkdatasys/data_zlk_bjtwitem.json';
        $ql = QueryList::get($url);
        return $ql->removeHead()->getHtml();
    }

    public function StuffedBorderBreakOutItemJSONIntoDB($list,$table,$fieldsArr)
    {
        $fields = joinFieldsToSelect($fieldsArr);
        $L = count($fieldsArr);
        $qArr =  array_fill(0, $L, '?');
        $qStr = implode(',',$qArr);
        $preSql="INSERT INTO `{$table}`({$fields}) VALUES({$qStr})";
        $stmt = DB::getStmt($preSql);
        $dbFieldsArr  = array_keys($fieldsArr);
        $sArr = array_values($fieldsArr);
        $sStr = implode('',$sArr);
        $vToBind = array_fill(0, $L,'v');
        $stmt->bind_param($sStr, $vToBind[0], $vToBind[1], $vToBind[2],$vToBind[3],
            $vToBind[4], $vToBind[5],$vToBind[6]);
        // 设置参数并执行
        foreach ($list as $key => $item)
        {
            foreach ($vToBind as $seq => $value)
            {
                $field = $dbFieldsArr[$seq];
                $vToBind[$seq] = getItemFromArray($item,$field,null);
            }
            $stmt->execute();
        }
    }

    public function saveBorderBreakOutItemJsonToFile($str):int
    {
        $path = $this->jsonFilePath;
        $file = $path.'/download_itemBorderBreakOutList.json';
        if(false === file_exists($file))
        {
            touch($file);
        }
        return file_put_contents($file,$str);
    }

//=======================================================================================
//=======================================================================================

    public function grabSummonerJson():string
    {
        $url = $this->urlBase.'/js/summoner.json';
        $ql = QueryList::get($url);
        return $ql->removeHead()->getHtml();
    }

    public function StuffedSummonerJSONIntoDB($list,$table,$fieldsArr)
    {
        $fields = joinFieldsToSelect($fieldsArr);
        $L = count($fieldsArr);
        $qArr =  array_fill(0, $L, '?');
        $qStr = implode(',',$qArr);
        $preSql="INSERT INTO `{$table}`({$fields}) VALUES({$qStr})";
        $stmt = DB::getStmt($preSql);
        $dbFieldsArr  = array_keys($fieldsArr);
        $sArr = array_values($fieldsArr);
        $sStr = implode('',$sArr);
        $vToBind = array_fill(0, $L,'v');
        $stmt->bind_param($sStr, $vToBind[0], $vToBind[1], $vToBind[2],$vToBind[3]);
        // 设置参数并执行
        foreach ($list as $key => $item)
        {
            foreach ($vToBind as $seq => $value)
            {
                $field = $dbFieldsArr[$seq];
                $vToBind[$seq] = getItemFromArray($item,$field,null);
            }
            $stmt->execute();
        }
    }

    public function saveSummonerJsonToFile($str):int
    {
        $path = $this->jsonFilePath;
        $file = $path.'/download_summoner.json';
        if(false === file_exists($file))
        {
            touch($file);
        }
        return file_put_contents($file,$str);
    }
}
