<?php
namespace Back\Controller;

use Think\Controller;
use Think\Image;
use Think\Page;
use Think\Upload;

class GoodsController extends Controller
{
    protected function addIndex($goods_id)
    {
        $row = M('Goods')->field('goods_id, name, UPC, description, b.title brand_title, c.title category_title, price, quantity, date_available, g.sort_number')
            ->alias('g')
            ->join('left join __BRAND__ b using(brand_id)')
            ->join('left join __CATEGORY__ c using(category_id)')
            ->find($goods_id);
        //获取索引对象
        require VENDOR_PATH . 'XunSearch/lib/XS.php';
        $xs    = new \XS('goods');
        $index = $xs->index;
        //索引文档对象处理
        $doc = new \XSDocument;
        $doc->setFields($row);
        //添加索引
        $index->add($doc);
    }
    /**
     * 添加动作
     */
    public function addAction()
    {
        // 判断是否为POST数据提交
        if (IS_POST) {
            // 数据处理
            $model  = D('Goods');
            $result = $model->create();

            if (!$result) {
                $this->error('数据添加失败: ' . $model->getError(), U('add'));
            }

            $goods_id = $model->add();
            if (!$goods_id) {
                $this->error('数据添加失败:' . $modle->getError(), U('add'));
            }
            // 自动更新当前商品对应的索引
            // $this->addIndex($goods_id);

            //商品相册图像数据添加
            $t_upload = new Upload();
            //配置上传信息
            $t_upload->rootPath = APP_PATH . 'Upload/';
            $t_upload->savePath = 'Goods/';
            $t_upload->exts     = ['jpeg', 'jpg', 'gif', 'png'];
            $t_upload->maxSize  = 2 * 1024 * 1024;
            //开始上传
            $goods_image_list = $t_upload->uploadMulti($_FILES['goods_image']);
            //生成缩略图
            $t_image = new Image;
            //配置缩略图信息
            $thumb_root = './Public/Thumb/';

            $w_s = getConfig('goods_small_width', 100);
            $h_s = getConfig('goods_small_height', 100);

            $w_m = getConfig('goods_medium_width', 300);
            $h_m = getConfig('goods_medium_height', 300);

            $w_b = getConfig('goods_big_width', 800);
            $h_b = getConfig('goods_big_height', 800);

            foreach ($goods_image_list as $key => $image) {
                if (!is_dir($thumb_root . $image['savepath'])) {
                    mkdir($thumb_root . $image['savepath'], 0775, ture);
                }

                $s_file = $image['savepath'] . 'small_' . $image['savename'];
                $t_image->open(APP_PATH . 'Upload/' . $image['savepath'] . $image['savename']);
                $t_image->thumb($w_s, $h_s)->save($thumb_root . $s_file);

                $m_file = $image['savepath'] . 'medium_' . $image['savename'];
                $t_image->open(APP_PATH . 'Upload/' . $image['savepath'] . $image['savename']);
                $t_image->thumb($w_m, $h_m)->save($thumb_root . $m_file);

                $b_file = $image['savepath'] . 'big_' . $image['savename'];
                $t_image->open(APP_PATH . 'Upload/' . $image['savepath'] . $image['savename']);
                $t_image->thumb($w_b, $h_b)->save($thumb_root . $b_file);
                //拼凑数据,插入数据库
                $date_image[] = [
                    'goods_id'     => $goods_id,
                    'image'        => $image['savepath'] . $image['savename'],
                    'image_small'  => $s_file,
                    'image_medium' => $m_file,
                    'image_big'    => $b_file,
                    'sort_number'  => I('post.goods_image.' . $key . '.sort_number'),
                ];
            }
            //一次插入goods_image数据记录
            M('GoodsImage')->addAll($date_image);

            //保存商品属性
            $attr_list  = I('post.attribute');
            $value_data = [];
            foreach ($attr_list as $goods_attribute_id => $value) {
                $m_attr_option = M('AttributeOption');
                if (is_string($value) && strpos($value, '|||') !== fasle) {
                    //是多值自定义属性
                    $option_data['goods_attribute_id'] = $goods_attribute_id;
                    foreach (explode('|||', $value) as $option_title) {
                        $option_data['title'] = $option_title;
                        $cond                 = $option_data;
                        if ($attribute_option_id = $m_attr_option->where($cond)->getField('attribute_option_id')) {
                            $new_option_id[] = $attribute_option_id;
                            continue;
                        }
                        $new_option_id[] = $m_attr_option->add($option_data);
                    }
                    $value = $new_option_id;
                }

                //判断是否为多选
                $is_option = 0;
                if (is_array($value)) {
                    $value = implode(',', $value);

                    $is_option_list = I('post.is_option', []);
                    if (in_array($goods_attribute_id, $is_option_list)) {
                        $is_option = 1;
                    }
                }
                $value_data[] = [
                    'goods_id'           => $goods_id,
                    'goods_attribute_id' => $goods_attribute_id,
                    'value'              => $value,
                    'is_option'          => $is_option,
                ];
            }
            M('GoodsAttributeValue')->addAll($value_data);

            //调用方法生成静态页面
            $this->staticGoods($goods_id);

            // 成功重定向到list页
            $this->redirect('list', [], 0);
        } else {
            //获取关联数据进行展示
            $this->assign('brand_list', M('Brand')->order('sort_number')->select());
            //分类
            $this->assign('category_list', D('Category')->getTreeList());
            //长度单位
            $this->assign('length_unit_list', M('LengthUnit')->select());
            //重量单位
            $this->assign('weight_unit_list', M('WeightUnit')->select());
            //税类型
            $this->assign('tax_list', M('Tax')->select());
            //库存状态
            $this->assign('stock_status_list', M('StockStatus')->select());

            //获取商品属性分组
            $this->assign('goods_type_list', M('GoodsType')->select());
            // 表单展示
            $this->display();
        }
    }

    /**
     * 列表相关动作
     */
    public function listAction()
    {

        $model = M('Goods');

        // 分页, 搜索, 排序等
        // 搜索, 筛选, 过滤
        // 判断用户传输的搜索条件, 进行处理
        // $filter 表示用户输入的内容
        // $cond 表示用在模型中查询条件
        $cond = $filter = []; // 初始条件
        // 在生成代码的基础上, 自定义完成搜索条件
        //
        // 分配筛选数据, 到模板, 为了展示搜索条件
        $this->assign('filter', $filter);

        // 排序
        $sort = $order = [];
        // 考虑用户所传递的排序方式和字段
        // 在生成代码的基础上,自定义默认的排序字段(假设,表中存在sort_number字段, 不存在需要修改)
        // $order['field'] = I('get.field', 'sort_number', 'trim');// 初始排序, 字段
        // $order['type'] = I('get.type', 'asc', 'trim');// 初始排序, 方式

        if (!empty($order)) {
            $sort = $order['field'] . ' ' . $order['type'];
        }
        $this->assign('order', $order);

        // 分页
        $page     = I('get.p', '1'); // 当前页码
        $pagesize = 10; // 每页记录数\\

        // 获取总记录数
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
     * 编辑
     */
    public function editAction()
    {

        if (IS_POST) {

            $model  = D('Goods');
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
            $goods_id = I('get.goods_id', '', 'trim');
            $this->assign('row', M('Goods')->find($goods_id));

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
                $cond = ['goods_id' => ['in', $selected]];
                M('Goods')->where($cond)->delete();
                $this->redirect('list', [], 0);
                break;
            default:
                # code...
                break;
        }
    }

    public function staticgoods($goods_id)
    {
        if ($goods_id == 0) {
            $this->redirect('/index', [], 0);
        }

        $m_goods = D('Home/Goods');

        // 获取商品信息
        $goods = $m_goods->find($goods_id);
        $this->assign('goods', $goods);

        // 面包屑信息
        $breadcrumb = $m_goods->getBreadcrumb($goods_id);
        $this->assign('breadcrumb', $breadcrumb);
        // dump($breadcrumb);die;

        // 图像展示
        $this->assign('image_list', M('GoodsImage')->where(['goods_id' => $goods_id])->select());

        //属性信息
        $attribute_list = D('Home/GoodsAttributeValue')->field('gav.*, ga.title attribute_title, at.title type_title')->where(['goods_id' => $goods_id])->alias('gav')->join('left join __GOODS_ATTRIBUTE__ ga using(goods_attribute_id)')->join('left join __ATTRIBUTE_TYPE__ at using(attribute_type_id)')->relation(true)->select();

        $option_list = [];
        foreach ($attribute_list as $key => $attribute) {
            if ($attribute['type_title'] == 'select') {
                $attribute_list[$key]['option'] = M('AttributeOption')->where(['attribute_option_id' => ['in', $attribute['value']]])->select();
            }
            if ($attribute['is_option'] == '1') {
                $option_list[] = $attribute_list[$key];
            }
        }

        $this->assign('attribute_list', $attribute_list);
        $this->assign('option_list', $option_list);

        $content = $this->fetch('Home@Shop:goods');
        $file = './goods/'. $goods_id. '.html';
        file_put_contents($file, $content);
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
                $count = M('Goods')->where($cond)->count();
                // 如果记录数>0, 条件为真, 说明存在记录, 重复, 验证未通过, 响应false
                echo $count ? 'false' : 'true';
                break;

            case 'getAttribute':
                $cond['goods_type_id'] = I('request.goods_type_id');
                //获取当前分类下的全部属性
                $rows = D('GoodsAttribute')->field('ga.*, gat.title type_title')->alias('ga')->join('left join __ATTRIBUTE_TYPE__ gat using(attribute_type_id)')->relation(true)->where($cond)->select();
                if ($rows) {
                    $this->ajaxReturn([
                        'error' => 0,
                        'rows'  => $rows,
                    ]);
                } else {
                    $this->ajaxReturn([
                        'error'     => 1,
                        'errorInfo' => '查询数据不存在',
                    ]);
                }
                break;
        }
    }
}
