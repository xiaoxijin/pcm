<?php
/**
 * Model类，提供对某个数据库表的接口
 * @author 肖喜进
 */
namespace DB;
class Service extends Adapter
{
    public $debug = false;

    //初始默认数据
    protected $_db;
    public $_table = '';
    public $_field = '';
    public $_chk_field = [];
    public $_primary = '';

    //锁表
    public $for_update = '';
    //Count计算
    protected $count_fields = '*';
    public $if_cache = false;
    protected $select_failed_msg = '参数错误，查不到数据';
    protected $check_field_failed_msg = '参数错误';
    protected $get_failed_msg = '查不到数据，或者数据记录多于一条';
    protected $add_failed_msg = '不能添加，参数错误';
    protected $set_failed_msg = '更新记录失败,或者没有相关的记录被更新';
    protected $del_failed_msg = '删除记录失败,或者没有相关的记录被删除';
//    protected $list_ret_null = [];


    /**
     * 通用数据接口，返回符合条件的记录数
     * @desc 通用数据接口，返回符合条件的记录数
     * @author xijin.xiao
     * @param array $params optional 无 筛选记录的条件
     * @return int
     * @return bool false 没有符合条件的记录
     */
    public function count($params=[])
    {
        if(count($params)>0)
            $this->putSelectParams($params);

        $count_select_string = "count({$this->count_fields}) as __c ";
        $select_string = $this->getSelectString();
        if($select_string!=='')
            $select_string =$select_string.", ".$count_select_string;
        else
            $select_string ="SELECT ".$count_select_string;
        $count_sql = $select_string.$this->getSelectFilterString(false);
        $_c = $this->query($count_sql);
        if ($_c === false)
            return false;
        else
            $c = $_c->fetch();
        $count = intval($c['__c']);
        return $count;
    }


    /**
     * 通用数据接口批量获取一组列表数组
     * @author xijin.xiao
     * @desc 通用数据接口批量获取一组列表数组，筛选条件为：$params
     * @param array $params mixed   无   数据类型服务的筛选参数
     * @param string $ret_flag optional 'array' 返回值类型标志，array为[0-~],kv为[k=>v]
     * @return array    $data 返回列表数组
     * @return bool    false  sql参数错误,查不到数据
     */
    public function list($params=[],& $count=0){

        //返回值的数据类型
        $ret_struct = $this->getRetStruct($params);
        //返回值的键
        $ret_key=$this->getRetKey($params);

        return $this->setRet($ret_key,$this->getListRet($params,$count,$ret_struct,[$this,'formatRowIndex'],[$this,'formatRowData']));
    }


    public function items($params,& $count=0){

        //返回值的数据类型
        $ret_struct = $this->getRetStruct($params);
        //返回值的键
        $ret_key=$this->getRetKey($params);

        return $this->setRet($ret_key,$this->getListRet($params,$count,$ret_struct));
    }

    protected function getListRet($params,& $count=0,$ret_struct,callable $format_index=null,callable $format_data=null){

        if(!is_array($params) && $params!=''){
            return pushFailedMsg($this->check_field_failed_msg);
        }
        $data=[];

        $record_set = $this->getRet($params,$count);
        if($ret_struct=='array'){
            if(is_callable($format_data) && method_exists($record_set,'fetch')){
                while ($row = $record_set->fetch())
                    $data[] = $this->formatRowData($row);
            }elseif(method_exists($record_set,'fetch'))
                while ($row = $record_set->fetch())
                    $data[] = $row;

        }else{
            if(is_callable($format_index) && is_callable($format_data) && method_exists($record_set,'fetch')){
                while ($row = $record_set->fetch())
                {
                    $key =$this->formatRowIndex($row);
                    $data[$key] = $this->formatRowData($row);
                }
            }elseif(method_exists($record_set,'fetch'))
                while ($row = $record_set->fetch())
                {
                    $data[$this->_primary] = $row;
                }
        }
        return $data;
    }

    protected function getRetKey(& $params){
        $ret_key='';
        if(isset($params['ret_key'])){
            $ret_key = $params['ret_key'];
            unset($params['ret_key']);
        }
        return $ret_key;
    }

    protected function getRetStruct(& $params){
        //返回值的数据类型
        $ret_struct = 'array';
        if(isset($params['ret_struct'])){
            $ret_struct = $params['ret_struct'];
            unset($params['ret_struct']);
        }
        return $ret_struct;
    }

    protected function setRet($ret_key,$data){
        if($data===false)
            return false;
        if($ret_key=='')
            return $data;
        else{
            return [$ret_key=>$data];
        }
    }

    protected function getRet($select_params,& $count=0){

        $this->putSelectParams($select_params);
        $count = $this->count();
        if($count>0){
            return $this->query($this->getSelectStatement(false));
        }
        else
            return [];
    }

    protected function parseGetParams($object_id){
        if(is_array($object_id)){
            $select_params = $object_id;
        }elseif($object_id = trim($object_id," \t\n\r\0\x0B\\/")){
            $select_params = array($this->_primary=>$object_id);
        }else{
            return pushFailedMsg($this->check_field_failed_msg);
        }
        return $select_params;
    }

    public function detail($params){

        $ret_key=$this->getRetKey($params);
        $select_params = $this->parseGetParams($params);
        if (!$select_params)
            return false;
        if($record_set=$this->getRet($select_params)){
            return $this->setRet($ret_key,$record_set->fetch());
        }else{
            return pushFailedMsg($this->get_failed_msg);
        }
    }
    /**
     * 通用数据接口获取单条记录
     * @author xijin.xiao
     * @desc 通用数据接口获取单条记录，筛选条件为：主键值
     * @param string|int $object_id mixed   无   主键值
     * @return array    $data 返回记录数组
     * @return bool    false  sql参数错误,查不到数据
     */
    public function get($params)
    {
        $ret_key=$this->getRetKey($params);
        $select_params = $this->parseGetParams($params);
        if (!$select_params)
            return false;

        if($record_set=$this->getRet($select_params)){
            return $this->setRet($ret_key,$this->formatRowData($record_set->fetch()));
        }else{
            return pushFailedMsg($this->get_failed_msg);
        }
    }


    /**
     * 将数组作为指令调用
     * @param $params
     * @return null
     */
    protected function putSelectParams($params)
    {
        $this->initSqlParams();
        $params['from']=$params['from']??[$this->_table,$this->_table_alias];
        $params['select']=$params['select']??[$this->_field];
        $this->putSqlParams($params);
    }

    /**
     * 将数组作为指令调用
     * @param $params
     * @return null
     */
    protected function putDeleteParams($params)
    {
        $this->initSqlParams();
        $this->delete();
        $params['from']=$params['from']??$this->_table;
        $this->putSqlParams($params);
    }


    /**
     * 将数组作为指令调用
     * @param $params
     * @return null
     */
    protected function putUpdateParams($where,$data)
    {
        $this->initSqlParams();
        $this->putSqlParams($data,'set');
        $where['update']=$where['update']??$this->_table;
        $this->putSqlParams($where);
    }


    /**
     * 将数组作为指令调用
     * @param $params
     * @return null
     */

    protected function putInsertParams($params){
        $this->initSqlParams();
        $params['insert']=$params['insert']??$this->_table;
        $this->putSqlParams($params,'set');
    }
    /**
     * 将数组作为指令调用
     * @param $params
     * @return null
     */
    protected function putSqlParams($params,$default_func='where')
    {
        if(is_array($params))
        {
            foreach ($params as $key => $value)
            {
                $act_params=[];
                if (method_exists($this, $key))
                {
//                    //调用对应的方法
                    if(!is_array($value))
                        $act_params[0]= $value;
                    else
                        $act_params = $value;
                    call_user_func_array([$this,$key],$act_params);
                }else{

                    $act_params[]=$key;
                    if(is_array($value)){
                        $act_params = array_merge_recursive($act_params,$value);
                    }else{
                        $act_params[]=$value;
                    }

                    call_user_func_array([$this,$default_func],$act_params);
                }
            }
        }
    }

    /**
     * 通用数据接口，增加一条新纪录
     * @author xijin.xiao
     * @desc 增加一条新纪录
     * @param array $data mixed   无   要插入的记录数组
     * @return int  lastInsertId  插入新纪录的主键id
     * @return bool false sql参数错误,查不到数据
     */
    public function add($params){

        if (!$params)
            return pushFailedMsg($this->add_failed_msg);

        $this->putInsertParams($params);
        if ($this->query($this->getInsertStatement(false))) {
            $lastInsertId = $this->lastInsertId();
            if ($lastInsertId == 0)
            {
                return true;
            }
            else
            {
                return $lastInsertId;
            }
        }else {
            return false;
        }

    }

    /**
     * 通用数据接口，更新数据记录
     * @author xijin.xiao
     * @desc 通用数据接口,更新一条或多条数据记录
     * @param array|primary_key $object_id mixed   无  更新记录时的匹配条件
     * @param array $data mixed   无   要更新的数据记录
     * @return bool true 更新成功
     * @return bool false 不能修改，参数错误
     */
    public function sets($where,$data)
    {

        if(!$where || !$data)
            return pushFailedMsg($this->check_field_failed_msg);

        $this->putUpdateParams($where,$data);
        if ($this->query($this->getUpdateStatement(false)) && $this->getAffectedRows()) {
           return true;
        }else {
           return pushFailedMsg($this->set_failed_msg);
        }
    }

    /**
     * 通用数据接口，删除数据记录
     * @author xijin.xiao
     * @desc 通用数据接口，删除一条或多条数据纪录
     * @param array|primary_key $object_id mixed 无 删除记录时的匹配条件
     * @return bool true 删除成功
     * @return bool false 不能删除，参数错误
     */
    public function del($params)
    {

        if (!$params)
            return pushFailedMsg($this->check_field_failed_msg);

        if(!is_array($params)){
            $params = array($this->_primary=>$params);
        }

        $this->putDeleteParams($params);
        if ($this->query($this->getDeleteStatement(false)) && $this->getAffectedRows()) {
            return true;
        }else {
            return pushFailedMsg($this->del_failed_msg);
        }
    }

    protected function formatRowData($row){
        return $row;
    }
    protected function formatRowIndex($row){
        return $row[$this->_primary];
    }

    protected function filterRowData($filter_field,& $row){
        foreach ($filter_field as $field){
            if(isset($row[$field]))
                unset($row[$field]);
        }
    }


    /**
     * 初始化，select的值
     * @param $what
     */
    function __init($quote_func='quoteField')
    {

        if($this->_field!='')
            return true;

        $this->_db = Connector::get('master');
        $fields_ret = $this->query('describe '.$this->_table);
        if (!$fields_ret)
            return false;

        while ($field_info=$fields_ret->fetch())
        {
            array_push($this->_chk_field,$this->getColumnPrefix().call_user_func([$this,$quote_func],$field_info['Field']));
            if($field_info['Key']=='PRI')
                $this->_primary=$field_info['Field'];
        }
        $this->_field=implode(',',$this->_chk_field);
        return true;
    }

    protected function quoteField($quoteField){
        return $quoteField;
    }

    protected function convertSafeField($field){
        return trim(preg_replace('/([a-zA-Z0-9_]+),?/','`$1`,',$field),',');
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
     * @return \Data\Source\MySQLiRecord
     */
    protected function query($sql)
    {
        return $this->_db->query($sql);
    }


    /**
     * 锁定行或表
     * @return null
     */
    protected function lock()
    {
        $this->for_update = 'for update';
    }

//    protected function getTableAlias($alias=null){
    protected function getColumnPrefix($alias=null){
        if ($alias)
            return $alias.'.';
        elseif ($this->_table_alias)
            return $this->_table_alias.'.';
        else
            return '';
    }

    /**
     * 调用$driver的自带方法
     * @param $method
     * @param array $args
     * @return mixed
     */
    function __call($method, $args = array())
    {
        if(method_exists($this->_db, $method) && is_callable([$this->_db,$method]))
            return call_user_func_array(array($this->_db, $method), $args);
        else
            return false;
    }
}