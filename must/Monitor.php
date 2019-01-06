<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/19
 * Time: 20:18
 */
namespace must;
class Monitor
{
    public     $notify;
    protected  $dirToListen;
    public     $allItems;
    protected $lastSthHappenTime;
    public function  __construct($dirToListen)
    {
        $this->notify = inotify_init();
        $this->dirToListen = $dirToListen;
    }

    public function watchMainDir()
    {
        //主目录打上监听器
        $this->allItems[]= [ $this->dirToListen => '主目录' ];
        $this->addWatch_dir($this->dirToListen);
    }

    protected  function addWatch_dir($item)
    {
        inotify_add_watch($this->notify, $item, IN_CREATE | IN_DELETE | IN_MODIFY );
    }

    public function recursiveWatch($dirToListen)
    {
        //主目录的文件、子目录及其文件打上监听器
        $arr = scandir($dirToListen);
        if(false === is_array($arr))
        {
            return false;
        }
        foreach($arr as $str )
        {
            if( $str === '.'|| $str === '..')
            {
                continue;
            }
            $item = $dirToListen.'/'.$str;
            if( true === is_file($item) )
            {
                $this->allItems[]= [ $item => '文件'];
                $this->addWatch_dir($item);
            }
            else if( true === is_dir($item) )
            {
                $this->allItems[]= [ $item => '目录' ];
                $this->addWatch_dir($item);
                $this->recursiveWatch($item);
            }
        }
        return true;
    }

    public function watchSthHappen($reloadFunc)
    {
        //\console::sayMultiInTerminal('watchSthHappen') ;
        $events = inotify_read($this->notify);
        if (true === empty($events))
        {
            return false;
        }
        /*修改一个文件，次函数运行至少2次的问题：第一次载入，之后不予理睬 */
         $time = time();
        $this->lastSthHappenTime = $this->lastSthHappenTime ?? $time;
        if($time ===  $this->lastSthHappenTime)
        {
            return false;
        }
        /*连续修改，非常短时间内多次重新载入代码的问题：计算距离上一次载入的时间，
        */
        call_user_func($reloadFunc);
        $this->lastSthHappenTime = $time;
        /*$formatTime = date('Y-m-d H:i:s',$time);
        echo '有变化---'.microtime().'---'.$formatTime.PHP_EOL;
        print_r($events);*/

        //如果新增文件夹或者文件，则对其打上监听器=>重头开始监听
        $isStartOver = false;
        foreach($events as $event)
        {
            if(  1073742080 === $event['mask'])
            {
                $isStartOver = true;
                break;
            }
        }
        if($isStartOver === true )
        {
            $this->allItems = [];
            $this->watchMainDir();
            $this->recursiveWatch($this->dirToListen);
            //echo '重头开始监听：';
            //var_dump($allItems);
        }

    }
}