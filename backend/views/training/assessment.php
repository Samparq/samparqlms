<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 24/9/17
 * Time: 2:51 PM
 */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\widgets\DateTimePicker;
use kartik\widgets\Select2;

/* @var $this yii\web\View */
/* @var $model backend\models\Training */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="row">
    <div class="col-xs-12">
        <div class="panel panel-info">
            <!-- <div class="panel-heading">    <h2>Select Training</h2> </div> -->
            <div class="panel-body">
                <div class="training-form">

                    <?php $form = ActiveForm::begin([
                        'id' => 'trainForm',
                        'enableAjaxValidation' => true,
                    ]); ?>

                    <div class="col-md-6 col-sm-10">
                        <?= $form->field($model, 'training_id')->widget(Select2::classname(), [
                            'data' => Yii::$app->samparq->getTrainingList($type),
                            'options' => ['placeholder' => 'Select Training'],
                            'pluginOptions' => [
                                'id' => 'senderList',
                            ],
                        ])->label('Select Training'); ?>

                    </div>
                    <div class="clearfix"></div>
                    <div class="form-group">
                        <?= Html::submitButton('Next', ['class' => 'pull-right btn btn-primary']) ?>
                   </div>

                    <?php ActiveForm::end(); ?>

                </div>
            </div>
        </div>
    </div>
</div>


