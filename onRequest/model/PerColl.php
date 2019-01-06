<?php
/**
 * Created by PhpStorm.
 * User: kuf
 * Date: 2018/10/26
 * Time: 20:02
 */
namespace onRequest\application\model;
class PerColl
{
    protected  $ip = '';
    protected  $merchantNumber = '';
    protected  $merchantSecret = '';
    public $debug_msg = '';
    private $debug =  false;
    public $errCode = 0;
    public $errMsg = '';

    public function __construct($config)
    {
        $this->ip = $config['ip'];
        $this->merchantNumber = $config['merchantNumber'];
        $this->merchantSecret = $config['merchantSecret'];
    }

    protected function getPayChannel($payChannel)
    {
        $payChannel = strtolower($payChannel);
        switch (true)
        {
            case true === in_array($payChannel,['微信','微信支付','wx','wechat','weixin'] ):
                return 'WXPAY';
            case true === in_array($payChannel,['支付宝','支付宝支付','ali','alipay'] ):
                return 'ALIPAY';
            default:
                return 'UNPAY';
        }
    }

    protected function getPayType($payType)
    {
        $payType = strtolower($payType);
        switch (true)
        {
            case true === in_array($payType,['移动端','app'] ):
                return 'APP';
            case true === in_array($payType,['电脑','pc'] ):
                return 'PC';
            default:
                return 'H5';
        }
    }

    public function SupportTheChannelOrNot($payChannel,$payType)
    {
        $flag = $payChannel === 'WXPAY' || $payChannel === 'UNPAY';
        if( $flag === false  )
        {
            return true;
        }
        $flag = $payType === 'H5' || $payType === 'APP';
        if( $flag === true  )
        {
            return '支付渠道为微信支付、云闪付的时候，不支持H5和APP';
        }
        return true;
    }

    public function  unifiedOrder($config)
    {
        $noIdealData = array();
        $debug_msg_pre = '统一下单接口,';
        $ip = $this->ip;
        $merchantNumber = $this->merchantNumber;
        $merchantSecret = $this->merchantSecret;
        $urlBody = "http://{$ip}/pay/unifiedorder";
        $merchantOrderNumber = $config['商户订单号'];
        $goodsName = $config['商品名称'];
        $attach = $config['附加信息'];
        $notifyUrl = $config['异步通知url'];
        $callBackUrl = $config['前台回调url'];
        $totalFee = $config['订单金额'];
        $payType = $config['客户端类型'];
        $payType = $this->getPayType($payType);
        $payChannel = $config['支付渠道'];
        $payChannel = $this->getPayChannel($payChannel);
        $result = $this->SupportTheChannelOrNot($payChannel,$payType);
        if( true  !== $result )
        {
            return $result;
        }
        $paramArr = [
            'merId'=> $merchantNumber,//必填，商户号
            'outTradeNo'=>$merchantOrderNumber,//必填，商户订单号
            'body'=>$goodsName,//必填，商品名称
            'attach'=>$attach,//附加信息
            'notifyUrl'=>$notifyUrl,//必填，异步回调 url
            'callBackUrl'=>$callBackUrl,//前台回调 url  payType 为 H5 时必填
            'totalFee'=>$totalFee * 100,//必填，订单金额,单位：分
            'payType'=>$payType,//必填，交易类型
            'payChannel'=>$payChannel,//必填，支付渠道
            'nonceStr'=>$this->getNonceStr(),//必填，随机字符串
        ];
        $paramArr['sign'] = $this->dealParamsToGetSignature($paramArr,$merchantSecret);
        //say($paramArr['sign'] ,'$paramArr[\'sign\'] ');
        $paramsStr = $this->joinUrlParamsAsStr( $paramArr );
        //say($paramsStr ,'$paramsStr');
        $result = $this->curl_post($urlBody,$paramsStr);
        if ( !$result)
        {
            return $this->noResultAfterHttpRequest( $debug_msg_pre ,$noIdealData );
        }
        $json = json_decode($result,true);
        say($json,'$json');
        if(  $this->noIdealDataInJSON($json,$debug_msg_pre) === false  )
        {
            return $noIdealData;
        }
        //


    }

    //=================================================================================

    public  function getNonceStr()
    {
        return '12345';
    }

    protected function dealParamsToGetSignature($params,$merchantSecret)
    {
        $keyArr = array_keys($params);
        sort($keyArr, SORT_STRING);
        $newKeyArr = [];
        foreach($keyArr as $seq => $key )
        {
            if($key === 'sign')
            {
                continue;
            }
            $value = $params[$key];
            $value = trim($value);
            if( $value !== '')
            {
                $newKeyArr[$key] = $value;
            }
        }
        //say($keyArr,'sor-$keyArr');
        //say($newKeyArr,'$newKeyArr');
        $rawSignatureStr = $this->joinUrlParamsAsStr( $newKeyArr );
        unset($keyArr,$newKeyArr);
        //say($rawSignatureStr,'$rawSignatureStr');
        //
        $strToEncrypt = $rawSignatureStr.'&key='.$merchantSecret;
        //say($strToEncrypt,'$strToEncrypt');
        //exit;
        $strHasEncrypt = md5($strToEncrypt);
        //say($strHasEncrypt,'$strHasEncrypt');
        return strtoupper($strHasEncrypt);
    }

    //=================================================================================

    protected function joinUrlParamsAsStr($paramArr)
    {
        $params = array();
        foreach( $paramArr as $field => $value )
        {
            $params[] = "{$field}={$value}";
        }
        $str = implode('&',$params);
        return $str;
    }

    protected function noResultAfterHttpRequest( $debug_msg_pre ,$noIdealData )
    {
        $this->debug_msg = $debug_msg_pre.'返回的JSON数据包为空';
        return $noIdealData;
    }

    protected function noIdealDataInJSON($json,$debug_msg_pre)
    {
        $noIdealData = false;
        if ( !$json )
        {
            $this->debug_msg = $debug_msg_pre.'返回的JSON数据包，解析后的数组为空';
            return $noIdealData;
        }
        if ( isset($json['errcode']) && !empty($json['errcode']))
        {
            $this->errCode = $json['errcode'];
            $this->errMsg = $json['errmsg'];
            $this->debug_msg = "{$debug_msg_pre}返回的JSON数据包，包含错误信息：
                {$this->errCode}--{$this->errMsg}";
            return $noIdealData;
        }
        return true;
    }

    /**
     * POST 请求
     * @param string $url
     * @param array|string $param
     * @param boolean $post_file 是否文件上传
     * @return string content
     */
    protected function curl_post($url, $param, $post_file=false)
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
            //$this->searchBugMsgArr[] = '$is_curlFile = true;';
        }
        else
        {
            $is_curlFile = false;
            //$this->searchBugMsgArr[] = '$is_curlFile = false';
            if (defined('CURLOPT_SAFE_UPLOAD'))
            {
                // $this->searchBugMsgArr[] = 'defined(\'CURLOPT_SAFE_UPLOAD\')';
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
}