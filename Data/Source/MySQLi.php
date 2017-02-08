<?php
namespace Data\Source;

/**
 * MySQL数据库封装类
 *
 * @package XphpExtend
 * @author  Tianfeng.Han
 *
 */
class MySQLi extends \mysqli implements \IFace\Database
{
    public $debug = false;
    public $conn = null;
    public $config;

    function lastInsertId()
    {
        return $this->insert_id;
    }

    /**
     * @param null $host
     * @param null $user
     * @param null $password
     * @param null $database
     * @param null $port
     * @param null $socket
     * @return bool
     */
    function connect($host = null, $user = null, $password = null, $database = null, $port = null, $socket = null)
    {
        parent::connect(
            $host,
            $user,
            $password,
            $database,
            $port
        );
        if ($this->connect_errno)
        {
            trigger_error("mysqli connect to server[$host:{$port}] failed: " . mysqli_connect_error(), E_USER_WARNING);
            return false;
        }
        if (!empty($this->config['charset']))
        {
            $this->set_charset($this->config['charset']);
        }
        return true;
    }

    /**
     * 过滤特殊字符
     * @param $value
     * @return string
     */
    function quote($value)
    {
        return $this->tryReconnect(array($this, 'escape_string'), array($value));
    }

    /**
     * SQL错误信息
     * @param $sql
     * @return string
     */
    protected function errorMessage($sql)
    {
        $msg = $this->error . "<hr />$sql<hr />\n";
        $msg .= "Server: {$this->config['host']}:{$this->config['port']}. <br/>\n";
        if ($this->connect_errno)
        {
            $msg .= "ConnectError[{$this->connect_errno}]: {$this->connect_error}<br/>\n";
        }
        $msg .= "Message: {$this->error} <br/>\n";
        $msg .= "Errno: {$this->errno}\n";
        return $msg;
    }

    protected function tryReconnect($call, $params)
    {
        $result = false;
        for ($i = 0; $i < 2; $i++)
        {
            $conn = $this->checkConnection();
            if ($conn === true)
            {
                $result = @call_user_func_array($call, $params);
                break;
            }
        }
        return $result;
    }


    /**
     * 执行一个SQL语句
     * @param string $sql 执行的SQL语句
     * @return MySQLiRecord | false
     */
    function query($sql)
    {

        $result = $this->tryReconnect(array('parent', 'query'), array($sql));

        if (!$result)
        {
//            trigger_error(__CLASS__." SQL Error:". $this->errorMessage($sql), E_USER_WARNING);
            throw new \ErrorException("DATABASE_QUERY_ERROR");
        }
        if (is_bool($result))
        {
            return $result;
        }

        return new MySQLiRecord($result);
    }
    /**
     * 获取表主键
     */
//    public function getPrimaryKey($table_name){
//        $sql = "SELECT k.column_name as primary_key FROM information_schema.table_constraints t JOIN information_schema.key_column_usage k USING (constraint_name,table_schema,table_name) WHERE t.constraint_type='PRIMARY KEY' AND t.table_schema='{$this->config['name']}' AND t.table_name='{$table_name}'";
//        $ret= $this->query($sql);
//        return $ret->fetch()['primary_key'];
//    }
//    /**
//     * 判断表是否存在
//     */
//    public function classExist($table_name){
//        $sql = "select TABLE_NAME as class_name from INFORMATION_SCHEMA.TABLES where TABLE_SCHEMA='{$this->config['name']}' and TABLE_NAME='{$table_name}'";
//        $ret = $this->query($sql);
//        return $ret->fetch()['class_name'];
//    }
    /**
     * 执行多个SQL语句
     * @param string $sql 执行的SQL语句
     * @return MySQLiRecord | false
     */
    function multi_query($sql)
    {
        $result = $this->tryReconnect(array('parent', 'multi_query'), array($sql));
        if (!$result) {
//            \Exception\Error::info(__CLASS__ . " SQL Error", $this->errorMessage($sql));
            throw new \ErrorException("DATABASE_CONNECT_ERROR");
//            return false;
        }

        $result = call_user_func_array(array('parent', 'use_result'), array());
        $output = array();
        while ($row = $result->fetch_assoc()) {
            $output[] = $row;
        }
        $result->free();

        while (call_user_func_array(array('parent', 'more_results'), array()) && call_user_func_array(array('parent', 'next_result'), array())) {
            $extraResult = call_user_func_array(array('parent', 'use_result'), array());
            if ($extraResult instanceof \mysqli_result) {
                $extraResult->free();
            }
        }
        return $output;
    }

    /**
     * 异步SQL
     * @param $sql
     * @return bool|\mysqli_result
     */
    function queryAsync($sql)
    {
        $result = $this->tryReconnect(array('parent', 'query'), array($sql, MYSQLI_ASYNC));
        if (!$result)
        {
            throw new \ErrorException("DATABASE_CONNECT_ERROR");
//            \Exception\Error::info(__CLASS__." SQL Error", $this->errorMessage($sql));
//            return false;
        }
        return $result;
    }

    /**
     * 检查数据库连接,是否有效，无效则重新建立
     */
    protected function checkConnection()
    {
        if (!@$this->ping())
        {
            $this->close();
            $db_config = $this->config;
            return $this->connect($db_config['host'],$db_config['user'],$db_config['passwd'],$db_config['name'],$db_config['port']);
        }
        return true;
    }

    /**
     * 获取错误码
     * @return int
     */
    function errno()
    {
        return $this->errno;
    }

    /**
     * 获取受影响的行数
     * @return int
     */
    function getAffectedRows()
    {
        return $this->affected_rows;
    }

    /**
     * 返回上一个Insert语句的自增主键ID
     * @return int
     */
    function Insert_ID()
    {
        return $this->insert_id;
    }

}

class MySQLiRecord implements \IFace\DbRecord
{
    /**
     * @var \mysqli_result
     */
    public $result;

    function __construct($result)
    {
        $this->result = $result;
    }

    function fetch()
    {
        return $this->result->fetch_assoc();
    }

    function fetchall()
    {
        $data = array();
        while ($record = $this->fetch())
        {
            $data[] = $record;
        }
        return $data;
    }

    function free()
    {
        $this->result->free_result();
    }

    function __get($key)
    {
        return $this->result->$key;
    }

    function __call($func, $params)
    {
        return call_user_func_array(array($this->result, $func), $params);
    }
}
