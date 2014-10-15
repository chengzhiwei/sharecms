<?php

namespace Common\Controller;

class SitevhookController extends \Think\Controller
{

    public $vhook_tpl;

    public function _initialize()
    {
        C('LAYOUT_ON', false);
        $this->vhook_tpl = $this->vhooktplpath();
    }

    public function vhooktplpath()
    {
        $class = new \ReflectionClass($this);
        $arr = explode('\\', $class->name);
        $path = '';
        for ($i = 0; $i < count($arr) - 1; $i++)
        {
            $path.=$arr[$i] . '/';
        }
        return 'Template/Plugin/' . $path;
    }

    public function vhookshow($path)
    {
        if (file_exists($path))
        {
            $this->display($path);
        } else
        {
            $this->display($this->vhook_tpl . $path . '.php');
        }
    }

}