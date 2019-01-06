<?php
//------------调试------------------------------------------------------------------------------

/**
 * @param array $arr
 * @param string $field
 * @param mixed $default
 * @return mixed
 */
function getItemFromArray($arr,$field,$default='')
{
    return isset( $arr[$field] ) ? $arr[$field] : $default;
}

/*
2 判断当前的运行环境是否是cli模式
3 */
function isRunInCLI()
{
    static $isCLi = null;
    if( true === is_bool($isCLi))
    {
        return $isCLi;
    }
    $type  = php_sapi_name();
    $isCLi = preg_match("/cli/i",$type) ? true : false;
    //
    return $isCLi;
}

function isOutputForTerminal()
{
    $isCLi = isRunInCLI();
    if(true === $isCLi)
    {
        $obInfo = ob_get_status();
        /* 空数组=>ob未开启=>终端
           有内容的数据=>ob开启=>浏览器
        */
        return  empty($obInfo);
    }
    else
    {
        return false;
    }
}

//------调试-----------------------------------------------
class  CliColor{

}

//变量类型不同，打印方法不同： array、object + [ string,  integer/double， null， bool ]
//CGI，简洁打印
//cli下，不要html标签
class pfDebug
{
   // public static $isInCLI = false;
    //font-size:54px;
    public static $foregroundColors = [
        'black' => '0;30',
        'dark_gray' => '1;30',
        'red' => '0;31',
        'light_red' => '1;31',
        'green' => '0;32',
        'light_green' => '1;32',
        'brown' => '0;33',//棕色
        'yellow' => '1;33',
        'blue' => '0;34',
        'light_blue' => '1;34',
        'purple' => '0;35',
        'light_purple' => '1;35',
        'cyan' => '0;36',//青色
        'light_cyan' => '1;36',
        'light_gray' => '0;37',
        'white' => '1;37',
    ];
    public static $backgroundColors = [
        'black' => '40',
        'red' => '41',
        'green' => '42',
        'yellow' => '43',
        'blue' => '44',
        'magenta' => '45',//品红
        'cyan' => '46',
        'light_gray' => '47',
    ];

    public static function cliSetColor($content,$color)
    {
        $value = self::$foregroundColors[$color];
        return "\e[{$value}m{$content}\e[0m ";
    }

    public static function cliSetBackgroundColor($content,$color)
    {
        $value = self::$backgroundColors[$color];
        return "\e[{$value}m{$content}\e[0m ";
    }

    private static function  getTypeStr($variableType, $isCLI, $isJustClean)
    {
        if( true === $isCLI )
        {
            return  "[{$variableType}]";
        }
        return  true === $isJustClean ? '' :
            "<span style=\"color:#D3D3D3;margin-right: 10px;\">{$variableType}</span>";
    }

    public static function  sayByCase($mix, $str,$isCLI,$isStrict)
    {
        $variableType = gettype($mix);
        if( true === $isCLI)
        {
            $str = self::cliSetColor($str,'yellow');
        }
        $isJustClean =  $str === ''|| $str === null;
        $arrow = true === $isJustClean ? '' : '=>';
        $typeStr = self::getTypeStr($variableType, $isCLI, $isJustClean);
        $isNeedSayBr = true;
        switch ($variableType)
        {
            case 'string':
                $idealString = self::getBodyWhenIsString($mix, $isCLI);
                if( true === $isCLI )
                {
                    $typeStr = true === $isJustClean ? '' : $typeStr;
                    $typeStr = self::cliSetColor("$typeStr",'cyan');
                }
                break;
            case 'integer':
            case 'double':
                $idealString =  self::getBodyWhenIsNumber($mix, $isCLI);
                if( true === $isCLI )
                {
                    $typeStr = self::cliSetColor("$typeStr",'cyan');
                }
                break;
            case 'boolean':
                $idealString =  self::getBodyWhenIsBool($mix, $isCLI);
                if( true === $isCLI )
                {
                    $typeStr = self::cliSetColor("$typeStr",'cyan');
                }
                break;
            case 'NULL'://必须 全部大写
                if( true === $isCLI )
                {
                    $typeStr = self::cliSetColor("$typeStr",'cyan');
                }
                $idealString =  self::getBodyWhenIsNull($isCLI);
                break;
            case 'array':
            case 'object':
            case 'resource':
                $isNeedSayBr = false;
                $idealString =  self::getBodyWhenIsArr($mix, $isCLI, $isStrict);
                if( true === $isCLI )
                {
                    $typeStr = self::cliSetColor("$typeStr",'cyan');
                }
                break;
            default://unknown type
                $idealString =  "[$variableType]";
                if( true === $isCLI )
                {
                    $typeStr = self::cliSetColor("$typeStr",'cyan');
                }
        }
        if( true === $isNeedSayBr )
        {
            $idealString .=  false === $isCLI ?'<br/>' :PHP_EOL;
        }
        return "{$str}{$arrow}{$typeStr}$idealString";
    }

    private static function getBodyWhenIsString($mix, $isCLI)
    {
        $isEmpty =  $mix === '';
        if( true === $isCLI )
        {
            $content = true === $isEmpty ? '[空字符串]' : $mix;
            return  self::cliSetColor($content,'brown');
        }
        return  true === $isEmpty ?  "<span style=\"color:#D3D3D3\">空字符串</span>" :
            "<span>{$mix}</span>";
    }

    private static function getBodyWhenIsNumber($mix, $isCLI)
    {
        if( true === $isCLI )
        {
            return  self::cliSetColor($mix,'light_blue');
        }
        return   "<span style=\"color:#5B75FF\">{$mix}</span>";
    }

    private static function getBodyWhenIsBool($mix, $isCLI)
    {
        $boolMean = $mix === true ? 'true' : 'false';
        if( true === $isCLI )
        {
            $color = $mix === true ? 'light_green' : 'light_red';
            return self::cliSetColor("[{$boolMean}]",$color);
        }
        $color = $mix === true ? 'forestgreen' : 'blue';
        return  "<span style=\"color:{$color}\">{$boolMean}</span>" ;
    }

    private static function getBodyWhenIsNull($isCLI)
    {
        if( true === $isCLI )
        {
            return self::cliSetColor("[null]",'light_cyan');
        }
        return   "<span style=\"color:#D3D3D3\">null</span><br/>";
    }

    private static function getBodyWhenIsArr($mix, $isCLI, $isStrict)
    {
        $body = /*true === $isStrict ? var_export($mix,true):*/print_r($mix,true);
        if( true === $isCLI )
        {
            return  self::cliSetBackgroundColor($body,'magenta');
        }
        $style = <<<EOF
        font-size: 18px;
white-space: pre-wrap;       /* css-3 */
 white-space: -moz-pre-wrap;  /* Mozilla, since 1999 */
 white-space: -pre-wrap;      /* Opera 4-6 */
 white-space: -o-pre-wrap;    /* Opera 7 */
 word-wrap: break-word;       /* Internet Explorer 5.5+ */
EOF;
        return  "<pre style=\"{$style}\">".$body.'</pre>';
    }
}

//say2('mean1',$var,'mean2',$var2); 0-2-4-6为解释
function say(...$manyParams)
{
    $isCLI = isOutputForTerminal();
    $br = true === $isCLI ? PHP_EOL :'<br/>';
    //回溯，不然记不得哪个位置调用了这个方法，debug完成后不知道上哪儿注释。
    if( function_exists('xdebug_call_function') )
    {
        //xdebug_call_*  系列的函数必须发放在这里，才能知道哪里调用了say方法
        $callSayClass = xdebug_call_class();
        $callSayFunc =  xdebug_call_function();
        $callSayFile =  xdebug_call_file();
        $callSayLine = xdebug_call_line();
    }
    else
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,1);
        $trace = $trace[0];
        /*echo '$traceInfo---<pre>';
        print_r($traceInfo);
        echo '</pre>';*/
        $callSayClass = getItemFromArray($trace,'class');
        $callSayFunc =  getItemFromArray($trace,'function');
        $callSayFile =  getItemFromArray($trace,'file');
        $callSayLine =  getItemFromArray($trace,'line');
        unset($trace);
    }
    $callSayClass = $callSayClass ? $callSayClass.'的 ' : '';
    $stack = "{$callSayClass}{$callSayFunc}()，在 {$callSayFile} 的第 {$callSayLine} 行";
    $stackStr =  true === $isCLI ? "{$stack}{$br}": <<<EOF
    <span style="color:gray">{$stack}</span><br/>
EOF;
    //
    $allContents = '';
    $isStrict = true;//true === $isStrict || 1 === $isStrict;
    $str = '';
    if( count($manyParams) === 1)
    {
        $allContents = \pfDebug::sayByCase($manyParams[0], $str,$isCLI,$isStrict);
    }
    else
    {
        foreach($manyParams as $seq => $arg )
        {
            if( $seq % 2 === 0 )
            {
                $str = $arg;
            }
            else
            {
                $allContents .=  \pfDebug::sayByCase($arg, $str,$isCLI,$isStrict);
            }
        }
    }
    //
    //style="font-size:52px"
    $el_start = true === $isCLI ? '' : '<div >';
    $el_end   = true === $isCLI ? '' : '</div>';
    $output = <<<EOF
        {$el_start}{$allContents}{$stackStr}{$el_end}
EOF;
    echo $output;
}

function strictDump($mix, $str,$isStrict=true)
{
    $isCLI = isRunInCLI();
    $style = $isCLI === true ? '' : <<<EOF
    font-size:18px;
white-space: pre-wrap;       /* css-3 */
 white-space: -moz-pre-wrap;  /* Mozilla, since 1999 */
 white-space: -pre-wrap;      /* Opera 4-6 */
 white-space: -o-pre-wrap;    /* Opera 7 */
 word-wrap: break-word;       /* Internet Explorer 5.5+ */
EOF;
    $arrStyle_start = true === $isCLI ? "{$str}=>" : "<div style=\"{$style}\"><mark>{$str}</mark>=>";
    $arrStyle_end = true === $isCLI ? '' : '</div></br/>';
    echo $arrStyle_start;
    if( true === $isStrict)
    {
        var_dump($mix);
    }
    else
    {
        print_r($mix);
    }
    echo $arrStyle_end;
}

/**
 * 判断是否存在PHP错误
 * @return bool
 */
function isExistsPhpError()
{
    $lastError = error_get_last();
    if( IS_LOCAL && $lastError )
    {
        say($lastError,'$lastError');
    }
    return !empty($lastError) && IS_LOCAL;
}

function throw_phpError($error_level,$error_message,$error_file,$error_line,$traceList = [])
{
    /* $error_level_arr = array(
         2=>''
     );*/
    $errMsg =  "{$error_level}：{$error_message} in {$error_file} line {$error_line}";
    $traceList = $traceList ?? debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    $trace = traceHTML($traceList,'html',$errMsg);
    echo $trace;
}

function traceHTML($traceList,$way,$caption)
{
    $isAlert = $way==='alert';
    $trace = true === $isAlert ? '' : <<<EOF
    <mark style="font-size:24px">{$caption}</mark><hr/>
<ul style="list-style-type: none">
EOF;
    $wrap = true === $isAlert ? "\n" : '<br/>';
    foreach($traceList as $k => $traceArr )
    {
        $traceClass = getItemFromArray($traceArr,'class');
        $traceType = getItemFromArray($traceArr,'type');
        $traceFunction = getItemFromArray($traceArr,'function');
        $traceFile = getItemFromArray($traceArr,'file');
        $traceLine = getItemFromArray($traceArr,'line');
        $args = getItemFromArray($traceArr,'args',[]);
        $args = print_r($args, true);
        $args = $args ? "<pre>{$args}</pre>" : '';
        if( true === $isAlert )
        {
            $trace.="方法：{$traceClass} {$traceType}{$traceFunction}(){$wrap}";
            $trace.="文件：{$traceFile}，";
            $trace.="第：{$traceLine}行{$wrap}";
        }
        else
        {
            //{$traceArr['object']}
            /*style="background-color:lawngreen;"*/
            $light_style = $traceClass && strpos($traceClass,'Controller')!==false?
                'style="background-color:#f57900"':null;
            //say($light_style,$traceClass.'--$light_style');
            $hr = $k > 0 ? '<hr/>':'';
            $trace .=<<<EOF

    <li>{$hr}<div {$light_style}>{$traceClass}{$traceType}{$traceFunction}()</div>
    <div>{$args}</div>
    <div {$light_style}>{$traceFile}  第{$traceLine}行<li/>
EOF;
        }
    }
    if( true === $isAlert )
    {
        $trace .= '</ul>';
    }
    return $trace;
}

function trace()
{
    //debug_print_backtrace ();
    /*$s = debug_backtrace ();
    $s = array_reverse($s);
    say('以下是回溯：');
    echo '<pre>';
    print_r( $s );
    echo '</pre>';*/
    $traceList = $traceList ?? debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    $traceList = array_reverse($traceList);
    //say($traceList,'$traceList');
    echo traceHTML($traceList,'html','');
}