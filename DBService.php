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
    /**
     * 通用数据接口批量获取一组列表数组
     * @author xijin.xiao<xiaoxijin@jcy.cc>
     * @desc 通用数据接口批量获取一组列表数组，筛选条件为：$params
     * @param array $params
     * @version 1.0
     * @return array $data 正常情况返回列表数组
     * @return bool false sql参数错误,或是查不到数据
     */
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
            return pushFailedMsg('sql参数错误');
        }

        $record_set =  $this->select($select_params);
        if(!$record_set){
            return pushFailedMsg('sql:'.$this->sql.' 查不到数据');
        }
        if($this->return_ret_flag=='array'){
            while ($row = $record_set->fetch())
                $data[] = $this->formatRowData($row);
            return $data;
        }else{
            while ($row = $record_set->fetch())
            {
                $key =$this->formatRowIndex($row);
                $data[$key] = $this->formatRowData($row);
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
            return pushFailedMsg('sql参数错误');
        }

        $record_set =  $this->select($select_params);
        if(!$record_set || $record_set->result->num_rows>1){
            return pushFailedMsg('sql参数有误，查不到数据，或者数据记录多余一条');
        }
        return $this->formatRowData($record_set->fetch());
    }

    /**
     * 插入一条新的记录到表
     * @return int
     */
    public function add($data){

        if (!$data)
            return pushFailedMsg('不能添加，参数错误');

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
        if (!$object_id)
            return pushFailedMsg('不能修改，参数错误');
        if(is_array($object_id)){
            $params = $object_id;
        }else{
            $params = array($this->_primary=>$object_id);
        }
        if($this->update($params,$data) && $this->getAffectedRows())
            return true;
        else
            return pushFailedMsg('更新记录失败');
    }

    /**
     * 删除数据主键为$id的记录，
     * @return true/false
     */
    public function del($object_id)
    {
        if (!$object_id)
            return pushFailedMsg('不能删除，参数错误');

        if(is_array($object_id)){
            $params = $object_id;
        }else{
            $params = array($this->_primary=>$object_id);
        }
        if($this->delete($params) && $this->getAffectedRows())
            return true;
        else
            return pushFailedMsg('删除记录失败');
    }

    protected function formatRowData($row){
        return $row;
    }
    protected function formatRowIndex($row){
        return $row[$this->_primary];
    }


}