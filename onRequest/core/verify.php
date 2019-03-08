<?php
namespace onRequest\core;
class verify
{
	/**生成验证码图片
	 * @param number $type
	 * @param number $length
	 * @param string $sess_name
	 * @param number $pixel
	 * @param number $line
	 */
	public static	function verifyImage($type = 1,$length = 4,$sess_name = 'verify',$pixel = 50,$line = 5)
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
		$chars =self::buildRandomString ( $type, $length );
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
			//fonts的路径是相对于执行本类方法的文件，而不是本文件
			$fontfile = '../../img/fonts/' . $fontfiles [mt_rand ( 0, $fontfilesLength - 1 )];
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
	
	public static function buildRandomString($type,$length)
	{
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
}