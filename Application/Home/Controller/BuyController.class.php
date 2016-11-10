<?php

namespace Home\Controller;

use Home\Cart\Cart;

/**
 *
 */
class BuyController extends CommonController
{
    public function addGoodsAction()
    {
        $cart             = new Cart;
        $goods_id         = I('post.goods_id');
        $buy_quantity     = I('post.buy_quantity');
        $goods_product_id = I('post.goods_product_id');

        $cart->addGoods($goods_id, $buy_quantity, $goods_product_id);

        $this->redirect('/cart', [], 0);
    }

    public function cartAction()
    {
        $cart       = new Cart;
        $goods_list = $cart->getGoodsList();

        // dump($goods_list);
        // die;
        $this->assign('goods_list', $goods_list);
        $this->assign('cart_info', $cart->getCartInfo());

        $this->display();
    }

    public function removeGoodsAction()
    {
        $cart             = new Cart;
        $goods_id         = I('request.goods_id');
        $goods_product_id = I('request.goods_product_id');

        $result = $cart->removeGoods($goods_id, $goods_product_id);

        if ($result) {
            $this->ajaxReturn(['error' => 0]);
        } else {
            $this->ajaxReturn(['error' => 1, 'errorInfo' => $cart->getError()]);
        }
    }

    public function orderAction()
    {
        $this->checkLogin('/order');
        $this->display();

    }

    public function ajaxAction()
    {
        $operate = I('request.operate', '', 'trim');
        if ($operate === '') {
            $this->ajaxReturn(['error' => 1, 'errorInfo' => '没有指定操作']);
        }

        switch ($operate) {
            case 'memberAddress':
                if (!$this->checkLogin('', false)) {
                    $this->ajaxReturn(['error' => 1, 'errorInfo' => '会员未登陆']);
                };

                $rows = M('Address')->alias('a')
                    ->field('a.*, rc.title country_title, rz.title zone_title, rcc.title city_title')
                    ->where(['member_id' => session('member.member_id')])
                    ->join('left join __REGION__ rc on rc.region_id = a.country_id')
                    ->join('left join __REGION__ rz on rz.region_id = a.zone_id')
                    ->join('left join __REGION__ rcc on rcc.region_id = a.city_id')
                    ->select();
                if ($rows) {
                    $this->ajaxReturn(['error' => 0, 'rows' => $rows]);
                } else {
                    $this->ajaxReturn(['error' => 1, 'errorInfo' => '会员没有货运地址']);
                }
                break;

            case 'getRegion':
                $level = I('request.level', 0);
                $parent_id = I('request.region_id');
                if ($level == '0') {
                    $cond = ['parent_id' => 1];
                }else {
                    $cond = ['parent_id' => $parent_id];
                }
                $rows = M('Region')->where($cond)->select();
                if ($rows) {
                    $this->ajaxReturn(['error' => 0, 'rows' => $rows]);
                } else {
                    $this->ajaxReturn(['error' => 1, 'errorInfo' => '没有地区']);
                }
                break;
            case 'addAddress':
                if (!$this->checkLogin('', false)) {
                    $this->ajaxReturn(['error' => 1, 'errorInfo' => '会员未登陆']);
                };

                $member_id = session('member.member_id');
                //添加新地址设置为默认
                M('Address')->auto([['is_default', 1], ['member_id', $member_id]])->create();
                $address_id = M('Address')->add();
                //将其他地址改为非默认
                M('Address')->where(['member_id'=>$member_id, 'address_id'=>['neq', $address_id]])->save(['is_default'=>0]);
                $this->ajaxReturn(['error'=>0]);
                break;
        }
    }
}
