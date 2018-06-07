<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\widgets\DateTimePicker;
use kartik\select2\Select2;
use dosamigos\ckeditor\CKEditor;

?>

<div class="row">
    <div class="col-md-12">

        <ul class="cd-breadcrumb">
            <li class="current"><a href="#0">Step 1: Create Training</a></li>
            <li class="next"><a>Group Management</a></li>
            <li class="next"> <a>Add Trainees/Attachment</a></li>
            <li class="next"> <a>Assessment/Quiz Setting</a></li>
            <li class="next"> <a >Add/Update Questions</a></li>
        </ul>

        <h1>Step 1: Create Training</h1>
        <div class="">
            <div class="">

                <?php $form = ActiveForm::begin([
                    'id' => 'trainForm',
                    'enableAjaxValidation' => true,
                    'options' => [
                        'enctype' => 'multipart/form-data',
                    ]
                ]); ?>

                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h2>Basic settings</h2>
                    </div>
                    <div class="panel-body">
                        <?= $form->field($model, 'trainer_name')->widget(Select2::classname(), [
                            'data' => Yii::$app->samparq->getTrainingUserList(),
                            'value' => Yii::$app->user->can('instructor') ? Yii::$app->user->identity->email : '',
                            'options' => ['placeholder' => 'Trainer Name'],
                            'pluginOptions' => [
                                'id' => 'senderList',
                            ],
                        ])->label('Trainer Name'); ?>


                        <?= $form->field($model, 'training_title')->textInput(['maxlength' => true]) ?>
                        <?= $form->field($model, 'description')->widget(CKEditor::className(), [
                            'options' => ['rows' => 6, 'placeholder' => 'Trainer Name'],
                            'preset' => 'basic',

                        ]) ?>
                    </div>
                </div>

                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h2>Training settings</h2>
                    </div>
                    <div class="panel-body">
                        <div class="col-md-6">
                             
                                <?= $form->field($model, 'start_date')->widget(DateTimePicker::classname(), [
                                    'options' => [
                                        'placeholder' => 'Select start date ...'
                                    ],
                                    'pluginOptions' => [
                                        'autoclose' => true,
                                        'startDate' => date('Y-m-d H:i')
                                    ]
                                ]); ?>
                            
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'end_date')->widget(DateTimePicker::classname(), [
                                'options' => ['placeholder' => 'Select end date ...'],
                                'pluginOptions' => [
                                    'autoclose' => true,
                                    'startDate' => date('Y-m-d H:i')
                                ]
                            ]); ?>
                        </div>

                        <?= $form->field($model, 'file_new_name')->fileInput() ?>
                    </div>
                </div>

                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h2>Assessment/Survey time settings</h2>
                    </div>
                    <div class="panel-body">
                        <div class="col-md-6">

                            <div class="row">
                                <?= $form->field($model, 'training_sd')->textInput(['placeholder' => 'Start Date','autocomplete' => 'off']); ?>
                            </div>

                        </div>
                        <div class="col-md-6">
                            <?= $form->field($model, 'training_ed')->textInput(['placeholder' => 'End Date','autocomplete' => 'off']); ?>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <?= Html::submitButton('Save & Next', ['class' => 'pull-right btn btn-primary']) ?>
                </div>

                <?php ActiveForm::end(); ?>

            </div>
        </div>
    </div>
</div>
<?php

$date = date('2012-12-01 14:20');
$script = <<<JS
$("#training-end_date").change(function() {
  var endDate = $(this).val(),
      startDate = $("#training-start_date").val();
  $('#training-training_sd').datetimepicker({
startDate: startDate,
endDate:endDate,
todayHighlight: false,
autoclose:true
});
});

$('#training-training_sd').change(function() {
  var target = $(this).val(),
      endTargetVal = $("#training-end_date").val();
  $('#training-training_ed').datetimepicker({
startDate: target,
endDate:endTargetVal,
todayHighlight: false,
autoclose:true
});
});


setTimeout(function() {
  $("#cke_21").remove();
}, 65);

JS;

$this->registerJs($script);


?>


