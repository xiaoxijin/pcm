<?php
namespace Xphp;
/**
 * Model类，提供对某个数据库表的接口
 * @author 肖喜进
 */
class DataService
{

    use \Xphp\Data\DBAdapter;
    public $if_cache = false;
    /**
     * 获取主键$primary_key为$object_id的数据
     * 或者获取表的一段数据，查询的参数由$params指定
     * 如果参数为空的话，则返回一条空白的Record，可以赋值，产生一条新的记录
     * @param $object_id or $params
     * @return array
     */
    public function get($object_id)
    {

        if (!$object_id)
            return false;
        $result_type='';
        if(is_array($object_id)){
            $params = $object_id;
            if (!isset($params['order']))
                $params['order'] = "{$this->_table}.{$this->_primary} desc";
            if (!isset($params['where']))
                $params['where']=1;
            $result_type='list';
        }else{
            $params = array($this->_primary=>$object_id);
            $result_type='single';
        }

        $result =  $this->select($params);

        if($result_type=='list'){
            while ($row = $result->fetch())
            {
                $key =$this->__format_row_index($row);
                $data[$key] = $this->__format_row_data($row);
            }
            return $data;
        }
        else
            return $this->__format_row_data($result->fetch());
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

        return $this->update($params,$data);
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
        return $this->delete($params);
    }

    protected function __format_row_data($row){
        return $row;
    }
    protected function __format_row_index($row){
        return $row[$this->_primary];
    }


}