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
        require  ROOT.'/phpLibrary/Tightenco/Collect/Support/helpers.php';
        require  ROOT.'/phpLibrary/GuzzleHttp/src/functions.php';
        require  ROOT.'/phpLibrary/GuzzleHttp/Psr7/functions.php';
        //require '/home/ffxhd/samba_share/swooleNoob/phpLibrary/QL/QueryList.php';
        $data = QueryList::get('http://www.nipic.com')->find('img')->attrs('src');;
        say($data);
    }
}
