<?php
/**
 * Model类，提供对某个数据库表的接口
 * @author 肖喜进
 */
class DBService
{

    use DBAdapter;
    public $if_cache = false;
    //get 方法的返回值类型
    public $return_ret_flag = 'array';

    public function list($params){
        if(is_array($params)){
            $select_params = $params;
            if (!isset($select_params['order']))
                $select_params['order'] = "{$this->_table}.{$this->_primary} desc";
            if (!isset($select_params['where']))
                $select_params['where']=1;
        }elseif($params===''){
            $select_params['where']=1;
        }else{
            pushFailedMsg('sql参数错误');
            return false;
        }

        $record_set =  $this->select($select_params);
        if(!$record_set){
            pushFailedMsg('sql:'.$this->sql.' 查不到数据');
            return false;
        }
        if($this->return_ret_flag=='array'){
            while ($row = $record_set->fetch())
                $data[] = $this->__format_row_data($row);
            return $data;
        }else{
            while ($row = $record_set->fetch())
            {
                $key =$this->__format_row_index($row);
                $data[$key] = $this->__format_row_data($row);
            }
            return $data;
        }
    }
    /**
     * 获取主键$primary_key为$object_id的数据
     * 或者获取表的一段数据，查询的参数由$params指定
     * 如果参数为空的话，则返回一条空白的Record，可以赋值，产生一条新的记录
     * @param $object_id or $params
     * @return array
     */
    public function get($object_id)
    {
        if(is_array($object_id)){
            $select_params = $object_id;
        }elseif($object_id = trim($object_id," \t\n\r\0\x0B\\/")){
            $select_params = array($this->_primary=>$object_id);
        }else{
            pushFailedMsg('sql参数错误');
            return false;
        }

        $record_set =  $this->select($select_params);
        if(!$record_set || $record_set->result->num_rows>1){
            pushFailedMsg('sql参数有误，查不到数据，或者数据记录多余一条');
            return false;
        }
        return $this->__format_row_data($record_set->fetch());
    }

    /**
     * 插入一条新的记录到表
     * @return int
     */
    public function add($data){

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
     * 更新数据记录,值为$object_id关联数组
     * @param $object_id
     * @param $data
     * @return bool
     */
    public function set($object_id, $data)
    {
        if(is_array($object_id)){
            $params = $object_id;
        }else{
            $params = array($this->_primary=>$object_id);
        }
        if($this->update($params,$data) && $this->getAffectedRows())
            return true;
        else
            return false;
    }

    /**
     * 删除数据主键为$id的记录，
     * @return true/false
     */
    public function del($object_id)
    {
        if (!$object_id)
           return false;

        if(is_array($object_id)){
            $params = $object_id;
        }else{
            $params = array($this->_primary=>$object_id);
        }
        if($this->delete($params) && $this->getAffectedRows())
            return true;
        else
            return false;
    }

    protected function __format_row_data($row){
        return $row;
    }
    protected function __format_row_index($row){
        return $row[$this->_primary];
    }


}