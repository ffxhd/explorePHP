<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/2
 * Time: 23:29
 */
namespace onRequest\controller;
use onRequest\testTrait\A;
//use \testTrait\A2,\testTrait\B2;
class  TestTrait extends A{
    use \onRequest\testTrait\A2,//必须以/开头，
        \onRequest\testTrait\B2
    {
        \onRequest\testTrait\A2::sayHi  insteadof  \onRequest\testTrait\B2;
    }//trait类必须放在class里边，才能显示出“插足”的效果。
    public function index()
    {
        $this->sayGoodBye();
        $this->sayHi();
        $this->sayHello();
        self::sayNaNaNa();
        self::sayHello();
        self::sayGoodBye();
    }
}



