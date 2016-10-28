<?php
namespace Back\Controller;

use Think\Controller;
use Think\Page;

class MemberController extends Controller
{
    public function listAction()
    {
        $m_member = M('Member');
        $page     = I('get.p', 1);
        $pagesize = 1;

        $rows = $m_member
            ->page($page, $pagesize)
            ->select();
        //获取总的记录数
        $count  = $m_member->count();
        $t_page = new Page($count, $pagesize);
        $t_page->setConfig('next', '&gt;');
        $t_page->setConfig('last', '&gt;|');
        $t_page->setConfig('prev', '&lt;');
        $t_page->setConfig('first', '|&lt;');
        $t_page->setConfig('theme', '<div class="col-sm-6 text-left"><ul class="pagination">%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% </ul></div><div class="col-sm-6 text-right">%HEADER%</div>');
        $t_page->setConfig('header', '显示开始 %FIRST_ROW% 到 %LAST_ROW% 之 %TOTAL_ROW% （总 %TOTAL_PAGE% 页）');

        $page_html = $t_page->show();
        $this->assign('rows', $rows);
        $this->assign('page_html', $page_html);
        //展示分页列表
        $this->display();
    }
}
