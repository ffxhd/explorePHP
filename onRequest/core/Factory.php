<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/20
 * Time: 9:14
 */

namespace onRequest\core;
use onRequest\core\MyTpl;

class Factory
{
    public function __call($name, $arguments)
    {
        // TODO: Implement __call() method.
        say($name,'$name');
        say($arguments,'$arguments');
        $obj = new MyTpl();

        //初次尝试
        //$obj->hello($arguments);

        //升级
        try{
            $reflectionMethod = new \ReflectionMethod('\core\MyTpl', 'hello');
            $reflectionMethod->invokeArgs($obj,$arguments);
        }
        catch(\ReflectionException $exception)
        {
            say('ReflectionException');
        }

    }

}