<?php
namespace Back\Controller;

use Think\Controller;

class SettingController extends Controller
{
    /**
     * 添加动作
     */
    public function addAction()
    {
        // 判断是否为POST数据提交
        if (IS_POST) {
            // 数据处理
            // $model = M('Setting');
            $model  = D('Setting');
            $result = $model->create();

            if (!$result) {
                $this->error('数据添加失败: ' . $model->getError(), U('add'));
            }

            $result = $model->add();
            if (!$result) {
                $this->error('数据添加失败:' . $modle->getError(), U('add'));
            }
            // 成功重定向到list页
            $this->redirect('list', [], 0);
        } else {
            // 表单展示
            $this->display();
        }
    }

    /**
     * 列表相关动作
     */
    public function listAction()
    {
        //实例化分组表模型对象
        $m_group    = M('SettingGroup');
        $group_rows = $m_group->select();
        $this->assign('group_rows', $group_rows);

        //获取配置项列表
        $m_setting    = D('Setting');
        $setting_rows = $m_setting
            ->alias('s')
            ->join('left join __SETTING_TYPE__ st Using(setting_type_id)')
            ->relation(true)
            ->select();

        //遍历所有配置项,按照分组管理
        $group_setting = [];
        foreach ($setting_rows as $setting) {
            //判断是否为多选类型
            if ($setting['type_title'] == 'select-multi') {
                $setting['value_list'] = explode(',', $setting['value']);
            }
            $group_id                   = $setting['setting_group_id'];
            $group_setting[$group_id][] = $setting;
        }

        $this->assign('group_setting', $group_setting);

        $this->display();
    }

    /**
     * 编辑
     */
    public function editAction()
    {

        if (IS_POST) {

            $model  = D('Setting');
            $result = $model->create();

            if (!$result) {
                $this->error('数据修改失败: ' . $model->getError(), U('edit'));
            }

            $result = $model->save();
            if (!$result) {
                $this->error('数据修改失败:' . $modle->getError(), U('edit'));
            }
            // 成功重定向到list页
            $this->redirect('list', [], 0);

        } else {

            // 获取当前编辑的内容
            $setting_id = I('get.setting_id', '', 'trim');
            $this->assign('row', M('Setting')->find($setting_id));

            // 展示模板
            $this->display();
        }
    }

    /**
     * 批处理
     */
    public function multiAction()
    {
        // 确定动作
        $operate = I('post.operate', 'delete', 'trim');
        // 确定ID列表
        $selected = I('post.selected', []);
        if (empty($selected)) {
            $this->redirect('list', [], 0);
            return;
        }

        switch ($operate) {
            case 'delete':
                // 使用in条件, 删除全部的品牌
                $cond = ['setting_id' => ['in', $selected]];
                M('Setting')->where($cond)->delete();
                $this->redirect('list', [], 0);
                break;
            default:
                # code...
                break;
        }
    }

    /**
     * ajax的相关请求
     */
    public function ajaxAction()
    {
        $operate = I('request.operate', null, 'trim');

        if (is_null($operate)) {
            return;
        }

        switch ($operate) {
            // 验证品牌名称唯一的操作
            case 'checkBrandUnique':
                // 获取填写的品牌名称
                $title         = I('request.title', '');
                $cond['title'] = $title;
                // 判断是否传递了brand_id
                $brand_id = I('request.brand_id', null);
                if (!is_null($brand_id)) {
                    // 存在, 则匹配与当前ID不相同的记录
                    $cond['brand_id'] = ['neq', $brand_id];
                }
                // 获取模型后, 利用条件获取匹配的记录数
                $count = M('Setting')->where($cond)->count();
                // 如果记录数>0, 条件为真, 说明存在记录, 重复, 验证未通过, 响应false
                echo $count ? 'false' : 'true';
                break;
        }
    }
}
