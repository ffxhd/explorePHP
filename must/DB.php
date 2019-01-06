<?php
namespace must;
//工厂类,用于统一管理类的实例化
class DB{
	public static $db;
    public static $db_type;
    public static $config;
    public static function initial_db_config($config)
    {
        self::$db_type = $config['class'];
        self::$config = $config;
    }

    public static function init()
    {
        $db_type  = self::$db_type ;
        $config = self::$config;
        $host = $config['host'];//主机名
        $user = $config['user'];//用户名
        $password = $config['password'];//密码
        $databaseName = $config['databaseName'];//数据库名
        $charset = $config['charset'];//字符集/编码
        unset($config);
        self::$db = new $db_type($host,$user,$password,$databaseName,$charset);
    }

    public static function showTables($db_name)
    {
        $sql = "show tables from {$db_name}";
        $query = self::$db->query($sql);
        $list = self::$db->findAll($query);
        return $list;
    }

	public static function query($sql)
	{
        self::init();
		return self::$db ->query($sql);
	}

    public static function multiQuery($sql)
	{
	    self::init();
		return self::$db ->multiQuery($sql);
	}

	public static function query_only($sql,$toInsert)
	{
        self::init();
		return self::$db -> query_only($sql,$toInsert);
	}

	public static function findAll($sql)
	{
        //self::init();
        /*say(self::$config,'self::$config');
        exit;*/
		$query = self::$db->query($sql);
		return self::$db->findAll($query);	
	}

	public static function findOne($sql)
	{
        self::init();
		$query = self::$db->query($sql);
		return self::$db->findOne($query);	
	}
	
	public static function findResult($sql,$field=0,$row=0)
	{
        self::init();
		$query  = self::$db->query($sql);
		return self::$db->findResult($query,$row,$field);	
	}

	public static function findResultFromTheInfo($sql,$field)
    {
        self::init();
        $query  = self::$db->query($sql);
        return self::$db->findResultFromTheInfo($query,$field);
    }
	
	public static function insert($table,$arr)
	{
        self::init();
		return self::$db->insert($table,$arr);	
	}

	public static function update($table,$arr,$where)
	{
        self::init();
		return self::$db->update($table,$arr,$where);	
	}

	public static function del($table,$where)
	{
        self::init();
		return self::$db->del($table,$where);	
	}

	public static function getStmt($preSql)
    {
        self::init();
        return self::$db->preDeal($preSql);
    }

    public static function getPreInsertSql($table,$arr)
    {
        self::init();
        return self::$db->sqlForInsert($table,$arr,true);
    }
}