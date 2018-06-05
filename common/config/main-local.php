<?php
return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=192.168.4.9;dbname=qdeg_123456',
            'username' => 'mridul',
            'password' => '123456',
            'charset' => 'utf8',
        ],
        'dbDynamic' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=192.168.4.9;dbname=qdeg_123456',
            'username' => 'mridul',
            'password' => '123456',
            'charset' => 'utf8',
        ],
        //'db' => [
//            'class' => 'yii\db\Connection',
//            'dsn' => 'mysql:host=103.231.209.72;dbname=qdegrees_qds_sampark',
//            'username' => 'qdegrees_sampark',
//            'password' => 'admin@123',
//            'charset' => 'utf8',
//        ],

        'secondaryDb' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=103.231.209.72;dbname=qdegrees_webcast', // Maybe other DBMS such as psql (PostgreSQL),...
            'username' => 'qdegrees_webcast',
            'password' => 'Civil@789',
        ],

        'cosecDb' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=103.231.209.72;dbname=qdegrees_cosec',
            'username' => 'qdegrees_cosec',
            'password' => 'cosec@123$',
        ],


        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@common/mail',
            'useFileTransport' => false,
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'mail.qdegrees.in',
                'username' => 'samparq@qdegrees.in',
                'password' => 'samparQ@!23#',
                'port' => '587',
                'encryption' => false,
            ],
        ],

    ],
];
