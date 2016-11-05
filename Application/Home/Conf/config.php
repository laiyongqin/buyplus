<?php
return array(
    //'配置项'=>'配置值'
    'SHOW_PAGE_TRACE' => true,
    'URL_MODEL'       => 2,
    'URL_ROUTER_ON'   => true,
    'URL_ROUTE_RULES' => [
        'register'  => 'Member/register',// 注册URL
        'center'    => 'Member/center', // 用户中心
        'login'     => 'Member/login', // 登陆
        'verify'    => 'Member/verify',// 验证码
        'logout'    => 'Member/logout', // 退出

        'index' => 'Shop/index',
        
        // 带参数的路由
        'goods/:goods_id\d'   => 'Shop/goods' 
        
    ],
    'LOAD_EXT_CONFIG' => 'db',
    'SESSION_TYPE'    => 'Db',

    'DEFAULT_CONTROLLER'    => 'Shop',
    'DEFAULT_ACTION'    => 'index',
);
