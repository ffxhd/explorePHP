<?php
namespace onRequest\core\wx;
class wpf_WeChat
{
    const OAUTH_PREFIX = 'https://open.weixin.qq.com/connect/oauth2';
    const OAUTH_AUTHORIZE_URL = '/authorize?';
    const API_BASE_URL_PREFIX = 'https://api.weixin.qq.com'; //以下API接口URL需要使用此前缀
    const OAUTH_TOKEN_URL = '/sns/oauth2/access_token?';
    const OAUTH_AUTH_URL = '/sns/auth?';
    const OAUTH_REFRESH_URL = '/sns/oauth2/refresh_token?';
    const OAUTH_USERINFO_URL = '/sns/userinfo?';
    const CUSTOM_SEND_URL='/message/custom/send?';
    //
    const API_URL_PREFIX = 'https://api.weixin.qq.com/cgi-bin';
    const AUTH_URL = '/token?grant_type=client_credential&';
    const GET_TICKET_URL = '/ticket/getticket?';
    //
    const TEMPLATE_SEND_URL = '/message/template/send?';

    private $token;//【类型：字符串】
    private $appId;//【类型：字符串】
    private $appSecret;//【类型：字符串】
    private $user_token;//微信网页授权凭证【类型：字符串】
    private $user_token_cache_file;//绝对路径
    private $access_token;//access_token【公众号的全局唯一票据】【类型：字符串】
    private  $access_token_cache_file;//绝对路径
    private $js_api_ticket;
    private $js_api_ticket_cache_file;//绝对路径

    private $debug =  false;
    public $errCode = 40001;
    public $errMsg = 'no access';
    public $debug_msg = '';
    
    public function __construct($options)
    {
        $this->token = isset( $options['token'] ) ? $options['token'] : '';
        $this->appId = isset( $options['appId'] ) ? $options['appId'] : '';
        $this->appSecret = isset( $options['appSecret'] ) ? $options['appSecret'] : '';
        $this->debug = isset( $options['debug'] ) ? $options['debug'] : false;
        $this->access_token_cache_file = isset( $options['access_token_cache_file'] ) ?
            $options['access_token_cache_file'] : '';
        $this->js_api_ticket_cache_file = isset( $options['js_api_ticket_cache_file'] ) ?
            $options['js_api_ticket_cache_file'] : '';
        $this->user_token_cache_file = isset( $options['user_token_cache_file'] ) ?
            $options['user_token_cache_file'] : '';
    }

    public function __destruct()
    {
        /*if( $this->debug_msg )
        {
            //PHP_EOL,换行符, 即\n\r
            $this->debug_msg = $this->debug_msg.PHP_EOL;
        }*/
    }

    //-----------------------------------------------------------------------------------------------

    /**
     * GET 请求
     * @param $url
     * @return bool|mixed
     */
    private function http_get($url)
    {
        $oCurl = curl_init();
        if(stripos($url,"https://")!== false)
        {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1);
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if(intval($aStatus['http_code']) ==200 )
        {
            return $sContent;
        }
        else
        {
            return false;
        }
    }

    /**
     * POST 请求
     * @param string $url
     * @param array|string $param
     * @param boolean $post_file 是否文件上传
     * @return string content
     */
    private function http_post($url,$param,$post_file=false)
    {
        $oCurl = curl_init();
        if(stripos($url,"https://") !== FALSE)
        {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }
        if (PHP_VERSION_ID >= 50500 && class_exists('\CURLFile'))
        {
            $is_curlFile = true;
        }
        else
        {
            $is_curlFile = false;
            if (defined('CURLOPT_SAFE_UPLOAD'))
            {
                curl_setopt($oCurl, CURLOPT_SAFE_UPLOAD, false);
            }
        }
        if (is_string($param))
        {
            $strPOST = $param;
        }
        elseif($post_file)
        {
            if($is_curlFile)
            {
                foreach ($param as $key => $val)
                {
                    if (substr($val, 0, 1) == '@')
                    {
                        $param[$key] = new \CURLFile(realpath(substr($val,1)));
                    }
                }
            }
            $strPOST = $param;
        }
        else
        {
            $aPOST = array();
            foreach($param as $key=>$val)
            {
                $aPOST[] = $key."=".urlencode($val);
            }
            $strPOST =  join("&", $aPOST);
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($oCurl, CURLOPT_POST,true);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS,$strPOST);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if(intval($aStatus["http_code"])==200)
        {
            return $sContent;
        }
        else
        {
            return false;
        }
    }

    /**
     * 微信api不支持中文转义的json结构
     * @param array  $arr
     * @return string
     */
    private static function json_encode($arr) {
        if (count($arr) == 0) return "[]";
        $parts = array ();
        $is_list = false;
        //Find out if the given array is a numerical array
        $keys = array_keys ( $arr );
        $max_length = count ( $arr ) - 1;
        if (($keys [0] === 0) && ($keys [$max_length] === $max_length )) { //See if the first key is 0 and last key is length - 1
            $is_list = true;
            for($i = 0; $i < count ( $keys ); $i ++) { //See if each key correspondes to its position
                if ($i != $keys [$i]) { //A key fails at position check.
                    $is_list = false; //It is an associative array.
                    break;
                }
            }
        }
        foreach ( $arr as $key => $value ) {
            if (is_array ( $value )) { //Custom handling for arrays
                if ($is_list)
                    $parts [] = self::json_encode ( $value ); /* :RECURSION: */
                else
                    $parts [] = '"' . $key . '":' . self::json_encode ( $value ); /* :RECURSION: */
            } else {
                $str = '';
                if (! $is_list)
                    $str = '"' . $key . '":';
                //Custom handling for multiple data types
                if (!is_string ( $value ) && is_numeric ( $value ) && $value<2000000000)
                    $str .= $value; //Numbers
                elseif ($value === false)
                    $str .= 'false'; //The booleans
                elseif ($value === true)
                    $str .= 'true';
                else
                    $str .= '"' . addslashes ( $value ) . '"'; //All other things
                // :TODO: Is there any more datatype we should be in the lookout for? (Object?)
                $parts [] = $str;
            }
        }
        $json = implode ( ',', $parts );
        if ($is_list)
            return '[' . $json . ']'; //Return numerical JSON
        return '{' . $json . '}'; //Return associative JSON
    }

    //-----------------------------------------------------------------------------------------------

    /**
     * 验证消息的确来自微信服务器
     * @return bool
     */
    private function checkSignature()
    {
        $signature = isset( $_GET['signature'] )? $_GET['signature'] : '';
        //
        $token = $this->token;
        $timestamp = isset( $_GET['timestamp'] )? $_GET['timestamp'] : '';
        $nonce = isset($_GET['nonce'])?$_GET['nonce']:'';
        //1）将token、timestamp、nonce三个参数进行字典序排序
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        //2）将三个参数字符串拼接成一个字符串进行sha1加密
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        //3）开发者获得加密后的字符串可与signature对比，标识该请求来源于微信
        return $tmpStr == $signature;
    }

    /**
     * 成为开发者
     */
    public function valid()
    {
        if (isset($_GET['echostr']))
        {
            $echoStr = $_GET['echostr'];
            if ( $this->checkSignature() )//成为开发者成功
            {
                die($echoStr);
            }
            else//接入失败
            {
                $this->debug_msg = '成为开发者失败';
                die('成为开发者失败');
            }
        }
    }

    //-----------------------------------------------------------------------------------------------

    public function noResultAfterHttpRequest( $debug_msg_pre ,$noIdealData )
    {
        $this->debug_msg = $debug_msg_pre.'微信返回的JSON数据包为空';
        return $noIdealData;
    }

    public function noIdealDataInJSON($json,$debug_msg_pre)
    {
        $noIdealData = false;
        if ( !$json )
        {
            $this->debug_msg = $debug_msg_pre.'微信返回的JSON数据包，解析后的数组为空';
            return $noIdealData;
        }
        if ( isset($json['errcode']) && !empty($json['errcode']))
        {
            $this->errCode = $json['errcode'];
            $this->errMsg = $json['errmsg'];
            $this->debug_msg = "{$debug_msg_pre}微信返回的JSON数据包，包含错误信息：
                {$this->errCode}--{$this->errMsg}";
            return $noIdealData;
        }
        return true;
    }

    //--------微信网页授权---------------------------------------------------------------------------------------

    /**
     * 微信网页授权  第一步：引导用户进入授权页面,建议单独一个public function
     * @param string  $redirect_uri 如果用户同意授权，页面将跳转至 redirect_uri/?code=CODE&state=STATE。
     * @param string $state
     * @param string $scope
     */
    public  function getOauthRedirect($redirect_uri,$state='',$scope='snsapi_userinfo')
    {
        $authorize_url =  self::OAUTH_PREFIX.self::OAUTH_AUTHORIZE_URL.'appid='.$this->appId.
        '&redirect_uri='.urlencode($redirect_uri).'&response_type=code&scope='.$scope.'&state='.$state.
        '#wechat_redirect';
        header( 'Location: '. $authorize_url);
    }

    /**
     * 微信网页授权  第二步：用户同意授权，获取code,通过code换取网页授权access_token（与基础支持中的access_token不同）
     * @return array
     */
    public function getOauthAccessToken()
    {
        /*正确时返回的JSON数据包如下：
            { "access_token":"ACCESS_TOKEN",
            "expires_in":7200,
            "refresh_token":"REFRESH_TOKEN",
            "openid":"OPENID",
            "scope":"SCOPE" }
         错误时微信会返回JSON数据包如下（示例为Code无效错误）:
            {"errcode":40029,"errmsg":"invalid code"}
        */
        $debug_msg_pre = '微信网页授权  通过code换取网页授权access_token，';
        $noIdealData = array();
        $data = $this->getCache( $this->user_token_cache_file,'','网页授权access_token缓存');
        if( $data )
        {
            $this->user_token = $data['access_token'];
            return $data;
        }
        $code = isset($_GET['code'])? $_GET['code']:'';
        if (!$code)
        {
            $this->debug_msg = $debug_msg_pre.'!$_GET[\'code\']';
            return $noIdealData;
        }
        $result = $this->http_get(self::API_BASE_URL_PREFIX.self::OAUTH_TOKEN_URL.
            'appid='.$this->appId.'&secret='.$this->appSecret.'&code='.$code.
            '&grant_type=authorization_code');
        if ($result)
        {
            $json = json_decode($result,true);
            if(  $this->noIdealDataInJSON($json,$debug_msg_pre) === false  )
            {
                return $noIdealData;
            }
            $this->setCache($json, $this->user_token_cache_file,'网页授权access_token');
            $this->user_token = $json['access_token'];
            return $json;
        }
        else
        {
            return $this->noResultAfterHttpRequest( $debug_msg_pre ,$noIdealData );
        }
    }

    /**
     * 检验授权凭证【类型：字符串】（access_token）是否有效
     * 微信网页授权----插曲
     * @param string $access_token 【类型：字符串】
     * @param string $openid
     * @return boolean 是否有效
     */
    public function getOauthAuth($access_token,$openid)
    {
        /*正确的JSON返回结果：
        { "errcode":0,"errmsg":"ok"}
        错误时的JSON返回示例：
        { "errcode":40003,"errmsg":"invalid openid"}
        */
        $debug_msg_pre = '检验授权凭证access_token是否有效，';
        $noIdealData = false;
        $result = $this->http_get(self::API_BASE_URL_PREFIX.self::OAUTH_AUTH_URL.
            'access_token='.$access_token.'&openid='.$openid);
        if ($result)
        {
            $json = json_decode($result,true);
            return $this->noIdealDataInJSON($json,$debug_msg_pre);
        }
        else
        {
            return $this->noResultAfterHttpRequest( $debug_msg_pre ,$noIdealData );
        }
    }

    /**
     * 微信网页授权  第三步：刷新授权凭证【类型：字符串】access token并续期
     * （如果需要，即授权凭证无效）
     * @param string $refresh_token
     * @return array
     */
    public function getOauthRefreshToken($refresh_token)
    {
        /*正确时返回的JSON数据包如下：
        { "access_token":"ACCESS_TOKEN",
         "expires_in":7200,
         "refresh_token":"REFRESH_TOKEN",
         "openid":"OPENID",
         "scope":"SCOPE" }
        错误时微信会返回JSON数据包如下（示例为code无效错误）:
        {"errcode":40029,"errmsg":"invalid code"}
        */
        $debug_msg_pre = '微信网页授权  刷新授权凭证access_token，';
        $noIdealData = array();
        $result = $this->http_get(self::API_BASE_URL_PREFIX.self::OAUTH_REFRESH_URL.
            'appid='.$this->appId.'&grant_type=refresh_token&refresh_token='.$refresh_token);
        if ($result)
        {
            $json = json_decode($result,true);
            if(  $this->noIdealDataInJSON($json,$debug_msg_pre) === false  )
            {
                return $noIdealData;
            }
            $this->setCache($json, $this->user_token_cache_file,'刷新网页授权access_token');
            $this->user_token = $json['access_token'];
            return $json;
        }
        else
        {
            return $this->noResultAfterHttpRequest( $debug_msg_pre ,$noIdealData );
        }
    }

    /**
     * 微信网页授权  第四步：拉取用户信息(需scope为 snsapi_userinfo)
     * @param string $access_token
     * @param string $openid
     * @return array {openid,nickname,sex,province,city,country,headimgurl,privilege,[unionid]}
     * 注意：unionid字段 只有在用户将公众号绑定到微信开放平台账号后，才会出现。建议调用前用isset()检测一下
     */
    public function getOauthUserInfo($access_token,$openid)
    {
        /*正确时返回的JSON数据包如下：
        {    "openid":" OPENID",
         " nickname": NICKNAME,
         "sex":"1",
         "province":"PROVINCE"
         "city":"CITY",
         "country":"COUNTRY",
         "headimgurl":    "http://wx.qlogo.cn/mmopen/g3MonUZtNHkdmzicIlibx6iaFqAc56vxLSUfpb6n5WKSYVY0ChQKkiaJSgQ1dZuTOgvLLrhJbERQQ
                        4eMsv84eavHiaiceqxibJxCfHe/46",
        "privilege":[ "PRIVILEGE1" "PRIVILEGE2"     ],
         "unionid": "o6_bmasdasdsad6_2sgVt7hMZOPfL"//只有在用户将公众号绑定到微信开放平台帐号后，才会出现该字段。
        }
        错误时微信会返回JSON数据包如下（示例为openid无效）:
        {"errcode":40003,"errmsg":" invalid openid "}
        */
        $debug_msg_pre = '微信网页授权  刷新授权凭证access_token，';
        $noIdealData = array();
        $result = $this->http_get(self::API_BASE_URL_PREFIX.self::OAUTH_USERINFO_URL.
            'access_token='.$access_token.'&openid='.$openid);
        if ($result)
        {
            $json = json_decode($result,true);
            if(  $this->noIdealDataInJSON($json,$debug_msg_pre) === false  )
            {
                return $noIdealData;
            }
            return $json;
        }
        else
        {
            return $this->noResultAfterHttpRequest( $debug_msg_pre ,$noIdealData );
        }
    }

    //-----------------------------------------------------------------------------------------------

    /**
     * 保存
     * @param $array
     * @param $file
     * @param string $mean
     * @return bool
     */
    public function setCache($array,$file, $mean='')
    {
        if( !file_exists($file) )
        {
            $this->debug_msg = $file.'不存在--'.$mean;
            return false;
        }
        $array['expires_in'] += $_SERVER['REQUEST_TIME'];
        $array['expire_format'] = date('Y-m-d H:i:s',$array['expires_in']);
        $json = json_encode($array,JSON_UNESCAPED_UNICODE );
        $flag = file_put_contents( $file, $json );
        if( !$flag )
        {
            $this->debug_msg = "将{$mean}写入{$file}失败";
            return false;
        }
        else
        {
           return true;
        }
    }

    /**
     * 取得保存的
     * @param string $file
     * @param string $field
     * @param string $mean
     * @return string|array
     */
    public function getCache($file,$field = '',$mean='')
    {
        $noValidData = $field ? '' : array() ;
        if( !file_exists($file) )
        {
            $this->debug_msg = $file.'不存在--'.$mean;
            return $noValidData;
        }
        $str = file_get_contents( $file );
        if( !$str )
        {
            $this->debug_msg = $file.'--无内容--'.$mean;
            return $noValidData;
        }
        $array = json_decode($str,true);
        if( !$array )
        {
            $this->debug_msg = $file.'--内容转为数组，为空--'.$mean;
            return $noValidData;
        }
        if( $array['expires_in'] < $_SERVER['REQUEST_TIME'] )
        {
            return $noValidData;
        }
        else
        {
            return $field ? $array[$field] : $array ;
        }
        //return $array['expires_in'] < $_SERVER['REQUEST_TIME'] ? $noValidData : $array[$field];
    }

    /**
     * 获取access_token【公众号的全局唯一票据】
     * @param string $appid 如在类初始化时已提供，则可为空
     * @param string $appsecret 如在类初始化时已提供，则可为空
     * @param string $token 手动指定access_token，非必要情况不建议用
     * @return string
     */
    public function checkAuth($appid='',$appsecret='',$token='')
    {
        /*公众号的全局唯一票据，公众号调用各接口时都需使用access_token。
        开发者需要进行妥善保存。access_token的存储至少要保留512个字符空间。
        access_token的有效期目前为2个小时，需定时刷新

        正常情况下，微信会返回下述JSON数据包给公众号：
        {"access_token":"ACCESS_TOKEN","expires_in":7200}
        错误时微信会返回错误码等信息，JSON数据包示例如下（该示例为AppID无效错误）:
        {"errcode":40013,"errmsg":"invalid appid"}
        */
        $debug_msg_pre = '公众号的全局唯一票据access_token，';
        $noIdealData = '';
        if (!$appid || !$appsecret)
        {
            $appid = $this->appId;
            $appsecret = $this->appSecret;
        }
        if ( $token )//手动指定token，优先使用
        {
            $this->access_token=$token;
            return $this->access_token;
        }
        $access_token = $this->getCache($this->access_token_cache_file,'access_token','access_token缓存');
        if( $access_token )
        {
            $this->access_token = $access_token;
            return $access_token;
        }
        $result = $this->http_get(self::API_URL_PREFIX.self::AUTH_URL.'appid='.$appid.
            '&secret='.$appsecret);
        if ($result)
        {
            $json = json_decode($result,true);
            if(  $this->noIdealDataInJSON($json,$debug_msg_pre) === false )
            {
                return $noIdealData;
            }
            $this->setCache($json,$this->access_token_cache_file,'获取公众号的全局唯一票据access_token');
            $this->access_token = $json['access_token'];
            return $this->access_token;
        }
        else
        {
            return $this->noResultAfterHttpRequest( $debug_msg_pre ,$noIdealData );
        }
    }

    //---------JS SDK--------------------------------------------------------------------------------------

    /**
     * 获取jsapi_ticket--公众号用于调用微信JS接口的临时票据
     * @param string $js_api_ticket 手动指定jsapi_ticket，非必要情况不建议用
     * @return  string
     */
    public function getJsApiTicket(  $js_api_ticket = '')
    {
        /*正常情况下，jsapi_ticket的有效期为7200秒，通过access_token来获取。
        由于获取jsapi_ticket的api调用次数非常有限，频繁刷新jsapi_ticket会导致api调用受限，
        影响自身业务，开发者必须在自己的服务全局缓存jsapi_ticket 。
        成功返回如下JSON：
        {
        "errcode":0,
        "errmsg":"ok",
        "ticket":"bxLdikRXVbTPdHSM05e5u5sUoXNKd8-41ZO3MhKoyN5OfkWITD
                  Ggnr2fwJ0m9E8NYzWKVZvdVtaUgWvsdshFKA",
        "expires_in":7200
        }
        */
        $debug_msg_pre = '获取调用微信JS接口的临时票据jsapi_ticket，';
        $noIdealData = '';
        if (!$this->access_token && !$this->checkAuth())
        {
            $this->debug_msg = $debug_msg_pre.'没有access_token';
            return $noIdealData;
        }
        if ($js_api_ticket) //手动指定token，优先使用
        {
            $this->js_api_ticket = $js_api_ticket;
            return $this->js_api_ticket;
        }
        $data = $this->getCache( $this->js_api_ticket_cache_file,'ticket','js_api_ticket缓存');
        if( $data )
        {
            $this->js_api_ticket = $data;
            return $data;
        }
        $result = $this->http_get(self::API_URL_PREFIX.self::GET_TICKET_URL.
            'access_token='.$this->access_token.'&type=jsapi');
        if ($result)
        {
            $json = json_decode($result,true);
           if(  $this->noIdealDataInJSON($json,$debug_msg_pre) === false  )
           {
               return $noIdealData;
           }
            $this->js_api_ticket = $json['ticket'];
            $this->setCache($json, $this->js_api_ticket_cache_file,'js_api_ticket');
            return $this->js_api_ticket;
        }
        else
        {
            return $this->noResultAfterHttpRequest( $debug_msg_pre ,$noIdealData );
        }
    }

    /**
     * 生成JS-SDK权限验证的配置
     * @return array
     */
    public function get_JS_SDK_config($url='', $timestamp = '',$nonceStr ='')
    {
        $jsApiTicket = $this->getJsApiTicket();
        if(!$jsApiTicket )
        {
            return array();
        }
        // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
        $url = $url ? $url : "{$protocol}{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
        $timestamp = $timestamp ? $timestamp : time();
        $nonceStr = $nonceStr ? $nonceStr : $this->createNonceStr();
        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket={$jsApiTicket}&noncestr={$nonceStr}&timestamp={$timestamp}&url={$url}";
        $signature = sha1($string);
        $signPackage = array(
            'appId'     => $this->appId, // 必填，公众号的唯一标识
            'nonceStr'  => $nonceStr,// 必填，生成签名的随机串
            'timestamp' => $timestamp,// 必填，生成签名的时间戳
            'url'       => $url,
            'signature' => $signature,//必填，JS-SDK使用权限签名
            'rawString' => $string
        );
        return $signPackage;
    }

    private function createNonceStr($length = 16)
    {
        //return 'noncestr';
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str = '';
        $pfSr_L = strlen($chars) - 1;
        for ($i = 0; $i < $length; $i++)
        {
            $num  = mt_rand(0, $pfSr_L);
            //$str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
            $str .= substr($chars,$num, 1);
        }
        return $str;
    }
    //-----------------------------------------------------------------------------------------------

    /**
     * 发送客服消息
     * @param array $data 消息结构{"touser":"OPENID","msgtype":"news","news":{...}}
     * @return array
     */
    public function sendCustomMessage($data)
    {
        $debug_msg_pre = '发送客服消息，';
        $noIdealData = array();
        if (!$this->access_token && !$this->checkAuth())
        {
            $this->debug_msg = $debug_msg_pre.'没有access_token';
            return array();
        }
        $url = self::API_URL_PREFIX.self::CUSTOM_SEND_URL.
            'access_token='.$this->access_token;
        $param = self::json_encode($data);
        //$param = json_encode($data);
        $result = $this->http_post( $url,$param);
        if ($result)
        {
            $json = json_decode($result,true);
            return $this->noIdealDataInJSON($json,$debug_msg_pre) === false ? $noIdealData: $json;
        }
        else
        {
            return $this->noResultAfterHttpRequest( $debug_msg_pre ,$noIdealData );
        }
    }

    //-----------------------------------------------------------------------------------------------

    /**
     * 发送模板消息
     * @param  array $data
     * @return array|mixed
     */
    public function sendTemplateMessage($data)
    {
        /*POST数据示例如下：
      {
           "touser":"OPENID",
           "template_id":"ngqIpbwh8bUfcSsECmogfXcV14J0tQlEpBO27izEYtY",
           "url":"http://weixin.qq.com/download",
           "miniprogram":{
             "appid":"xiaochengxuappid12345",
             "pagepath":"index?foo=bar"
           },
           "data":{
                   "first": {
                       "value":"恭喜你购买成功！",
                       "color":"#173177"
                   },
                   "keynote1":{
                       "value":"巧克力",
                       "color":"#173177"
                   },
                   "keynote2": {
                       "value":"39.8元",
                       "color":"#173177"
                   },
                   "keynote3": {
                       "value":"2014年9月22日",
                       "color":"#173177"
                   },
                   "remark":{
                       "value":"欢迎再次购买！",
                       "color":"#173177"
                   }
           }
       }*/
        $debug_msg_pre = '发送模板消息，';
        $noIdealData = array();
        if (!$this->access_token && !$this->checkAuth())
        {
            $this->debug_msg = $debug_msg_pre.'没有access_token';
            return $noIdealData;
        }
        $result = $this->http_post(self::API_URL_PREFIX.self::TEMPLATE_SEND_URL.
            'access_token='.$this->access_token,self::json_encode($data));
        if($result)
        {
            $json = json_decode($result,true);
            return  $this->noIdealDataInJSON($json,$debug_msg_pre) === false ?  $noIdealData : $json ;
        }
        else
        {
            return $this->noResultAfterHttpRequest( $debug_msg_pre ,$noIdealData );
        }
    }

    //-----------------------------------------------------------------------------------------------
}
