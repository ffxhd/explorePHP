<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/29
 * Time: 16:54
 */

namespace onRequest\core\db;;
class MyMongo
{
    public  $client = null;
    public $db = null;
    public $collection = null;
    public function __construct()
    {
        $this->client = new \MongoClient(); // 连接默认主机和端口为：mongodb://localhost:27017
    }

    public function selectDB($dbName)
    {
        $this->db = $this->client->selectDB($dbName);
    }

    public function createCollection($dbName)
    {
        /*if($this->db === null)
        {
            $this->db = $this->connection->local;
        }*/
        $this->collection =  $this->db->createCollection($dbName);
    }


    protected function selectCollectionOrNot($table)
    {
        if($this->collection === null)
        {
            $this->collection = $this->db->selectCollection($table);
        }
    }

    public function insert($table, $array)
    {
        $this->selectCollectionOrNot($table);
        $this->collection->insert($array);
        return  $array['_id']->{'$id'};
    }

    public function update($table,$where, $array)
    {
        /*Array
        (
            [n] => 1
            [nModified] => 1
            [ok] => 1
            [err] =>
            [errmsg] =>
            [updatedExisting] => 1
        )*/
        $this->selectCollectionOrNot($table);
        $result= $this->collection->update($where, [ '$set' => $array ] );
        say($result,'mongo-更新');
        if( $result['ok'] !== 1)
        {
            return  $result['errmsg'];
        }
        return  true;
    }

    public function deleteOne($table,$where)
    {
        $this->selectCollectionOrNot($table);
        return  $this->collection->remove($where, [ 'justOne' => true ] );
    }

    public function findOne($table,$where,$fields=[])
    {
        $this->selectCollectionOrNot($table);
        if(isset($where['_id']))
        {
            $where['_id'] = new \MongoId($where['_id']);
        }
        return  $this->collection->findOne($where, $fields);
    }
}