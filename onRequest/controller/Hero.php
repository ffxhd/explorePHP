<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/14
 * Time: 17:56
 */

namespace onRequest\controller;
use must\DB;
use onRequest\spiderModel\HeroModel;
use onRequest\spiderModel\Like;
use onRequest\controller\User;
class Hero
{
    protected function setFieldWithMean($fields,$fieldsMeans)
    {
        $arr = [];
        foreach($fields as $key => $field)
        {
            $arr[$field] = $fieldsMeans[$key];
        }
        return $arr;
    }

    public function getAll()
    {
        $eName = getItemFromArray($_GET,'eName',0);
        if( $eName < 1)
        {
            $data = creatApiData(1,'需要英雄的id');
            return outputApiData($data);
        }
        $eName = intval($eName);
        $obj = new HeroModel();
        $apiData = $obj->getAll($eName);
        //
        $isLike = false;
        if( true === User::isHaveLogin())
        {
            $userId = User::getUserId();
            $obj = new Like();
            $isLike = $obj->isLikeTheHero($eName,$userId);
        }
        $apiData['is_like'] = $isLike;
        //
        $data = creatApiData(0,'获取英雄的详情数据成功',$apiData);
        return outputApiData($data);
    }

    public function getDetail()
    {
        $eName = getItemFromArray($_GET,'eName',0);
        if( $eName < 1)
        {
            $data = creatApiData(1,'需要英雄的id');
            return outputApiData($data);
        }
        $eName = intval($eName);
        $fieldsArr = HeroModel::getHeroMainFieldArr();
        $fieldsMeansArr = [
            '主键', '英雄编号', '外号', '英雄名字', '英雄视频详情url', '生存能力', '攻击伤害',
            '技能效果', '上手难度', '英雄故事', '铭文附加介绍', '出装附加介绍', '英雄介绍视频链接'
        ];
        $fields = joinFieldsToSelect($fieldsArr);
        $sql = "select {$fields} from `honor_main` where `ename`= {$eName}";
        $info = DB::findOne($sql);
        $isSuccess = false === empty($info) ;
        $resMsg = true === $isSuccess ? '成功':'失败';
        $apiData = [
            'info'=>$info,
            'field_mean'=>$this->setFieldWithMean($fieldsArr,$fieldsMeansArr),
        ];
        $data = creatApiData(0,"获取英雄详情数据{$resMsg}", $apiData);
        return outputApiData($data);
    }

    public function getSkins()
    {
        $eName = getItemFromArray($_GET,'eName',0);
        if( $eName < 1)
        {
            $data = creatApiData(1,'需要英雄的id');
            return outputApiData($data);
        }
        $eName = intval($eName);
        $fieldsArr = [
            'id', 'ename', 'pf_name',
        ];
        $fieldsMeansArr = [
            '主键', '英雄编号', '英雄皮肤名字', '英雄皮肤大图url', '英雄皮肤小图'
        ];
        $skinField = "concat('https:',`lpf_src`) as `lpf_src`";
        $skinField2 = "concat('https:',`bpf_src`) as `bpf_src`";
        $field_mean = $this->setFieldWithMean($fieldsArr,$fieldsMeansArr);
        $field_mean['lpf_src'] = '英雄皮肤大图url';
        $field_mean['bpf_src'] = '英雄皮肤小图';
        $fields = joinFieldsToSelect($fieldsArr);
        $sql = "select {$fields},{$skinField},{$skinField2} from `honor_pf` where `ename`= {$eName}";
        $info = DB::findAll($sql);
        $isSuccess = false === empty($info) ;
        $resMsg = true === $isSuccess ? '成功':'失败';
        $apiData = [
            'list'=>$info,
            'field_mean'=>$field_mean,
        ];
        $data = creatApiData(0,"获取英雄皮肤数据{$resMsg}", $apiData);
        return outputApiData($data);
    }

    public function getSkills()
    {
        $eName = getItemFromArray($_GET,'eName',0);
        if( $eName < 1)
        {
            $data = creatApiData(1,'需要英雄的id');
            return outputApiData($data);
        }
        $eName = intval($eName);
        $fieldsArr = [
            'id', 'ename', 'skill_name', 'cool_value', 'expend', 'skill_intr', 'skill_tips'
        ];
        $fieldsMeansArr = [
            '主键', '英雄编号', '技能名字', '冷却值', '消耗', '技能简介', '技能附加介绍'
        ];
        $srcField = "concat('https:',`skill_src`) as `skill_src`";
        $field_mean = $this->setFieldWithMean($fieldsArr,$fieldsMeansArr);
        $field_mean['skill_src'] = '技能图片url';
        $fields = joinFieldsToSelect($fieldsArr);
        $sql = "select {$fields},{$srcField} from `honor_skill` where `ename`= {$eName}";
        $info = DB::findAll($sql);
        $isSuccess = false === empty($info) ;
        $resMsg = true === $isSuccess ? '成功':'失败';
        $apiData = [
            'list'=>$info,
            'field_mean'=>$field_mean
        ];
        $data = creatApiData(0,"获取英雄技能数据{$resMsg}", $apiData);
        return outputApiData($data);
    }

    public function getInscriptionMatch()
    {
        $eName = getItemFromArray($_GET,'eName',0);
        if( $eName < 1)
        {
            $data = creatApiData(1,'需要英雄的id');
            return outputApiData($data);
        }
        $eName = intval($eName);
        $fieldsArr = [
            'id', 'ename', 'name', HeroModel::$bonus_field
        ];
        $fieldsMeansArr = [
            '主键', '英雄编号', '铭文名称', '铭文效果'
        ];
        $srcField = "concat('https:',`src`) as `src`";
        $field_mean = $this->setFieldWithMean($fieldsArr,$fieldsMeansArr);
        $field_mean['src'] = '铭文图片url';
        $fields = joinFieldsToSelect($fieldsArr);
        $sql = "select {$fields},{$srcField} from `honor_inscription_match` where `ename`= {$eName}";
        $data = DB::findAll($sql);
        $data = HeroModel::washInscriptionMatch($data);
        $isSuccess = false === empty($data) ;
        $resMsg = true === $isSuccess ? '成功':'失败';
        $apiData = [
            'list'=>$data,
            'field_mean'=>$field_mean
        ];
        $data = creatApiData(0,"获取英雄铭文搭配数据{$resMsg}", $apiData);
        return outputApiData($data);
    }

    public function getSkillsPlusSuggestions()
    {
        $eName = getItemFromArray($_GET,'eName',0);
        if( $eName < 1)
        {
            $data = creatApiData(1,'需要英雄的id');
            return outputApiData($data);
        }
        $eName = intval($eName);
        $fieldsArr = [
            'id', 'ename', 'm_up_skill', 'a_up_skill',
            'sum_skill',
        ];
        $fieldsMeansArr = [
            '主键', '英雄编号', '主升技能', '副升技能',
             '召唤师技能',
        ];
        $field_mean = $this->setFieldWithMean($fieldsArr,$fieldsMeansArr);
        //
        $srcField = 'm_up_src';
        $field_mean[$srcField] = '主升技能图片';
        $srcField1 = "concat('https:',`{$srcField}`) as `{$srcField}`";
        //
        $srcField = 'a_up_src';
        $field_mean[$srcField] = '副升技能图片';
        $srcField2 = "concat('https:',`{$srcField}`) as `{$srcField}`";
        //
        //$srcField = 'sum_skill_src';
        $srcField = HeroModel::$sum_skill_field;
        $field_mean[$srcField] = '召唤师技能图片url';
        $srcField3 = "concat('https:',`{$srcField}`) as `{$srcField}`";
        //
        $fields = joinFieldsToSelect($fieldsArr);
        $sql = "select {$fields},{$srcField1},{$srcField2},{$srcField3} from `honor_sug` where `ename`= {$eName}";
        $info = DB::findOne($sql);
        //
        $info = HeroModel::washSkillsPlusSuggestions($info);
        //
        $isSuccess = false === empty($info) ;
        $resMsg = true === $isSuccess ? '成功':'失败';
        $apiData = [
            'info'=>$info,
            'field_mean'=>$field_mean
        ];
        $data = creatApiData(0,"获取英雄技能加点建议数据{$resMsg}", $apiData);
        return outputApiData($data);
    }

    public function getOutfitSuggestion()
    {
        $eName = getItemFromArray($_GET,'eName',0);
        if( $eName < 1)
        {
            $data = creatApiData(1,'需要英雄的id');
            return outputApiData($data);
        }
        $eName = intval($eName);
        $fieldsArr = [
            'id', 'ename', 'eq_name', 'eq_price', 'eq_total_price', 'eq_bonus',
            'eq_introduce', 'eq_advice',
        ];
        $fieldsMeansArr = [
           '主键', '英雄编号', '出装名称', '出装售价', '装备总价', '装备效果',
            '装备介绍', '建议几',
        ];
        $field_mean = $this->setFieldWithMean($fieldsArr,$fieldsMeansArr);
        //
        $srcField = 'eq_src';
        $field_mean[$srcField] = '装备图片链接';
        $srcField1 = "concat('https:',`{$srcField}`) as `{$srcField}`";
        //
        $fields = joinFieldsToSelect($fieldsArr);
        $sql = "select {$fields},{$srcField1} from `honor_eq` where `ename`= {$eName}";
        $info = DB::findAll($sql);
        $isSuccess = false === empty($info) ;
        $resMsg = true === $isSuccess ? '成功':'失败';
        $apiData = [
            'list'=>$info,
            'field_mean'=>$field_mean
        ];
        $data = creatApiData(0,"获取英雄出装建议数据{$resMsg}", $apiData);
        return outputApiData($data);
    }

    public function getRelations()
    {
        $eName = getItemFromArray($_GET,'eName',0);
        if( $eName < 1)
        {
            $data = creatApiData(1,'需要英雄的id');
            return outputApiData($data);
        }
        $eName = intval($eName);
        $fieldsArr = [
            'id', 'ename', 'op_relation', 'op_ename', 'op_tips'
        ];
        $fieldsMeansArr = [
            '主键', '英雄编号', '对方英雄关系','对方英雄编号', '搭档说明'
        ];
        $field_mean = $this->setFieldWithMean($fieldsArr,$fieldsMeansArr);
        //
        $srcField = 'op_src';
        $field_mean[$srcField] = '对方英雄图片';
        $srcField1 = "concat('https:',`{$srcField}`) as `{$srcField}`";
        //
        $fields = joinFieldsToSelect($fieldsArr);
        $sql = "select {$fields},{$srcField1} from `honor_op` where `ename`= {$eName}";
        $info = DB::findAll($sql);
        $isSuccess = false === empty($info) ;
        $resMsg = true === $isSuccess ? '成功':'失败';
        $apiData = [
            'list'=>$info,
            'field_mean'=>$field_mean
        ];
        $data = creatApiData(0,"获取英雄关系数据{$resMsg}", $apiData);
        return outputApiData($data);
    }

    public function getVideos()
    {
        $eName = getItemFromArray($_GET,'eName',0);
        if( $eName < 1)
        {
            $data = creatApiData(1,'需要英雄的id');
            return outputApiData($data);
        }
        $eName = intval($eName);
        $fieldsArr = [
            'id', 'ename', 'video_title', 'video_link', 'video_scope',
        ];
        $fieldsMeansArr = [
            '主键', '英雄编号', '视频标题', '视频链接', '视频范围',
        ];
        $field_mean = $this->setFieldWithMean($fieldsArr,$fieldsMeansArr);
        //
        $srcField = 'video_src';
        $field_mean[$srcField] = '视频图片';
        $srcField1 = "concat('https:',`{$srcField}`) as `{$srcField}`";
        //
        $fields = joinFieldsToSelect($fieldsArr);
        $sql = "select {$fields},{$srcField1} from `honor_video` where `ename`= {$eName}";
        $info = DB::findAll($sql);
        $isSuccess = false === empty($info) ;
        $resMsg = true === $isSuccess ? '成功':'失败';
        $apiData = [
            'list'=>$info,
            'field_mean'=>$field_mean
        ];
        $data = creatApiData(0,"获取英雄视频数据{$resMsg}", $apiData);
        return outputApiData($data);
    }
}
