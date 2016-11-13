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
                $level     = I('request.level', 0);
                $parent_id = I('request.region_id');
                if ($level == '0') {
                    $cond = ['parent_id' => 1];
                } else {
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
                M('Address')->where(['member_id' => $member_id, 'address_id' => ['neq', $address_id]])->save(['is_default' => 0]);
                $this->ajaxReturn(['error' => 0]);
                break;
            case 'getShipping':
                $rows = M('Shipping')->where(['enabled' => 1])->order('sort_number')->select();
                if ($rows) {
                    foreach ($rows as &$row) {
                        $class        = 'Common\Shipping\\' . $row['key'];
                        $shipping     = new $class;
                        $row['price'] = $shipping->price();
                    }
                    $this->ajaxReturn(['error' => 0, 'rows' => $rows]);
                } else {
                    $this->ajaxReturn(['error' => 1, 'errorInfo' => '没有配送方式']);
                }
                break;
            case 'getGoods':
                if (!$this->checkLogin('', false)) {
                    $this->ajaxReturn(['error' => 1, 'errorInfo' => '会员未登陆']);
                };

                $cart       = new Cart;
                $goods_list = $cart->getGoodsList();
                $cart_info  = $cart->getCartInfo();
                $this->ajaxReturn(['error' => 0, 'rows' => $goods_list, 'cartInfo' => $cart_info]);
                break;
            case 'getOrderStatus':
                // sleep(25);
                ini_set('max_execution_time', '0');
                $order_sn = I('request.order_sn');
                $redis    = new \Redis;
                $redis->connect('127.0.0.1', '6379');

                while (true) {
                    $status = $redis->hget('order', $order_sn);
                    if ($status) {
                        break;
                    }
                }

                if ($status) {
                    if ($status == 'yes') {
                        $result = '订单生成成功';
                    } else {
                        $result = '订单失败';
                    }
                } else {
                    $result = '处理中';
                }

                $this->ajaxReturn(['error' => 0, 'status' => $result]);
                break;

        }
    }

    public function checkoutAction()
    {
        if (!$this->checkLogin('', false)) {
            $this->ajaxReturn(['error' => 1, 'errorInfo' => '会员未登陆']);
        };

        $order = I('post.');
        $cart  = new Cart;

        $order['goods_list'] = $cart->getGoodsListRaw();
        $order['member_id']  = session('member.member_id');

        $redis = new \Redis;
        $redis->connect('127.0.0.1', '6379');
        //生成订单号
        $time_arr = explode(' ', microtime());
        $order_sn = $time_arr[1] . substr($time_arr[0], 2, 6);
        $order_sn .= $redis->incr('order_sn');
        $order['order_sn'] = $order_sn;

        //将订单信息加入队列
        $result = $redis->lpush('order_list', serialize($order));

        if ($result) {
            $this->ajaxReturn(['error' => 0, 'order_sn' => $order_sn,
                'order_url'                => U('/orderInfo/' . $order_sn)]);
        } else {
            $this->ajaxReturn(['error' => 1, 'errorInfo' => '订单未被添加']);
        }
    }

    public function orderInfoAction()
    {
        $this->assign('order_sn', I('get.order_sn'));
        $this->display();
    }
}
