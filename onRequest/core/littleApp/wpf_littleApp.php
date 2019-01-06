<?php
namespace onRequest\core\littleApp;
class wpf_littleApp{

    public  $searchBugMsgArr = array();
    public  $debug_msg = '';
    public  $errCode = 0;
    public  $errMsg = '';
    public  $errCodeMean = '';
    public  $access_token;//access_token【公众号的全局唯一票据】【类型：字符串】

    private $appId;//【类型：字符串】
    private $appSecret;//【类型：字符串】
    private $token;//【类型：字符串】

    public function __construct($options)
    {
        $this->token = isset( $options['token'] ) ? $options['token'] : '';
        $this->appId = isset( $options['appId'] ) ? $options['appId'] : '';
        $this->appSecret = isset( $options['appSecret'] ) ? $options['appSecret'] : '';
        $this->debug = isset( $options['debug'] ) ? $options['debug'] : false;
    }

    /**
     * 新增临时素材,小程序可以使用本接口把媒体文件（目前仅支持图片）
    上传到微信服务器，用户发送客服消息或被动回复用户消息。
     * @param $file
     * @return array|mixed
     */
    public function addNewTemporaryMediaFile($file)
    {
        $debug_msg_pre = '新增临时素材，';
        $noIdealData = array();
        $access_token = $this->access_token;
        $access_token = $access_token ? $access_token : $this->getGlobalAccessToken();
        if ( !$access_token )
        {
            $this->debug_msg = $debug_msg_pre.'没有access_token';
            return $noIdealData;
        }
        $url ="https://api.weixin.qq.com/cgi-bin/media/upload?access_token={$access_token}&type=image";
        /*$data = array(
            'media'=>'@'.$file
        );
        $param = self::json_encode($data);*/
        $param = array(
            'media'=>'@'.$file
        );
        $result = $this->http_post( $url,$param,true);
        if ($result)
        {
            /*正确情况下的返回 JSON 数据包结果如下：
            {
            "type":"TYPE",
            "media_id":"MEDIA_ID",//媒体文件上传后，获取标识
            "created_at":123456789 //媒体文件上传时间戳
            }
            错误情况下的返回JSON数据包示例如下（示例为无效媒体类型错误）：
            {
              "errcode":40004,
              "errmsg":"invalid media type"
            }*/
            $json = json_decode($result,true);
            $flag =  $this->noIdealDataInJSON($json,$debug_msg_pre) === false ;
            if( $this->errCode )
            {
                //$this->errCodeMean = $this->( $this->errCode );
            }
            return $flag ?  $noIdealData : $json ;
        }
        else
        {
            return $this->noResultAfterHttpRequest( $debug_msg_pre ,$noIdealData );
        }
    }

    public  function littleAppContactInterface_auth()
    {
        if (isset($_GET['echostr']))
        {
            $echoStr = $_GET['echostr'];
            if ( $this->checkSignature() )//成为开发者成功
            {
                echo $echoStr;
            }
            else//接入失败
            {
                $this->debug_msg = '成为开发者失败';
                echo '成为开发者失败';
            }
        }
    }

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
     * 发送客服消息
     * @param array $data 消息结构{"touser":"OPENID","msgtype":"news","news":{...}}
     * @return array
     */
    public function sendCustomMessage($data)
    {
        $debug_msg_pre = '发送客服消息，';
        $noIdealData = array();
        $access_token = $this->access_token;
        $access_token = $access_token ? $access_token : $this->getGlobalAccessToken();
        if ( !$access_token )
        {
            $this->debug_msg = $debug_msg_pre.'没有access_token';
            return $noIdealData;
        }
        $url ="https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$access_token}";
        $param = self::json_encode($data);
        $result = $this->http_post( $url,$param);
        if ($result)
        {
            $json = json_decode($result,true);
            $flag =  $this->noIdealDataInJSON($json,$debug_msg_pre) === false ;
            if( $this->errCode )
            {
                $this->errCodeMean = $this->sendCustomMessageError( $this->errCode );
            }
            return $flag ?  $noIdealData : $json ;
        }
        else
        {
            return $this->noResultAfterHttpRequest( $debug_msg_pre ,$noIdealData );
        }
    }

    /**
     * 解释客服消息发送失败的原因
     * @param number $errorCode
     * @return string
     */
    public function sendCustomMessageError( $errorCode )
    {
        $errorCode_mean_array = array(
            '-1' => '系统繁忙，此时请开发者稍候再试',
            '0' => '请求成功',
            '40001 '=> '获取access_token时AppSecret错误，'.
                '或者access_token无效。请开发者认真比对AppSecret的正确性，'.
                '或查看是否正在为恰当的小程序调用接口',
            '40002' => '不合法的凭证类型',
            '40003' => '不合法的OpenID，请开发者确认OpenID否是其他小程序的OpenID',
            '45015' => '回复时间超过限制',
            '45047' => '客服接口下行条数超过上限',
            '48001' => 'api功能未授权，请确认小程序已获得该接口',
            //
            '40037' => 'template_id不正确',
            '41028' => 'form_id不正确，或者过期',
            '41029' => 'form_id已被使用',
            '41030' => 'page不正确',
            '45009' => '接口调用超过限额',
        );
        if (isset($errorCode_mean_array[$errorCode]) )
        {
            return $errorCode_mean_array[$errorCode];
        }
        else
        {
            return '不在小程序客服消息接口返回码说明内';
        }
    }

    /**
     * 发送模板消息
     * @param  array $data
     * @return array|mixed
     */
    public function sendTemplateMessage($data)
    {
        $debug_msg_pre = '发送模板消息，';
        $noIdealData = array();
        $access_token = $this->access_token;
        $access_token = $access_token ? $access_token : $this->getGlobalAccessToken();
        if ( !$access_token )
        {
            $this->debug_msg = $debug_msg_pre.'没有access_token';
            return $noIdealData;
        }
        $url = 'https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?'.
            "access_token={$access_token}";
        $result = $this->http_post($url,self::json_encode($data));
        if($result)
        {
            $json = json_decode($result,true);
            $flag =  $this->noIdealDataInJSON($json,$debug_msg_pre) === false ;
            if( $this->errCode )
            {
                $this->errCodeMean = $this->sendTemplateMessageError( $this->errCode );
            }
            return $flag ?  $noIdealData : $json ;
        }
        else
        {
            return $this->noResultAfterHttpRequest( $debug_msg_pre ,$noIdealData );
        }
    }

    /**
     * 解释模板消息发送失败的原因
     * @param $errorCode
     * @return string
     */
    public function sendTemplateMessageError( $errorCode )
    {
        $errorCode_mean_array = array(
            40037 =>'template_id不正确',
            41028	=>'form_id不正确，或者过期',
            41029	=>'form_id已被使用',
            41030	=>'page不正确',
            45009	=>'接口调用超过限额（目前默认每个帐号日调用限额为100万）'
        );
        if (isset($errorCode_mean_array[$errorCode]) )
        {
            return $errorCode_mean_array[$errorCode];
        }
        else
        {
            return '不在小程序模板消息接口返回码说明内';
        }
    }
    
    /**
     * 获取access_token【全局唯一票据】
     * @param string $appId 如在类初始化时已提供，则可为空
     * @param string $appSecret 如在类初始化时已提供，则可为空
     * @return array
     */
    public function getGlobalAccessToken($appId='', $appSecret='')
    {
        /*全局唯一票据，公众号调用各接口时都需使用access_token。
        开发者需要进行妥善保存。access_token的存储至少要保留512个字符空间。
        access_token的有效期目前为2个小时，需定时刷新

        正常情况下，微信会返回下述JSON数据包给公众号：
        {"access_token":"ACCESS_TOKEN","expires_in":7200}
        错误时微信会返回错误码等信息，JSON数据包示例如下（该示例为AppID无效错误）:
        {"errcode":40013,"errmsg":"invalid appid"}
        */
        $debug_msg_pre = '全局唯一票据access_token，';
        $noIdealData = array();
        if ( !$appId || !$appSecret )
        {
            $appId = $this->appId;
            $appSecret = $this->appSecret;
        }
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential'.
            "&appid={$appId}&secret={$appSecret}";
        $result = $this->http_get($url);
        if ($result)
        {
            $json = json_decode($result,true);
            if(  $this->noIdealDataInJSON($json,$debug_msg_pre) === false )
            {
                return $noIdealData;
            }
            $this->access_token = $json['access_token'];
            return $json;
        }
        else
        {
            return $this->noResultAfterHttpRequest( $debug_msg_pre ,$noIdealData );
        }
    }

    /**
     * 手动指定access_token
     * @param string $yourAccessToken
     */
    public function setGlobalAccessToken( $yourAccessToken )
    {
        if ( !$yourAccessToken )
        {
            say($yourAccessToken,"手动指定access_token的值不符合要求");
            exit;
        }
        $this->access_token = $yourAccessToken;
    }

    /**
     *通过code换取session_key和 openid
     * @return array
     */
    public function getSessionKeyAndOpenId( )
    {
        /*//正常返回的JSON数据包
            {
                  "openid": "OPENID",
                  "session_key": "SESSIONKEY"
            }
            //错误时返回JSON数据包(示例为Code无效)
            {
                "errcode": 40029,
                "errmsg": "invalid code"
            }
        */
        $actionMean = '通过code换取session_key和 openid';
        $debug_msg_pre = "微信小程序，{$actionMean}，";
        $noIdealData = array();
        $code = isset($_GET['code'])? $_GET['code']:'';
        if (!$code)
        {
            $this->debug_msg = $debug_msg_pre.'!$_GET[\'code\']';
            return $noIdealData;
        }
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid={$this->appId}".
            "&secret={$this->appSecret}&js_code={$code}&grant_type=authorization_code";
        $result = $this->http_get($url);
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

    public function getRandStr($length = 16)
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

    public function noResultAfterHttpRequest( $debug_msg_pre ,$noIdealData )
    {
        $this->debug_msg = $debug_msg_pre.'微信返回的JSON数据包为空';
        return $noIdealData;
    }
    
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
            $this->searchBugMsgArr[] = '$is_curlFile = true;';
        }
        else
        {
            $is_curlFile = false;
            $this->searchBugMsgArr[] = '$is_curlFile = false';
            if (defined('CURLOPT_SAFE_UPLOAD'))
            {
                $this->searchBugMsgArr[] = 'defined(\'CURLOPT_SAFE_UPLOAD\')';
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
}
