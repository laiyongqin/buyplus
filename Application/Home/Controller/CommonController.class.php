<?php

namespace Home\Controller;

use Think\Controller;

class CommonController extends Controller
{

    public function _initialize()
    {
        // 数据的获取
        // 分类数据
        $m_category    = D('Category');
        $category_list = $m_category->getNested(); // nested嵌套
        $this->assign('category_list', $category_list);

        // 当前会员信息
        $this->assign('member', session('member'));
    }

    public function checkLogin($target = '', $is_redirect = true)
    {
        if (session('member')) {
            return true;
        } else {
            if ($is_redirect) {
                if ($target !== '') {
                    session('login_target', $target);
                }
                $this->redirect('/login', [], 0);
            }
            return false;
        }
    }
}
