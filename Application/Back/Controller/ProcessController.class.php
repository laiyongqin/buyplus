<?php

namespace Back\Controller;

use Think\Controller;

/**
 *处理队列中的订单
 */
class ProcessController extends Controller
{

    public function orderAction()
    {
        $redis = new \Redis;
        $redis->connect('127.0.0.1', '6379');

        while (true) {
            if (!$order_str = $redis->rpop('order_list')) {
                continue;
            }

            $order = unserialize($order_str);

            $m_goods         = M('Goods');
            $m_goods_product = M('GoodsProduct');

            //校验库存
            $flag = true;

            foreach ($order['goods_list'] as $key => $goods) {
                if ($goods['goodsproduct_id'] == '0') {
                    $quantity = $m_goods->where(['goods_id' => $goods['goods_id']])->getField('quantity');
                } else {
                    $quantity = $m_goods_product->where(['goods_product_id' => $goods['goods_product_id']])->getField('product_quantity');
                }

                if ($quantity < $goods['buy_quantity']) {
                    $flag = false;
                    break;
                }
            }

            //拼凑订单信息
            $data                    = $order;
            $data['shipping_id']     = $order['shipping_method'];
            $data['payment_id']      = $order['payment_method'];
            $data['order_time']      = time();
            $data['payment_status']  = '未支付';
            $data['shipping_status'] = '未发货';

            $m_goods = D('Home/Goods');
            foreach ($order['goods_list'] as $key => $goods) {
                $price = $m_goods->getPrice($goods['goods_id'], $goods['goods_product_id']);
                $total_price += $price * $goods['buy_quantity'];
            }

            $data['goods_total'] = $total_price;
            //获取运费
            $shipping_key = M('Shipping')->where(['shipping_id' => $data['shipping_id']])->getField('key');

            $shipping_class         = 'Common\Shipping\\' . $shipping_key;
            $shipping               = new $shipping_class;
            $data['shipping_total'] = $shipping->price();
            //计算总价
            $data['total'] = $data['goods_total'] + $data['shipping_total'];

            if ($flag) {
                $data['order_status'] = '确定';
                $redis->hSet('order', $order['order_sn'], 'yes');
                echo $order['order_sn'], " Success", "\n";
            } else {
                // 失败
                $data['order_status'] = '未确定';
                $redis->hSet('order', $order['order_sn'], 'no');
                echo $order['order_sn'], ' Error', "\n";
            }

            //生成订单表数据
            if (M('Order')->create($data)) {
                $order_id = M('Order')->add();
            }

            //生成订单商品表数据
            foreach ($order['goods_list'] as $goods) {
                $price              = $m_goods->getPrice($goods['goods_id'], $goods['goods_product_id']);
                $goods['order_id']  = $order_id;
                $goods['buy_price'] = $price;
                $data_list[]        = $goods;
            }

            M('OrderGoods')->addAll($data_list);

        }
    }
}
