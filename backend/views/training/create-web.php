<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 16/10/17
 * Time: 2:48 PM
 */
use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use dosamigos\ckeditor\CKEditor;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
?>

    <div class="container">
        <div class="row">
            <?php $form = ActiveForm::begin(); ?>
            <div class="col-md-12">
                <div class="x_title">
                    <?= Html::submitButton('Save & Close', ['class' => 'btn btn-info pull-right'])?>
                    <div class="clearfix"></div>
                </div>
                <div class="clearfix"></div>
            </div>

            <div class="col-md-8">
                <div class="col-md-12">

                    <?= $form->field($traineesModel, 'user_id')->widget(Select2::classname(), [
                        'data' => $uList,
                        'options' => ['placeholder' => 'Add Trainees', 'multiple' => true],
                        'pluginOptions' => [
                            'id' => 'senderList',
                            'tokenSeparators' => [',', '']
                        ],
                    ])->label('Add Trainees'); ?>
                </div>
                <div class="col-md-12">

                    <?= $form->field($traineesModel, 'training_id')->widget(Select2::classname(), [
                        'data' => $trainingList,
                        'options' => ['placeholder' => 'Select Training'],
                        'pluginOptions' => [
                            'id' => 'senderList',
                        ],
                    ])->label('Select Training'); ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($trainingModel, 'welcome_template')->widget(CKEditor::className(), [
                        'preset' => 'full'
                    ]) ?>
                </div>
                <div class="col-md-12">
                    <?= $form->field($trainingModel, 'thanks_template')->widget(CKEditor::className(), [
                        'preset' => 'full'
                    ]) ?>
                </div>
            </div>
            <div class="col-md-3">
                <div class="col-md-12">
                    <label>Enable back button:</label>
                    <div class="statusBtn">
                        <div class="s-switch-on" id="allow_prev_on" data-type="on" data-input="allow_prev"><span>On</span></div>
                        <div class="s-switch-off" id="allow_prev_off" data-type="off" data-input="allow_prev"><span>Off</span></div>
                        <?= $form->field($trainingModel, 'allow_prev')->hiddenInput(['value' => '0'])->label(FALSE); ?>
                        <div class="clearfix"></div>
                    </div>
                </div>
                <div class="clearfix"></div>
                <br/>
                <br/>
                <br/>
                <div class="col-md-12">
                    <label>Enable OTP authentication:</label>
                    <div class="statusBtn">
                        <div class="s-switch-on" id="enable_otp_on" data-type="on" data-input="enable_otp"><span>On</span></div>
                        <div class="s-switch-off" id="enable_otp_off" data-type="off" data-input="enable_otp"><span>Off</span></div>
                        <?= $form->field($trainingModel, 'enable_otp')->hiddenInput(['value' => '0'])->label(FALSE); ?>
                        <div class="clearfix"></div>
                    </div>
                </div>
                <div class="clearfix"></div>
                <br/>
                <br/>
                <br/>
                <div class="col-md-12">
                    <label>Show Result:</label>
                    <div class="statusBtn">
                        <div class="s-switch-on" id="show_result_on" data-type="on" data-input="show_result"><span>On</span></div>
                        <div class="s-switch-off" id="show_result_off" data-type="off" data-input="show_result"><span>Off</span></div>
                        <?= $form->field($trainingModel, 'show_result')->hiddenInput(['value' => '0'])->label(FALSE); ?>
                        <div class="clearfix"></div>
                    </div>
                </div>
                <br/>
                <br/>
                <br/>
                <br/>
                <div class="col-md-12">
                    <label>Show ansersheet:</label>
                    <div class="statusBtn">
                        <div class="s-switch-on" id="allow_answer_on" data-type="on" data-input="allow_answer"><span>On</span></div>
                        <div class="s-switch-off" id="allow_answer_off" data-type="off" data-input="allow_answer"><span>Off</span></div>
                        <?= $form->field($trainingModel, 'allow_answer')->hiddenInput(['value' => '0'])->label(FALSE); ?>
                        <div class="clearfix"></div>
                    </div>
                </div>
                <br/>
                <br/>
                <br/>
                <br/>
                <div class="col-md-12">
                    <label>Allow to print answer sheet:</label>
                    <div class="statusBtn">
                        <div class="s-switch-on" id="allow_print_answersheet_on" data-type="on" data-input="allow_print_answersheet"><span>On</span></div>
                        <div class="s-switch-off" id="allow_print_answersheet_off" data-type="off" data-input="allow_print_answersheet"><span>Off</span></div>
                        <?= $form->field($trainingModel, 'allow_print_answersheet')->hiddenInput(['value' => '0'])->label(FALSE); ?>
                        <div class="clearfix"></div>
                    </div>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>


<?php

$script = <<<JS



$(".s-switch-on").click(function(){
        var getInput = $(this).data("input");
         var getInputId = "training-"+getInput;
         $("#"+getInputId).val(1);
         
            $("#"+getInput+"_on").css({
                "left":"109px","transition":"0s ease-out"
                });
                $("#"+getInput+"_off").css({"left":"53px","transition":"0.6s ease-out"});
            });

        $(".s-switch-off").click(function(){
        var getInput = $(this).data("input");
        var getInputId = "training-"+getInput;
        $("#"+getInputId).val(0);
         
        $("#"+getInput+"_off").css({"left":"-56px","transition":"0s ease-out"});
        $("#"+getInput+"_on").css({"left":"0px","transition":"0.6s ease-out"});
        });
        

JS;

$this->registerJs($script);


?>
