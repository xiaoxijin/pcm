<?php
namespace Xphp;

/**
 * 查询数据库的封装类，基于底层数据库封装类，实现SQL生成器
 * 注：仅支持MySQL，不兼容其他数据库的SQL语法
 * @author Xijin.Xiao
 * @package XphpSystem
 * @subpackage Database
 */
trait DBAdapter
{
    public $debug = false;
    public $read_times = 0;
    public $write_times = 0;

    static $error_call = '';
    static $allow_regx = '#^([a-z0-9\(\)\._=\-\+\*\`\s\'\",]+)$#i';
    public $_db;
    public $table = '';
    public $primary = 'id';
    private $select = '*';
    public $sql = '';
    public $limit = '';
    public $where = '';
    public $order = '';
    public $group = '';
    public $use_index = '';
    public $having = '';
    public $join = '';
    public $union = '';
    public $for_update = '';
    public $row_data_index = 0;

    /**
     * @var \Xphp\RecordSet
     */
    protected $result;

    //Union联合查询
    private $if_union = false;
    private $union_select = '';

    //Join连接表
    private $if_join = false;
    private $if_add_tablename = false;

    protected $extraParmas = array();
    
    /**
     * 缓存选项
     * @var array
     */
    protected $cacheOptions = array();

    //Count计算
    private $count_fields = '*';

    public $page_size = 10;
    public $num = 0;
    public $pages = 0;
    public $page = 0;
    public $pager = null;

    public $auto_cache = false;
    protected $enableCache;

//    const CACHE_PREFIX = 'xphp_selectdb_';
//    const CACHE_LIFETIME = 300;

    public $RecordSet;

    public $is_execute = 0;

    public $result_filter = array();

    public $call_by = 'func';

    public function __construct()
    {
        $this->_db = Data::getInstance()->data("db");
    }

    /**
     * 初始化，select的值，参数$where可以指定初始化哪一项
     * @param $what
     */
    function init($what='')
    {
        if($what=='')
        {
            $this->table='';
            $this->primary='id';
            $this->select='*';
            $this->sql='';
            $this->limit='';
            $this->where='';
            $this->order='';
            $this->group='';
            $this->use_index='';
            $this->join='';
            $this->union='';
        }
        else
        $this->$what = '';
    }

    /**
     * 字段等于某个值，支持子查询，$where可以是对象
     * @param $field
     * @param $_where
     */
    function equal($field, $_where)
    {
        if ($_where instanceof DBAdapter)
        {
            $where = $field.'=('.$_where->getsql().')';
        }
        else
        {
            $where = "`$field`='$_where'";
        }
        $this->where($where);
    }

    /**
     * 指定表名，可以使用table1,table2
     * @param $table
     */
    function from($table)
    {

        if (strpos($table,"`") === false)
        {
            $this->table= "`".$table."`";
        }
        else{
            $this->table=$table;
        }
    }

    /**
     * 指定查询的字段，select * from table
     * 可多次使用，连接多个字段
     * @param $select
     * @param $force
     * @return null
     */
    function select($select, $force = false)
    {
        if (is_array($select))
        {
            $select = implode(',', $select);
        }
        if ($this->select == "*" or $force)
        {
            $this->select = $select;
        }
        else
        {
            $this->select = $this->select . ',' . $select;
        }
    }

    /**
     * where参数，查询的条件
     * @param $where
     * @return null
     */
    
    protected function filterWhere($k, $v, $pre = "")
    {
        if ($v === NULL) {
            return 1;
        } else if (is_array($v)) {
            $vs = "'" . implode("','", $v) . "'";
            return "$pre`$k` IN($vs)";
        } else if (preg_match("/^(\d+)~(\d+)$/", $v, $m)) {
            return "($pre`$k` BETWEEN $m[1] AND $m[2])";
        } else if (preg_match("/^(LIKE|~LIKE|NOTLIKE):(.*)$/i", $v, $m)) {
            if (strtoupper($m[1]) == "LIKE") {
                return $pre . $this->field("`$k`", $m[2], "LIKE");
            } else {
                return "$pre`$k` NOT LIKE $m[2]";
            }
        } else if (preg_match("/^(IN|~IN|NOTIN):(.*)$/i", $v, $m)) {

            if (strtoupper($m[1]) == "IN") {
                return $pre . $this->field($k, $m[2], "IN");
            } else {
                return $pre .$this->field("`$k`", $m[2], "NOTIN");
            }
        } else if (preg_match("/^([\=\>\<\|\^\&\+\-]{1,2}):(.+)/i", $v, $m)) {
            return $pre . $this->field("`$k`", $m[2], $m[1]);
        } else {
            return "$pre`$k`='$v'";
        }
    }

    protected function field($field, $val, $glue = "=")
    {
        $field = $this->_quote_field($field);

        $glue = strtoupper($glue);

        if (is_array($val)) {
            $glue = ($glue == "NOTIN" ? "NOTIN" : "IN");
        } else if ($glue == "IN") {
            $glue = "=";
        }

        switch ($glue) {
            case "=":
                return $field . $glue . $this->_quote($val);
                break;

            case "-":
            case "+":
                return $field . "=" . $field . $glue . $this->_quote($val);
                break;

            case "|":
            case "&":
            case "^":
                return $field . "=" . $field . $glue . $this->_quote($val);
                break;

            case ">":
            case "<":
            case "<>":
            case "<=":
            case ">=":
                return $field . $glue . $this->_quote($val);
                break;

            case "LIKE":
                return $field . " LIKE(" . $this->_quote($val) . ")";
                break;

            case "IN":
            case "NOTIN":
                $val = ($val ? implode(",", $val) : "''");
                return $field . ($glue == "NOTIN" ? " NOT" : "") . " IN(" . $val . ")";
                break;

            default:
                trigger_error("Not allow this glue between field and value: \"" . $glue . "\"");
        }
    }

    protected function _quote_field($field)
    {
        if (is_array($field)) {
            foreach ($field as $k => $v) {
                $field[$k] = $this->_quote($v);
            }
        } else {
            if (strpos($field, "`") !== false) {
                $field = str_replace("`", "", $field);
            }

            $field = "`" . $field . "`";
        }

        return $field;
    }

    protected function _quote($val)
    {
        if (is_string($val)) {
            return "'" . addcslashes($val, "\n\r\'\"\032") . "'";
        }

        if (is_int($val) || is_float($val)) {
            return "'" . $val . "'";
        }

        if (is_array($val)) {
            if ($noarray === false) {
                foreach ($val as & $v) {
                    $v = $this->_quote($v, true);
                }

                return $val;
            } else {
                return "''";
            }
        }

        if (is_bool($val)) {
            return $val ? "1" : "0";
        }

        return "''";
    }

    /**
     * where参数，查询的条件
     * @param $where
     * @return null
     */
    function where($where)
    {

        //$where = str_replace(' or ','',$where);
        if ($this->where == "")
        {
            $this->where = "where " . $where;
        }
        else
        {
            $this->where = $this->where . " and " . $where;
        }
    }

    /**
     * 指定查询所使用的索引字段
     * @param $field
     * @return null
     */
    function useIndex($field)
    {
        self::sql_safe($field);
        $this->use_index = "use index($field)";
    }

    /**
     * 相似查询like
     * @param $field
     * @param $like
     * @return null
     */
    function like($field,$like)
    {
        self::sql_safe($field);
        $this->where("`{$field}` like '{$like}'");
    }

    /**
     * 使用or连接的条件
     * @param $where
     * @return null
     */
    function orwhere($where)
    {
        if ($this->where == "")
        {
            $this->where = "where " . $where;
        }
        else
        {
            $this->where = $this->where . " or " . $where;
        }
    }

    /**
     * 查询的条数
     * @param $limit
     * @return null
     */
    function limit($limit)
    {
        if (!empty($limit))
        {
            $_limit = explode(',', $limit, 2);
            if (count($_limit) == 2)
            {
                $this->limit = 'limit ' . (int)$_limit[0] . ',' . (int)$_limit[1];
            }
            else
            {
                $this->limit = "limit " . (int)$limit;
            }
        }
        else
        {
            $this->limit = '';
        }
    }

    /**
     * 指定排序方式
     * @param $order
     * @return null
     */
    function order($order)
    {
        if (!empty($order))
        {
            self::sql_safe($order);
            $this->order = "order by $order";
        }
        else
        {
            $this->order = '';
        }
    }

    /**
     * 组合方式
     * @param $group
     * @return null
     */
    function group($group)
    {
        if (!empty($group))
        {
            self::sql_safe($group);
            $this->group = "group by $group";
        }
        else
        {
            $this->group = '';
        }
    }

    /**
     * group后条件
     * @param $having
     * @return null
     */
    function having($having)
    {
        if (!empty($having))
        {
            $this->having = "HAVING $having";
        }
        else
        {
            $this->having = '';
        }
    }

    /**
     * IN条件
     * @param $field
     * @param $ins
     * @return null
     */
    function in($field, $ins)
    {
        if (is_array($ins))
        {
            $ins = implode(',', $ins);
        }
        else
        {
            //去掉两边的分号
            $ins = trim($ins, ',');
        }
        $this->where("`$field` in ({$ins})");
    }

    /**
     * NOT IN条件
     * @param $field
     * @param $ins
     * @return null
     */
    function notin($field,$ins)
    {
        if (is_array($ins))
        {
            $ins = implode(',', $ins);
        }
        else
        {
            //去掉两边的分号
            $ins = trim($ins, ',');
        }
        $this->where("`$field` not in ({$ins})");
    }

    /**
     * INNER连接
     * @param $table_name
     * @param $on
     * @return null
     */
    function join($table_name,$on)
    {
        $this->join.="INNER JOIN `{$table_name}` ON ({$on})";
    }

    /**
     * 左连接
     * @param $table_name
     * @param $on
     * @return null
     */
    function leftjoin($table_name,$on)
    {
        $this->join.="LEFT JOIN `{$table_name}` ON ({$on})";
    }

    /**
     * 右连接
     * @param $table_name
     * @param $on
     * @return null
     */
    function rightjoin($table_name,$on)
    {
        $this->join.="RIGHT JOIN `{$table_name}` ON ({$on})";
    }

    /**
     * 分页参数,指定每页数量
     * @param $pagesize
     * @return null
     */
    function pagesize($pagesize)
    {
        $this->page_size = (int)$pagesize;
    }

    /**
     * 分页参数,指定当前页数
     * @param $page
     * @return null
     */
    function page($page)
    {
        $this->page = (int)$page;
    }

    /**
     * 主键查询条件
     * @param $id
     * @return null
     */
    function id($id)
    {
        $this->where("`{$this->primary}` = '$id'");
    }

    /**
     * 启用缓存
     * @param $params
     */
    function cache($params = true)
    {
        if ($params === false)
        {
            $this->enableCache = false;
        }
        else
        {
            $this->cacheOptions = $params;
            $this->enableCache = true;
        }
    }

    /**
     * 检查SQL参数是否安全（有特殊字符）
     * @param $sql_sub
     * @throws SQLException
     */
    static function sql_safe($sql_sub)
    {
        if (!preg_match(self::$allow_regx, $sql_sub))
        {
            if (self::$error_call === '')
            {
                throw new SQLException("sql block '{$sql_sub}' is unsafe!");
            }
            else
            {
                call_user_func(self::$error_call);
            }
        }
    }

    /**
     * 获取组合成的SQL语句字符串
     * @param $ifreturn
     * @return string | null
     */
    function getsql($ifreturn = true)
    {
        $this->sql = "select {$this->select} from {$this->table}";


//        var_dump($this->sql);

        $this->sql .= implode(' ',
            array(
                $this->join,
                $this->use_index,
                $this->where,
                $this->union,
                $this->group,
                $this->having,
                $this->order,
                $this->limit,
                $this->for_update,
            ));

        if ($this->if_union)
        {
            $this->sql = str_replace('{#union_select#}', $this->union_select, $this->sql);
        }



        if ($ifreturn)
        {
            return $this->sql;
        }
    }


    function raw_put($params)
    {
        foreach ($params as $array)
        {
            if (isset($array[0]) and isset($array[1]) and count($array) == 2)
            {
                $this->_call($array[0], $array[1]);
            }
            else
            {
                $this->raw_put($array);
            }
        }
    }

    /**
     * 锁定行或表
     * @return null
     */
    function lock()
    {
        $this->for_update = 'for update';
    }

    /**
     * 执行生成的SQL语句
     * @param $sql
     * @return null
     */
    function exeucte($sql = '')
    {
        if ($sql == '')
        {
            $this->getsql(false);
        }
        else
        {
            $this->sql = $sql;
        }

        $this->result = $this->query($this->sql);
        $this->is_execute++;
    }

    /**
     * SQL联合
     * @param $sql
     * @return null
     */
    function union($sql)
    {
        $this->if_union = true;
        if($sql instanceof SelectDB)
        {
            $this->union_select = $sql->select;
            $sql->select = '{#union_select#}';
            $this->union = 'UNION ('.$sql->getsql(true).')';
        }
        else $this->union = 'UNION ('.$sql.')';
    }

    /**
     * 将数组作为指令调用
     * @param $params
     * @return null
     */
    function put($params)
    {
        $this->where="";
        if(isset($params['put']))
        {
            \Xphp\Error::info('SelectDB Error!','Params put() cannot call put()!');
        }

        //处理where条件
        if(isset($params['where']))
        {
            $wheres = $params['where'];
            if(is_array($wheres)) foreach($wheres as $where) $wheres = $this->where($where);
            else $wheres = $this->where($wheres);
            unset($params['where']);
        }

        //处理orwhere条件
        if(isset($params['orwhere']))
        {
            $orwheres = $params['orwhere'];
            if(is_array($orwheres)) foreach($orwheres as $orwhere) $orwheres = $this->orwhere($orwhere);
            else $orwheres = $this->orwhere($orwheres);
            unset($params['orwhere']);
        }

        //处理walk调用
        if (isset($params['walk']))
        {
            foreach($params['walk'] as $call)
            {
                list($key, $value) = each($call);
                if (strpos($key, '_') !== 0)
                {
                    $this->_call($key, $value);
                }
                else
                {
                    $this->extraParmas[substr($key, 1)] = $value;
                }
            }
            unset($params['walk']);
        }
        //处理其他参数


        foreach ($params as $key => $value)
        {

            if(strstr($value,":")){

                $this->where($this->filterWhere($key,$value));
            }
            elseif (strpos($key, '_') !== 0)
            {

                $this->_call($key, $value);
            }
            else
            {

                $this->extraParmas[substr($key, 1)] = $value;
            }
        }

    }


    /**
     * @param $method
     * @param $param
     * @return bool
     */
    protected function _call($method, $param)
    {
        if ($method == 'update' or $method == 'delete' or $method == 'insert')
        {
            return false;
        }

        //调用对应的方法
        if (method_exists($this, $method))
        {
            if (is_array($param))
            {
                call_user_func_array(array($this, $method), $param);
            }
            else
            {
                $this->$method($param);
            }
        }
//        直接将Key作为条件
        else
        {

            $param = $this->quote($param);

            if ($this->call_by == 'func')
            {
                $this->where($method . '="' . $param . '"');
            }
            else
            {
                Error::info('Error: Db util 错误的参数', "<pre>参数$method=$param</pre>");
            }
        }
        return true;
    }
    /**
     * 获取记录
     * @param $field
     * @return array
     */
    function getone($field = '')
    {

        $this->limit('1');
        if ($this->auto_cache or !empty($cache_id))
        {
            $cache_key = empty($cache_id) ? self::CACHE_PREFIX . '_one_' . md5($this->sql) : self::CACHE_PREFIX . '_all_' . $cache_id;
            global $php;
            $record = $php->cache->get($cache_key);
            if (empty($data))
            {
//                if ($this->is_execute == 0)
//                {
                    $this->exeucte();
//                }
                $record = $this->result->fetch();
                $php->cache->set($cache_key, $record, $this->cache_life);
            }
        }
        else
        {

//            var_dump($this->is_execute);
//            if ($this->is_execute == 0)
//            {
                $this->exeucte();
//            }
            $record = $this->result->fetch();

        }
        if ($field === '')
        {
            return $record;
        }

        return $record[$field];
    }

    protected function _execute()
    {
//        if ($this->is_execute == 0)
//        {
            $this->exeucte();
//        }
        if ($this->result)
        {
//            return $this->result->fetchall(); 之前的版本
            $data = array();
            $this->row_data_index=0;
            while ($row = $this->result->fetch())
            {
                $key =$this->__format_row_index($row);
                $data[$key] = $this->__format_row_data($row);
            }
            return $data;
        }
        else
        {
            return false;
        }
    }

    public function __format_row_data($row){
        return $row;
    }
    public function __format_row_index($row){
        return $this->row_data_index++;
    }

    public function get($params){
        $this->put($params);
    }
    /**
     * 获取所有记录
     * @return array | bool
     */
    function getall()
    {
        //启用了Cache
        return $this->_execute();

    }
    /**
     * 返回符合条件的记录数 ,或者是当前条件下的记录数
     * @param array $params
     * @return int
     */
    public function count($params=array())
    {
        if(count($params)>0) $this->put($params);

        $sql = "select count({$this->count_fields}) as c from {$this->table} {$this->join} {$this->where} {$this->union} {$this->group}";

        if ($this->if_union)
        {
            $sql = str_replace('{#union_select#}', "count({$this->count_fields}) as c", $sql);
            $c = $this->query($sql)->fetchall();
            $cc = 0;
            foreach ($c as $_c)
            {
                $cc += $_c['c'];
            }
            $count =  intval($cc);
        }
        else
        {
            $_c = $this->query($sql);
            if ($_c === false)
            {
                return false;
            }
            else
            {
                $c = $_c->fetch();
            }
            $count = intval($c['c']);
        }
        return $count;
    }

    /**
     * 执行插入操作
     * @param $data
     * @return bool
     */
    function insert($data)
    {
        $field = "";
        $values = "";

        foreach($data as $key => $value)
        {
            $value = $this->quote($value);
            $field = $field . "`$key`,";
            $values = $values . "'$value',";
        }

        $field = substr($field, 0, -1);
        $values = substr($values, 0, -1);
        return $this->query("insert into {$this->table} ($field) values($values)");
    }

    /**
     * 获取最新插入数据 id值
     * @return mixed
     */
    function lastInsertId(){
        return $this->lastInsertId();
    }
    /**
     * 对符合当前条件的记录执行update
     * @param $data
     * @return bool
     */
    function update($data)
    {
        $update = "";
        foreach ($data as $key => $value)
        {
            $value = $this->quote($value);
            if ($value != '' and $value{0} == '`')
            {
                $update = $update . "`$key`=$value,";
            }
            else
            {
                $update = $update . "`$key`='$value',";
            }
        }
        $update = substr($update, 0, -1);
        return $this->query("update {$this->table} set $update {$this->where} {$this->limit}");
    }

    /**
     * 删除当前条件下的记录
     * @return bool
     */
    function delete()
    {
        return $this->query("delete from {$this->table} {$this->where} {$this->limit}");
    }

    /**
     * 获取受影响的行数
     * @return int
     */
    function rowCount()
    {
        return $this->getAffectedRows();
    }

    /**
     * 初始化参数
     */
    function __init()
    {
        $this->check_status();
        $this->read_times = 0;
        $this->write_times = 0;
    }

    /**
     * 检查连接状态，如果连接断开，则重新连接
     */
    function check_status()
    {

        if (!$this->_db->ping())
        {
//            $this->_db->close();
            $this->_db->connect();
        }
    }

    /**
     * 启动事务处理
     * @return bool
     */
    function start()
    {
        return $this->query('START TRANSACTION');
    }

    /**
     * 提交事务处理
     * @return bool
     */
    function commit()
    {
        return $this->query('COMMIT');
    }

    /**
     * 事务回滚
     * @return bool
     */
    function rollback()
    {
        $this->query('ROLLBACK');
    }

    /**
     * 执行一条SQL语句
     * @param $sql
     * @return \Xphp\Data\Source\MySQLiRecord
     */
    public function query($sql)
    {
        if ($this->debug)
        {
            echo "$sql<br />\n<hr />";
        }

        $this->read_times += 1;
        return $this->_db->query($sql);
    }
    /**
     * 调用$driver的自带方法
     * @param $method
     * @param array $args
     * @return mixed
     */
    public function __call($method, $args = array())
    {
        return call_user_func_array(array($this->_db, $method), $args);
    }
}