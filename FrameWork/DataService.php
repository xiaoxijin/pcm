<?php
namespace Xphp;
/**
 * Model类，提供对某个数据库表的接口
 * @author 肖喜进
 */
class DataService
{

    use \Xphp\Data\DBAdapter;
    protected $_table_before_shard;//切割表标记

    /**
     * 表切片参数
     *
     * @var int
     */
    public $tablesize = 1000000;
    public $if_cache = false;

    /**
     * 按ID切分表
     * @param $id
     * @return null
     */
//    function shard_table($id)
//    {
//        if (empty($this->_table_before_shard))
//        {
//            $this->_table_before_shard = $this->table;
//        }
//        $table_id = intval($id / $this->tablesize);
//        $this->table = $this->_table_before_shard . '_' . $table_id;
//    }
//
    /**
     * 获取主键$primary_key为$object_id的一条记录对象(Record Object)
     * 或者获取表的一段数据，查询的参数由$params指定
     * 如果参数为空的话，则返回一条空白的Record，可以赋值，产生一条新的记录
     * @param $object_id or $params
     * @throws \Exception
     * @return array
     */
    public function get($object_id)
    {

        if (!$object_id)
            throw new \Exception("no object pa.");
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
            $result_type='detail';
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
     * 更新ID为$id的记录,值为$data关联数组
     * @param $id
     * @param $data
     * @param $where string 指定匹配字段，默认为主键
     * @return bool
     */
    public function set($id, $data, $where = '')
    {
        if (empty($where))
        {
            $where = $this->primary;
        }
        return $this->db->update($id, $data, $this->table, $where);
    }

    /**
     * 更新一组数据
     * @param array $data 更新的数据
     * @param array $params update的参数列表
     * @return bool
     * @throws \Exception
     */
    public function sets($data, $params)
    {
        if (empty($params))
        {
            throw new \Exception("Model sets params is empty!");
        }

        $this->put($params);
        return $this->update($data);
    }

    /**
     * 删除一条数据主键为$id的记录，
     * @param $id
     * @param $where string 指定匹配字段，默认为主键
     * @return true/false
     */
    public function del($object_id, $where=null)
    {
        if ($where == null)
        {
            $where = $this->_primary;
        }
//        $this->where($where);
        return $this->delete($object_id,$where);
    }

    /**
     * 删除一条数据包含多个参数
     * @param $params
     * @return bool
     * @throws \Exception
     */
    public function dels($params)
    {
        if (empty($params))
        {
            throw new \Exception("Model dels params is empty!");
        }

        $this->put($params);
        $this->delete();
        return true;
    }



    protected function __format_row_data($row){
        return $row;
    }
    protected function __format_row_index($row){
        return $row[$this->_primary];
    }


}