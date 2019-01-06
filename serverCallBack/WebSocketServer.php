<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/30
 * Time: 19:06
 */

namespace serverCallBack;
use onRequest\core\db\MySQLiOOP;
class WebSocketServer
{

    public function onOpen($ws, $request)
    {
        $fd = $request->fd;
        $myName =  $request->get['myName'];
        //
        global $shareTable;
        $key = (string)$fd;
        $shareTable->set($key,[
            'fd'=>$fd,
            'userName'=>$myName,
        ]);
        $L = $shareTable->count();
        say('L=',$L);
        //给新上线者发送未读消息;
        /*global $server;
        foreach($server->connections as $fd)
        {
            if( $fd === $request->fd)
            {
                continue;
            }
            $ws->push($fd, $string);
        }*/
        //$ws->push($request->fd, "hello, welcome");
        //记录上线者的信息，以及$fd, 写入redis;

    }

    public function onClose($ws, $fd)
    {
        /*删除*/
        global $shareTable;
        $fd = (string)$fd;
        $shareTable->del($fd);
        //
        echo "client-{$fd} is closed\n";
    }

    public function onMessage($ws, $frame)
    {
        global $shareTable;
        $mix = $frame->data;
        $currentFd = $frame->fd;
        $mix = json_decode($mix,true);
        $toUser = $mix['toUser'];
        $a =[
           'msg' => $mix['msg'],
            'sendByWhom'=>$mix['myName']
        ];
        $string = json_encode($a,JSON_UNESCAPED_UNICODE);
        $aimFd = false;
        //是否为私聊
        if($toUser )
        {
            foreach($shareTable as $row)
            {
                if( $row['userName'] === $toUser)
                {
                    $aimFd = $row['fd'];
                    break;
                }
            }
            //
            $aimFd = (integer)$aimFd;
            if( $aimFd !==  false)
            {
                $ws->push($aimFd, $string);
            }
        }
        else
        {
            global $server;
            foreach($server->connections as $aimFd)
            {
                if( $aimFd === $currentFd )
                {
                    continue;
                }
                $ws->push($aimFd, $string);
            }
        }

    }
}