<?php

namespace Home\Cart;

/**
 *
 */
class Cart
{
    private $goods_list = []; //存储购物车内商品属性

    public function __construct()
    {
        $this->initGoods(); //初始化商品数据
    }

    public function __destruct()
    {
        $this->saveGoods(); //保存商品数据
    }

    private function initGoods()
    {
        if ($member = session('member')) {
            $cond['member_id'] = $member['member_id'];

            $rows = M('CartGoods')->field('goods_id, goods_product_id, buy_quantity')
                ->where($cond)
                ->select();
            $goods_list = [];
            foreach ($rows as $goods) {
                $goods_key = $goods['goods_id'] . ':' . $goods['goods_product_id'];

                $goods_list[$goods_key] = $goods;
            }
        } else {
            // dump(cookie());
            $goods_list = unserialize(cookie('cart_goods_list'));
        }

        $this->goods_list = $goods_list ? $goods_list : [];
    }

    private function saveGoods()
    {
        if ($member = session('member')) {
            $cart_goods = M('CartGoods');

            $cart_goods_list = []; //记录当前处理过的所购的商品的ID
            foreach ($this->goods_list as $key => $goods) {
                $cond['member_id']        = $member['member_id'];
                $cond['goods_id']         = $goods['goods_id'];
                $cond['goods_product_id'] = $goods['goods_product_id'];
                if ($cart_goods->where($cond)->find()) {
                    $cart_goods_list[] = $cart_goods->cart_goods_id;

                    $cart_goods->buy_quantity = $goods['buy_quantity'];
                    $cart_goods->save();
                } else {
                    $data                 = $cond;
                    $data['buy_quantity'] = $goods['buy_quantity'];
                    $cart_goods_list[]    = $cart_goods->add($data);
                }
            }
            $cond = [
                'cart_goods_id' => ['not in', $cart_goods_list],
            ];
            $cart_goods->where($cond)->delete();

        } else {
            cookie('cart_goods_list', serialize($this->goods_list), ['expire' => 30 * 24 * 3600]);
        }
    }

    public function addGoods($goods_id, $buy_quantity = 1, $goods_product_id = 0)
    {
        $goods_key = $goods_id . ':' . $goods_product_id;
        if (isset($this->goods_list[$goods_key])) {
            $this->goods_list[$goods_key]['buy_quantity'] += $buy_quantity;
        } else {
            $this->goods_list[$goods_key] = [
                'goods_id'         => $goods_id,
                'goods_product_id' => $goods_product_id,
                'buy_quantity'     => $buy_quantity,
            ];
        }
    }

    public function getGoodsList()
    {
        $m_goods           = D('Goods');
        $m_product_option  = M('ProductOption');
        $return_goods_list = [];

        foreach ($this->goods_list as $key => $goods) {
            $goods_info  = $m_goods->field('goods_id', 'image', 'name', 'price')->find($goods['goods_id']);
            $option_list = $m_product_option->alias('po')
                ->field('ga.title ga_title, ao.title ao_title')
                ->join('left join __ATTRIBUTE_OPTION__ ao using(attribute_option_id)')
                ->join('left join __GOODS_ATTRIBUTE__ ga using(goods_attribute_id)')->where(['goods_product_id' => $goods['goods_product_id']])
                ->select();
            $goods_info['option_list'] = $option_list;

            $goods_info = array_merge($goods_info, $goods);

            //获取当前商品真实价格
            $member                   = session('member');
            $goods_info['real_price'] = $m_goods->getPrice($goods['goods_id'], $goods['goods_product_id'], $member ? $member['member_id'] : 0);

            $return_goods_list[$key] = $goods_info;

        }
        return $return_goods_list;
    }

    public function mergeCookie()
    {
        $goods_list = unserialize(cookie('cart_goods_list'));
        // dump($goods_list);

        $this->goods_list = array_merge($this->goods_list, $goods_list ? $goods_list : []);
        // dump($this->goods_list);
        cookie('cart_goods_list', null);

    }

    public function removeGoods($goods_id, $goods_product_id)
    {
        $goods_key = $goods_id . ':' . $goods_product_id;

        if (isset($this->goods_list[$goods_key])) {
            unset($this->goods_list[$goods_key]);
            return true;
        } else {
            $this->error = '商品(货品)不存在';
            return false;
        }

    }
    public function getCartInfo()
    {
        $total_price  = 0;
        $total_weight = 0;

        $m_goods = D('Goods');

        foreach ($this->goods_list as $key => $goods) {
            $row = $m_goods->field('weight, wu.title weight_title')->join('left join __WEIGHT_UNIT__ wu using(weight_unit_id)')->find($goods['goods_id']);
            switch ($row['weight_title']) {
                case '克':
                    $total_weight += $row['weight'] * $goods['buy_quantity'];
                    break;
                case '千克':
                    $total_weight += $row['weight'] * 1000 * $goods['buy_quantity'];
                    break;
                case '500克(斤)':
                    $total_weight += $row['weight'] * 500 * $goods['buy_quantity'];
                    break;
            }
            $member = session('member');
            $total_price += $m_goods->getPrice($goods['goods_id'], $goods['goods_product_id'], $member ? $member['member_id'] : 0) * $goods['buy_quantity'];
        }

        return ['total_price' => $total_price, 'total_weight' => $total_weight];
    }

    public function clearGoods()
    {

    }

    private $error;
    public function getError()
    {
        return $this->error;
    }
}
