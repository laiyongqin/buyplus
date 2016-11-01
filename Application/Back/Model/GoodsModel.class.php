<?php

namespace Back\Model;

use Think\Model;

class goodsModel extends Model
{
    protected $_auto = [
        ['created_at', 'time', self::MODEL_INSERT, 'function'],
        ['updated_at', 'time', self::MODEL_BOTH, 'function'],
    ]; // 自动完成定义

}
