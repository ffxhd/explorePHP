<?php
class Autoload
{
    public static $filesNotExist = [];
	public static function load($className)
	{
        $filePartPath = sprintf('%s.php',str_replace('\\', '/', $className));
        if( true === isset( self::$filesNotExist[$filePartPath]))
        {
            return  false;
        }
        $filePath = ROOT.'/'.$filePartPath;
        $result = self::loadTheFile($filePath);
        if( true === $result )
        {
           return true;
        }
        self::$filesNotExist[$filePartPath] = $filePath;
        //$errMsg = '';
        //$errMsg .= "<br/>文件{$filePath}不存在，";
        //
        //echo "自动加载类文件，类：{$className}，{$errMsg}<br/><hr/>";
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

