<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/20
 * Time: 9:16
 */

namespace onRequest\core;


class MyTpl
{
    public function  hello_1($argArr)
    {
        $argumentsArr = func_get_args();
        say($argumentsArr,'$argumentsArr');
    }

    public function  hello($key,$value,$bool)
    {
        say($key,'$key');
        say($value,'$value');
        say($bool,'$bool');
    }

}