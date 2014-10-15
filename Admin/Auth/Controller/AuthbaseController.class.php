<?php

namespace Auth\Controller;

import('Common\Controller\AdminbaseController', 'Admin');

class AuthbaseController extends \Common\Controller\AdminbaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->_chkauth();
        $this->_common();
    }

    private function _chkauth()
    {

        if (!session('Dream_admin'))
        {
            $this->redirect('Auth/Login/login');
        }
    }

    private function _common()
    {
        $authgroupmodel=DD('AdminAuthGroup');
        $authgrouplist=$authgroupmodel->selall();
        $this->assign('authgrouplist',$authgrouplist);
    }
    /**
     * 重写DISPLAY方法 支持PHP模板引擎的模板布局
     * @param string $view
     */
    public function display($view = '')
    {
        if (C('LAYOUT_ON'))
        {
            if ($view == '')
            {
                $view = TMPL_PATH . MODULE_NAME . '/' . CONTROLLER_NAME . '/' . ACTION_NAME . C('TMPL_TEMPLATE_SUFFIX');
            }
            $this->assign('view', $view);
            parent::display(TMPL_PATH . 'Layout/layout.php');
        } else
        {
            parent::display();
        }
    }

}