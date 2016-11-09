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
}
