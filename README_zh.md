支持: 
* 仅仅使用 swoole http server;
* nginx + swoole http server; 
* nginx + (php-fpm), 没有 swoole. 
* 在Linux中的CLI,部分输出是五彩缤纷的.

#swoole
支持传统的一些开发习惯，比如
* 使用$_SESSION进行会话；
* 需要输出，直接在控制器中echo、var_dump();

请使用stop()代替exit/die;

# debug
强烈推荐:
<blockquote>
<pre>
require '/home/ffxhd/samba_share/explorePHP/function/function_debug.php';
say($variable);
say('variableMean',$variable,
'variableMean2',$variable2,
'variableMean3',$variable3);
</pre>
</blockquote>
say()的参数的个数无限的，就像 javaScript 的 console.log();
say()会告诉你，你在哪边调用了它。
如果在linux 终端下,输出是五彩缤纷的。

![alt effect](./onRequest/public/images/php-cli-colorful2.png "effect")

# 如果需要nginx, 请添加一些配置
*所有静态文件都在/onRequest/public中。当你访问html文件时，为了避免总是输入“/onRequest/public”以节省时间和精力,
为nginx添加一些配置，如下所示：
<blockquote>
<pre>
location ~ \.(html)$ {
     root  /home/ffxhd/samba_share/explorePHP/onRequest/public; #your absolute path
}
</pre>
</blockquote>
现在“ http：//nginx.swoole.local/websocket.html ”与“ http：//nginx.swoole.local/onRequest/public/websocket.html ”相同
* 如果没有swoole，为了避免总是输入“/index.php”
<blockquote>
<pre>
location /{
	if (!-e $request_filename) {
	   rewrite  ^(.*)$  /index.php$1  last;
	   break;
	}
}
</pre>
</blockquote>
现在“ http：//test.php.local/index/test ”与“ http：//test.php.local/index.php/index/test ”相同
 
# 要求:
* php 7 
* 如果用到swoole, 为了热更新onRequest目录中的文件，php需要inotify扩展;
* 如果用到swoole, 为了会话，需要安装redis，php需要redis扩展。
* 为了worker 报告php出错了，浏览器不能获得相应之时，能够回过神来，建议安装文本转语音的软件：apt-get install espeak。