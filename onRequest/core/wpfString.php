<?php
namespace onRequest\core;
class wpfString
{
	public static function buildRandomString($type,$length)
	{
        $chars = '';
		switch ($type)
		{
			case 1 :
				$chars=join("",range(0,9));//range(0,9)创建包含0-9的数组；join,同implode,将数组连接成字符串
				break;
			case 2:
				$chars=join("",array_merge(range('a','z'),range('A','Z')));
				break;
			case 3:
				$chars=join("",array_merge(range('a','z'),range('A','Z'),range(0,9)));
				break;
		}
		if($length>strlen($chars))
		{
			exit('字符串长度不够');
		}
		$chars=str_shuffle($chars);//打乱原字符串
		$chars=substr($chars,0,$length);//截取字符串
		return $chars;
	}
	/**
	 * 产生唯一字符串
	 * @return string
	 */
	public  static function getUniName()
	{
		return md5(uniqid(microtime(true),true));
	}
	
	/**
	 * 取得文件扩展名
	 * @param string $filename
	 * @return string
	 */
	public  static function getExt($filename)
	{
		return strtolower(pathinfo($filename,PATHINFO_EXTENSION));
	}
	/**
	 *检测文件名是否合法
	 * @param string $filename
	 * @return boolean
	 */
	public static function checkFilename($filename){
		//验证文件名的合法性,是否包含/,*,<>,?,|
		$pattern = "/[\/,\*,<>,\?\|]/";
		if (preg_match ( $pattern,  $filename )) {
			return false;
		}else{
			return true;
		}
	}

}

?>