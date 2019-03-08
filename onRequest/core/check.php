<?php
/**
 * Created by PhpStorm.
 * User: pf
 * Date: 2016/7/14
 * Time: 14:01
 */
namespace onRequest\core;
class check{
    public static function test()
    {
        $htmlArr     = array("money" ,  "age",  "percent", "userName" );
        $explainArr  = array("提现金额", "年纪", "比例",    "用户名" );
        $typeArr     = array("number" ,  "number",  "number", "str");
        $mustFillArr = array(true,true,false,true);
        $rangeArr = array();
        $rangeArr[0] = "(30,120)";
        $way ='get';
        $headerUrl ='';
        self::validate($way, $headerUrl,$htmlArr, $explainArr,$mustFillArr, $typeArr,$rangeArr);
    }

    public static  function validate($way,$headerUrl, $htmlArr,$explainArr,$mustFillArr,$typeArr,$rangeArr=null)
    {
        $arr = $way=='get'?$_GET:$_POST;
        $data = array();
        foreach($htmlArr as $n =>$field)
        {
            $value = trim($arr[$field]);
            $explain = $explainArr[$n];
            $kind    = $typeArr[$n];
            $must =$mustFillArr[$n];
            if( $must )//必填
            {
                $mes = self::check_empty($value,$explain);
                if( $mes !== true )
                {
                    self::remind( $mes,$headerUrl);
                }
            }
            if($value!='')
            {
                switch($kind)
                {
                    case "number":
                        $mes =self::checkNumber($value,$explain,$rangeArr[$n]);
                        break;
                    case "str":
                        $value = addSlashesOrNot($value);
                        $mes =self::checkStr($value,$explain);
                        break;
                    default:
                        $mes =self::checkPreg($value,$kind,$explain);
                }
                if( $mes !== true )
                {
                    self::remind($mes,$headerUrl);
                }
            }
            $data[$field] = $value;
        }
        return $data;
    }
    protected  static function remind($mes,$headerUrl)
    {
        echo "<script language='javascript'>alert('$mes');window.location.href='$headerUrl';</script>";
       return false;
    }
    protected static function check_empty($value,$explain)
    {
        if($value=='')
        {
            return $explain.'不能为空';
        }
        else
        {
            return true;
        }
    }
    protected static function checkNumber($value,$explain,$range=null)
    {
        if(!is_numeric($value))
        {
            return $explain.'必须是数字';
        }
        if(!$range)
        {
            return true;
        }
        $arr = explode(',',$range);
        $left =substr( $arr[0],0,1);
        $min = substr( $arr[0],1);
        $right = substr( $arr[1],-1,1);
        $max = substr( $arr[1],0,-1);
        switch($left)
        {
            case '(':
                if(!($value > $min))
                {
                    return $explain.'必须大于'.$min;
                }
                break;
            case '[':
                if(!($value<=$min))
                {
                    return $explain.'必须大于等于'.$min;
                }
                break;
            default:
        }
        switch($right)
        {
            case ')':
                if(!($value<$max))
                {
                    return $explain.'必须小于'.$max;
                }
                break;
            case ']':
                if(!($value<=$max))
                {
                    return $explain.'必须小于等于'.$max;
                }
                break;
            default:
        }
        return true;
    }
    
    protected static function checkStr($value,$explain)
    {
        if(is_numeric($value))
        {
            return $explain.'不能全部是数字';
        }
        else
        {
            return true;
        } 
    }
    protected static function checkPreg($value,$pregExp,$explain)
    {
        if(!preg_match($pregExp,$value ))
        {
            return $explain.'格式不正确';
        }
        else
        {
            return true;
        }
    }
}