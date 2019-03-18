<?php
/**
 * Created by PhpStorm.
 * User: kuf
 * Date: 2018/10/26
 * Time: 19:19
 */

function startSession()
{
    /*1.PHP_SESSION_DISABLED  if sessions are disabled.
    2. PHP_SESSION_NONE  if sessions are enabled, but none exists.
    3. PHP_SESSION_ACTIVE  if sessions are enabled, and one exists.
*/
    $a = function_exists('session_status') ? session_status()==1 : !isset($_SESSION);
    //say($sessionStatus,'$sessionStatus');
    if( $a )
    {
        session_start();
    }
}

function getWashedData($source,$field,$default='')
{
    $value = getItemFromArray($source,$field,$default);
    if( true === is_string($value))
    {
        $value = trim($value);
    }
    return addSlashesOrNot($value);
}

function isAjaxOrNot($arr)
{
    $v = getItemFromArray($arr,'x-requested-with',null);//推荐
    if( null === $v)
    {
        //HTTP_X_REQUESTED_WITH
        $v = getItemFromArray($_SERVER,'http_x_requested_with','');
    }
    $_SERVER['IS_AJAX'] = 'XMLHttpRequest' === $v;
}


function set_url( $controller,$method,$extraParam = array() )
{
    $base_url = readConfig( 'base_url' );
    return "http://{$base_url}/index.php/{$controller}/{$method}";
}

function joinUrlParamsAsStr( $paramArr )
{
    $params = array();
    foreach( $paramArr as $field => $value )
    {
        $params[] = "{$field}={$value}";
    }
    $str = implode('&',$params);
    return $str;
}

//扩展，想取得$post['a']['b']['c'],任意层级=>$field = a[b][c]或者[a][b][c]
function https_rawData_by_POST($field = '' )
{
    $post = file_get_contents('php://input');//这样才可以获得https的post
    $post = json_decode($post,true);
    if(!is_array($post) )
    {
        return array();
    }
    if( $field )
    {
        if( strpos($field,'[')===false && strpos($field,'[')=== false )
        {
            return  isset($post[$field])? $post[$field] : null;
        }
        else
        {

        }
    }
    else
    {
        return $post;
    }
}

//扩展，想取得$post['a']['b']['c'],任意层级=>$field = a[b][c]或者[a][b][c]
function https_rawData_by_GET($field = '' )
{
    $data = $_GET;
    foreach($data as $k =>$mix)
    {
        if( is_string($mix))
        {
            if( strpos($mix,'{') === 0 && strpos($mix,'}')!== false )
            {
                $data[$k] = json_decode($mix,true);
            }
        }
    }
    if( $field )
    {
        if( strpos($field,'[')===false && strpos($field,'[')=== false )
        {
            return  isset($data[$field])? $data[$field] : null;
        }
        else
        {

        }
    }
    else
    {
        return $data;
    }
}


/**
 * GET 请求
 * @param $url
 * @return bool|mixed
 */
function curl_get($url)
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
function curl_post($url, $param, $post_file=false)
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
