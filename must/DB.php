<?php
namespace must;
//工厂类,用于统一管理类的实例化
class DB{
	public static $db = null;
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
        //self::$db = new \onRequest\core\db\MySQLiOOP($host,$user,$password,$databaseName,$charset);
    }

    public static function showTables($db_name)
    {
        $sql = "show tables from {$db_name}";
        $query = self::$db->query($sql);
        $list = self::$db->findAll($query);
        return $list;
    }

    public static function fetchSqlArr()
    {
        return self::$db ->fetchSqlArr();
    }

	public static function query($sql)
	{
	    if( self::$db === null )
        {
            self::init();
        }
		return self::$db ->query($sql);
	}

    public static function getAffectedRows()
    {
        return self::$db->getAffectedRows();
    }

    public static function multiQuery($sql)
	{
	    self::init();
		return self::$db ->multiQuery($sql);
	}

    public static function multiFind($sqlArr,$allItemAreList=false)
    {
        self::init();
        return self::$db ->multiFind($sqlArr,$allItemAreList);
    }

	public static function query_only($sql,$toInsert)
	{
        self::init();
		return self::$db -> query_only($sql,$toInsert);
	}

	public static function findAll($sql)
	{
        self::init();
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

	public static function delete($table,$where)
	{
        self::init();
		return self::$db->delete($table,$where);
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

    public static function resetTable($table)
    {
        self::init();
        //清空表,从1开始
        $sql = "TRUNCATE TABLE `{$table}`;ALTER TABLE `{$table}` AUTO_INCREMENT = 1";;
        return self::$db ->multiQuery($sql);
    }
}
