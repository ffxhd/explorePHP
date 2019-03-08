<?php
namespace onRequest\core\db;
interface db
{
	public function err($error);
	public function connect($host,$user,$password,$databaseName,$charset);
	public function query($sql);
	public function query_only($sql,$toInsert);
	public function findAll($query);
	public function findOne($query);
	public function findResult($query,$row=0,$field=0);
	public function insert($table,$arr);
	public function update($table,$arr,$where);
	public function del($table,$where);
}
