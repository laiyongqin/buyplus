<?php
namespace Back\Controller;

use Think\Controller;

class CodeController extends Controller
{
    /**
     * 代码生成相关控制器
     */
    public function generateAction()
    {
        if (IS_POST) {
            //根据用户提交配置文件,生成CRUD操作
            $module = I('post.module', MODULE_NAME, 'trim');
            $table  = I('post.table', '', 'trim');

            $module_name     = ucfirst($module); //模块名
            $model_name      = implode('', array_map('ucfirst', explode('_', $table))); //模型名
            $controller_name = $model_name; //控制器名
            $table_title     = I('post.table_title', $model_name, 'trim'); //表名标题

            //获取模型表的字段列表
            $model  = M($model_name);
            $fields = $model->getDbFields(); //字段列表
            $pk     = $model->getPk(); //主键名

            //1.替换控制器代码模版
            $controller_template_file = APP_PATH . 'Back/Code/Controller.template.php';
            $controller_template      = file_get_contents($controller_template_file);

            $search             = ['__MODULE__', '__CONTROLLER__', '__MODEL__', '__PK_FIELD__'];
            $replace            = [$module_name, $controller_name, $model_name, $pk];
            $controller_content = str_replace($search, $replace, $controller_template);
            //生成替换文件
            $controller_path = APP_PATH . $module_name . '/Controller/';
            $controller_file = $controller_path . $controller_name . 'Controller.class.php';

            //确保目录正确
            if (!is_dir($controller_path)) {
                mkdir($controller_path, 0775, true);
            }
            //写入文件
            $length = file_put_contents($controller_file, $controller_content);
            if ($length) {
                echo $controller_file, ' 自动生成完成<br>';
            }

            //2.生成列表模版
            //替换表单头部和身体
            $table_head_template_file = APP_PATH . "Back/Code/field_table_head.template.html";
            $table_body_template_file = APP_PATH . "Back/Code/field_table_body.template.html";
            //遍历所有字段
            $table_head_list = $table_body_list = "";
            foreach ($fields as $field) {
                $search  = ['__FIELD_NAME__'];
                $replace = [$field];
                $table_head_list .= str_replace($search, $replace, file_get_contents($table_head_template_file));
                $table_body_list .= str_replace($search, $replace, file_get_contents($table_body_template_file));
            }
            //替换list整体模板内容
            $search                = ['__TABLE_TITLE__', '__MODEL__', '__TABLE_HEAD_LIST__', '__TABLE_BODY_LIST__', '__PK_FIELD__'];
            $replace               = [$table_title, $model_name, $table_head_list, $table_body_list, $pk];
            $list_template_content = str_replace($search, $replace, file_get_contents(APP_PATH . 'Back/Code/list.template.html'));
            //生成list模板
            $list_template_path = APP_PATH . $module_name . '/View/' . $controller_name;
            $list_template_file = $list_template_path . '/list.html';
            if (!is_dir($list_template_path)) {
                mkdir($list_template_path, 0775, true);
            }
            $length = file_put_contents($list_template_file, $list_template_content);
            if ($length) {
                echo $list_template_file, '自动生成完成<br>';
            }

            //3.生成添加模板
            $form_field_list = '';
            foreach ($fields as $field) {
                //因为添加时,不用处理主键字段
                if ($field == $pk) {
                    continue;
                }

                //非主键字段进行处理
                $search  = ['__FIELD_NAME__'];
                $replace = [$field];
                $form_field_list .= str_replace($search, $replace, file_get_contents(APP_PATH . 'Back/Code/add_field.template.html'));
            }

            //替换整体
            $search               = ['__MODEL__', '__TABLE_TITLE__', '__FORM_FIELD_LIST__'];
            $replace              = [$model_name, $table_title, $form_field_list];
            $add_template_content = str_replace($search, $replace, file_get_contents(APP_PATH . 'Back/Code/add.template.html'));

            //生成模版文件
            $add_template_path = APP_PATH . $module_name . '/View/' . $controller_name;
            $add_template_file = $add_template_path . '/add.html';
            if (!is_dir($add_template_path)) {
                mkdir($add_template_path, 0775, true);
            }
            $length = file_put_contents($add_template_file, $add_template_content);
            if ($length) {
                echo $add_template_file, '自动生成成功<br>';
            }

            //4.生成编辑模版
            $form_field_list = '';
            //替换局部
            foreach ($fields as $field) {
                if ($filed == $pk) {
                    continue;
                }
                $search  = ['__FIELD_NAME__'];
                $replace = [$field];
                $form_field_list .= str_replace($search, $replace, file_get_contents(APP_PATH . 'Back/Code/edit_field.template.html'));
            }

            //替换整体
            $search                = ['__MODEL__', '__TABLE_TITLE__', '__FORM_FIELD_LIST__', '__PK_FIELD__'];
            $replace               = [$model_name, $table_title, $form_field_list, $pk];
            $edit_template_content = str_replace($search, $replace, file_get_contents(APP_PATH . 'Back/Code/edit.template.html'));
            //生成模版文件
            $edit_template_path = APP_PATH . $module_name . '/View/' . $controller_name;
            $edit_template_file = $edit_template_path . '/edit.html';
            if (! is_dir($edit_template_path)) {
                mkdir($edit_template_path, 0775, true);
            }
            $length = file_put_contents($edit_template_file, $edit_template_content);
            if ($length) {
                echo $edit_template_file, '自动生成成功', '<br>';
            }

        } else {
            $this->display();
        }
    }
}
