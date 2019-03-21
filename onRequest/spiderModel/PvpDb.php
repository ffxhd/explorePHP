<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/19
 * Time: 16:04
 */

namespace onRequest\spiderModel;

use must\DB;
use onRequest\core\page;
use onRequest\spiderModel\PvpSpider;
class PvpDb extends PvpSpider
{
    protected $heroConfig = [
        'table' =>'hero_json',
        'fieldsArr'=>[
            'ename' => 'i',//i
            'cname' => 's',
            'title' => 's',
            'skin_name' => 's'
        ],
        'imgField'=>"concat('https://game.gtimg.cn/images/yxzj/img201606/heroimg/'".
            ",`ename`,'/',`ename`,'.jpg') as `hero_avatar`",
    ];
    public function searchHeroSql($where)
    {
        $table = $this->heroConfig['table'];
        $fieldsArr = $this->heroConfig['fieldsArr'];
        $fields = joinFieldsToSelect($fieldsArr);
        $imgField = $this->heroConfig['imgField'];
        $where = '' === $where ? '': "where {$where}";
        return "select {$fields},{$imgField} from `{$table}` {$where}";
    }

    public function getHeroesList($where,$p,$pageSize):array
    {
        static $i = 0;
        /*$fieldsArr = [
            'ename' => 'i',//i
            'cname' => 's',
            'title' => 's',
            'skin_name' => 's'
        ];*/
        $fieldsArr = $this->heroConfig['fieldsArr'];
        //$table = 'hero_json';
        $table = $this->heroConfig['table'];
        //
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
        //$hrefField = "concat('{$this->urlBase}/herodetail/',`ename`,'.shtml') as `detail_href`";
        //189/189.jpg
        /*$imgField = "concat('https://game.gtimg.cn/images/yxzj/img201606/heroimg/'".
            ",`ename`,'/',`ename`,'.jpg') as `hero_avatar`";*/

        //$sql = "select {$fields},{$imgField} from `{$table}` {$where} limit {$offset},{$pageSize}";
        $sqlPart = $this->searchHeroSql($where);
        $sql = "{$sqlPart} limit {$offset},{$pageSize}";
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

    //===============================================================

    protected $itemConfig = [
        'descriptionField'=>'des1',
        'description2Field'=>'des2',
        'table' =>'item_json',
        'imgField'=>"concat('https://game.gtimg.cn/images/yxzj/img201606/itemimg/'".
            ",`item_id`,'.jpg') as `item_img`",
    ];

    protected function getItemFieldsArr()
    {
        $descriptionField = $this->itemConfig['descriptionField'];
        $description2Field = $this->itemConfig['description2Field'];
        return  [
            $descriptionField => 's',
            $description2Field =>'s',
            'item_id' => 'i',
            'item_name' => 's',
            'item_type' => 'i',
            'price' => 'i',
            'total_price' => 'i',
        ];
    }

    public function searchItemSql($where)
    {
        $table = $this->itemConfig['table'];
        $fieldsArr = $this->getItemFieldsArr();
        $fieldsToSelect = array_keys($fieldsArr);
        $fields = joinFieldsToSelect($fieldsToSelect);
        $imgField = $this->itemConfig['imgField'];
        $where = '' === $where ? '': "where {$where}";
        return "select {$fields},{$imgField} from `{$table}` {$where}";
    }

    public function getItemList($where,$p,$pageSize):array
    {
        $i = 0;
        /*$descriptionField = 'des1';
        $description2Field = 'des2';*/
        $descriptionField = $this->itemConfig['descriptionField'];
        $description2Field = $this->itemConfig['description2Field'];
        /*$fieldsArr = [
            $descriptionField => 's',
            $description2Field =>'s',
            'item_id' => 'i',
            'item_name' => 's',
            'item_type' => 'i',
            'price' => 'i',
            'total_price' => 'i',
        ];*/
        $fieldsArr = $this->getItemFieldsArr();
        //$table = 'item_json';
        $table = $this->itemConfig['table'];
        //
        $whereCount = '' === $where ? '': "where {$where}";
        $rowsField = 'totalRows';
        $sqlCount = "select count(`id`) as `{$rowsField}` from `{$table}` {$whereCount}";
        $totalRows = DB::findResultFromTheInfo($sqlCount,$rowsField);
        $totalPage = page::getTotalPage($totalRows,$pageSize);
        $offset = page::getOffsetByPage($p,$totalPage,$pageSize);
        //
        //



        ////game.gtimg.cn/images/yxzj/img201606/itemimg/1314.jpg
       /* $imgField = "concat('https://game.gtimg.cn/images/yxzj/img201606/itemimg/'".
            ",`item_id`,'.jpg') as `item_img`";*/
        $imgField = $this->itemConfig['imgField'];
        $sqlPart = $this->searchItemSql($where);
        //$sql = "select {$fields},{$imgField} from `{$table}` {$where}  limit {$offset},{$pageSize}";
        $sql = "{$sqlPart}  limit {$offset},{$pageSize}";
        $list = DB::findAll($sql);
        if( false === empty($list) /*&& false === IS_LOCAL*/)
        {
            $data = [
                'list' => $this->washDesOfItemList($list),
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

    public function washDesOfItemList($list)
    {
        $descriptionField = $this->itemConfig['descriptionField'];
        $description2Field = $this->itemConfig['description2Field'];
        //<p>+10物理攻击<br>+8%物理吸血</p>
        foreach($list as $seq => $item)
        {
            $description = $item[$descriptionField];
            $description = trim($description);
            $description = ltrim($description,'<p>');
            $description = rtrim($description,'</p>');
            $list[$seq][$descriptionField] = explode('<br>',$description);
            //
            $description = $item[$description2Field];
            $description = trim($description);
            $description = ltrim($description,'<p>');
            $description = rtrim($description,'</p>');
            $list[$seq][$description2Field] = $description;
        }
        return $list;
    }

    //===============================================================

    protected $borderBreakOutConfig = [
        'table'=>'item_border_breakout',
        'itemIdField'=>'itemidzbid_4a',
        'descriptionField'=>'des1zbsx_a6',
    ];

    protected function fieldsArr_BorderBreakOutItem()
    {
        $itemIdField = $this->borderBreakOutConfig['itemIdField'];
        $descriptionField = $this->borderBreakOutConfig['descriptionField'];//'des1zbsx_a6';
        $des2Field = 'des2fszx_cc';
        $itemLevelField = 'itemlvzbdj_96';
        $itemNameField = 'itemnamezwm_cd';
        $ItemTypeField = 'itemtypezbfl_30';
        return [
            'fieldsArr' => [
                $descriptionField => 's',
                $des2Field =>'s',
                $itemIdField => 'i',
                $itemLevelField =>'s',
                $itemNameField => 's',
                $ItemTypeField => 'i',
                'zbid_7c' => 's',
            ],
            'fieldsAsArr'=>[
                $descriptionField => 'des1',
                $des2Field =>'des2',
                $itemIdField => 'item_id',
                $itemLevelField =>'item_level',
                $itemNameField => 'item_name',
                $ItemTypeField => 'item_type',
            ]

        ];
    }

    protected function borderBreakout_imgField()
    {
        $itemIdField = $this->borderBreakOutConfig['itemIdField'];
        return "concat('https://game.gtimg.cn/images/yxzj/img201606/itemimg/'".
        ",`{$itemIdField}`,'.jpg') as `item_img`";
    }

    public function searchBorderBreakOutItemSql($where)
    {
        $table = $this->borderBreakOutConfig['table'];
        $res = $this->fieldsArr_BorderBreakOutItem();
        //$fieldsToSelect = array_keys($res['fieldsAsArr']);
        $fields = joinFieldsToSelect($res['fieldsAsArr']);
        $imgField  = $this->borderBreakout_imgField();
        $where = '' === $where ? '': "where {$where}";
        return "select {$fields},{$imgField} from `{$table}` {$where} ";
    }

    public function getBorderBreakOutItemList($where,$p,$pageSize):array
    {
        $i = 0;
        //$itemIdField = 'itemidzbid_4a';
        $itemIdField = $this->borderBreakOutConfig['itemIdField'];
        $descriptionField = $this->borderBreakOutConfig['descriptionField'];//'des1zbsx_a6';

        $table = 'item_border_breakout';
        //
        $whereCount = '' === $where ? '': "where {$where}";
        $rowsField = 'totalRows';
        $sqlCount = "select count(`id`) as `{$rowsField}` from `{$table}` {$whereCount}";
        $totalRows = DB::findResultFromTheInfo($sqlCount,$rowsField);
        $totalPage = page::getTotalPage($totalRows,$pageSize);
        $offset = page::getOffsetByPage($p,$totalPage,$pageSize);
        //
        //


        ////game.gtimg.cn/images/yxzj/img201606/itemimg/1314.jpg
        /*$imgField = "concat('https://game.gtimg.cn/images/yxzj/img201606/itemimg/'".
            ",`{$itemIdField}`,'.jpg') as `item_img`";*/
        //$sql = "select {$fields},{$imgField} from `{$table}` {$where}  limit {$offset},{$pageSize}";
        $sqlPart = $this->searchBorderBreakOutItemSql($where);
        $sql = "{$sqlPart} limit {$offset},{$pageSize}";
        $list = DB::findAll($sql);
        $res = $this->fieldsArr_BorderBreakOutItem();
        if( false === empty($list) /*&& false === IS_LOCAL*/)
        {
            $data = [
                'list' => $this->washDesOfBorderBreakOutItemList($list),
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
        $fieldsArr = $res['fieldsArr'];
        $this->StuffedBorderBreakOutItemJSONIntoDB($list,$table,$fieldsArr);
        $this->saveBorderBreakOutItemJsonToFile($str);
        $i++;
        return $i === 1 ? $this->getBorderBreakOutItemList($where,$p,$pageSize):[];
    }

    public function washDesOfBorderBreakOutItemList($list)
    {
        //<p>+10物理攻击<br>+8%物理吸血</p>
        $descriptionField = $this->borderBreakOutConfig['descriptionField'];//'des1zbsx_a6';
        $res = $this->fieldsArr_BorderBreakOutItem();
        $fieldsAsArr = $res['fieldsAsArr'];
        $descriptionField = $fieldsAsArr[$descriptionField];
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

    //===============================================================

    protected $summonerConfig = [
        'table' =>'summoner_json',
        'fieldsArr'=>[
            'summoner_description' => 's',
            'summoner_id' => 'i',
            'summoner_name' => 's',
            'summoner_rank' => 's',
        ],
        'imgField'=>"concat('https://game.gtimg.cn/images/yxzj/img201606/summoner/'".
            ",`summoner_id`,'.jpg') as `summoner_img`",
        'bigImgField' =>"concat('https://game.gtimg.cn/images/yxzj/img201606/summoner/'".
            ",`summoner_id`,'-big.jpg') as `big_img`"
    ];

    public function searchSummonerSql($where)
    {
        $table = $this->summonerConfig['table'];
        $fieldsArr = $this->summonerConfig['fieldsArr'];
        $fields = joinFieldsToSelect($fieldsArr);
        $imgField = $this->summonerConfig['imgField'];
        $bigImgField = $this->summonerConfig['bigImgField'];
        $where = '' === $where ? '': "where {$where}";
        return "select `id`,{$fields},{$imgField},{$bigImgField} from `{$table}` {$where}";
    }

    public function getSummonerList():array
    {
        static $i = 1;
        /*$fieldsArr = [
            'summoner_description' => 's',
            'summoner_id' => 'i',
            'summoner_name' => 's',
            'summoner_rank' => 's',
        ];*/
        $fieldsArr = $this->summonerConfig['fieldsArr'];
        //$table = 'summoner_json';
        $table = $this->summonerConfig['table'];
        //
        ////game.gtimg.cn/images/yxzj/img201606/itemimg/1314.jpg
       /* $imgField = "concat('https://game.gtimg.cn/images/yxzj/img201606/summoner/'".
            ",`summoner_id`,'.jpg') as `summoner_img`";*/
        $sqlPart = $this->searchSummonerSql('');
        $sql = $sqlPart;
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

    //=============================================================================

}
