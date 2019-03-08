<?php
/**
 * Created by PhpStorm.
 * User: pf
 * Date: 2016/7/18
 * Time: 14:50
 */
namespace  onRequest\core;
class where{
    /*$whereArr = array('nickName', 'run_date','issue');
     $whereAsArr = array('a.userName','b.issue');
    $whereAsArr = array(
         array('a.user_id','in'),
         array('a.userName','like'),
        array('b.discount',null),//is null,>0
        array('b.reward_times','>=')
        array('c.issue','between','issueStart',,'issueEnd')
    );
      $orderByArr = array('dateline','sum');//dateline desc
    $orderByAsArr = array('a.dateline','b.sum');//a.dateline desc
    */
    //public static $toAssign = array();
    public static $pageUrl='';

    public  static  function getOrderBy($orderByArr,$defaultSort)//$asArr_orderBy,
    {
        $pageUrl ='';
        $arr = array();
        foreach($orderByArr as $seq =>$key )
        {
            $value = $_GET[$key];
            $value = addSlashesOrNot(trim($value));
            if($value)
            {
                //$field = $asArr_orderBy[$seq];
               // $arr[$key] = "{$field} {$value}";
                $arr[$key] = "{$value}";
            }
            else
            {
                continue;
            }
            $pageUrl  .= "&{$key}={$value}";
        }
        self::$pageUrl .= $pageUrl;
        //self::$toAssign[$key] = $value;
        if($arr)
        {
            $order_by = implode(',',$arr);
            $orderBy  = 'order by '.$order_by;
            return $orderBy;
        }
        else
        {
            return  'order by '.$defaultSort;
        }
    }

    public static function getCondition($whereArr, $asArr_where,$needExtraWhere = false)
    {
        //$toAssign = array();
        $pageUrl ='';
        $arr = array();
        foreach($whereArr as $seq =>$key )
        {
            $value = trim($_GET[$key]);
            if($value!=null)
            {
                $value = addSlashesOrNot($value);
                switch(true)
                {
                    case  is_string( $asArr_where[$seq] ):
                        $field = $asArr_where[$seq];
                        $arr[$key] = self::eqValue($field,$value);
                        break;
                    case is_array( $asArr_where[$seq] ):
                        $arr[$key] = self::chooseLink($asArr_where[$seq],$value);
                        break;
                    default:
                }
            }
            else
            {
                continue;
            }
            $pageUrl  .= "&{$key}={$value}";
            //$toAssign[$key] = $value;
        }
        self::$pageUrl .= $pageUrl;
        //self::$toAssign = $toAssign;
        if($arr)
        {
            $where = implode('',$arr);
            $where = ltrim($where,'and');
            $condition = 'where '.$where;
            return $needExtraWhere?$condition.' and ': $condition;
        }
        else
        {
            return $needExtraWhere?' where ':null;
        }
    }

    protected static function eqValue($field,$value)
    {
        switch(true)
        {
            case is_numeric($value):
                return  "and  {$field}={$value} ";
                break;
            default:
                $value = addSlashesOrNot($value);
                return "and  {$field}='{$value}' ";
        }
    }

    protected static function chooseLink($field_link_arr,$value)
    {
        $field = $field_link_arr[0];
        $link = $field_link_arr[1];
        switch($link)
        {
            case 'in':
                return  "and  {$field} in({$value}) ";
                break;
            case 'like':
                return "and  {$field} like '%{$value}%' ";
                break;
            case '>':
            case '<':
            case '>=':
            case'<=':
            case'!=':
                $value2 = is_numeric($value)?$value:"'{$value}'";
                return "and  {$field} {$link} {$value2} ";
                break;
            case '':
                echo 'null';
                return "and  {$field}  {$value} ";
                break;
            case 'between':
                $endField = $field_link_arr[2];
                $startValue = $value;
                $endValue = addSlashesOrNot(trim($_GET[$endField]));
                //$endValue = $endValue > $startValue?$endValue : $startValue;
                if($startValue && $endValue)
                {
                    $startValue = is_numeric($startValue) ? $startValue : "'{$startValue}'";
                    $endValue   = is_numeric($endValue)   ?  $endValue  : "'{$endValue}'";
                    return "and  {$field} between {$startValue} and  {$endValue} ";
                }
                else
                {
                    return null;
                }
                break;
            default:
                return null;
        }
    }

/* $whereAsArr = array($issue,$prizeNumber,$userName);
    $asArr_where = array(
        'a.issue',
         'a.prizeNumber',
        array('a.userName','like'),
    );
 * */
    public static function combineCondition($asArr_where ,$whereArr,$needExtraWhere = false)
    {
        $arr = array();
        foreach($whereArr as $seq =>$val )
        {
            $value = trim($val);
            if($value!=null)
            {
                $value = addSlashesOrNot($value);
                switch(true)
                {
                    case  is_string( $asArr_where[$seq] ):
                        $field = $asArr_where[$seq];
                        $arr[] = self::eqValue($field,$value);
                        break;
                    case is_array( $asArr_where[$seq] ):
                        $arr[] = self::chooseLink($asArr_where[$seq],$value);
                        break;
                    default:
                }
            }
            else
            {
                continue;
            }
        }
        if($arr)
        {
            $where = implode('',$arr);
            $where = ltrim($where,'and');
            $condition = 'where '.$where;
            return $needExtraWhere?$condition.' and ': $condition;
        }
        else
        {
            return $needExtraWhere?' where ':null;
        }
    }

}