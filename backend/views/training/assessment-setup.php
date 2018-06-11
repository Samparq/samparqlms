<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 21/11/17
 * Time: 2:30 PM
 */

use yii\bootstrap\ActiveForm;
use kartik\widgets\Select2;
use kartik\file\FileInput;
use yii\helpers\Url;
use yii\helpers\Html;

?>
    <div class="row">
        <div class="col-md-12">
            <ul class="cd-breadcrumb">
                <li class="next">
                    <a href="javascript:void(0)">Training</a></li>
                <li class="next"><a href="<?= Url::toRoute(['user/add-group-members', 'tid' => Yii::$app->samparq->encryptUserData($model->id)])?>">Group Management</a></li>
                <li class="active"> <a href="<?= Url::toRoute(['create-training', 'tid' => Yii::$app->samparq->encryptUserData($model->id) ])?>">ADD Trainees/Attachment</a></li>
                <li class="current" href="javascript:void(0)"> <a>Step 4: Assessment/Quiz Setting</a></li>
                <li class="next" > <a >Add/Update Questions</a></li>
            </ul>
            <h1>Step 4: Assessment/Quiz Setting</h1>
            <?php $form = ActiveForm::begin([
                'id' => 'setupForm',
                'enableAjaxValidation' => true
            ]); ?>
            <fieldset>
                 
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h2>Basic settings</h2>
                    </div>
                    <div class="panel-body">                               

                        <section class="content">

                            <div class="mailboxadiin">
                                <div class="row">
                                    <div class="col-xs-12">
                                        <div class="mailboxsidebzr">
                                        <div class="row">
                                            <?php if ($model->assessment_type == 0) { ?>
                                                <div class="col-md-5">
                                                    <?= $form->field($model, 'assessment_type')->dropDownList($surveyType, ['prompt' => 'Select training mode']) ?>
                                                </div>
                                            <?php } else { ?>
                                                <div class="col-md-5 col-sm-8">
                                                    <label class="control-label" for="training-duration">Training
                                                        type</label>
                                                    <?= Html::textInput('temp', $model->assessment_type == 1 ? "Quiz mode" : "Survey", ['class' => 'form-control', 'disabled' => true]) ?>
                                                </div>

                                            <?php } ?>

                                            <div class="clearfix"></div>

                                            <div class="col-md-1">
                                                <?= $form->field($model, 'duration')->textInput() ?>
                                            </div>
                                            <div class="col-md-1 quizmode hide">
                                                <?= $form->field($model, 'pass_score')->textInput() ?>
                                            </div>


                                            <div class="clearfix"></div>

</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        </section>
                                            </div>
                </div>
                
                 
                <div class="panel panel-default">
                    <div class="panel-heading">
                    <h2>Advance Setting</h2>
                    </div>
                    <div class="panel-body">  
                        <section class="content">
                            <div class="mailboxadiin">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mailboxsidebzr">

                                          <div class="row">      
                                            <div class="col-xs-12 col-md-3">
                                                <?= $form->field($model, 'shuffle_question')->checkbox() ?>
                                            </div>
                                            <div class="col-xs-12 col-md-3">
                                                <?= $form->field($model, 'allow_prev')->checkbox()->label('Allow to go backward') ?>
                                            </div>
                                            <div class="col-xs-12 col-md-3 quizmode hide">
                                                <?= $form->field($model, 'show_result')->checkbox() ?>
                                            </div>
                                            <div class="col-xs-12 col-md-3 quizmode hide">
                                                <?= $form->field($model, 'download_report')->checkbox()->label('Allow to download certificate') ?>
                                            </div>
                                            <div class="col-xs-12 col-md-3">
                                                <?= $form->field($model, 'feedback_required')->checkbox() ?>
                                            </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        </section>
                    </div>
                </div>

            </fieldset>
            <div class="form-group">
                <?= Html::submitButton('Save & Next', ['class' => 'btn btn-primary btnorange marginright']) ?>
                <?= Html::a('Close & Exit',['view','id' => Yii::$app->samparq->encryptUserData($model->id) ],['class' => 'btn btn-danger']) ?>

            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>


<?php

$assessmentType = $model->assessment_type;

$script = <<<JS

if($assessmentType == 1 || $assessmentType == 0){
   $(".quizmode").removeClass("hide");
}

$('input[type=radio]').prop("checked", false);

$('#training-assessment_type').change(function() {
    var val = $(this).val();
    console.log(val);
    if(val == 1){
        $(".quizmode").removeClass("hide");
    } else {
        $('input[type=radio]').prop("checked", false);
        $(".quizmode").addClass("hide");
    }
});


JS;


$this->registerJs($script);

?>