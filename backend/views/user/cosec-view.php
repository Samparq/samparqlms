<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 1/9/17
 * Time: 9:24 AM
 */


use yii\helpers\Html;
use kartik\grid\GridView;
use kartik\export\ExportMenu;
use yii\helpers\Url;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel common\models\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Users';
$this->params['breadcrumbs'][] = $this->title;
?>
<style>
    .rc-handle {
        position: absolute;
        width: auto !important;
    }
</style>
<div class="user-index">
    <br/>
    <br/>
    <br/>

    <h1><?= Html::encode($this->title) ?></h1>

    <?php if(Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success alert-dismissible show hideAlertBox" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <strong>Congrats! </strong> <?= Yii::$app->session->getFlash('success') ?>
        </div>

    <?php endif; ?>
    
    <div class="pull-right">
        <?= Html::a("Sync from cosec server", "http://samparq.qdegrees.com/api/sync-cosec-data", ['class' => 'btn btn-primary'])?>
    </div>
    <div class="clearfix"></div>


    <?php


    $gridColumns = [
        ['class' => 'yii\grid\SerialColumn'],
         'employee_id',
         'name',
         'gender',
         'email',
         'aadhar_no',
         'phone',
         'pan',
         [
             'attribute' => 'active',
             'header' => 'Employment Status',
             'value' => function($model){
                   return $model->active === 1 ? "Active" : "Inactive";
             }
         ],
         'dob',
         'joining_date',
         'confirmation_date',
         'marital_status',
         'branch',
         'designation',
         'department',
         'category',
         'reporting_in_charge1',
         'reporting_in_charge2',
    ];

    echo ExportMenu::widget([
        'dataProvider' => $exportDataProvider,
        'columns' => $gridColumns
    ]);

    echo GridView::widget([
        'pjax'=>true,
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => $gridColumns
    ]);
    ?>
</div>

<?php

$script = <<<JS

$(".hideAlertBox").fadeOut(3000, function() {
  $(this).remove();
});

function getLimit(val){
    $("#gLimit").val(val);
}


$('body').on('change',"#gLimit", function() {
    var currentVal = $(this).val();
    
    $("#test").submit();
    $(document).on('ready pjax:success', function() {
         getLimit(currentVal);
    });
});



JS;

$this->registerJs($script);

?>
