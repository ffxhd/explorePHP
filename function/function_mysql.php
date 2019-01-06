<?php
//---------------mysql ------------------------------------------------------------------

/**
 * 字符串转义,防止SQL注入
 * @param string $str
 * @return string <string, unknown>
 */
function  addSlashesOrNot($str)
{
    global $get_magic_quotes_gpc_result;
    return !$get_magic_quotes_gpc_result? addslashes($str):$str;
    /* addslashes() 函数返回在预定义的字符前添加反斜杠的字符串。
     默认情况下，PHP 指令 magic_quotes_gpc 为 on，
     对所有的 GET、POST 和 COOKIE 数据自动运行 addslashes()。
     不要对已经被 magic_quotes_gpc 转义过的字符串使用 addslashes()，
     因为这样会导致双层转义。遇到这种情况时可以使用函数 get_magic_quotes_gpc() 进行检测。 */
}

/**
 * @param array $fieldsArr 你想要展示的字段(取自$formArr)，在表头显示
 * @param array $formArr  添加表单时用到的所有字段： 字段=>配置(便于vue.js)
 * @return array
 */
function tableHeadArr($fieldsArr,$formArr)
{
    $a = array();
    foreach( $fieldsArr as $key => $field )
    {
        if( isset($formArr[$field]) )
        {
            if( $field === 'add_time' || $field ==='modify_time' )
            {
                $a[$field] = isset($formArr[$field])?  $formArr[$field] : array(
                    'mean'=>$field === 'add_time'?'添加时间':'上次修改',
                    'tip'=>'',
                    'html'=>array(
                        'tagName'=>'input',
                        'type'=>'text'
                    ),
                    'validate'=>array( 'must'=>2 )
                );
            }
            else
            {
                $a[$field]  = $formArr[$field] ;
            }
        }
    }
    return $a;
}

function getEmptyInfo($table)
{
    $prefix = C('DB_PREFIX');
    $tableInfo = M()->query("show COLUMNS from `{$prefix}{$table}`");
    $fieldsArr = getColumnsAsArrayFromList($tableInfo,'Field');
    $a = array();
    foreach( $fieldsArr as $key => $field )
    {
        $a[$field]  = '' ;
    }
    return $a;
}

function sql_in($idsArr)
{
    return  $idsArr ? implode(',',$idsArr) :'null';
}

function listToStrForSql_in($list,$field)
{
    $array = array();
    foreach($list as $k => $info)
    {
        $array[] =  $info[$field];
    }
    $str = implode(',',$array);
    return $array ? $str : 'null';
}

function joinFieldsToSelect($fields)
{
    $fieldsArr = is_array($fields) ? $fields :  explode(',',$fields);
    $s = array();
    foreach($fieldsArr as $k =>$value)
    {
        $s[] = "`{$value}`";
    }
    $s = implode(',',$s);
    return $s;
}

function getPageSize()
{
    $pageSize = I('request.pageSize',10,'intval');;//每页数量
    return  $pageSize <= 0 ? 10 : intval($pageSize);
}

/**
 * 统一判断是否操作成功
 * @param $op_result
 * @return bool
 */
function isOperateSuccessfully($op_result)
{
    return $op_result !== false;
}

function createPreInsertSql_logic($table, $oneDataArr)
{
    $fieldsToInsert = array_keys($oneDataArr);
    $L = count($fieldsToInsert);
    $fieldsToInsert = joinFieldsToSelect($fieldsToInsert);
    $virtualValue = array_fill ( 0 ,  $L ,  '?' );
    $virtualValue = implode(',',$virtualValue);
    $prefix = C('DB_PREFIX');
    return  "insert into `{$prefix}{$table}`  ({$fieldsToInsert}) VALUES ({$virtualValue})";
}

function createPreUpdateSql_logic($table, $oneDataArr, $oneWhereArr)
{
    $toSet = array();
    $where = array();
    //
    foreach( $oneDataArr as $field => $fieldVal )
    {
        $toSet[] = "`{$field}` = ? ";
    }
    $toSet = implode(',',$toSet);
    //
    foreach( $oneWhereArr as $field => $fieldVal )
    {
        $where[] = "`{$field}` = ?";
    }
    $where = implode(' and ',$where);
    $prefix = C('DB_PREFIX');
    return  "update `{$prefix}{$table}`  set  {$toSet}  where  {$where}";
}

function auto_batchUpdate($table,$whereDataList, $commonWhereArr = array())
{
    //$pre_sql = "update cn_pf_stmt set `appellation` = ?, `subtotal`= ? where id= ?";
    //$pre_sql = "insert into cn_pf_stmt  (`appellation`, `subtotal`) VALUES (?, ?)";

    /*$desk_updateArr[] = array(
            'where' => array(
                'desk_id' => $oriDeskId,
            ),
            'data'=>array(
                $now_total_busy_seats_field => $oriAllBusy - $billBusy,
                $now_total_free_seats_field => $nowAllFree,
                $now_state_field => DeskController::deskState($nowAllFree, $oriTotal),
            )
      );*/

    $oneDataArr = $whereDataList[0]['data'];
    $oneWhereArr = getItemFromArray($whereDataList[0],'where',array());
    $isUpdate = !empty($actualWhereArr);
    if( true === $isUpdate )
    {
        $oneWhereArr = array_merge($oneWhereArr,$commonWhereArr);
    }
    $pre_sql = true === $isUpdate ?
        createPreUpdateSql_logic($table, $oneDataArr, $oneWhereArr) :
        createPreInsertSql_logic($table, $oneDataArr);
    $stmt = M($table)->getStmt($pre_sql);
    $executeFailArr = array();
    $whereActual = array();
    foreach( $whereDataList as $seq => $arr )
    {
        $dataToExecute =  $arr['data'];
        $tempArr =$dataToExecute;
        if( true === $isUpdate)
        {
            $whereActual = getItemFromArray($arr, 'where', array());
            $tempArr = array_merge($tempArr,$whereActual,$commonWhereArr);
        }
        $executeParam = array_values($tempArr);
        $result = $stmt->execute($executeParam);
        if( false === $result)
        {
            $errorInfo = $stmt->errorInfo();
            $executeFailArr[$seq] = array(
                'where'=> $whereActual,
                'data' => $dataToExecute,
                'errorInfo' => $errorInfo
            );
        }
    }

    return  empty( $executeFailArr ) ? true : $executeFailArr;
}

function createPreSql_insert_q_mark($table,$actualValuesArr)
{
    return createPreInsertSql_logic($table, $actualValuesArr[0]);
}

function createPreSql_update_q_mark($table,$actualValuesArr,$actualWhereArr)
{
    return createPreUpdateSql_logic($table,$actualValuesArr[0],$actualWhereArr[0]);
}

function auto_batchInsertOrUpdate($table,$actualValuesArr, $actualWhereArr = array())
{
    //$pre_sql = "update cn_pf_stmt set `appellation` = ?, `subtotal`= ? where id= ?";
    //$pre_sql = "insert into cn_pf_stmt  (`appellation`, `subtotal`) VALUES (?, ?)";
    /* $actualValuesArr = array();
        $actualWhereArr = array();
        for($i= 22; $i<= 29;$i++)
        {
            $actualValuesArr[] = array(
                'appellation' => 'update2_'.'func'.'_'.$i,
                'subtotal' => $i * 100
            );
            $actualWhereArr[] = array(
                'id' => $i
            );
        }*/
    $isUpdate = !empty($actualWhereArr);
    $pre_sql = true === $isUpdate ?
        createPreSql_update_q_mark($table,$actualValuesArr,$actualWhereArr) :
        createPreSql_insert_q_mark($table,$actualValuesArr);
    $stmt = M($table)->getStmt($pre_sql);
    $executeFailArr = array();
    $whereActual = array();
    foreach( $actualValuesArr as $seq => $arr )
    {
        $tempArr = $arr;
        if( true === $isUpdate)
        {
            $whereActual = getItemFromArray($actualWhereArr, $seq, array());
            $tempArr = array_merge($tempArr,$whereActual);
        }
        $executeParam = array_values($tempArr);
        $result = $stmt->execute($executeParam);
        if( false === $result)
        {
            $errorInfo = $stmt->errorInfo();
            $executeFailArr[$seq] = array(
                'where'=> $whereActual,
                'data' => $arr,
                'errorInfo' => $errorInfo
            );
        }
    }
    return  empty( $executeFailArr ) ? true : $executeFailArr;
}
