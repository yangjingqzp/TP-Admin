<?php
return array(
    'DB_TYPE'         => 'Mysql', // 数据库类型
    'DB_PREFIX'       => 'xy_', // 数据库表前缀
    'DB_PORT'         => 3306,
    // 普通配置
    'DB_USER'         => 'root', // 用户名
    'DB_PWD'          => 'root', // 密码
    'DB_HOST'         => '127.0.0.1',
    'DB_NAME'         =>'tp3',
    // DSN 配置 采用DSN配置，必须也同时普通配置。
    // 'DB_DSN'          => 'mysql:host=127.0.0.1;port=3306;dbname=tp-admin;charset=utf8;',

    'house'        => array(
        'DB_TYPE'         => 'Mysql', // 数据库类型
        'DB_USER'           => 'root', // 用户名
        'DB_PREFIX'         => 'xy_', // 数据库表前缀
        'DB_PORT'           => 3306,
        'DB_PWD'            => 'root', // 密码
        'DB_HOST'           => 'localhost',
        'DB_NAME'           =>'cdfdc_new',
        // 'DB_DSN'            => 'mysql:unix_socket=/Applications/MAMP/tmp/mysql/mysql.sock;port=3306;dbname=cdfdc_new;',
    ),
    'fdc'        => array(
        'DB_TYPE'         => 'Mysql', // 数据库类型
        'DB_USER'           => 'root', // 用户名
        'DB_PREFIX'         => 'sl_', // 数据库表前缀
        'DB_PORT'           => 3306,
        'DB_PWD'            => 'root', // 密码
        'DB_HOST'           => 'localhost',
        'DB_NAME'           =>'cdfdc',
        // 'DB_DSN'            => 'mysql:unix_socket=/Applications/MAMP/tmp/mysql/mysql.sock;port=3306;dbname=cdfdc;',
    ),
);