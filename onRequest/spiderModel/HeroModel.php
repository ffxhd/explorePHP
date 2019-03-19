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
    public function getAll($eName)
    {
        $fieldsArr = [
            'id', 'ename', 'title', 'cname', 'hvideo_url', 'viability', 'ad',
            'cover_skill', 'difficulity', 'hero_story', 'inscr_tips', 'eq_tips', 'honor_link'
        ];
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
            'id', 'ename', 'name', 'bouns'
        ];
        $srcField = "concat('https:',`src`) as `src`";
        $fields = joinFieldsToSelect($fieldsArr);
        $sqlArr['inscriptionMatch'] = "select {$fields},{$srcField} from `honor_inscription_match` where `ename`= {$eName}";
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
        $srcField = 'sum_skill_src';
        $srcField3 = "concat('https:',`{$srcField}`) as `{$srcField}`";
        //
        $fields = joinFieldsToSelect($fieldsArr);
        $sqlArr['skillsPlusSuggestions'] = "select {$fields},{$srcField1},{$srcField2},{$srcField3} from `honor_sug` where `ename`= {$eName}";
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
        return DB::multiFind($sqlArr);
    }
}
