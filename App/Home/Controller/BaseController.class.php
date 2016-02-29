<?php
// +----------------------------------------------------------------------
// | TP-Admin [ 多功能后台管理系统 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2016 http://www.hhailuo.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 逍遥·李志亮 <xiaoyao.working@gmail.com>
// +----------------------------------------------------------------------

namespace Home\Controller;
use Think\Controller;

/**
* 前台基类
*/
class BaseController extends Controller {
    protected $siteid, $siteInfo;

    function __construct() {
        parent::__construct();
        $this->siteid = get_siteid();
        $this->siteInfo = get_site_info($this->siteid);
        $site_theme = empty($this->siteInfo['template']) ? 'hhailuo' : $this->siteInfo['template'];
        C('DEFAULT_THEME', $site_theme);
        $this->assign('siteInfo', $this->siteInfo);
        $this->assign('siteid', $this->siteid);
    }

    protected function beforeFilter($method, $params=array()) {
        if (empty($params)) {
            call_user_func(array($this, $method));
        } else {
            if (isset($params['only'])) {
                if (in_array(ACTION_NAME, $params['only'])) {
                    call_user_func(array($this, $method));
                }
            } elseif (isset($params['except'])) {
                if (!in_array(ACTION_NAME, $params['except'])) {
                    call_user_func(array($this, $method));
                }
            }
        }
    }
}