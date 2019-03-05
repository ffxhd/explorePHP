<?php
class Autoload
{
	public static function load($className)
	{
	    $errMsg = '';
        $filePartPath = sprintf('%s.php',str_replace('\\', '/', $className));
        $filePath = ROOT.'/'.$filePartPath;
        $result = self::loadTheFile($filePath);
        if( true === $result )
        {
           return true;
        }
        $errMsg .= "<br/>文件{$filePath}不存在，";
        //
        $filePath = ROOT.'/phpLibrary/'.$filePartPath;
        $result = self::loadTheFile($filePath);
        if( true === $result )
        {
            return true;
        }
        $errMsg .= "<br/>文件{$filePath}也不存在,";
        //
        $arr = explode('\\',$className);
        $a = $arr[0];
        unset($arr[0]);
        $str = implode('/',$arr);
        $filePath = ROOT."/phpLibrary/{$a}/src/{$str}.php";
        $result = self::loadTheFile($filePath);
        if( true === $result )
        {
            return true;
        }
        $errMsg .= "<br/>文件{$filePath}也不存在";
        //
        echo "自动加载类文件，类：{$className}，{$errMsg}<br/><hr/>";
	}

	public static function loadTheFile($filePath)
    {
        if( false === file_exists($filePath))
        {
            return false;
        }
        require_once  $filePath;
        return true;
    }
}
spl_autoload_register(array('Autoload','load'));

