
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>语言列表</title>
        <link rel="stylesheet" type="text/css" href="<?php echo CSS_PATH ?>base.css"/>
        <link rel="stylesheet" type="text/css" href="<?php echo CSS_PATH ?>all_list.css"/>
    </head>
    <body class="marg">
        <table class="t_table txt_list box">
            <col width="20%"/>
            <col width="20%"/>
            <col width="20%"/>
            <col width="20%"/>
            <col width="20%"/>
            <thead>
                <tr height="25" align="center">
                    <td>语言</td>
                    <td>标识</td>
                    <td>是否默认</td>
                    <td>当前模板</td>
                    <td>操作</td>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($list as $v)
                {
                    $id = $v['id'];
                    ?>
                    <tr align="center">
                        <td><?php echo $v['title']; ?></td>
                        <td><?php echo $v['lang']; ?></td>
                        <td><?php
                            if ($v['default'] == '1')
                            {
                                echo "是";
                            } else
                            {
                                echo "否";
                            }
                            ?></td>
                        <td><?php echo $v['tmpl']; ?></td>
                        <td>
                            <a href="<?php echo U("del", array("id" => "$id")); ?>" onclick="return confirm('确定删除吗？')">删除</a>
                            |
                            <a href="<?php echo U("edit", array("id" => "$id")); ?>">编辑</a>
                        </td>
                    </tr>
<?php } ?>
            </tbody>
        </table>
    </body>
</html>
