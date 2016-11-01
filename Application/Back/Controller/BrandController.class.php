<?php
namespace Back\Controller;

use Think\Controller;
use Think\Page;
use Think\Upload;
use Think\Image;

class BrandController extends Controller
{

    /**
     * 添加品牌动作
     */
    public function addAction()
    {
        if (IS_POST) {
            //数据处理
            $t_upload = new Upload;
            //配置上传的属性
            $t_upload->rootPath = APP_PATH . 'Upload/';
            $t_upload->savePath = 'Brand/';
            $t_upload->exts     = ['jpeg', 'png', 'git', 'jpg'];//设置上传的文件格式
            $t_upload->maxSize  = 1 * 1024 * 1024;//文件大小
            $upload_info = $t_upload->uploadOne($_FILES['logo_ori']);
            // dump($upload_info);die();
            //上传成功讲数据保存到POST数组中,便于create()创建数据
            if ($upload_info) {
                $_POST['logo_ori'] = $upload_info['savepath'] . $upload_info['savename'];
            }
            //生成缩略图
            $t_image = new Image();
            $t_image->open($t_upload->rootPath . $_POST['logo_ori']);
            $w = getConfig('brand_thumb_width', 100);
            $h = getConfig('brand_thumb_height', 100);
            $thumb_root = './Public/Thumb/';
            $thumb_path = $thumb_root . $upload_info['savepath'];
            if (! is_dir($thumb_path)) {
                mkdir($thumb_path, 0775, true);
            }
            $thumb_file = $thumb_path . 'thumb_' . $w . 'x' . $h . '_' .$upload_info['savename'];
            $t_image->thumb($w, $h)->save($thumb_file);
            $_POST['logo'] = $upload_info['savepath'] . 'thumb_' . $w . 'x' . $h . '_' .$upload_info['savename'];

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
        $operate = I('post.operate_type', 'delete', 'trim');

        //确定主键列表
        $selected = I('post.selected', []);
        if (empty($selected)) {
            $this->redirect('list', [], 0);
            return;
        }

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
