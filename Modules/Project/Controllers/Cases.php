<?php
namespace Module\Project\Controllers;
use \Module\Project\Controller as Controller;

class Cases extends Controller
{

    public function delete($params){

        if (isset($params['case_id']) && $params['case_id']>0) {
            $this->Model("cases")->set($params['case_id'],array('closed'=>1));
            $this->Model("space")->sets(array('closed'=>1),array('case_id'=>$params['case_id']));
        }
    }

}
