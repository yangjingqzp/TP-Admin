<?php
// +----------------------------------------------------------------------
// | TP-Admin [ 多功能后台管理系统 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2016 http://www.hhailuo.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 逍遥·李志亮 <xiaoyao.working@gmail.com>
// +----------------------------------------------------------------------

namespace Model;
use Think\Page as Page;

class PageModel extends BaseModel {

    public function addPage($data) {
        $data['siteid'] = get_siteid();
        return $this->add($data);
    }

    public function updatePage($page_id, $data, $meta_data) {
        $page = $this->find($page_id);
        if(empty($page)) return false;
        require MODEL_PATH . 'content_input.class.php';
        $content_input = new \content_input($page['template'], 2);
        $inputinfo = $content_input->get($meta_data);
        $meta_data = $inputinfo['system'];
        if ($this->where("id = %d", $page_id)->save($data) !== false) {
            return $this->savePageMeta($page_id, $meta_data);
        } else {
            return false;
        }
    }

    public function savePageMeta($page_id, $meta_data) {
        if (empty($meta_data)) {
            return true;
        }
        $all_meta = array();
        foreach ($meta_data as $key => $value) {
            $all_meta[] = array('page_id' => $page_id, 'meta_key' => $key, 'meta_value' => $value);
        }
        if (!empty($all_meta)) {
            return model('pagemeta')->addAll($all_meta);
        }
        return true;
    }

}