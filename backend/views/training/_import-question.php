<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 9/4/18
 * Time: 11:17 AM
 */

use kartik\form\ActiveForm;
use yii\helpers\Html;
use kartik\select2\Select2;

?>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-info">
            <div class="panel-body">
                <div class="training-form">

                    <?php $form = ActiveForm::begin([
                        'id' => 'trainForm',
                        'enableAjaxValidation' => true,
                    ]); ?>

                    <div class="col-md-6">
                        <?= $form->field($model, 'import_id')->widget(Select2::classname(), [
                            'data' => $trainingList,
                            'options' => ['placeholder' => 'Select Training'],
                            'pluginOptions' => [
                                'id' => 'senderList',
                            ],
                        ])->label('Select Training'); ?>



                    </div>
                    <div class="clearfix"></div>
                    <div class="form-group">
                        <?= Html::submitButton('Import', ['class' => 'pull-right btn btn-primary']) ?>
                    </div>

                    <?php ActiveForm::end(); ?>

                </div>
            </div>
            <div class="panel-footer">Note: Select training in which you want to import questions</div>
        </div>
    </div>
</div>

