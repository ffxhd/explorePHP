<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/21
 * Time: 13:59
 */
namespace serverCallBack;
use onRequest\core\session\SessionFactory;
/** 发起http请求 $fd = 1， 服务端认为请求结束后，刷新，再次发起http请求$fd = 2,
 （一个浏览器，同一个窗口）
 * Class HttpServerCallback
 * @package must
 */
class HttpServer
{
    /**
    swoole_server $server是Swoole\Server对象
    int $fd  是连接的文件描述符，发送数据/关闭连接时需要此参数
    int $reactorId来自哪个Reactor线程
     *
     * $fd是TCP客户端连接的标识符，在Server实例中是唯一的，在多个进程内不会重复
     * fd 是一个自增数字，范围是1 ～ 1600万，fd超过1600万后会自动从1开始进行复用
     * $fd是复用的，当连接关闭后fd会被新进入的连接复用
     * 正在维持的TCP连接fd不会被复用
     *
     * 可以理解为Reactor就是nginx，Worker就是php-fpm
     一个更通俗的比喻，假设Server就是一个工厂，那Reactor就是销售，接受客户订单。
     而Worker就是工人，当销售接到订单后，Worker去工作生产出客户要的东西。
     而TaskWorker可以理解为行政人员，可以帮助Worker干些杂事，让Worker专心工作。
     *
     * onConnect/onClose这2个回调发生在worker进程内，而不是主进程。
     UDP协议下只有onReceive事件，没有onConnect/onClose事件
     */
    public function onConnect( $server,int $fd,int $reactorId)
    {

    }

    /**无论由客户端发起close还是服务器端主动调用$serv->close()关闭连接，
     都会触发此事件。因此只要连接关闭，就一定会回调此函数
     * @param $server
     * @param $fd
     * @param $reactorId
     */
    public function onClose($server,int $fd,int $reactorId)
    {
        //\console::sayMultiInTerminal('-onClose',$_POST);
    }

    //作为http_server不接受onReceive回调设置
    public function onReceive( $server, int $fd, int $reactor_id, string $data)
    {

    }

    //
    public function onRequest($request, $response)
    {
        $uri = $request->server['request_uri'];
        if ($uri === '/favicon.ico')
        {
            global $config;
            $response->sendfile($config['favicon_ico']);
            return false;
        }
        /*say('onRequest--$request',$request,
            '$response',$response);*/
        ob_start();
        try{
            //
            $_GET     = $request->get ?? [];
            $_POST    = $request->post ?? [];
            $_REQUEST = $request->request ?? [];
            $_COOKIE  = $request->cookie ?? [];
            $_FILES   = $request->files ?? [];
            global $host;
            $_SERVER = [];
            $_SERVER['SERVER_NAME'] = $host;
            $_SERVER['REQUEST_URI'] = $uri;
            $_SERVER = array_merge($_SERVER,$request->server);
            //
            isAjaxOrNot( $request->header);
            /*根据cookie，到session池中取出数据，赋值给$_SESSION，
            如果cookie中的PHPSESSID为空，需要生成唯一的sessionId
            便于业务操作*/
            $requestTime = $_SERVER['request_time'];
            $session_id = SessionFactory::initialSessionFromPool($request->fd,$response,$requestTime);
            //业务操作
            global $config;
            \must\PC::run($config);
            //将$_SESSION弄到session池中
            SessionFactory::setSessionToPool($session_id, $requestTime);
        }
        catch (\Error $e)
        {
            $errFile = $e->getFile();
            $errLine = $e->getLine();
            $errMsg = $e->getMessage();
            $errTrace = $e->getTrace();
            $errCode = $e->getCode();
            /*$previous = $e->getPrevious();
            say($previous,'$previous');*/
            throw_phpError($errCode,$errMsg, $errFile,$errLine,$errTrace);
        }
        $response->header("Content-Type", "text/html; charset=utf-8");
        $html = ob_get_clean();
        $response->end($html);
    }

    /**
     * swoole_server $serv, int $worker_id, int $worker_pid, int $exit_code, int $signal
     * @param $server
     * @param $worker_id
     * @param $worker_pid
     * @param $exit_code
     * @param $signal
     */
    public function onWorkerError($server, $worker_id,$worker_pid,$exit_code,$signal)
    {
        say('woker出现错误--', $worker_id,
            '$worker_pid', $worker_pid,
            '$exit_code', $exit_code,
            '$signal', $signal);
    }

    /**如果想使用Reload机制实现代码重载入，必须在onWorkerStart中require你的业务文件，
    而不是在文件头部。
    在onWorkerStart调用之前已包含的文件，不会重新载入代码。
     * 可以将公用的、不易变的php文件放置到onWorkerStart之前。
    这样虽然不能重载入代码，但所有Worker是共享的，不需要额外的内存来保存这些数据。
    onWorkerStart之后的代码每个进程都需要在内存中保存一份
     *
     * 是否可以共用1个redis或mysql连接
    绝对不可以。必须每个进程单独创建Redis、MySQL、PDO连接，其他的存储客户端同样也是如此。
    原因是如果共用1个连接，那么返回的结果无法保证被哪个进程处理。
    持有连接的进程理论上都可以对这个连接进行读写，这样数据就发生错乱了。
     * 在swoole_server中，应当在onWorkerStart中创建连接对象
     * @param $server
     * @param $worker_id
     * @return bool
     */
    public function onWorkerStart($server, $worker_id)
    {
        //热更新
       /* $startTime = date('Y-m-d H:i:s',time());
       \console::sayMultiInTerminal($startTime.'--onWorkerStart--$worker_id',
           $worker_id);*/
       if( false === IS_LOCAL )
       {
           return false;
       }
        $dirToListen = OnRequestDir;
        $monitor = new \must\Monitor($dirToListen);
        $monitor->watchMainDir();
        $monitor->recursiveWatch($dirToListen);
        //$allItems = $monitor->allItems;
        //say($allItems,'onWorkerStart,监听开始：');
        /*while(true)
        {
            $monitor->watchSthHappen(function(){
                echo '重新载入代码--';
            });
        }*/
        swoole_event_add($monitor->notify, function() use($monitor,$server)
        {
            $monitor->watchSthHappen(function() use($server){
                echo '重新载入代码--';
                $server->reload();
            });
        });
        return true;
    }
}