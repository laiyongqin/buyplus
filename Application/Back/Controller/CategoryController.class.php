<?php
namespace Back\Controller;

use Think\Controller;

class CategoryController extends Controller
{
    public function listAction()
    {
    	$m_category = D('Category');
    	$this->assign('rows', $m_category->getTreeList());
    	$this->display();
    }
}
