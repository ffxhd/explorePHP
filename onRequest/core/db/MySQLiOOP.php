<?php
namespace onRequest\core\db;
//MySQLi面向对象
class MySQLiOOP{
    private $conn = null;
    private $stmt = null ;
    protected $sqlArr = [];
    /**
     * 报错函数
     * @param $error
     * @param string $errPrefix
     */
    private function err($error,$errPrefix = '对不起,您的操作有误,原因是:')
    {
        $str = <<<EOF
            <div style="font-size: 30px;width: 80%">
                <span style="color:red;">{$errPrefix}</span>
                <div style="display:inline-block;background-color:#FFFF00;">{$error}</div>
            </div><br/>
EOF;
        echo $str;
    }

    public function isConnecting()
    {
        return $this->conn->ping();
    }

    public function connectDbServer($host,$user,$password,$databaseName,$charset)
    {
        if(  $this->conn != null )
        {
            return null;
        }
        $conn = @new \mysqli($host,$user,$password);
        $this->conn = $conn;
        $error = $conn->connect_error;
        if( $error )
        {
            $error = iconv('gbk','utf-8',$error);
            $this->err("<mark>{$error}</mark>", 'mysql服务器连接失败：');
        }
        if($databaseName)
        {
            $this->selectDB($databaseName);
        }
        $conn->set_charset ($charset);
        return $conn;
    }

    /**connectDbServer
     * MySQLiOOP constructor. 连接数据库
     * @param $host
     * @param $user
     * @param $password
     */
    public function __construct($host,$user,$password)
    {
        if( 0 > 1 )
        {
            $this->conn = @new \mysqli($host,$user,$password);
            $this->stmt = $this->conn->prepare('');
        }
    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        $this->conn->close();
        $this->stmt = null;
    }

    public function selectDB($db_name)
    {
        $conn = $this->conn;
        if(!$conn->select_db($db_name))
        {
            $this->err('<mark>select_db错误：'.$conn->error.'</mark>');
        }
    }

    public function fetchSqlArr()
    {
        return $this->sqlArr;
    }

    public function cleanSqlArr()
    {
       $this->sqlArr = [];
    }

    /**执行sql语句
     * @param string $sql
     * @return resource|\mysqli_result  返回执行成功、资源或执行失败
     */
    function query($sql)
    {
        //say($sql,'$sql');
        $this->sqlArr[] = $sql;
        $conn = $this->conn;
        $query = $conn->query($sql);
        if($query === false )
        {
            $this->err('您的SQL语句：<mark>'.$sql."</mark><br/>错误：<span style=\"background-color:yellow;color:red\">{$conn->error}</span>");
        }
        return $query;
    }

    /**执行多条sql语句
     * @param array $sqlArr
     * @return bool
     */
    function multiQuery($sqlArr)
    {
        $this->sqlArr[] = $sqlArr;
        $conn = $this->conn;
        $sql = implode(';',$sqlArr);
        $query = $conn->multi_query($sql) ;
        if($query === false )
        {
            $this->err("错误：".$conn->error);
            say('您的sql语句有：',$sqlArr);
        }
        return $query;
    }

    /**
     * @param array $sqlArr
     * @return array
     */
    public function multiFind($sqlArr)
    {
        $this->sqlArr[] = $sqlArr;
        $con=$this->conn;
        //say('$sql',$sql);
        $query = $this->multiQuery($sqlArr);
        //$result = $query->fetch_all();
        if( !$query)
        {
            return [];
        }
        $keyArr = array_keys($sqlArr);
        $allData = [];
        $i = 0;
        do
        {
            if( $i > 0)
            {
                mysqli_next_result($con);
            }
            $key = $keyArr[$i];
            $result = $con->store_result();// 存储第一个结果集
            if( $result )
            {
                //$L = $result->num_rows;
                //say('$L',$L);
                $data = $result->fetch_all(MYSQLI_ASSOC);
                //say('fetch_all-$data',$data);
                /*if( false === $allItemAreList && $L === 1)
                {
                    $data = $data[0];
                }*/
                $allData[$key] = $data;
                $result->free();
                $i++;
            }
        }
        while ($con->more_results());
        return $allData;
    }

    /**
     *列表
     *
     *@param resource|\mysqli_result $query sql语句通过$conn->query 执行出来的资源
     *@return array   返回列表数组
     **/
    function findAll($query)
    {
        return $query->fetch_all(MYSQLI_ASSOC);
    }

    /**单条
     * @param resource|\mysqli_result $query sql语句通过$conn->query执行出的来的资源
     * @return array 返回单条信息数组
     */
    function findOne($query)
    {
        $rs = $query->fetch_array(MYSQLI_ASSOC );
        return $rs? $rs : array();
    }

    /**
     * @param resource|\mysqli_result $query
     * @param $field
     * @return mixed
     */
    public function findResultFromTheInfo($query,$field)
    {
        $obj = $query->fetch_object();
        return $obj->$field;
    }

    /**
     * 指定行的指定字段的值
     * @param resource|\mysqli_result $query $query sql语句通过$conn->query执行出的来的资源
     * @param $row
     * @param string $field 指定字段的
     * @return mixed 返回指定行的指定字段的值
     */
    function findResult($query,$row,$field)
    {
        /* fetch object array */
        $i = 0;
        while ( $obj  =  $query ->fetch_object())
        {
            if($i == $row )
            {
                return $obj -> $field;
            }
            $i ++;
        }
        return '';
    }

    /**添加函数
     * @param string $table 表名
     * @param array $arr 添加数组（包含字段和值的一维数组）
     * @return mixed
     */
    function insert($table,$arr)
    {
        $sql = $this->sqlForInsert($table,$arr,false);
        $this->query($sql);
        return $this->conn->insert_id;
    }

    public function sqlForInsert($table,$arr,$isPreInert)
    {
        $keyArr = array();
        $valueArr = array();
        foreach($arr as $key=>$value)
        {
            //$value=mysql_real_escape_string($value);
            $keyArr[]="`".$key."`";
            $valueArr[]= $isPreInert=== true? '?' : "'{$value}'";
        }
        $keys=implode(',',$keyArr);
        $values=implode(',',$valueArr);
        return  "insert into {$table} ({$keys}) values ({$values})";
    }

    function preDeal($preSql)
    {
        $conn = $this->conn;
        //例如$preSql="INSERT INTO MyGuests (firstname, lastname, email) VALUES(?, ?, ?)";
        $stmt = $conn->prepare($preSql);//插入，更新
        if(false === $stmt)
        {
            $this->err("预处理语句有错误：<pre>{$preSql}</pre>");
        }
        $this->stmt = $stmt;
        return $stmt;
        //注意 主键未设置自增，循环插入会全部失败--2017-12-22
        /* 以下外界使用
         * $stmt->bind_param("sss", $firstname, $lastname, $email);
        // 设置参数并执行
        $firstname = "John";
        $lastname = "Doe";
        $email = "john@example.com";
        $stmt->execute();

        $firstname = "Mary";
        $lastname = "Moe";
        $email = "mary@example.com";
        $stmt->execute();

        $firstname = "Julie";
        $lastname = "Dooley";
        $email = "julie@example.com";
        $stmt->execute();
        $stmt->close();
        $conn->close(); */
    }

    public function stmtExecuteResult()
    {
        //$stmt->affected_rows === -1
        if( false === is_object($this->stmt))
        {
            return false;
        }
        if(  false === empty($this->stmt->error_list))
        {
            $this->err('预处理语句执行出错');
            say('预处理语句执行出错 ',$this->stmt->error_list );
            return false;
        }
        return true;
    }

    /**修改函数
     * @param string $table  表名
     * @param array $arr 修改数组（包含字段和值的一维数组）
     * @param  string|array $where 条件
     * @return bool|resource
     */
    function update($table,$arr,$where)
    {
        $keyWithValArr = array();
        foreach($arr as $key=>$value)
        {
            $keyWithValArr[]='`'.$key."`='".$value."'";
        }
        $keyAndValues=implode(',',$keyWithValArr);
        $sql='update '.$table.' set '.$keyAndValues.' where '.$where;
        return $this->query($sql);
    }

    function doCommit($sql1,$sql2)
    {
        $conn=$this->conn;;
        $conn->query("SET AUTOCOMMIT=0");//设置为不自动提交，因为MYSQL默认立即执行
        $conn->query("BEGIN");//开始事务定义
        if(!$conn->query($sql1))
        {
            $conn->query("ROLLBACK");//判断当执行失败时回滚
        }
        if(!$conn->query($sql2))
        {
            $conn->query("ROLLBACK");//判断执行失败回滚
        }
        $conn->query("COMMIT");//执行事务
    }

    /**删除函数
     * @param string $table
     * @param string|array $where
     * @return bool|resource
     */
    function delete($table,$where)
    {
        if(true === is_array($where))
        {
            $where = implode(' and ',$where);
        }
        $sql='delete from '.$table.' where '.$where;
        return $this->query($sql);
    }

    public function getAffectedRows()
    {
        return $this->conn->affected_rows;
    }
}

