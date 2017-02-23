<?php
/**
 * Model类，提供对某个数据库表的接口
 * @author 肖喜进
 */
class DBService
{

    use DBAdapter;
    public $if_cache = false;

    /**
     * 通用数据接口批量获取一组列表数组
     * @author xijin.xiao
     * @desc 通用数据接口批量获取一组列表数组，筛选条件为：$params
     * @param array $params mixed   无   数据类型服务的筛选参数
     * @param string $ret_flag optional 'array' 返回值类型标志，array为[0-~],kv为[k=>v]
     * @return array    $data 返回列表数组
     * @return bool    false  sql参数错误,查不到数据
     */
    public function list($params,$ret_flag='array'){
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
        if($ret_flag=='array'){
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
     * 通用数据接口获取单条记录
     * @author xijin.xiao
     * @desc 通用数据接口获取单条记录，筛选条件为：主键值
     * @param string|int $object_id mixed   无   主键值
     * @return array    $data 返回记录数组
     * @return bool    false  sql参数错误,查不到数据
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
     * 通用数据接口，增加一条新纪录
     * @author xijin.xiao
     * @desc 增加一条新纪录
     * @param array $data mixed   无   要插入的记录数组
     * @return int  lastInsertId  插入新纪录的主键id
     * @return bool false sql参数错误,查不到数据
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
     * 通用数据接口，更新数据记录
     * @author xijin.xiao
     * @desc 通用数据接口,更新一条或多条数据记录
     * @param array|primary_key $object_id mixed   无  更新记录时的匹配条件
     * @param array $data mixed   无   要更新的数据记录
     * @return bool true 更新成功
     * @return bool false 不能修改，参数错误
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