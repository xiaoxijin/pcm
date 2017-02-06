<?php
namespace Xphp\Data;

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
    public $_table = '';
    public $_field = '';
    public $_primary = '';
    //用于数据交互
    public $primary='';
    public $sql='';
    public $table = '';
    public $field = '*';
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

    public function __construct()
    {
        $this->_db = \Xphp\Data::getInstance()->data("db");
    }


    /**
     * 字段等于某个值，支持子查询，$where可以是对象
     * @param $field
     * @param $_where
     */
    protected function equal($field, $_where)
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
    protected function from($table)
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
    function field($field, $force = false)
    {
        if (is_array($field))
        {
            $field = implode(',', $field);
        }
        if ($this->field == "*" or $force)
        {
            $this->field = $field;
        }
        else
        {
            $this->field = $this->field . ',' . $field;
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
                return $pre . $this->_field("`$k`", $m[2], "LIKE");
            } else {
                return "$pre`$k` NOT LIKE $m[2]";
            }
        } else if (preg_match("/^(IN|~IN|NOTIN):(.*)$/i", $v, $m)) {

            if (strtoupper($m[1]) == "IN") {
                return $pre . $this->_field($k, $m[2], "IN");
            } else {
                return $pre .$this->_field("`$k`", $m[2], "NOTIN");
            }
        } else if (preg_match("/^([\=\>\<\|\^\&\+\-]{1,2}):(.+)/i", $v, $m)) {
            return $pre . $this->_field("`$k`", $m[2], $m[1]);
        } else {
            return "$pre`$k`='$v'";
        }
    }

    /**
     * where参数，查询的条件
     * @param $where
     * @return null
     */
    protected function _field($field, $val, $glue = "=")
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
    function where($where,$if_return=false)
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

        if($if_return)
            return $this->where;
    }

    /**
     * 指定查询所使用的索引字段
     * @param $field
     * @return null
     */
    protected function useIndex($field)
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
    protected function like($field,$like)
    {
        self::sql_safe($field);
        $this->where("`{$field}` like '{$like}'");
    }

    /**
     * 使用or连接的条件
     * @param $where
     * @return null
     */
    protected function orwhere($where)
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
    protected function limit($limit)
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
    protected function order($order)
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
    protected function group($group)
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
    protected function having($having)
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
    protected function in($field, $ins)
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
    protected function notin($field,$ins)
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
    protected function join($table_name,$on)
    {
        $this->join.="INNER JOIN `{$table_name}` ON ({$on})";
    }

    /**
     * 左连接
     * @param $table_name
     * @param $on
     * @return null
     */
    protected function leftjoin($table_name,$on)
    {
        $this->join.="LEFT JOIN `{$table_name}` ON ({$on})";
    }

    /**
     * 右连接
     * @param $table_name
     * @param $on
     * @return null
     */
    protected function rightjoin($table_name,$on)
    {
        $this->join.="RIGHT JOIN `{$table_name}` ON ({$on})";
    }

    /**
     * 分页参数,指定每页数量
     * @param $pagesize
     * @return null
     */
    protected function pagesize($pagesize)
    {
        $this->page_size = (int)$pagesize;
    }

    /**
     * 分页参数,指定当前页数
     * @param $page
     * @return null
     */
    protected function page($page)
    {
        $this->page = (int)$page;
    }

    /**
     * 主键查询条件
     * @param $id
     * @return null
     */
    protected function primary($primary)
    {
        $this->where("`{$this->primary}` = '$primary'");
    }

    /**
     * 启用缓存
     * @param $params
     */
    protected function cache($params = true)
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
                throw new \Exception("sql block '{$sql_sub}' is unsafe!");
            }
            else
            {
                call_user_func(self::$error_call);
            }
        }
    }



    /**
     * 锁定行或表
     * @return null
     */
    protected function lock()
    {
        $this->for_update = 'for update';
    }


    /**
     * SQL联合
     * @param $sql
     * @return null
     */
    protected function union($sql)
    {
        $this->if_union = true;
        if($sql instanceof DBAdapter)
        {
            $this->union_select = $sql->select;
            $sql->select = '{#union_select#}';
            $this->union = 'UNION ('.$sql->getsql(true).')';
        }
        else $this->union = 'UNION ('.$sql.')';
    }

    /**
     * 获取记录
     * @param $field
     * @return array
     */
//    protected function getone($field = '')
//    {
//
//        $this->limit('1');
////        if ($this->auto_cache or !empty($cache_id))
////        {
////            $cache_key = empty($cache_id) ? self::CACHE_PREFIX . '_one_' . md5($this->sql) : self::CACHE_PREFIX . '_all_' . $cache_id;
////            global $php;
////            $record = $php->cache->get($cache_key);
////            if (empty($data))
////            {
//////                if ($this->is_execute == 0)
//////                {
////                    $this->exeucte();
//////                }
////                $record = $this->result->fetch();
////                $php->cache->set($cache_key, $record, $this->cache_life);
////            }
////        }
////        else
////        {
//
////            var_dump($this->is_execute);
////            if ($this->is_execute == 0)
////            {
//            $this->exeucte();
////            }
//            $record = $this->result->fetch();
//
////        }
//        if ($field === '')
//        {
//            return $record;
//        }
//
//        return $record[$field];
//    }
    /**
     * 获取所有记录
     * @return array | bool
     */
//    protected function getall()
//    {
//        //启用了Cache
//        return $this->_execute();
//
//    }

//    protected function _execute()
//    {
////        if ($this->is_execute == 0)
////        {
//            $this->exeucte();
////        }
//        if ($this->result)
//        {
////            return $this->result->fetchall(); 之前的版本
//            $data = array();
//            $this->row_data_index=0;
//            while ($row = $this->result->fetch())
//            {
//                $key =$this->__format_row_index($row);
//                $data[$key] = $this->__format_row_data($row);
//            }
//            return $data;
//        }
//        else
//        {
//            return false;
//        }
//    }



    /**
     * 返回符合条件的记录数 ,或者是当前条件下的记录数
     * @param array $params
     * @return int
     */
    protected function count($params=array())
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
     * 初始化，select的值，参数$where可以指定初始化哪一项
     * @param $what
     */
    function initPutParams()
    {
        $this->table=$this->_table;
        $this->primary=$this->_table;
        $this->field=$this->_field;
        $this->sql='';
        $this->limit='';
        $this->where='';
        $this->order='';
        $this->group='';
        $this->use_index='';
        $this->join='';
        $this->union='';
        $this->union_select='';
    }
    /**
     * 将数组作为指令调用
     * @param $params
     * @return null
     */
    function put($params)
    {
        $this->initPutParams();
        foreach ($params as $key => $value)
        {

            if(strstr($value,":")){

                $this->where($this->filterWhere($key,$value));
            }
            elseif (method_exists($this, $key) && $key != 'update' && $key != 'delete' && $key != 'insert')
            {
                //调用对应的方法
                $this->$key($value);
            }
            else
            {
                $this->where($key . '="' . $this->quote($value) . '"');
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
        $this->sql = "select {$this->field} from {$this->table}";
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

    /**
     * 将数组作为指令调用
     * @param $params
     * @return null
     */
    protected function select($params)
    {

        $this->put($params);
        return $this->query($this->getSql($params));
    }

    /**
     * 执行插入操作
     * @param $data
     * @return bool
     */
    protected function insert($data)
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
        return $this->query("insert into {$this->_table} ($field) values($values)");
    }

    /**
     * 对符合当前条件的记录执行update
     * @param $data
     * @return bool
     */
    protected function update($data)
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

    public function delete($params)
    {
        $this->put($params);
        $this->write_times += 1;
        return $this->query("delete from {$this->_table} {$this->where}");
    }


    /**
     * 获取受影响的行数
     * @return int
     */
    protected function rowCount()
    {
        return $this->getAffectedRows();
    }

    /**
     * 初始化，select的值
     * @param $what
     */
    function __init()
    {

        if($this->_field!='')
            return true;
        $fields_ret = $this->query('describe '.$this->_table);
        $fields=[];
        while ($field_info=$fields_ret->fetch())
        {
            array_push($fields,$field_info['Field']);
            if($field_info['Key']=='PRI')
                $this->_primary=$field_info['Field'];
        }
        $this->_field=implode(',',$fields);
    }

    /**
     * 检查连接状态，如果连接断开，则重新连接
     */
    protected function check_status()
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
    protected function start()
    {
        return $this->query('START TRANSACTION');
    }

    /**
     * 提交事务处理
     * @return bool
     */
    protected function commit()
    {
        return $this->query('COMMIT');
    }

    /**
     * 事务回滚
     * @return bool
     */
    protected function rollback()
    {
        $this->query('ROLLBACK');
    }

    /**
     * 执行一条SQL语句
     * @param $sql
     * @return \Xphp\Data\Source\MySQLiRecord
     */
    protected function query($sql)
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
    function __call($method, $args = array())
    {
        if(is_callable([$this->_db,$method]))
            return call_user_func_array(array($this->_db, $method), $args);
        else
            return false;
    }
}