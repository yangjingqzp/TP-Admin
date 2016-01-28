<?php
// +----------------------------------------------------------------------
// | TP-Admin [ 多功能后台管理系统 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2016 http://www.hhailuo.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 逍遥·李志亮 <xiaoyao.working@gmail.com>
// +----------------------------------------------------------------------
namespace Model;

/**
 * 菜单模型
 */
class MenuModel extends BaseModel {
    protected $tableName = 'node';

    public function getMenus($where, $order, $limit) {
        return $this->where($where)->order($order)->limit($limit)->select();
    }

    public function nodeList($siteid='') {
        $nodes = $this->order("sort desc")->select();
        $siteid = empty($siteid) ? get_siteid() : $siteid;
        $models = model('model')->where(array('siteid' => $siteid))->field('tablename')->select();
        $post_type = array();
        foreach ($models as $key => $value) {
            $post_type[] = $value['tablename'];
        }
        // 过滤不属于当前站点的POST TYPE 菜单
        foreach ($nodes as $key => $value) {
            if (empty($value['post_type']) || in_array($value['post_type'], $post_type)) {
                continue ;
            }
            unset($nodes[$key]);
        }

        $list = list_to_tree($nodes,'id','pid');
        $nodes = array();
        tree_to_array($list,$nodes);
        return $nodes;
    }
}