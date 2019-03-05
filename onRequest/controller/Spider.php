<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/5
 * Time: 17:34
 */

namespace onRequest\controller;

use QL\QueryList;
class Spider
{
    public function index()
    {
        $data = QueryList::get('https://www.baidu.com/s?wd=QueryList')
            // 设置采集规则
            ->rules([
                'title'=>array('h3','text'),
                'link'=>array('h3>a','href')
            ])
            ->queryData();

        say($data);
    }
}
