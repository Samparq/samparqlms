<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 7/10/17
 * Time: 5:56 PM
 */
use yii\widgets\ActiveForm;
use kartik\widgets\Select2;
use yii\helpers\Html;

?>

<section class="content">
    <div class="mailboxadiin">
        <div class="row">
            <div class="col-xs-12">  <h1>send notification</h1> </div>
            <div class="col-xs-12">
               
                <div class="mailboxsidebzr whitebox ">
                    <?php $form = ActiveForm::begin([
                        'id' => 'composeForm',
                    ]); ?>

                    <?= $form->field($model, 'user_id')->widget(Select2::classname(), [
                        'data' => $userType,
                        'options' => ['placeholder' => 'To', 'multiple' => true],
                        'pluginOptions' => [
                            'id' => 'senderList',
                            'tags' => true,
                            'tokenSeparators' => [',', '']
                        ],
                    ])->label('To'); ?>
                    <?= $form->field($model, 'title')->textInput()->label('Subject'); ?>
                    <?= $form->field($model, 'description')->textarea([
                        'rows' => 7,
                    ]); ?>
                    <div class="msgBox" style="color:green"></div>


                    <div class="form-group" style="margin-top: 20px;">
                        <?= Html::submitButton('Send', ['class' => 'btn btn-primary btnorange marginright', 'data-type' => 'sent']) ?>
                    </div>

                    <?php ActiveForm::end(); ?>

                </div>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>
</section>


<?php
$script = <<<JS
 $(".msgBox").html('<i>Allowed characters 150 remaining 150</i>');
$("#trainingnotification-description").on('keyup',function() {
    var text = $(this).val();
    var getLength = text.replace(/\s/g, '').length;
    var balance = 150 - getLength;
    if(balance <= 0){
        balance = 0;
    }
    $(".msgBox").html('<i>Allowed characters 150 remaining '+balance+'</i>');
});

JS;

$this->registerJs($script);



?>