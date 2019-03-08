<?php
namespace onRequest\core;
use onRequest\core\wpfString;
class image
{
	/**生成验证码图片
	 * @param number $type
	 * @param number $length
	 * @param string $sess_name
	 * @param number $pixel
	 * @param number $line
	 */
	public static function verifyImage($type = 1,$length = 4,$sess_name = 'verify',$pixel = 50,$line = 5)
	{
		session_start();
		// 创建画布
		$width = 100;
		$height = 40;
		$image = imagecreatetruecolor ( $width, $height );
		$white = imagecolorallocate ( $image, 200, 255, 255 );
		$black = imagecolorallocate ( $image, 0, 0, 0 );
		// 用矩形填充画布
		imagefilledrectangle ( $image, 1, 1, $width - 2, $height - 2, $white );
		$chars = string::buildRandomString ( $type, $length );
		$_SESSION[$sess_name] = $chars;
		$fontfiles = array (
				'MSYH.TTC',
				'MSYHBD.TTC',
				'MSYHL.TTC',
				'SIMHEI.TTF',
				'SIMKAI.TTF',
				'SIMSUN.TTC'
		);
		$fontfilesLength = count ( $fontfiles );
		for($i = 0; $i < $length; $i ++)
		{
			$size = mt_rand ( 14, 18 );
			$angle = mt_rand ( - 15, 15 );
			$x = 5 + $i * $size;
			$y = mt_rand ( 20, 26 );
			$color = imagecolorallocate ( $image, mt_rand ( 50, 90 ), mt_rand ( 80, 200 ), mt_rand ( 90, 180 ) );
			$fontfile = '../fonts/' . $fontfiles [mt_rand ( 0, $fontfilesLength - 1 )];
			$text = substr ( $chars, $i, 1 );
			imagettftext ( $image, $size, $angle, $x, $y, $color, $fontfile, $text );
		}
		// 干扰点
		if ($pixel)
		{
			for($i = 0; $i < 50; $i ++)
			{
				imagesetpixel ( $image, mt_rand ( 0, $width - 1 ), mt_rand ( 0, $height - 1 ), $black );
			}
		}
		// 干扰线
		if ($line)
		{
			for($i = 1; $i < $line; $i ++)
			{
				$color = imagecolorallocate ( $image, mt_rand ( 50, 90 ), mt_rand ( 80, 200 ), mt_rand ( 90, 180 ) );
				imageline ( $image, mt_rand ( 0, $width - 1 ), mt_rand ( 0, $height - 1 ), mt_rand ( 0, $width - 1 ), mt_rand ( 0, $height - 1 ), $color );
			}
		}
		header ( "Content-type:image/png" );
		imagepng ( $image );
		imagedestroy ( $image );
	}
	
	/**
	 * 生成缩略图
	 * @param string $filename 要缩略的图片名称
	 * @param number $scale 缩略比例(小数),默认0.5
	 * @param number $dst_w 指定缩略图宽度
	 * @param number $dst_h 指定缩略图高度
	 * @param string $destination 举例:thumb/view.jpeg
	 * @param boolean $isReservedSource 是否删除原文件,默认保留
	 * @return string
	 */
	public static function thumb($filename,$destination=null,$dst_w=null,$dst_h=null,$isReservedSource=true,$scale=0.5)
	{
		if(!file_exists($filename)) die($filename.'不存在---thumb');
		list($src_w,$src_h,$imagetype)=getimagesize($filename);//$imagetype=2,如果$filename是真实图片
		if(is_null($dst_w)||is_null($dst_h))
		{
			$dst_w=ceil($src_w*$scale);
			$dst_h=ceil($src_h*$scale);
		}
		$mime=image_type_to_mime_type($imagetype);//image/扩展名;例如image/jpeg
		//$type=image_type_to_extension($info[2],false);//图片扩展名;其中$info=getimagesize($filename);
		$createFun=str_replace('/', 'createfrom', $mime);//magecreatefrom扩展名;例如imagecreatefromjpeg
		//$createFun="imagecreatefrom{$type}";
		$outFun=str_replace('/', null, $mime);//image扩展名;例如imagejpeg
		//$outFun="image{$type}";
		$src_image=$createFun($filename);
		$dst_image=imagecreatetruecolor($dst_w, $dst_h);
		imagecopyresampled($dst_image, $src_image, 0,0,0,0, $dst_w, $dst_h, $src_w, $src_h);
		//举例:$destination='thumb/cock.jpeg'
		if( $destination && !file_exists( dirname($destination) ) )
		{
			mkdir( dirname($destination),0777,true );//创建目录
		}
		$dstFilename= $destination==null?string::getUniName().'.'.string::getExt($filename):$destination;
		$outFun($dst_image,$dstFilename);//保存缩略图
		imagedestroy($src_image);
		imagedestroy($dst_image);
		if(!$isReservedSource)
		{
			unlink($filename);
		}
		return $dstFilename;
	}
	
	/**
	 * 添加文字水印
	 * @param string $filename 图片路径
	 * @param string $content 作为水印的文字内容
	 * @param string $fontFile  文字字体
	 */
	public static function addWaterText($filename,$content='枪蛋公司,www.gun-eggs.com',$fontFile='SIMHEI.TTF')
	{
		$info=getimagesize($filename);
		$type=image_type_to_extension($info[2],false);
		$creatImg="imagecreatefrom{$type}";
		$image=$creatImg($filename);
		$font='../fonts/'.$fontFile;
		$color=imagecolorallocatealpha($image,255,255,255,50);
		imagettftext($image,20,0,20,30,$color,$font,$content);
		//header('Content-type:'.$info['mime']);
		$func="image{$type}";
		//$func($image);
		$func($image,$filename);
		imagedestroy($image);
	}
	//waterText('image/MuscleTxuDarkBlue.jpg', '枪蛋公司www.gun-eggs.com');
	//waterPic('image/MuscleTxuDarkBlue.jpg','image/MuscleTxuBlue.jpg');
	/**
	 * 添加图片水印
	 * @param string $filename 主图片路径
	 * @param string $imageMark 作为水印的图片的路径
	 */
	public static function addWaterPic($filename,$imageMark='images/MuscleTxuBlue.jpg')
	{
		$info=getimagesize($filename);
		$type=image_type_to_extension($info[2],false);
		$fun="imagecreatefrom{$type}";
		$image=$fun($filename);
	
		$info2=getimagesize($filename);
		$type2=image_type_to_extension($info2[2],false);
		$fun2="imagecreatefrom{$type2}";
		$water=$fun2($imageMark);
	
		imagecopymerge($image,$water,20,30,0,0,$info2[0],$info2[1],30);
		imagedestroy($water);
	
		//header('Content-type:'.$info['mime']);
		$funs="image{$type}";
		//$funs($image);
		$funs($image,$filename);
		imagedestroy($image);
	}
}

?>
