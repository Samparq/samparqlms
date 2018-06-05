<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 31/8/17
 * Time: 2:49 PM
 */

namespace backend\assets;

use yii\web\AssetBundle;

/**
 * Main backend application theme asset bundle.
 */
class ThemeAsset extends AssetBundle
{

    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.css',
        'https://fonts.googleapis.com/css?family=Montserrat:400,500,600,700,800,900'
    ];

    public $js = [
        //'js/jquery-1.12.4.min.js',
        'js/jquery.nicescroll.min.js'
    ];

    public $depends = [
        'yiister\gentelella\assets\ThemeAsset',
        'yiister\gentelella\assets\ExtensionAsset'
        
      ];

}

