<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 22/5/18
 * Time: 5:00 PM
 */

use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\export\ExportMenu;
use kartik\grid\GridView;
use kartik\form\ActiveForm;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\TraineesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Trainees Query';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="trainees-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>


    <div class="clearfix"></div>
    <div class="pull-right">
        <?= Html::beginForm(['trainees-view'], 'get', ['data-pjax' => '', 'id' => 'testt']); ?>
        <?= Html::textInput('global', isset($_GET['global']) ? $_GET['global'] : '', ['class' => 'form-control', 'id' => 'globalSearch', 'placeholder' => 'Global Search']) ?>
        <?= Html::endForm(); ?>
    </div>
    <?php $gridColumns = [
        ['class' => 'yii\grid\SerialColumn'],
        'message',
        [
            'attribute' => 'training_id',
            'label' => 'Training Title',
            'value' => function ($model) {
                return Yii::$app->samparq->getTrainingTitle($model->training_id);
            }
        ],
        [
            'attribute' => 'sender_id',
            'label' => 'Sender Name',
            'value' => function ($model) {
                return Yii::$app->samparq->getUsernameById($model->sender_id);
            }
        ],
        [
            'header' => 'Total Queries',
            'value' => function ($model) {
                return Yii::$app->samparq->getQueryCount($model->training_id, $model->sender_id);
            }
        ],
        [
            'header' => 'Total Unread Queries',
            'value' => function ($model) {
                $count = Yii::$app->samparq->getUnreadQueryCount($model->training_id, $model->sender_id);
                return $count == 0 ? "No new query" : $count;
            }
        ],
        ['class' => 'yii\grid\ActionColumn',
            'header' => 'View Queries',
            'contentOptions' => ['style' => 'width: 8.7%'],
            'buttons' => [
                'view' => function ($url, $model) {
                    return Html::a('<i class="fa fa-eye" aria-hidden="true"></i>', ['training/chat-view', 'tid' => Yii::$app->samparq->encryptUserData($model->training_id), 'uid' => Yii::$app->samparq->encryptUserData($model->sender_id), 'rid' => Yii::$app->samparq->encryptUserData($model->receiver_id)], ['class' => 'btn btn-danger btn-xs']);
                },
                'update' => function ($url, $model) {
                    return false;
                },
                'delete' => function ($url, $model) {
                    return false;
                },
            ],
        ]
    ] ?>

    <?= ExportMenu::widget([
        'filterModel' => $searchModel,
        'dataProvider' => $dataProvider,
        'columns' => $gridColumns,
    ]); ?>

    <?= GridView::widget([
        'filterModel' => $searchModel,
        'dataProvider' => $dataProvider,
        'columns' => $gridColumns,
        'pjax' => true
    ]); ?>
</div>


<?php

$script = <<<JS
$(".filters").remove();
function getLimit(val){
    $('optgroup').contents().unwrap();
    $("#gLimit").val(val);
}

$('optgroup').contents().unwrap();

$('#globalSearch').focusout(function() {
  $("#testt").submit();
});

$('body').on('change',"#gLimit", function() {
    var currentVal = $(this).val();
    
    $("#test").submit();
    $(document).on('ready pjax:success', function() {
        console.log(currentVal);
         getLimit(currentVal);
    });
});

JS;

$this->registerJs($script);

?>
