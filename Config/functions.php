<?php

function hook($hookname)
{
    static $obj = array();
    $hookmod = DD('Hook');
    $hookinfo = $hookmod->findbyval($hookname);
    if (!$hookinfo)
    {
        return;
    }
    $listmod = DD('HookList');
    $hooklist = $listmod->sellistbyhid($hookinfo['id']);
    if ($hooklist)
    {
        foreach ($hooklist as $li)
        {
            if (!in_array($li['path'], $obj))
            {
                import($li['path'], 'Plugin');
                $obj[] = $li['path'];
            }
            $cls = str_replace('/', '\\', $li['path']);
            $Vhook = new $cls();
            $Vhook->show();
        }
    }
}

/**
 * 
 * @staticvar array $_adomodel
 * @param type $adomodel
 * @param type $ext
 * @return Model
 */
function DD($adomodel, $ext = 'Ado')
{
    static $_adomodel;
    if (isset($_adomodel[$adomodel . '_' . $ext]))
    {
        return $_adomodel[$adomodel . '_' . $ext];
    }
    $newmodel = "\\Model\\" . $ext . "model\\" . $adomodel . 'Model';
    $newmodel_obj = new $newmodel();
    $_adomodel[$adomodel . '_' . $ext] = $newmodel_obj;
    return $newmodel_obj;
}

function BO($bo, $namespace = '')
{
    static $_bo;
    if (isset($_bo[$bo . '_' . $namespace]))
    {
        return $_bo[$bo . '_' . $namespace];
    }
    if ($namespace)
    {
        $bo_path = $namespace . '\\Bo\\' . $bo . 'Bo';
    } else
    {
        $bo_path = '\\' . MODULE_NAME . '\\Bo\\' . $bo . 'Bo';
    }
    $bo_obj = new $bo_path();
    $_bo[$bo . '_' . $namespace] = $bo_obj;
    return $bo_obj;
}

function URL($url = '', $vars = '', $app = '', $suffix = true, $domain = false)
{
    // 解析URL
    $info = parse_url($url);
    $url = !empty($info['path']) ? $info['path'] : ACTION_NAME;
    if (isset($info['fragment']))
    { // 解析锚点
        $anchor = $info['fragment'];
        if (false !== strpos($anchor, '?'))
        { // 解析参数
            list($anchor, $info['query']) = explode('?', $anchor, 2);
        }
        if (false !== strpos($anchor, '@'))
        { // 解析域名
            list($anchor, $host) = explode('@', $anchor, 2);
        }
    } elseif (false !== strpos($url, '@'))
    { // 解析域名
        list($url, $host) = explode('@', $info['path'], 2);
    }
    // 解析子域名
    if (isset($host))
    {
        $domain = $host . (strpos($host, '.') ? '' : strstr($_SERVER['HTTP_HOST'], '.'));
    } elseif ($domain === true)
    {
        $domain = $_SERVER['HTTP_HOST'];
        if (C('APP_SUB_DOMAIN_DEPLOY'))
        { // 开启子域名部署
            $domain = $domain == 'localhost' ? 'localhost' : 'www' . strstr($_SERVER['HTTP_HOST'], '.');
            // '子域名'=>array('模块[/控制器]');
            foreach (C('APP_SUB_DOMAIN_RULES') as $key => $rule)
            {
                $rule = is_array($rule) ? $rule[0] : $rule;
                if (false === strpos($key, '*') && 0 === strpos($url, $rule))
                {
                    $domain = $key . strstr($domain, '.'); // 生成对应子域名
                    $url = substr_replace($url, '', 0, strlen($rule));
                    break;
                }
            }
        }
    }

    // 解析参数
    if (is_string($vars))
    { // aaa=1&bbb=2 转换成数组
        parse_str($vars, $vars);
    } elseif (!is_array($vars))
    {
        $vars = array();
    }
    if (isset($info['query']))
    { // 解析地址里面参数 合并到vars
        parse_str($info['query'], $params);
        $vars = array_merge($params, $vars);
    }

    // URL组装
    $depr = C('URL_PATHINFO_DEPR');
    $urlCase = C('URL_CASE_INSENSITIVE');
    if ($url)
    {
        if (0 === strpos($url, '/'))
        {// 定义路由
            $route = true;
            $url = substr($url, 1);
            if ('/' != $depr)
            {
                $url = str_replace('/', $depr, $url);
            }
        } else
        {
            if ('/' != $depr)
            { // 安全替换
                $url = str_replace('/', $depr, $url);
            }
            // 解析模块、控制器和操作
            $url = trim($url, $depr);
            $path = explode($depr, $url);
            $var = array();
            $varModule = C('VAR_MODULE');
            $varController = C('VAR_CONTROLLER');
            $varAction = C('VAR_ACTION');
            $var[$varAction] = !empty($path) ? array_pop($path) : ACTION_NAME;
            $var[$varController] = !empty($path) ? array_pop($path) : CONTROLLER_NAME;
            if ($maps = C('URL_ACTION_MAP'))
            {
                if (isset($maps[strtolower($var[$varController])]))
                {
                    $maps = $maps[strtolower($var[$varController])];
                    if ($action = array_search(strtolower($var[$varAction]), $maps))
                    {
                        $var[$varAction] = $action;
                    }
                }
            }
            if ($maps = C('URL_CONTROLLER_MAP'))
            {
                if ($controller = array_search(strtolower($var[$varController]), $maps))
                {
                    $var[$varController] = $controller;
                }
            }
            if ($urlCase)
            {
                $var[$varController] = parse_name($var[$varController]);
            }
            $module = '';

            if (!empty($path))
            {
                $var[$varModule] = implode($depr, $path);
            } else
            {
                if (C('MULTI_MODULE'))
                {
                    if (MODULE_NAME != C('DEFAULT_MODULE') || !C('MODULE_ALLOW_LIST'))
                    {
                        $var[$varModule] = MODULE_NAME;
                    }
                }
            }
            if ($maps = C('URL_MODULE_MAP'))
            {
                if ($_module = array_search(strtolower($var[$varModule]), $maps))
                {
                    $var[$varModule] = $_module;
                }
            }
            if (isset($var[$varModule]))
            {
                $module = $var[$varModule];
                unset($var[$varModule]);
            }
        }
    }
    $ext = __APP__;
    if ($app)
    {
        $ext = __ROOT__ . '/' . $app;
    }
    if (C('URL_MODEL') == 0)
    { // 普通模式URL转换
        $url = $ext . '?' . C('VAR_MODULE') . "={$module}&" . http_build_query(array_reverse($var));
        if ($urlCase)
        {
            $url = strtolower($url);
        }
        if (!empty($vars))
        {
            $vars = http_build_query($vars);
            $url .= '&' . $vars;
        }
    } else
    { // PATHINFO模式或者兼容URL模式
        if (isset($route))
        {
            $url = $ext . '/' . rtrim($url, $depr);
        } else
        {
            $module = (defined('BIND_MODULE') && BIND_MODULE == $module ) ? '' : $module;
            $url = $ext . '/' . ($module ? $module . MODULE_PATHINFO_DEPR : '') . implode($depr, array_reverse($var));
        }
        if ($urlCase)
        {
            $url = strtolower($url);
        }
        if (!empty($vars))
        { // 添加参数
            foreach ($vars as $var => $val)
            {
                if ('' !== trim($val))
                    $url .= $depr . $var . $depr . urlencode($val);
            }
        }
        if ($suffix)
        {
            $suffix = $suffix === true ? C('URL_HTML_SUFFIX') : $suffix;
            if ($pos = strpos($suffix, '|'))
            {
                $suffix = substr($suffix, 0, $pos);
            }
            if ($suffix && '/' != substr($url, -1))
            {
                $url .= '.' . ltrim($suffix, '.');
            }
        }
    }
    if (isset($anchor))
    {
        $url .= '#' . $anchor;
    }
    if ($domain)
    {
        $url = (is_ssl() ? 'https://' : 'http://') . $domain . $url;
    }

    return $url;
}

function Elt($url,$vars)
{
    $info = pathinfo($url);
    $action = $info['basename'];
    $module = $info['dirname'];
    $class = A($module, 'Element');
    dump( $class);
    if ($class)
    {
        if (is_string($vars))
        {
            parse_str($vars, $vars);
        }
        return call_user_func_array(array(&$class, $action . C('ACTION_SUFFIX')), $vars);
    } else
    {
        return false;
    }
}
