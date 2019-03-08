<?php
// $numArr = array(1,2,3,4,5,6,7,8,9,10,11,12);
//$chineseNumArr = array('一', '二', '三', '四', '五','六', '七', '八', '九', '十', '十一', '十二');
function start_end($timeSpan,$needString =true)
{
    $todayDate  = date('Y-m-d ',$timeSpan);
    $todayStart = date_create($todayDate.' 00:00:00');
    $todayEnd   = date_create($todayDate.' 23:59:59');
    $todayStartSpan =  date_timestamp_get($todayStart);  //今天0:00:00的时间戳
    $todayEndSpan   =  date_timestamp_get($todayEnd);    //今天23:59:59的时间戳
    return $needString? $todayStartSpan.' and '.$todayEndSpan : array( $todayStartSpan, $todayEndSpan );
}

function monthDayRange($timeSpan,$needString =true)
{
    $Ym = date('Y-m',$timeSpan);
    $arr = explode('-',$Ym);
    $year = $arr[0];
    $month = $arr[1];
    $month31d = array(1,3,5,7,8,10,12);
    $month30d = array(4,6,9,11);
    switch(true)
    {
        case in_array($month,$month31d);
            $lastDay =31;
            break;
        case in_array($month,$month30d);
            $lastDay =30;
            break;
        default:
            $lastDay = $year%4 ==0? 29:28;
    }
    $startDay  = $year.'-'.$month.'-01';
    $endDay    = $year.'-'.$month.'-'.$lastDay;
    return $needString? $startDay.' and '.$endDay : array($startDay,$endDay);
}

//本月的每一天以及对应星期几（0表示周日，1表示周一，2表示周二，...，6表示周六
function thisMonth($startDay,$lastDay)
{
    //本月的每一天对应星期几？
    $day_week = array();
//本月第一天是星期几
    $startDay   = date_create($startDay);
    $startDaySpan =  date_timestamp_get($startDay);  //本月第一天的时间戳
    $firstWeekNum = date('w',$startDaySpan);
//留白
    if($firstWeekNum>=1)
    {
        for($i=0;$i<$firstWeekNum;$i++)
        {
            $day_week[-10-$i] = null;
        }
    }
//本月的每一天对应星期几
    $theWeekNum = $firstWeekNum;
    for($i=1;$i<=$lastDay;$i++)
    {
        $day_week[$i] = $theWeekNum;
        $theWeekNum++;
        $theWeekNum = $theWeekNum==7? 0 : $theWeekNum;
    }
    return $day_week;
}