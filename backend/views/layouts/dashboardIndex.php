<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 21/9/17
 * Time: 5:22 PM
 */

/* @var $this \yii\web\View */
/* @var $content string */

use backend\assets\AppAsset;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use common\widgets\Alert;
use backend\assets\ThemeAsset;
use yii\helpers\Url;

ThemeAsset::register($this);

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="<?= Yii::getAlias('@web/images/favicon.ico')?>" type="image/x-icon" />
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
<body class="nav-md">
<div class="container body">
    <div class="main_container">
        <div class="col-md-3 left_col menu_fixed">
            <div class="left_col scroll-view">
                <div class="navbar nav_title" style="border: 0;">
                    <a href="<?= Url::toRoute(['/site/index'])?>" class="site_title"><img src="<?= Yii::getAlias('@web/images/ss-logo.png') ?>" /><span>Samparq</span></a>
                </div>

                <div class="clearfix"></div>
                <div class="profile clearfix">
                    <div class="profile_pic">
                        <img src="<?= Yii::$app->params['file_url'].Yii::$app->user->identity->image_name ?>" alt="..." class="img-circle profile_img"  style="width: 60px; height: 60px;" />
                    </div>
                    <div class="profile_info">
                        <span>Welcome</span>

                        <h2><?= Yii::$app->user->identity->name ?></h2>
                    </div>
                </div>

                <br>
                <?= $this->render('_sidebar')?>
                <div class="sidebar-footer hidden-small">
                    <?= Html::beginForm(['/site/logout'], 'post', ['class' => 'bb']); ?>

                    <?= Html::a('<span class="glyphicon glyphicon-off" aria-hidden="true"></span>','javascript:;', ['class' => 'tt','style' => 'width:100%'])?>
                    <?= Html::endForm(); ?>
                </div>
            </div>
        </div>
        <?= $this->render('_header-nav'); ?>
        <div class="right_col" role="main">
            <div class="row">
                <?= $content ?>
            </div>
        </div>
        <?= $this->render('_footer'); ?>
    </div>
</div>

<div class="jqvmap-label" style="display: none; left: 897px; top: 1026px;">United States of America</div><div class="daterangepicker dropdown-menu ltr opensleft"><div class="calendar left"><div class="daterangepicker_input"><input class="input-mini form-control" type="text" name="daterangepicker_start" value=""><i class="fa fa-calendar glyphicon glyphicon-calendar"></i><div class="calendar-time" style="display: none;"><div></div><i class="fa fa-clock-o glyphicon glyphicon-time"></i></div></div><div class="calendar-table"></div></div><div class="calendar right"><div class="daterangepicker_input"><input class="input-mini form-control" type="text" name="daterangepicker_end" value=""><i class="fa fa-calendar glyphicon glyphicon-calendar"></i><div class="calendar-time" style="display: none;"><div></div><i class="fa fa-clock-o glyphicon glyphicon-time"></i></div></div><div class="calendar-table"></div></div><div class="ranges"><ul><li data-range-key="Today">Today</li><li data-range-key="Yesterday">Yesterday</li><li data-range-key="Last 7 Days">Last 7 Days</li><li data-range-key="Last 30 Days">Last 30 Days</li><li data-range-key="This Month">This Month</li><li data-range-key="Last Month">Last Month</li><li data-range-key="Custom">Custom</li></ul><div class="range_inputs"><button class="applyBtn btn btn-default btn-small btn-primary" disabled="disabled" type="button">Submit</button> <button class="cancelBtn btn btn-default btn-small" type="button">Clear</button></div></div></div><div id="window-resizer-tooltip" style="display: none;"><a href="#" title="Edit settings"></a><span class="tooltipTitle">Window size: </span><span class="tooltipWidth" id="winWidth">1440</span> x <span class="tooltipHeight" id="winHeight">825</span><br><span class="tooltipTitle">Viewport size: </span><span class="tooltipWidth" id="vpWidth">1440</span> x <span class="tooltipHeight" id="vpHeight">380</span></div></body></html>

<?php

$script = <<<JS
 $('.tt').click(function() {
    $('.bb').submit();
 });

JS;

$this->registerJs($script);

?>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
