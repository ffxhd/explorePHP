<?php
/**
 * Created by PhpStorm.
 * User: 飞飞小坏蛋
 * Date: 2017/7/12
 * Time: 14:37
 */

//-----------redis----------------------------------------------------------------------

function getRedisConnection()
{
    static $redis = null;
    if( null === $redis )
    {
        $redis = new \Redis();
        $result = $redis->connect('127.0.0.1',6379);
        //say($result,'$result-$redis->connect');
        if( false === $result )
        {
            say( 'redis服务未开启');
            exit;
        }
        $redis->auth('123456');
        $redis->select(1);
    }
    return $redis;
}

function getHashInfoFromRedis($staffKey)
{
    $redis = getRedisConnection();
    //查，返回哈希表key中的所有域和值。[当key不存在时，返回一个空表]
    return $redis->hgetall($staffKey);
}

function isEmptyHashInfoFromRedis($info )
{
    return empty($info);
}

function setHashInfo($key,$arr)
{
    $redis = getRedisConnection();
    return $redis->hmset($key,$arr);//增，改，设置多值$arr为(索引|关联)数组,$arr[key]=field, [ true ]
}

function setSomeHashKeys($keyInRedis,$hashArr,$packName='')
{
    $redis = getRedisConnection();
    $isNeedPack = $packName === '';
    $keyInRedis =  true === $isNeedPack ? $keyInRedis : "{$packName}:{$keyInRedis}";
    $opResultArr = array();
    /*foreach($hashArr as $hashKey => $hashValue)
    {
        //增，改，将哈希表key中的域field的值设为value,不存在创建,存在就覆盖【1 | 0】
        $opResultArr[$hashKey] = $redis->hset($keyInRedis,$hashKey,$hashValue);
    }*/
    $opResultArr = $redis->hmset($keyInRedis,$hashArr);
    return $opResultArr;
}
