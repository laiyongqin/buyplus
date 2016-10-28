<?php
return array(
    //'配置项'=>'配置值'
    'SHOW_PAGE_TRACE' => true,
    'URL_MODEL'       => 2,
    'URL_ROUTER_ON'   => true,
    'URL_ROUTE_RULES' => [
        'register' => 'Member/register',
        'login'    => 'Member/login',
        'verify'   => 'Member/verify',
    ],
    'LOAD_EXT_CONFIG' => 'db',
    'SESSION_TYPE'    => 'Db',
);
