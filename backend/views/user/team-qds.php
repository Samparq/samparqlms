<?php

use yii\helpers\Html;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use kartik\daterange\DateRangePicker;
use kartik\export\ExportMenu;
use kartik\grid\GridView;
use yii\widgets\ActiveForm;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\TrainingSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'TeamQds';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="training-index">
    <br/>
    <br/>
    <br/>
    <?php if(Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success alert-dismissible" id="showAlertBox" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <strong>Success!</strong> <?= Yii::$app->session->getFlash('success') ?>
        </div>
    <?php endif; ?>

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
    <?php $form = ActiveForm::begin([
        'enableAjaxValidation' => true
    ]); ?>
        <div class="panel panel-info">
        <div class="panel-heading"><strong>Note:</strong> Use comma while adding multiple email. <strong>(For example: abc@example.com,xyz@example.com)</strong></div>
            <div class="panel-body">
                <div class="col-sm-12">
                    <div class="col-sm-3">
                        <?= $form->field($teamQdsModel, 'email')->textInput()->label(false); ?>
                    </div>
                    <div class="col-sm-3">
                        <?= Html::submitButton('Add', ['class' => 'btn btn-md btn-primary']) ?>
                    </div>

                </div>


            </div>


        </div>
    <?php ActiveForm::end(); ?>
    </p>

    <?php
    $gridColumns = [

        //'id',
        [
            'class' => 'yii\grid\SerialColumn',
        ],
        [
            'class'=>'kartik\grid\EditableColumn',
            'attribute'=>'email',

            'editableOptions'=>[
                'header'=>'email',
                'inputType'=>\kartik\editable\Editable::INPUT_TEXT,
            ],
        ],

        ['class' => 'yii\grid\ActionColumn',
            'header' => 'Action',
            'contentOptions' => ['style' => 'width: 8.7%'],
            'buttons'=>[
                'view'=>function ($url, $model) {
                    return  false;
                },
                'update'=>function ($url, $model) {
                    return false;
                },
                'delete'=>function ($url, $model) {
                    return  Html::a('<span class="glyphicon glyphicon-trash"></span>', Url::to(['/user/delete-team', 'id' => $model->id]), ['class' => 'btn btn-danger btn-xs']);

                },
            ],
        ]
    ]

    ?>
    <?= ExportMenu::widget([
        'dataProvider' => $exportDataProvider
    ]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => $gridColumns,
    ]); ?>

</div>



