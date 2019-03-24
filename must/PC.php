<?php
namespace must;
class PC{
	public  static $controller;
	public  static $method;
	private static $config;
	public static $response;

    private static function  init_controller_and_method()
    {
        /*swoole test.swoole.local:9501/test/index?field=val&field2=val2
          nginx  nginx.swoole.local:80/index.php/test/index?field=val&field2=val2
          nginx  test.php.local:80/test/index?field=val&field2=val2
        */
        //say($_SERVER,'$_SERVER');
        $defaultController = readConfig('defaultAction','controller');
        $defaultMethod = readConfig('defaultAction','method');
        //
        $url = $_SERVER['REQUEST_URI'];
        $url = ltrim($url,'/');
        $arr = explode('/',$url);
        //
        $ctrl = getItemFromArray($arr,0, '');
        $ctrl = $ctrl === '' ?  $defaultController : $ctrl;
        $ctrl = ucfirst($ctrl);
        $ctrlFull = "onRequest\\controller\\".$ctrl;
        self::$controller = $ctrlFull;
        //
        $method  = getItemFromArray($arr,1, '');
        self::$method = $method;
        $method = $method === '' ? $defaultMethod : $method;
        unset($arr);
        //
        /*say($ctrl,'tpl-$ctrl');
        say($method,'C-$method');*/
        if( true === IS_LOCAL)
        {
            //检测方法是否存在
            $rc = new  \ReflectionClass($ctrlFull);
            if( false === $rc->hasMethod($method))
            {
                $data = creatApiData(-1000,"{$ctrl}控制器不存在{$method}()");
                return outputApiData($data);
            }
            //是否为public


            //
        }
        $obj = new  $ctrlFull();
        $obj->$method();
        unset($obj);
        //(new $ctrl())->$method();
    }

	public static function run()
    {
        global $config;
		self::$config = $config;
		//DB::initial_db_config($config['db_config']);
        new  DB($config['db_config']);
		self::init_controller_and_method();
	}

	public static function outputResponse($str)
    {
        self::$response->status(200);
        self::$response->write($str);
    }

}
