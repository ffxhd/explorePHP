<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/22
 * Time: 19:48
 */

namespace onRequest\core\session;
class SessionIO
{
    public  $session = [];
    
    private  function read(string $sessionId):array
    {
        $arr = getItemFromArray($this->session, $sessionId,null );
        if( $arr == null )
        {
            return [];
        }
        return  getItemFromArray($arr,'data',[]);
    }

    private   function write( string $session_id ,  $session_data, int $request_time )
    {
        $unixTime = self::getFutureTime($request_time);
        $this->session[$session_id] = [
            'expire_time'=> $unixTime,
            'expire_format'=> date('Y-m-d H:i:s',$unixTime),
            'data' => $session_data
        ];
    }

}