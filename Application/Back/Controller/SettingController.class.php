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
     * 更新动作
     */
    public function updateAction()
    {
        //获取所有的配置项
        $setting = I('post.setting');
        // dump($setting);die;
        $m_setting = M('Setting');
        //保证多选配置项存在合理数据
        $cond['type_title'] = 'select-multi';
        $multi_setting      = $m_setting->alias('s')
            ->join('left join __SETTING_TYPE__ st Using(setting_type_id)')
            ->where($cond)
            ->getField('setting_id', true);
        // dump($multi_setting);die();
        //判断多选类型的配置项是否出现在用户提交的post数据中
        foreach ($multi_setting as $m_setting_id) {
            if (! isset($setting[$m_setting_id])) {
                $setting[$m_setting_id] = '';
            }
        }

        //遍历配置项,更新配置项
        foreach ($setting as $setting_id => $value) {
            if (is_array($value)) {
                $value = implode(',', $value);
            }
            $m_setting->save(['setting_id' => $setting_id, 'value' => $value]);
        }

        //更新完成后需要清空所有的配置项
        S(['type'=>'File']);
        foreach ($m_setting->getField('key', true) as $key) {
           S('setting_' . $key, null);
        }

        $this->redirect('list', [], 0);

    }

    /**
     * 批处理
     */
    public function multiAction()
    {
        var_dump(getConfig('shop_title'));
        var_dump(getConfig('non_key'));
        var_dump(getConfig('non_key', 'default-value'));
    }
}
