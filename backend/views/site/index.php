<?php

/* @var $this yii\web\View */


use yii\widgets\Pjax;
use yii\helpers\Html;

$this->title = 'Samparq';
?>

<div class="">
<div class="whitebox">
    <div class="row">
<div class="col-md-6">
<img src="<?= Yii::$app->request->baseUrl ?>/images/client_icon.png" alt="">
<div class="clientname">
<label>Client Name </label>
<h2><?= Yii::$app->samparq->getClientName(); ?> </h2>
</div>
</div>
<div class="col-md-2">
    <label>Subscription Stated On</label>
    <h3 style="color:#1c6f54; line-height: 1.508rem;"><?= date('d-M-Y', strtotime(Yii::$app->samparq->getClientDetails('subscription_sd'))); ?></h3>
</div>
<div class="col-md-2">
<label>Subscription Ended On</label>
    <h3 style="color:#e8313d; line-height: 1.508rem;"><?= date('d-M-Y', strtotime(Yii::$app->samparq->getClientDetails('subscription_ed'))); ?></h3>
</div>
<div class="col-md-2">
<label>Subscription Remaining</label>
    <h3 style="color:#0c6d92; line-height: 1.508rem;"><?= Yii::$app->samparq->getSubsriptionTime() ?> </h3>
</div>
</div>
<div class="clearfix"></div>
</div>
</div> 

<div class="clearfix"></div>
<div class="row" style="margin-top:20px">
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="text-left card tile_stats_count_active gradient-shadow-active">
            <img src="<?= Yii::$app->request->baseUrl ?>/images/user.png">
            <div class="rightpart">
                <span class="title">Active Users</span>
                <div class="number"><?= Yii::$app->samparq->getDashbaordData('active-users',$client_code) ?></div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="text-left card tile_stats_count_pendding gradient-shadow-pending">
            <img src="<?= Yii::$app->request->baseUrl ?>/images/user.png">
            <div class="rightpart">

                <span class="title"> Pending Users</span>
                <div class="number"><?= Yii::$app->samparq->getDashbaordData('pending-users',$client_code) ?></div>
            </div>

        </div>
    </div>
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="text-left card tile_stats_count_inactive gradient-shadow-inactive">
            <img src="<?= Yii::$app->request->baseUrl ?>/images/user.png">
            <div class="rightpart">
                <span class="title"> Inactive Users</span>
                <div class="number"><?= Yii::$app->samparq->getDashbaordData('inactive-users',$client_code) ?></div>
            </div>
        </div>

    </div>
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="text-left card tile_stats_count_block gradient-shadow-block">
            <img src="<?= Yii::$app->request->baseUrl ?>/images/user.png">
            <div class="rightpart">
                <span class="title"> Blocked Users</span>
                <div class="number"><?= Yii::$app->samparq->getDashbaordData('blocked-users',$client_code) ?></div>
            </div>
        </div>
    </div>
</div>

<?= $this->render('_comparison_chart', [
        'client_code' => $client_code
]); ?>

<div class="clearfix"></div>



