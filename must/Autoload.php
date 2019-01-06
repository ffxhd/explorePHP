<?php 
class Autoload
{
	public static function load($className)
	{
        $filePath = sprintf('%s.php',str_replace('\\', '/', $className));
        $filePath = ROOT.'/'.$filePath;
        if( true === file_exists($filePath))
        {
            require_once  $filePath;
        }
        else
        {
            echo "自动加载类文件，类：{$className}，文件{$filePath}不存在<br/>";
        }
	}
}
spl_autoload_register(array('Autoload','load'));

