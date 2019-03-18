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
use onRequest\core\page;
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

    protected function getHeroListByPageCode()
    {

    }

    public function getHeroesList($where,$p,$pageSize):array
    {
        static $i = 0;
        $fieldsArr = [
            'ename' => 'i',//i
            'cname' => 's',
            'title' => 's',
            'skin_name' => 's'
        ];
        $table = 'hero_json';
        //
        $where = '' === $where ? '': "where {$where}";
        $rowsField = 'totalRows';
        $sqlCount = "select count(`id`) as `{$rowsField}` from `{$table}` {$where}";
        $totalRows = DB::findResultFromTheInfo($sqlCount,$rowsField);
        //say('$totalRows',$totalRows);
        $totalPage = page::getTotalPage($totalRows,$pageSize);
        //say('$totalPage',$totalPage);
        $offset = page::getOffsetByPage($p,$totalPage,$pageSize);
        //say('$offset',$offset);
        //
        $fields = joinFieldsToSelect($fieldsArr);
        //$hrefField = "concat('{$this->urlBase}/herodetail/',`ename`,'.shtml') as `detail_href`";
        //189/189.jpg
        $imgField = "concat('https://game.gtimg.cn/images/yxzj/img201606/heroimg/'".
            ",`ename`,'/',`ename`,'.jpg') as `hero_avatar`";
        $sql = "select {$fields},{$imgField} from `{$table}` {$where} limit {$offset},{$pageSize}";
        $list = DB::findAll($sql);
        if( false === empty($list) /*&& false === IS_LOCAL*/)
        {
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
        $str = $this->grabHeroesJson();
        //
        $str = substr($str,3);//必须 加这一行代码
        $list = json_decode($str,true);
        //say('$list',$list);
        if( false === is_array($list))
        {
            return [];
        }
        //
        DB::resetTable($table);
        $fieldsArr_insert = [
            'new_type' => 'i',//i
            'hero_type' => 'i',
            'hero_type2' => 'i',
            'pay_type' => 'i',//i
        ];
        $fieldsArr_insert = array_merge($fieldsArr,$fieldsArr_insert);
        $this->StuffedHeroJSONIntoDB($list,$table,$fieldsArr_insert);
        $this->saveHeroesJsonToFile($str);
        $i++;
        return $i === 1 ? $this->getHeroesList($where,$p,$pageSize) : [];
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

    public function getItemList($where,$p,$pageSize):array
    {
        $i = 0;
        $descriptionField = 'des1';
        $fieldsArr = [
            $descriptionField => 's',
            'item_id' => 'i',
            'item_name' => 's',
            'item_type' => 'i',
            'price' => 'i',
            'total_price' => 'i',
        ];
        $table = 'item_json';
        //
        $where = '' === $where ? '': "where {$where}";
        $rowsField = 'totalRows';
        $sqlCount = "select count(`id`) as `{$rowsField}` from `{$table}` {$where}";
        $totalRows = DB::findResultFromTheInfo($sqlCount,$rowsField);
        $totalPage = page::getTotalPage($totalRows,$pageSize);
        $offset = page::getOffsetByPage($p,$totalPage,$pageSize);
        //
        //
        $fieldsToSelect = array_keys($fieldsArr);
        $fields = joinFieldsToSelect($fieldsToSelect);

        ////game.gtimg.cn/images/yxzj/img201606/itemimg/1314.jpg
        $imgField = "concat('https://game.gtimg.cn/images/yxzj/img201606/itemimg/'".
            ",`item_id`,'.jpg') as `item_img`";
        $sql = "select {$fields},{$imgField} from `{$table}` {$where}  limit {$offset},{$pageSize}";
        $list = DB::findAll($sql);
        if( false === empty($list) /*&& false === IS_LOCAL*/)
        {
            $data = [
                'list' => $this->washDesOfItemList($list,$descriptionField),
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
        $str = $this->grabItemJson();
        $str = substr($str,0);//必须 加这一行代码
        $list = json_decode($str,true);
        //
        if( false === is_array($list))
        {
            return [];
        }
        //
        DB::resetTable($table);
        $this->StuffedItemJSONIntoDB($list,$table,$fieldsArr);
        $this->saveItemJsonToFile($str);
        $i++;
        return $i === 1 ? $this->getItemList($where,$p,$pageSize):[];
    }

    protected function washDesOfItemList($list,$descriptionField)
    {
        //<p>+10物理攻击<br>+8%物理吸血</p>
        foreach($list as $seq => $item)
        {
            $description = $item[$descriptionField];
            $description = trim($description);
            $description = ltrim($description,'<p>');
            $description = rtrim($description,'</p>');
            $list[$seq][$descriptionField] = explode('<br>',$description);
        }
        return $list;
    }

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
            $vToBind[4], $vToBind[5]);
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

    public function getBorderBreakOutItemList($where,$p,$pageSize):array
    {
        $i = 0;
        $descriptionField = 'des1zbsx_a6';
        $itemIdField = 'itemidzbid_4a';
        $des2Field = 'des2fszx_cc';
        $itemLevelField = 'itemlvzbdj_96';
        $itemNameField = 'itemnamezwm_cd';
        $ItemTypeField = 'itemtypezbfl_30';
        $fieldsArr = [
            $descriptionField => 's',
            $des2Field =>'s',
            $itemIdField => 'i',
            $itemLevelField =>'s',
            $itemNameField => 's',
            $ItemTypeField => 'i',
            'zbid_7c' => 's',
        ];
        $fieldsAsArr = [
            $descriptionField => 'des1',
            $des2Field =>'des2',
            $itemIdField => 'item_id',
            $itemLevelField =>'item_level',
            $itemNameField => 'item_name',
            $ItemTypeField => 'item_type',
        ];
        $table = 'item_border_breakout';
        //
        $where = '' === $where ? '': "where {$where}";
        $rowsField = 'totalRows';
        $sqlCount = "select count(`id`) as `{$rowsField}` from `{$table}` {$where}";
        $totalRows = DB::findResultFromTheInfo($sqlCount,$rowsField);
        $totalPage = page::getTotalPage($totalRows,$pageSize);
        $offset = page::getOffsetByPage($p,$totalPage,$pageSize);
        //
        //
        $fields = joinFieldsToSelect($fieldsAsArr);

        ////game.gtimg.cn/images/yxzj/img201606/itemimg/1314.jpg
        $imgField = "concat('https://game.gtimg.cn/images/yxzj/img201606/itemimg/'".
            ",`{$itemIdField}`,'.jpg') as `item_img`";
        $sql = "select {$fields},{$imgField} from `{$table}` {$where}  limit {$offset},{$pageSize}";
        $list = DB::findAll($sql);
        if( false === empty($list) /*&& false === IS_LOCAL*/)
        {
            $descriptionField = $fieldsAsArr[$descriptionField];
            $data = [
                'list' => $this->washDesOfBorderBreakOutItemList($list,$descriptionField),
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
        $str = $this->grabItemBorderBreakOutJson();
        $str = substr($str,0);//必须 加这一行代码
        $list = json_decode($str,true);
        //
        if( false === is_array($list))
        {
            return [];
        }
        //
        DB::resetTable($table);
        $list = $list['bjtwzbsy_ba'];
        $this->StuffedBorderBreakOutItemJSONIntoDB($list,$table,$fieldsArr);
        $this->saveBorderBreakOutItemJsonToFile($str);
        $i++;
        return $i === 1 ? $this->getBorderBreakOutItemList($where,$p,$pageSize):[];
    }

    protected function washDesOfBorderBreakOutItemList($list,$descriptionField)
    {
        //<p>+10物理攻击<br>+8%物理吸血</p>
        foreach($list as $seq => $item)
        {
            $description = $item[$descriptionField];
            $description = trim($description);
//            $description = ltrim($description,'<p>');
//            $description = rtrim($description,'</p>');
            $list[$seq][$descriptionField] = explode('<br>',$description);
        }
        return $list;
    }

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

    public function getSummonerList():array
    {
        static $i = 1;
        $fieldsArr = [
            'summoner_description' => 's',
            'summoner_id' => 'i',
            'summoner_name' => 's',
            'summoner_rank' => 's',
        ];
        $table = 'summoner_json';
        //
        $fields = joinFieldsToSelect($fieldsArr);
        ////game.gtimg.cn/images/yxzj/img201606/itemimg/1314.jpg
        $imgField = "concat('https://game.gtimg.cn/images/yxzj/img201606/summoner/'".
            ",`summoner_id`,'.jpg') as `summoner_img`";
        $bigImgField = "concat('https://game.gtimg.cn/images/yxzj/img201606/summoner/'".
            ",`summoner_id`,'-big.jpg') as `big_img`";
        $sql = "select `id`,{$fields},{$imgField},{$bigImgField} from `{$table}`";
        $list = DB::findAll($sql);
        if( false === empty($list) /*&& false === IS_LOCAL*/)
        {
            return  $list;
        }
        $str = $this->grabSummonerJson();
        $str = substr($str,0);//必须 加这一行代码
        $list = json_decode($str,true);
        //
        if( false === is_array($list))
        {
            return [];
        }
        //
        DB::resetTable($table);
        $this->StuffedSummonerJSONIntoDB($list,$table,$fieldsArr);
        $this->saveSummonerJsonToFile($str);
        $i++;
        return $i ===2 ? $this->getSummonerList() : [];
    }

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
