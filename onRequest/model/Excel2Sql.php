<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/8
 * Time: 15:45
 */

namespace onRequest\model;
use must\DB;

class Excel2Sql
{
    public function batchInsertThem()
    {
        $str = <<<EOF
        一统鲜山罐,麻辣纸烧鱼,吮指香辣蟹,胡椒肚包鸡,特色干蒸鸡,猪脑,鸭肠,鸭胗,马勺金钱肚,羊棒骨,麻辣羊蹄,麻辣鸭头,口水鸡,香拌牛肉,捞汁黄贝,鲜美花蛤,辣鲜鲍鱼,鲜拌沙虫,虾,沙鱼,章鱼,旺螺,蛏,香辣蟹,盐爆海螺,海蛎炸,小龙虾,猪腰,猪尾,腌牛腱,腌牛筋,腌鸡爪,富贵猪手,秘制腌海螺,干锅土豆片,蔬菜蛋糕,拌娃娃菜,豆角金针菇,素拌土豆泥,特色泡萝卜,素菜丸子,泡鲜辣椒,卤海带,卤莲藕,拍黄瓜,卤金针菇,卤腐竹,莴笋丝,绝味拌面,酱油炒饭,脆皮五花,叫花鸡,椒盐猪手,疯狂鸡翅,可乐烧排,小葱拌五花渣,拌皮冻,拌千层耳,干煸牛肉丝,虎皮鸡爪,香拌金钱肚
EOF;
        $table = 'goods';
        DB::$config = [
            'host'   =>'149.28.155.135',
            'user'   =>'fantasy',
            'password'    =>'fantasy123123',
            'databaseName'   =>'fantasy',
            'charset'=>'utf8',
            'class' => '\\onRequest\\core\\db\\MySQLiOOP'
        ];
        //
        $testArr = explode(',',$str);
        $list = [];
        foreach($testArr as $seq=>$value)
        {
            $list[] = [
                'gname'=>$value,
                'images'=>'[{"url":"http:\/\/api.fantasy.com\/imgs\/d2af80eeaa569321efb70e67454ddfb0.png"}]',
                'gdesc'=>"{$value}好好吃呀",
                'price'=>mt_rand(15,50),
                'sid'=>1,
                'status'=>1,
                'num'=>mt_rand(200,800),
                'num_type'=> 1,
                'start_hour'=> 20,
                'end_hour'=> 2
            ];
        }
        $testArr = $list[0];
        $fieldsStmtConfig = [];
        foreach($testArr as $field=>$value)
        {
            $fieldsStmtConfig[$field] = is_integer($value) ? 'i' : 's';
        }
        //
        $fields = joinFieldsToSelect($fieldsStmtConfig);
        $L = count($fieldsStmtConfig);
        $qArr =  array_fill(0, $L, '?');
        $qStr = implode(',',$qArr);
        $preSql="INSERT INTO `{$table}`({$fields}) VALUES({$qStr})";
        $stmt = DB::getStmt($preSql);
        $dbFieldsArr  = array_keys($fieldsStmtConfig);
        $sArr = array_values($fieldsStmtConfig);
        $sStr = implode('',$sArr);
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
        $L = count($testArr);
        say('$L',$L);
    }
}
