<?php
/**
 * Model类，提供对某个数据库表的接口
 * @author 肖喜进
 */
namespace DB;
class Service extends \Common
{

    use Adapter;
    public $if_cache = false;
    protected $select_failed_msg = 'sql参数有误，查不到数据';
    protected $check_field_failed_msg = 'sql参数错误';
    protected $get_failed_msg = 'sql参数有误，查不到数据，或者数据记录多于一条';
    protected $add_failed_msg = '不能添加，参数错误';
    protected $set_failed_msg = '更新记录失败,或者没有相关的记录被更新';
    protected $del_failed_msg = '删除记录失败,或者没有相关的记录被删除';

    /**
     * 通用数据接口批量获取一组列表数组
     * @author xijin.xiao
     * @desc 通用数据接口批量获取一组列表数组，筛选条件为：$params
     * @param array $params mixed   无   数据类型服务的筛选参数
     * @param string $ret_flag optional 'array' 返回值类型标志，array为[0-~],kv为[k=>v]
     * @return array    $data 返回列表数组
     * @return bool    false  sql参数错误,查不到数据
     */
    public function list($params,& $count=0){

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
        if(is_array($params)){
            $select_params = $params;
            if (!isset($select_params['order']))
                $select_params['order'] = "{$this->_table}.{$this->_primary} desc";
            if (!isset($select_params['where']))
                $select_params['where']=1;
        }elseif($params===''){
            $select_params['where']=1;
        }else{
            return pushFailedMsg($this->check_field_failed_msg);
        }
        $record_set = $this->select($select_params,$count);
        if(!$record_set){
            return pushFailedMsg($this->select_failed_msg);
        }
        $data=[];
        if($ret_struct=='array'){
            if(is_callable($format_data)){
                while ($row = $record_set->fetch())
                    $data[] = $this->formatRowData($row);
            }else
                while ($row = $record_set->fetch())
                    $data[] = $row;

        }else{
            if(is_callable($format_index) && is_callable($format_data)){
                while ($row = $record_set->fetch())
                {
                    $key =$this->formatRowIndex($row);
                    $data[$key] = $this->formatRowData($row);
                }
            }else
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
        if(!$data)
            return false;
        if($ret_key=='')
            return $data;
        else{
            return [$ret_key=>$data];
        }
    }

    protected function getRet($object_id){
        if(is_array($object_id)){
            $select_params = $object_id;
        }elseif($object_id = trim($object_id," \t\n\r\0\x0B\\/")){
            $select_params = array($this->_primary=>$object_id);
        }else{
            return pushFailedMsg($this->check_field_failed_msg);
        }
        return $this->select($select_params);
    }

    public function detail($object_id){
        $ret_key=$this->getRetKey($object_id);
        if($record_set=$this->getRet($object_id)){
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
    public function get($object_id)
    {

        $ret_key=$this->getRetKey($object_id);

        if($record_set=$this->getRet($object_id)){
            return $this->setRet($ret_key,$this->formatRowData($record_set->fetch()));
        }else{
            return pushFailedMsg($this->get_failed_msg);
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
    public function add($data){

        if (!$data)
            return pushFailedMsg($this->add_failed_msg);

        if ($this->insert($data)) {
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
    public function set($params)
    {
        if(!isset($params['where']) || !isset($params['data']))
            return pushFailedMsg($this->check_field_failed_msg);

        if(!is_array($params['where'])){
            $params['where'] = array($this->_primary=>$params['where']);
        }

        if($this->update($params['where'],$params['data']) && $this->getAffectedRows())
            return true;
        else
            return pushFailedMsg($this->set_failed_msg);
    }

    /**
     * 通用数据接口，删除数据记录
     * @author xijin.xiao
     * @desc 通用数据接口，删除一条或多条数据纪录
     * @param array|primary_key $object_id mixed 无 删除记录时的匹配条件
     * @return bool true 删除成功
     * @return bool false 不能删除，参数错误
     */
    public function del($object_id)
    {
        if (!$object_id)
            return pushFailedMsg($this->check_field_failed_msg);

        if(is_array($object_id)){
            $params = $object_id;
        }else{
            $params = array($this->_primary=>$object_id);
        }
        if($this->delete($params) && $this->getAffectedRows())
            return true;
        else
            return pushFailedMsg($this->del_failed_msg);
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
}