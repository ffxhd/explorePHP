<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/19
 * Time: 10:56
 */

namespace onRequest\spiderModel;
use must\DB;

class HeroModel
{
    public static $sum_skill_field = 'sum_skill_src';
    public static $bonus_field = 'bouns';

    public static function getHeroMainFieldArr()
    {
        return [
            'id', 'ename', 'title', 'cname', 'hvideo_url', 'viability', 'ad',
            'cover_skill', 'difficulity', 'hero_story', 'inscr_tips', 'eq_tips',
            'hvideo_url' => 'honor_link',
            'tips1','tips2'
        ];
    }

    public function getAll($eName)
    {
        $fieldsArr = $this->getHeroMainFieldArr();
        $fields = joinFieldsToSelect($fieldsArr);
        $sqlArr = [];
        $sqlArr['main'] = "select {$fields} from `honor_main` where `ename`= {$eName}";
        //皮肤
        $fieldsArr = [
            'id', 'ename', 'pf_name',
        ];
        $skinField = "concat('https:',`lpf_src`) as `lpf_src`";
        $skinField2 = "concat('https:',`bpf_src`) as `bpf_src`";
        $fields = joinFieldsToSelect($fieldsArr);
        $sqlArr['skin'] = "select {$fields},{$skinField},{$skinField2} from `honor_pf` where `ename`= {$eName}";
        //技能介绍
        $fieldsArr = [
            'id', 'ename', 'skill_name', 'cool_value', 'expend', 'skill_intr', 'skill_tips'
        ];
        $srcField = "concat('https:',`skill_src`) as `skill_src`";
        $fields = joinFieldsToSelect($fieldsArr);
        $sqlArr['skill'] = "select {$fields},{$srcField} from `honor_skill` where `ename`= {$eName}";
        //铭文搭配
        $fieldsArr = [
            'id', 'ename', 'name', HeroModel::$bonus_field
        ];
        $srcField = "concat('https:',`src`) as `src`";
        $fields = joinFieldsToSelect($fieldsArr);
        $sqField_match = 'inscriptionMatch';
        $sqlArr[$sqField_match] = "select {$fields},{$srcField} from `honor_inscription_match` where `ename`= {$eName}";
        //技能加点建议
        $fieldsArr = [
            'id', 'ename', 'm_up_skill', 'a_up_skill',
            'sum_skill',
        ];
        $srcField = 'm_up_src';
        $srcField1 = "concat('https:',`{$srcField}`) as `{$srcField}`";
        //
        $srcField = 'a_up_src';
        $srcField2 = "concat('https:',`{$srcField}`) as `{$srcField}`";
        //
        //$srcField = 'sum_skill_src';
        $srcField = self::$sum_skill_field;
        $srcField3 = "concat('https:',`{$srcField}`) as `{$srcField}`";
        //
        $fields = joinFieldsToSelect($fieldsArr);
        $sqlField_skillPlus = 'skillsPlusSuggestions';
        $sqlArr[$sqlField_skillPlus] = "select {$fields},{$srcField1},{$srcField2},{$srcField3} from `honor_sug` where `ename`= {$eName}";
        //出装建议
        $fieldsArr = [
            'id', 'ename', 'eq_name', 'eq_price', 'eq_total_price', 'eq_bonus',
            'eq_introduce', 'eq_advice',
        ];
        $srcField = 'eq_src';
        $srcField1 = "concat('https:',`{$srcField}`) as `{$srcField}`";
        //
        $fields = joinFieldsToSelect($fieldsArr);
        $sqlArr['outfitSuggestions'] = "select {$fields},{$srcField1} from `honor_eq` where `ename`= {$eName}";
        //英雄关系
        $fieldsArr = [
            'id', 'ename', 'op_relation', 'op_ename', 'op_tips'
        ];
        $srcField = 'op_src';
        $srcField1 = "concat('https:',`{$srcField}`) as `{$srcField}`";
        //
        $fields = joinFieldsToSelect($fieldsArr);
        $sqlArr['relation'] = "select {$fields},{$srcField1} from `honor_op` where `ename`= {$eName}";
        //英雄攻略
        $fieldsArr = [
            'id', 'ename', 'video_title', 'video_link', 'video_scope',
        ];
        $srcField = 'video_src';
        $srcField1 = "concat('https:',`{$srcField}`) as `{$srcField}`";
        //
        $fields = joinFieldsToSelect($fieldsArr);
        $sqlArr['video'] = "select {$fields},{$srcField1} from `honor_video` where `ename`= {$eName}";
        //
        $data = DB::multiFind($sqlArr);
        $data[$sqlField_skillPlus] = self::washSkillsPlusSuggestions($data[$sqlField_skillPlus]);
        $data[$sqField_match] = self::washInscriptionMatch($data[$sqField_match]);
        return $data;
    }

    public static function washSkillsPlusSuggestions($info)
    {
        //say('$info',$info);
        $srcField = self::$sum_skill_field;
        if($info)
        {
            $info[$srcField] = explode(',', $info[$srcField]);
            $info[$srcField][1] = 'https:'.$info[$srcField][1];
        }
        return $info;
        //say('处理后$info',$info);
    }

    public static function washInscriptionMatch($list)
    {
        $field = self::$bonus_field;
        if($list)
        {
            foreach ($list as $seq => $info)
            {
                $list[$seq][$field] = explode(',', $info[$field]);
            }
        }
        return $list;
    }

    public function likeTheHeroOrNot($eName,$like,$userId)
    {
        $isAdd = $like > 0;
        //like_count总数增加
        $change = true === $isAdd ? '+1' : '-1';
        $sql = "update `honor_main` set `like_count` = `like_count` {$change} where `ename` = {$eName}";
        $result_hero = DB::query($sql);
        //插入记录或者删除记录
        $table = 'user_like_hero';
        if( true === $isAdd)
        {
            $result_user = DB::insert($table,[
                'user_id'=>$userId,
                'hero_id'=>$eName
            ]);
        }
        else
        {
            $result_user = DB::delete($table,[
                "user_id = {$userId}",
                "hero_id = {$eName}"
            ]);
        }
        return [$result_hero,$result_user];
    }
}
