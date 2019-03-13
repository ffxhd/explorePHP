<?php
/**
 * Created by PhpStorm.
 * User: 飞飞小坏蛋
 * Date: 2017/7/12
 * Time: 14:37
 */
//----------数组-----------------------------------------------------------------------

/**
 * 返回数组中指定的多列
 * @param array   $list 需要取出数组列的多维数组（或结果集）
 * @param string|array  $fields  比如："appellation,state"
 * 【需要返回值的列，它可以是索引数组的列索引，或者是关联数组的列的键。
 * 也可以是 NULL ，此时将返回整个数组（配合 index_key 参数来重置数组键的时候，非常管用）】
 * @param string  $fieldValAsInfoKey 比如： id
 * @return array
 */
function getColumnsAsArrayFromList_v2($list,$fields,$fieldValAsInfoKey = '')
{
    //appellation, state
    /*
        [appellation] = array(
               [id值] => appellation值,
               [id值] => appellation值,
              [id值] => appellation值
               ...
         )
         [state] = array(
               [id值] => state值,
              [id值] => state值,
             [id值] => state值
            ...
         )
       */
    $isSpecifyKey = !empty($fieldValAsInfoKey);
    $fields = is_string($fields) ? explode(',',$fields) : $fields;
    $wannaData = array();
    if( function_exists('array_column'))
    {
        foreach( $fields as $key => $field )
        {
            $wannaData[$field] = true === $isSpecifyKey ? array_column($list,$field,$fieldValAsInfoKey) : array_column($list,$field);
        }
        return $wannaData;
    }
    foreach( $fields as $fieldSeq => $field )
    {
        $a = array();
        foreach( $list as $key => $arr )
        {
            $value  = $arr[$field];
            if(true === $isSpecifyKey )
            {
                $newKey = $arr[$fieldValAsInfoKey];
                $a[$newKey] = $value;
            }
            else
            {
                $a[] = $value;
            }
        }
        $wannaData[$field] = $a;
    }
    return $wannaData;
}

/**
 * 返回数组中指定的一列
 * @param array   $list 需要取出数组列的多维数组（或结果集）
 * @param string  $field  比如：appellation
 * 【需要返回值的列，它可以是索引数组的列索引，或者是关联数组的列的键。
 * 也可以是 NULL ，此时将返回整个数组（配合 index_key 参数来重置数组键的时候，非常管用）】
 * @param string  $fieldValAsInfoKey 比如： id
 * @return array 比如： [id] => 名称
 */
function getColumnsAsArrayFromList($list,$field,$fieldValAsInfoKey = '')
{
    $isSpecifyKey = !empty($fieldValAsInfoKey);
    if( function_exists('array_column'))
    {
        return true === $isSpecifyKey ? array_column($list,$field,$fieldValAsInfoKey) : array_column($list,$field);
    }
    $a = array();
    foreach( $list as $key => $arr )
    {
        $value  = $arr[$field];
        if(true === $isSpecifyKey )
        {
            $newKey = $arr[$fieldValAsInfoKey];
            $a[$newKey] = $value;
        }
        else
        {
            $a[] = $value;
        }
    }
    return $a;
}


/**
 * 数组转JSON
 * @param  array $data
 * @return string
 */
function arrayToJSON($data)
{
    return defined('JSON_UNESCAPED_UNICODE') ?
        json_encode($data,JSON_UNESCAPED_UNICODE ) :json_encode($data);
}

function readConfig( ...$fields)
{
    global $config;
    $toReturn = getItemFromArray( $config,$fields[0],'');
    if(count($fields) === 1)
    {
        return  $toReturn;
    }

    foreach($fields as $seq => $arg )
    {
        if($seq === 0)
        {
            continue;
        }
        $toReturn = getItemFromArray( $toReturn,$fields[$seq],'');
        if($toReturn === '')
        {
            break;
        }
        return $toReturn;
    }
}