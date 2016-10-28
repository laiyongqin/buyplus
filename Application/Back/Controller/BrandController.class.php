<?php
namespace Back\Controller;

use Think\Controller;
use Think\Page;

class BrandController extends Controller
{

    /**
     * 添加品牌动作
     */
    public function addAction()
    {
        if (IS_POST) {
            //数据处理
            $model  = D('Brand');
            $result = $model->create();

            if (!$result) {
                $this->error('数据添加失败： ' . $model->getError(), U('add'));
            }

            $result = $model->add();
            if (!$result) {
                $this->error('数据添加失败： ' . $model->getError(), U('add'));
            }

            //成功重定向到list页
            $this->redirect('list', [], 0);
        } else {
            //表单展示
            $this->display();
        }
    }
    /**
     * 列表相关动作
     */
    public function listAction()
    {
        $model = M('Brand');

        //分页, 搜索,排序
        $cond                   = []; //初始化条件
        $filter['filter_title'] = I('get.filter_title', '', 'trim');
        if ($filter['filter_title'] !== '') {
            $cond['title'] = ['like', '%' . $filter['filter_title'] . '%'];
        }
        $this->assign('filter', $filter);

        //拼揍排序sql
        $order['field'] = I('get.field', 'sort_number', 'trim'); //初始化排序字段
        $order['type']  = I('get.type', 'desc', 'trim'); //初始化排序方式
        $sort           = [$order['field'] => $order['type']];
        $this->assign('order', $order);

        //分页
        $page     = I('get.p', '1'); // 当前页码
        $pagesize = 3; // 每页记录数

        //获取总记录数
        $count  = $model->where($cond)->count(); // 合计
        $t_page = new Page($count, $pagesize); // use Think\Page;
        // 配置格式
        $t_page->setConfig('next', '&gt;');
        $t_page->setConfig('last', '&gt;|');
        $t_page->setConfig('prev', '&lt;');
        $t_page->setConfig('first', '|&lt;');
        $t_page->setConfig('theme', '<div class="col-sm-6 text-left"><ul class="pagination">%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% </ul></div><div class="col-sm-6 text-right">%HEADER%</div>');
        $t_page->setConfig('header', '显示开始 %FIRST_ROW% 到 %LAST_ROW% 之 %TOTAL_ROW% （总 %TOTAL_PAGE% 页）');
        // 生成HTML代码
        $page_html = $t_page->show();
        $this->assign('page_html', $page_html);

        $rows = $model->where($cond)->order($sort)->page("$page, $pagesize")->select();
        $this->assign('rows', $rows);

        $this->display();

    }
    /**
     * 编辑动作模块
     */
    public function editAction()
    {
        if (IS_POST) {
            $model  = D('Brand');
            $result = $model->create();

            if (!$result) {
                $this->error('数据修改失败: ' . $model->getError(), U('edit'));
            }

            $result = $model->save();
            if (!$result) {
                $this->error('数据修改失败: ' . $model->getError(), U('edit'));
            }
            //修改成功重定向到list页面
            $this->redirect('list', [], 0);
        } else {
            //获取当前主键
            $brand_id = I('get.brand_id', '', 'trim');
            $this->assign('row', M('Brand')->find($brand_id));

            //展示模版
            $this->display();
        }

    }
    public function multiAction()
    {
        //确定动作
        $operate = I('post.operate', 'delete', 'trim');

        //确定主键列表
        $selected = I('post.selected', []);

        switch ($operate) {
            case 'delete':
                //使用 in 条件,删除所有选中品牌
                $cond = ['brand_id' => ['in', $selected]];
                M('Brand')->where($cond)->delete();
                $this->redirect('list', [], 0);
                break;

            default:
                # code...
                break;
        }
    }
    /**
     * 处理ajax相关请求
     */
    public function ajaxAction()
    {
        $operate = I('request.operate', null, 'trim');

        if (is_null($operate)) {
            return;
        }

        switch ($operate) {
            case 'checkBrandUnique':
                $title         = I('request.title', '');
                $cond['title'] = $title;
                $brand_id      = I('request.brand_id', null);
                // 判断是否传递了brand_id
                if (!is_null($brand_id)) {
                    $cond['brand_id'] = ['neq', $brand_id];
                }
                $count = M('Brand')->where($cond)->count();
                echo $count ? 'false' : 'true';
                break;

            default:
                # code...
                break;
        }
    }
}
