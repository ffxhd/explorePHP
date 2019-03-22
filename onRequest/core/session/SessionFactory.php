<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/22
 * Time: 19:48
 */

namespace onRequest\core\session;
class SessionFactory
{
    protected static  $manager = null;
    protected static function initial()
    {
        if( self::$manager === null)
        {
            self::$manager = new  \onRequest\core\session\SessionRedis();
        }
    }

    protected static function getSessionKey()
    {
        global $config;
       return $config['session']['name'];
    }

    public static  function initialSessionFromPool(int $fd):string
    {
        self::initial();
        //\console::sayMultiInBrowser('工厂--initialSessionFromPool--self::$manager',self::$manager);
        $key = self::getSessionKey();
        //say('$key',$key);
        $session_id = getItemFromArray($_COOKIE,$key,'');
        //say('$_COOKIE',$_COOKIE);
        if( $session_id === '')
        {
            $_SESSION = [];
            $session_id = self::getUniqueSessionId($fd);
           /* \console::sayMultiInBrowser("\$_COOKIE中的{$key}为空，取得新的{$key}：",
                $session_id,'_COOKIE',$_COOKIE);*/
        }
        else
        {
            $_SESSION = self::read($session_id);
           /* \console::sayMultiInBrowser("\$_COOKIE中的{$key}={$session_id},值为：",
                $session_id,'初始化$_SESSION',$_SESSION);*/
        }
        //say('$_SESSION',$_SESSION);
        return $session_id;
    }

    public static function setSessionToPool(string $session_id,int $request_time,$response)
    {
        self::initial();
        //\console::sayMultiInBrowser('工厂setSessionToPool--self::$manager',self::$manager);
        self::write($session_id,$_SESSION,$request_time);
        $key = self::getSessionKey();
        /*setcookie( string $name[,
         string $value = ""[,
         int $expire = 0[,
         string $path = ""[,
        string $domain = ""[,
        bool $secure = false[,
        bool $httponly = false]]]]]] ) : bool*/
        $expire = self::getFutureTime($request_time);
        $path = '/';
        $response->cookie($key,$session_id, $expire, $path);
    }

    public static function getUniqueSessionId(int $fd):string
    {
        return uniqid("fd_{$fd}_");
    }

    private static function read(string $sessionId):array
    {
        return self::$manager->read($sessionId);
    }

    private  static function write( string $session_id ,  $session_data, int $request_time )
    {
        $futureTime = self::getFutureTime($request_time);
        //\console::sayMultiInTerminal('工厂write--self::$manager',self::$manager);
        self::$manager->write($session_id,$session_data,$futureTime);
    }

    private static function getFutureTime(int $request_time):int
    {
        return $request_time + self::getExpireTime();
    }

    public static function getExpireTime()
    {
        global $config;
        return $config['session']['expireTime'];
    }
}
