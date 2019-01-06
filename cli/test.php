<?php
//echo __DIR__;
require '/home/ffxhd/samba_share/explorePHP/function/function_debug.php';
/*echo "\033[1;33m 你好，世界 \033[0m \n";
echo "\033[1;33m你好，世界\033[0m\n";
echo "\033[1;33m[你好，世界]\033[0m\n";*/
//echo "几大块的时刻=>[boolean]  \e[0;32m[true]\e[0m\n";
//echo "\e[47m\e[1;30m你好，世界\e[0m\e[0m\n";
/*$arr = \pfDebug::$foregroundColors;
foreach ($arr as $color => $value )
{
    echo "\e[{$value}m {$color} \e[0m \n";
}

$arr = \pfDebug::$backgroundColors;
foreach ($arr as $color => $value )
{
    echo "\e[{$value}m {$color} \e[0m \n";
}*/
say('几大块的时刻',true);
//say(true);
say('几大块的时刻',false);
//say(false);
say('几大块的时刻',null);
//say(null);
say('几大块的时刻',134.45);
//say(134.45);
say('几大块的时刻','1');
say('几大块的时刻','/index/index');
//say('/index/index');
//say('几大块的时刻','');
say('');
$a = new \stdClass();
$a->price=12;
$a->color='light_blue';
say('几大块的时刻',['key'=>'string','key2'=>$a]);
//say(['g'=>'gv','g2'=>'gv']);
echo $skkjd;