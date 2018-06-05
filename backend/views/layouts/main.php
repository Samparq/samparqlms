<?php

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
use yii\bootstrap\Modal;

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
        <div class="col-md-3 left_col">
            <div class="left_col scroll-view">
                <div class="pattern">
                <div class="navbar nav_title" style="border: 0;">
                    <a href="<?= Url::toRoute(['/site/index'])?>" class="site_title">
                        <img class="webView" src="<?= Yii::getAlias('@web/images/samparq-logo.png') ?>" />
                        <img class="mobileView" src="<?= Yii::getAlias('@web/images/ss-logo.png') ?>" />
                        </a>
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

                 
                <?= $this->render('_sidebar')?>
                <div class="sidebar-footer hidden-small">
                    <?= Html::a('<span class="glyphicon glyphicon-off" aria-hidden="true"></span>',Url::toRoute(['/site/logout']), ['class' => 'tt','style' => 'width:100%', 'data-method' => 'post'])?>
                </div>
                </div>
            </div>
        </div>
        <?= $this->render('_header-nav'); ?>
        <div class="right_col" role="main">
            <!-- <div class="row"> -->
                <?= $content ?>
            <!-- </div> -->
        </div>
    </div>
</div>

<?php
Modal::begin([
    'id' => "modalAssessment",
    'header' => "<h2>Select Training</h2> "
]);

echo "<div id='loadModalAssessment'><strong><i>Loading please wait...</i></strong></div>";

Modal::end();

?>


<?php

$script = <<<JS

/* for rippel effect */
var links = document.querySelectorAll('a , button');

for (var i = 0, len = links.length; i < len; i++) {
  links[i].addEventListener('click', function (e) {
    var targetEl = e.target;
    var inkEl = targetEl.querySelector('.ink');

    if (inkEl) {
      inkEl.classList.remove('animate');
    }
    else {
      inkEl = document.createElement('span');
      inkEl.classList.add('ink');
      inkEl.style.width = inkEl.style.height = Math.max(targetEl.offsetWidth, targetEl.offsetHeight) + 'px';
      targetEl.appendChild(inkEl);
    }

    inkEl.style.left = (e.offsetX - inkEl.offsetWidth / 2) + 'px';
    inkEl.style.top = (e.offsetY - inkEl.offsetHeight / 2) + 'px';
    inkEl.classList.add('animate');
  }, false);
}
/* end */ 


$(".show_assessment").click(function() {
  $('#modalAssessment').removeAttr('tabindex');
  $('#modalAssessment').modal('show')
      .find('#loadModalAssessment')
      .load($(this).attr('data-href'));
});


 $('.tt').click(function() {
    $('.bb').submit();
 });
 
 
$("#modal").removeAttr("tabindex");
 
//$(".left_col").niceScroll("#contentscroll2",{cursorcolor:"#F00",cursoropacitymax:0.7,boxzoom:true,touchbehavior:true});  // Second scrollable DIV


JS;

$this->registerJs($script);

?>
<?php $this->endBody() ?>

<script>

 

$(document).ready(function(){
           var scroll= $(".chatback").scrollTop();
           scroll= scroll+ 500;
           $('html, body').animate({
               scrollTop: scroll
           }, 1000);

       });

$(document).ready(function() {

$('.reply').on('click', function(){

$('textarea').focus();
});

function setHeight() {
windowHeight = $(window).innerHeight() - 150;
$('.chatback').css('min-height', windowHeight);

};
setHeight();

$(window).resize(function() {
setHeight();
});
});
</script>

</body>
</html>
<?php $this->endPage() ?>
