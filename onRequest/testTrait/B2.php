<?php
namespace onRequest\testTrait;
trait B2{
    private function  sayHello()
    {
        echo 'hello-B2<br/>';
    }

    public function  sayHi()
    {
        echo 'hi-B2<br/>';
    }

    private  static function  sayNaNaNa()
    {
        echo 'NaNaNa-B2<br/>';
    }

}