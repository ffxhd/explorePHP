<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/21
 * Time: 13:40
 */
namespace onRequest\core\session;
class SessionRedis
{
    private  $redis;
    public function __construct()
    {
        $this->redis = new \Redis();
        $redisConfig = readConfig('redis');
        $this->redis->connect($redisConfig['host'],$redisConfig['port']);
        $this->redis->auth($redisConfig['password']);
        $this->redis->select(0);
    }

    private function connectRedisOrNot()
    {
        $res = $this->redis->ping();
        if( $res === '+PONG')
        {
            return true;
        }
        $redisConfig = readConfig('redis');
        $this->redis->connect($redisConfig['host'],$redisConfig['port']);
        $this->redis->auth($redisConfig['password']);
        $this->redis->select(0);
        return true;
    }

    private function getCompleteKey(string $session_id):string
    {
        return $session_id;
    }

    public function read(string $session_id ):array
    {
        $this->connectRedisOrNot();
        $session_id = $this->getCompleteKey($session_id);
        //say('$session_id',$session_id);
        $session_data = $this->redis->get($session_id);//获取redis中的指定记录
        //say('$session_data',$session_data);
        if( $session_data === false )
        {
           return  [];
        }
        return json_decode($session_data,true);
    }

    public function write(string $session_id ,array $session_data,$futureTime )
    {
        $this->connectRedisOrNot();
        $session_id = $this->getCompleteKey($session_id);
        $session_data = json_encode($session_data, JSON_UNESCAPED_UNICODE);
        $result = $this->redis->set($session_id,$session_data);
        if( true === $result)
        {
           $expireTime = \onRequest\core\session\SessionFactory::getExpireTime();
           $this->redis->expire($session_id,$expireTime);
        }
    }

    public function __destruct()
    {
        $this->redis->close();
    }
}
