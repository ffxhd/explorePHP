<?php
//require 'string.func.php';
namespace onRequest\core;
use onRequest\core\wpfString;
class upload
{
	/**构建文件上传信息
	 * @param $FILES
	 * @return array
	 */
	private static function buildInfo($FILES)
	{
		if(!$FILES)
		{
			return array() ;
		}//'没有文件上传,故没有文件信息'
		$i=0;
		$files = array();
		foreach($FILES as $Files)//$_FILES三维数组 as $Files二维数组
		{
			if(is_string($Files['name']))//如果是单文件
			{
				$files[$i]=$Files;
				$i++;
			}
			else //多文件
			{
				foreach($Files['name'] as $key=>$val)
				{
					$files[$i]['name']=$val;
					$files[$i]['size']=$Files['size'][$key];
					$files[$i]['tmp_name']=$Files['tmp_name'][$key];
					$files[$i]['error']=$Files['error'][$key];
					$files[$i]['type']=$Files['type'][$key];
					$i++;
				}
			}
		}
		//printr($files);exit;
		return $files;
	}

	public static $mes = '';
    public static $noticeArray = array();
	/**
	 * 上传多文件或(和)多个单文件
	 * @param array $FILES
	 * @param string $path 文件夹
	 * @param array $allowExt 允许的扩展名
	 * @param int $maxSize 允许的文件最大大小
	 * @param bool $imgFlag 是否校验图片的真实性
	 * @return array|string 成功上传的文件组的名称
	 */
	public static function uploadFile($FILES,$path='uploads',
									  $allowExt=array('gif','jpeg','png','jpg','wbmp'),
									  $maxSize=2097152,$imgFlag=true)
	{
		if(!file_exists($path))
		{
			mkdir($path,0777,true);	//创建文件夹
		}
		$i = 0;
		$files = self::buildInfo( $FILES );
		if( !( $files && is_array( $files ) ) )
		{
			return '没有文件信息数组';
		}
		$uploadedFiles = array();
        //$noticeAll = '';
        //$notice = '';
		foreach($files as $file)
		{
			if($file['error'] === UPLOAD_ERR_OK)
			{
				$ext= wpfString::getExt($file['name']);
				//检测文件的扩展名
				if(!in_array($ext,$allowExt))
				{
					//$notice = $file['name'].'是非法文件类型<br/>';
                    self::$noticeArray[] = $file['name'].'是非法文件类型';
					//$imgFlag = false;
                    continue;
				}
				//校验是否是一个真正的图片类型
				if( $imgFlag )
				{
                    $isImageFile = function_exists('exif_imagetype') ?
						@exif_imagetype( $file['tmp_name'] ) : @getimagesize($file['tmp_name']);
                    //$isImageFile = getimagesize($file['tmp_name']);
					if( $isImageFile === false )
					{
						//$notice = $file['name'].'不是真正的图片类型<br/>';
                        self::$noticeArray[] = $file['name'].'不是真正的图片类型';
                        continue;
					}
				}
				//上传文件的大小
				if($file['size']>$maxSize)
				{
					//$notice.='上传文件'.$file['name'].'过大<br/>';
                    self::$noticeArray[] = '上传文件'.$file['name'].'过大';
                    continue;
				}
				if(!is_uploaded_file($file['tmp_name']))
				{
					//$notice.= $file['name'].'不是通过HTTP POST方式上传上来的<br/>';
                    self::$noticeArray[] = $file['name'].'不是通过HTTP POST方式上传上来的';
                    continue;
				}
//				if($notice=='')
//				{
					$filename = wpfString::getUniName().'.'.$ext;
					$destination = $path.'/'.$filename;
					if(move_uploaded_file($file['tmp_name'], $destination))
					{
						//echo $file['name'],'上传成功!<br/>';
						//$file['name'] = $filename;
						//unset( $file['tmp_name'], $file['size'], $file['type'], $file['error'] );
						//$uploadedFiles[$i] = $file;
                        unset($file['name'], $file['tmp_name'], $file['size'], $file['type'], $file['error'] );
                        $uploadedFiles[$i] = $filename;
                        $i++;
					}
				//}
			}
			else
			{
				$mes = '';
				switch($file['error'])
				{
					case 1:
						$mes='超过了配置文件上传文件的大小<br/>';//UPLOAD_ERR_INI_SIZE
						break;
					case 2:
						$mes='超过了表单设置上传文件的大小<br/>';			//UPLOAD_ERR_FORM_SIZE
						break;
					case 3:
						$mes='文件部分被上传<br/>';//UPLOAD_ERR_PARTIAL
						break;
					case 4:
						$mes='没有文件被上传<br/>';//UPLOAD_ERR_NO_FILE
						break;
					case 6:
						$mes='没有找到临时目录<br/>';//UPLOAD_ERR_NO_TMP_DIR
						break;
					case 7:
						$mes='文件不可写<br/>';//UPLOAD_ERR_CANT_WRITE;
						break;
					case 8:
						$mes='由于PHP的扩展程序中断了文件上传<br/>';//UPLOAD_ERR_EXTENSION
						break;
				}
				//self::$mes = $mes;
                self::$noticeArray[] = $mes;
			}
		}
		return $uploadedFiles;
	}
}
