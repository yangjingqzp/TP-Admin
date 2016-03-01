<?php
// +----------------------------------------------------------------------
// | TP-Admin [ 多功能后台管理系统 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2016 http://www.hhailuo.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 逍遥·李志亮 <xiaoyao.working@gmail.com>
// +----------------------------------------------------------------------

namespace Admin\Controller;
use Admin\Controller\CommonController;

/**
 * 内容控制器
 */
class PostController extends CommonController {
    protected $db;

    function __construct() {
        parent::__construct();
        $this->beforeFilter('filterPostTypeAuth');
        $this->db = model("Post");
    }

    public function index() {
        if (!isset($_GET['moduleid'])) {
            $this->error('模型参数缺失！');
        }
        $module = model('Model')->find($_GET['moduleid']);
        if (empty($module)) {
            $this->error('模型不存在！');
        }
        $this->db->setModel($module['id']);
        $list_fields = $this->db->getListFields(array('name', 'field'));
        $fields = array('id', 'listorder', 'updatetime');
        foreach ($list_fields as $key => $field) {
            $fields[] = $field['field'];
        }

        // 分类，日期过滤
        $tax = I('post.tax');
        $date = I('post.date');
        // 标题检索
        $title = I('post.title');
        $post_logic = logic('Post');
        $post_logic->db = $this->db;
        $post_logic->registerFilter('tax', $tax);
        $post_logic->registerFilter('date', $date);
        $post_logic->registerFilter('like', array('title' => $title));

        // 获取文章
        $data = $post_logic->getPosts($fields, "listorder desc, id desc", 10);

        // 获取日期、分类信息
        $months = $this->db->getMonths();
        $taxonomies = logic('taxonomy')->getPostTaxonomy($module['tablename']);
        $termsGroupByTaxonomy = logic('category')->getPostTermsGroupByTaxonomy($module['tablename']);
        // 搜索条件
        $this->assign('tax', $tax);
        $this->assign('date', $date);
        $this->assign('title', $title);
        // filter values
        $this->assign('months', $months);
        $this->assign('taxonomies', $taxonomies);
        $this->assign('termsGroupByTaxonomy', $termsGroupByTaxonomy);
        // contents
        $this->assign('module', $module);
        $this->assign('contents',$data['data']);
        $this->assign('list_fields',$list_fields);
        $this->assign('pages',$data['page']);
        $this->display();
    }

    public function add() {
        if (IS_POST) {
            $data = I('post.info');
            $module = $this->db->setModel($_POST['moduleid']);
            $this->db->startTrans();
            if ($post_id = $this->db->addContent($data)) {
                // 分类处理
                $taxonomies = logic('taxonomy')->getPostTaxonomy($module['tablename']);
                if (!empty($taxonomies)) {
                    $terms = array();
                    foreach ($taxonomies as $taxonomy) {
                        $key = $module['tablename'] . '_' . $taxonomy['name'];
                        $terms = array_merge($terms, I('post.' . $key, array()));
                    }
                    if (!empty($terms)) {
                        $category_post_datas = array();
                        foreach ($terms as $key => $value) {
                            $category_post_datas[] = array('term_id' => $value, 'post_id' => $post_id);
                        }
                        if (model('category_posts')->addAll($category_post_datas) !== false) {
                            $this->db->commit();
                            $this->success('添加成功!', U('Post/index', array('moduleid' => $_POST['moduleid'])));
                        } else {
                            $this->db->rollback();
                            $this->error('添加失败！');
                        }
                    }
                }
                // 分类处理结束
                $this->db->commit();
                $this->success('添加成功!', U('Post/index', array('moduleid' => $_POST['moduleid'])));
            } else {
                $this->db->roolback();
                $this->error('添加失败！');
            }
        } else {
            if (!isset($_GET['moduleid'])) {
                $this->error('模型参数缺失！');
            }
            $module = model('Model')->find($_GET['moduleid']);
            if (empty($module)) {
                $this->error('模型不存在！');
            }
            $taxonomies = logic('taxonomy')->getPostTaxonomy($module['tablename']);
            $termsGroupByTaxonomy = logic('category')->getPostTermsGroupByTaxonomy($module['tablename']);
            require MODEL_PATH.'content_form.class.php';
            $content_form = new \content_form($module['id']);
            $forminfos = $content_form->get();

            $default_template = 'post-' . $module['tablename'];
            $template_list = get_post_templates();
            $this->assign('template_list', $template_list);
            $this->assign('default_template', $default_template);
            $this->assign('taxonomies', $taxonomies);
            $this->assign('termsGroupByTaxonomy', $termsGroupByTaxonomy);
            $this->assign('formValidator', $content_form->formValidator);
            $this->assign('forminfos', $forminfos);
            $this->assign('module', $module);
            $this->display();
        }
    }

    public function edit() {
        if (IS_POST) {
            $hash[C('TOKEN_NAME')] = $_POST[C('TOKEN_NAME')];
            if (!isset($_POST['moduleid']) || !isset($_POST['id'])) {
                $this->error('模型参数缺失！');
            }
            $module = model('Model')->find($_POST['moduleid']);
            if (empty($module)) {
                $this->error('模型不存在！');
            }
            $this->db->setModel($module['id']);
            if (!$this->db->autoCheckToken($hash)) {
                $this->error('令牌验证失败, 请刷新页面');
            }
            $data = I('post.info');
            $post_id = I('post.id');
            $this->db->startTrans();
            if ($this->db->editContent($post_id, $data)) {
                // 分类处理
                if (model('category_posts')->where(array('post_id' => $post_id))->delete() === false) {
                    $this->db->rollback();
                    $this->error('更新失败！');
                };
                $taxonomies = logic('taxonomy')->getPostTaxonomy($module['tablename']);
                if (!empty($taxonomies)) {
                    $terms = array();
                    foreach ($taxonomies as $taxonomy) {
                        $key = $module['tablename'] . '_' . $taxonomy['name'];
                        $terms = array_merge($terms, I('post.' . $key, array()));
                    }
                    if (!empty($terms)) {
                        $category_post_datas = array();
                        foreach ($terms as $key => $value) {
                            $category_post_datas[] = array('term_id' => $value, 'post_id' => $post_id);
                        }
                        if (model('category_posts')->addAll($category_post_datas) !== false) {
                            $this->db->commit();
                            $this->success('更新成功!', U('Post/index', array('moduleid' => $_POST['moduleid'])));
                        } else {
                            $this->db->rollback();
                            $this->error('更新失败！');
                        }
                    }
                }
                // 分类处理结束

                $this->db->commit();
                $this->success('更新成功!', U('Post/index', array('moduleid' => $module['id'])));
            } else {
                $this->db->rollback();
                $this->error('更新失败！');
            }
        } else {
            if (!isset($_GET['moduleid']) || !isset($_GET['id'])) {
                $this->error('模型参数缺失！');
            }
            $module = model('Model')->find($_GET['moduleid']);
            if (empty($module)) {
                $this->error('模型不存在！');
            }
            // 设置模型，获取内容
            $this->db->setModel($module['id']);
            $post = $this->db->getContent($_GET['id']);
            if (empty($post)) {
                $this->error('内容不存在！');
            }
            $taxonomies = logic('taxonomy')->getPostTaxonomy($module['tablename']);
            $termsGroupByTaxonomy = logic('category')->getPostTermsGroupByTaxonomy($module['tablename']);
            $category_posts = model('category_posts')->where(array('post_id' => $post['id']))->select();
            $post_terms = array();
            foreach ($category_posts as $key => $value) {
                $post_terms[] = $value['term_id'];
            }
            require MODEL_PATH.'content_form.class.php';
            $content_form = new \content_form($module['id']);
            $forminfos = $content_form->get($post);

            $template_list = get_post_templates();
            $this->assign('template_list', $template_list);
            $this->assign('taxonomies', $taxonomies);
            $this->assign('termsGroupByTaxonomy', $termsGroupByTaxonomy);
            $this->assign('formValidator', $content_form->formValidator);
            $this->assign('forminfos', $forminfos);
            $this->assign('content', $post);
            $this->assign('module', $module);
            $this->assign('post_terms', $post_terms);
            $this->display();
        }
    }

    public function listorder(){
        if (!isset($_GET['moduleid'])) {
            $this->error('模型参数缺失！');
        }
        $module = model('Model')->find($_GET['moduleid']);
        if (empty($module)) {
            $this->error('模型不存在！');
        }
        $this->db->setModel($module['id']);
        if (isset($_POST['listorders']) && is_array($_POST['listorders'])) {
            $sort = $_POST['listorders'];
            foreach ($sort as $k => $v) {
                $this->db->where(array('id'=>$k))->save(array('listorder'=>$v));
            }
        }
        $this->success('排序成功！');
    }

    public function delete() {
        if (!isset($_REQUEST['moduleid'])) {
            $this->error('模型参数缺失！');
        }
        $module = model('Model')->find($_REQUEST['moduleid']);
        if (empty($module)) {
            $this->error('模型不存在！');
        }
        $this->db->setModel($module['id']);
        if (IS_POST) {
            $ids = $_POST['ids'];
            if (!empty($ids) && is_array($ids)) {
                if ($this->db->deleteContent($ids)) {
                    $this->success('删除成功！');
                } else {
                    $this->error('删除失败！');
                }
            } else {
                $this->error("您没有勾选信息");
            }
        } else {
            if ($this->db->deleteContent(intval($_GET['id']))) {
                $this->success('删除成功！');
            } else {
                $this->error('删除失败！');
            }
        }
    }

    /**
     * 标题重复检测
    */
    public function public_check_title() {
        if($_GET['data']=='' || (!$_GET['modelid'])) return '';
        $moduleid = intval($_GET['modelid']);
        $this->db->setModel($moduleid);
        $title = $_GET['data'];
        $r = $this->db->where(array('title'=>$title))->find();
        if($r) {
            exit('1');
        } else {
            exit('0');
        }
    }

    public function getPosts() {
        $modelid = I('post.modelid', '');
        if (empty($modelid)) {
            $this->ajaxReturn(array('code' => 10001, 'message' => '参数缺失！'));
        }
        $module = model('Model')->find($modelid);
        if (empty($module)) {
            $this->ajaxReturn(array('code' => 10002, 'message' => '参数错误！'));
        }
        $this->db->setModel($module['id']);
        $list_fields = $this->db->getListFields(array('name', 'field'));
        $fields = array('id', 'listorder', 'updatetime');
        foreach ($list_fields as $key => $field) {
            $fields[] = $field['field'];
        }
        $title = I('post.title', '');
        $post_logic = logic('Post');
        $post_logic->db = $this->db;
        $post_logic->registerFilter('like', array('title' => $title));
        // 获取文章
        $data = $post_logic->getPosts($fields, "listorder desc, id desc", 10);
        $selected_post_ids = I('post.selected_posts', array());
        $selected_posts = array();
        if (!empty($selected_post_ids)) {
            $selected_posts = $this->db->where(array('id' => array('in', $selected_posts)))->feild($fields)->select();
        }
        $this->ajaxReturn(array('code' => 0, 'message' => '', 'data' => array('avaliable_posts' => $data['data'], 'selected_posts' => $selected_posts)));
    }

}