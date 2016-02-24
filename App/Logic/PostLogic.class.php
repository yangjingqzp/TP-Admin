<?php
// +----------------------------------------------------------------------
// | TP-Admin [ 多功能后台管理系统 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2016 http://www.hhailuo.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 逍遥·李志亮 <xiaoyao.working@gmail.com>
// +----------------------------------------------------------------------

namespace Logic;
use Lib\Log;

/**
 * POST 逻辑处理
 */
class PostLogic extends BaseLogic {
    protected $filters;
    protected $db;

    /**
     * 获取内容
     * @param  string  $fields [description]
     * @param  string  $order  [description]
     * @param  integer $limit  [description]
     * @return [type]          [description]
     */
    public function getPosts($fields='*', $order='listorder desc, id desc', $limit=20) {
        $this->execFilter();
        $pagenum = I('get.p', 1);
        // 缓存查询条件
        $post_model = clone $this->db->where(array('siteid' => get_siteid()));
        $posts = $this->db->field($fields)->order($order)->page($pagenum . ', ' . $limit)->select();

        // 分页数据
        $count = $post_model->count();
        $page = new \Think\Page($count, $limit);
        $page_html = $page->show();
        return array('data' => $posts, 'page' => $page_html);
    }

    /**
     * 过滤注册
     * @param  [type] $type [description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function registerFilter($type, $data) {
        if (empty($data)) {
            return ;
        }
        $this->filters[$type] = $data;
    }

    protected function execFilter() {
        if (empty($this->filters)) {
            return ;
        }
        foreach ($this->filters as $type => $value) {
            $func = $type.'Filter';
            if (method_exists($this, $func)) {
                $this->$func($value);
            }
        }
    }

    /**
     * 日期过滤(按月) 格式 2016-01
     * @param  string $date 日期
     */
    protected function dateFilter($date) {
        if (!empty($date)) {
            $this->db->where(array('_query' => "DATE_FORMAT(`inputtime`,'%Y-%m')=".$date));
        }
    }

    protected function likeFilter($data) {
        foreach ($data as $key => $value) {
            if (empty($value)) continue;
            $this->db->where(array($key => array('like', '%' . $value . '%')));
        }
    }

    protected function taxFilter($taxs) {
        $terms = array();
        foreach ($taxs as $key => $value) {
            if (!empty($value)) {
                $terms[] = $value;
            }
        }
        if (!empty($terms)) {
            $tablename = $this->db->getTableName();
            $this->db->join(C('DB_PREFIX') . "category_posts as cp ON cp.post_id = " . $tablename . ".id")->where(array('cp.term_id' => array('in', $terms)));
        }
    }

    function __set($name, $value) {
        $this->$name = $value;
    }

}