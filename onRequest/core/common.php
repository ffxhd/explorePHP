<?php
namespace onRequest\core;
class common
{
	public static function alertMes($mes,$url)
	{
		echo "<script>alert('{$mes}');window.location='{$url}';</script>";
	}
	
	/**打印数组
	 * @param unknown $arr
	 */
	public static function sayArr($arr,$str=null)
	{
		$str= $str==null?null:$str;
		echo '<pre>',$str,'********';print_r($arr);echo '</pre><hr/>';
	}
	public static function DBFindToString($data)
	{
		foreach($data as $arr)
		{
			foreach($arr as $key=>$value)
			{
				$strArr[]=$value;
			}
		}
		$str=implode(',' ,$strArr).'<br/>';
		return $str;
	}
}
