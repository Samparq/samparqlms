<?php
return [
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'timeZone' => 'Asia/Kolkata',
    'components' => [
        'samparq' => [
            'class' => 'common\components\Samparq'
        ],
        'utility' => [

            'class' => 'common\components\Utility',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'placeholder' => [
            'class' => 'common\feature\Addons'
        ]

    ],
  
];
