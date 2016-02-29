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

class PostModel extends BaseModel {
    protected $autoCheckFields = false;
    protected $modelid, $my_fields;

    public function setModel($modelid) {
        $model = model('Model')->where("siteid = %d and id = %d",get_siteid(),$modelid)->find();
        if (empty($model)) {
            showmessage('模型不存在！');
        }
        $this->modelid = $modelid;
        $this->tableName = strtolower($model['tablename']);
        $this->trueTableName = C("DB_PREFIX").strtolower($model['tablename']);
        $this->setField();
        return $model;
    }

    public function contentList($where=array(), $order = "id desc", $limit=20, $page_params = array()) {
        $module_fields = $this->getListFields();
        $list_fields = array_merge($list_fields, array_translate($module_fields, 'fieldid', 'field'));
        array_push($list_fields, 'id', 'listorder', 'inputtime', 'updatetime');
        return $this->getList($where, $order, $limit, $list_fields, $page_params);
    }

    /**
     * 获取内容月份列表
     * @return [type] [description]
     */
    public function getMonths() {
        return $this->query("select DATE_FORMAT(inputtime,'%Y-%m') month from " . $this->trueTableName . " group by month order by month desc");
    }

    /**
     * 获取列表页所需字段
     * @param mix $field 需要获取的ModelField表的字段
     * @param int $modelid 模型ID
    */
    public function getListFields($field=true, $modelid=null) {
        if (is_null($modelid)) {
            $modelid = $this->modelid;
        }
        $module_fields = model('ModelField')->field($field)->where(array('modelid' => $modelid, 'islist' => 1 ,'siteid' => get_siteid()))->order('listorder asc')->select();
        return $module_fields;
    }

    public function getContent($id) {
        return $this->where(array('id'=>$id))->find();
    }

    public function addContent($data) {
        // 主表
        $modelid = $this->modelid;
        $tablename = $this->trueTableName;
        if (isset($data['relation'])) {
            $data['relation'] = array2string($data['relation']);
        }
        // 获取所有字段
        require MODEL_PATH . 'content_input.class.php';
        $content_input = new \content_input($this->modelid);
        $inputinfo = $content_input->get($data);
        $inputinfo = $inputinfo['system'];
        // 匹配数据库字段，防止SQL语句出错
        $postData = $this->parseField($inputinfo);
        $postData = array_merge($postData,array('username' => $_SESSION['user_info']['account'], 'siteid' => get_siteid()));
        return $this->add($postData);

    }

    public function editContent($post_id, $data) {
        $modelid = $this->modelid;
        if (isset($data['relation'])) {
            $data['relation'] = array2string($data['relation']);
        }
        require MODEL_PATH . 'content_input.class.php';
        $content_input = new \content_input($this->modelid);
        $inputinfo = $content_input->get($data);
        $inputinfo = $inputinfo['system'];
        $postData = $this->parseField($inputinfo);
        $postData['siteid'] = get_siteid();
        return $this->where("id = %d", $post_id)->save($postData);
    }

    public function deleteContent($ids) {
        $this->startTrans();
        if (is_array($ids)) {
            if ($this->where(array('id' => array('in', $ids)))->delete() === false || model('category_posts')->where(array('post_id' => array('in', $ids)))->delete() === false) {
                $this->rollback();
                return false;
            } else {
                $this->commit();
                return true;
            }
        } else {
            if ($this->where(array('id' => $ids))->delete() === false || model('category_posts')->where(array('post_id' => $ids))->delete() === false) {
                $this->rollback();
                return false;
            } else {
                $this->commit();
                return true;
            }
        }
    }

    public function parseField($options) {
        $temp = array();
        foreach ($this->my_fields as $key => $field) {
            if (isset($options[$field])) {
                $temp[$field] = $options[$field];
            }
        }
        return $temp;
    }

    public function setField() {
        $this->flush();
        $this->my_fields = $this->getDbFields();
    }
}
