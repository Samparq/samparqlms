<?php

use yii\bootstrap\ActiveForm;
use dosamigos\ckeditor\CKEditor;
use kartik\widgets\Select2;
use kartik\file\FileInput;
use yii\helpers\Url;
use yii\helpers\Html;
use dosamigos\fileupload\FileUploadUI;

?>
    <section class="content">

        <?php if(Yii::$app->session->hasFlash('notification')): ?>
        <br/>
        <br/>
        <br/>

        <div class="alert alert-success">
            <strong>Success!</strong> <?= Yii::$app->session->getFlash('notification'); ?>.
        </div>

        <?php endif; ?>
        <div class="mailboxadiin">
            <div class="row">
                <div class="col-lg-12">
                <h1>Push Notification</h1>  
                </div>
            </div>
            <div class="row">
                <div class="col-xs-6">
                   
                    <div class="mailboxsidebzr whitebox">
                        <?php $form = ActiveForm::begin([
                            'id' => 'indexForm'
                        ]); ?>
                        
                        <div class="row">
                <div class="col-xs-4">
                        <?= $form->field($model, 'user_ids')->widget(Select2::classname(), [
                            'data' => $uList,
                            'options' => ['placeholder' => 'Add Users', 'multiple' => true],
                            'pluginOptions' => [
                                'id' => 'senderList',
                                'tokenSeparators' => [',', '']
                            ],
                        ])->label('Add Users'); ?>

                        </div>
                        </div>
                        <div class="row">
                <div class="col-xs-6">
                        <?= $form->field($model, 'text')->textarea()->label('message') ?>
                        <i class="msgBox"></i>
                        <br/>
                        </div>
                        </div>
                        
                        </div>
                        <?= Html::submitButton('Send', ['class' => 'btn btn-md btn-primary disableButton'])?>

                        <?php ActiveForm::end(); ?>

                    
                </div>
            </div>
            <div class="clearfix"></div>
        </div>
    </section>



<?php

$script = <<<JS
 var checkForErrors = function(eventOn){
    var err = 0;
    $(eventOn).each(function(i,e){
        if($(this).text() != ""){
            err++;
        }
        console.log(err);
    });
    return err;
};


    $(".msgBox").html('<i>Allowed characters 300 remaining 300</i>');
    $("#pushnotification-text").on('keyup',function() {
    var text = $(this).val();
    var getLength = text.replace(/\s/g, '').length;
    var balance = 300 - getLength;
    if(balance <= 0){
        balance = 0;
    }
    $(".msgBox").html('<i>Allowed characters 300 remaining '+balance+'</i>');
});





$('#pushnotification-user_ids').select2({
  
  templateResult: function(item){
        if(typeof item.children != 'undefined') {
            var i = $(item.element).find('option').length - $(item.element).find('option:selected').length;
            var e = $('<span class="trainees-user_id_optgroup'+(i ? '' : ' pushnotification-user_ids_optgroup_selected')+'">'+item.text+'</span>');
    
            e.click(function() {
                $('#pushnotification-user_ids').find('optgroup[label="' + $(this).text() + '"] option').prop(
                    'selected',
                    $(item.element).find('option').length - $(item.element).find('option:selected').length
                );
                $('#pushnotification-user_ids').change();
                $('#pushnotification-user_ids').select2('close');
            });
    
            return e;
           }
        return item.text;
    }
   
});

var smartSubmit = function(eventOn, text, status, time) {
    setTimeout(function(){
        $(eventOn).text(text);
        $(eventOn).attr("disabled", status);
    }, time);

};

$(document).ready(function () {

    $('.disableButton').on('click', function() {
        var getText = $(this).text();
        smartSubmit('.disableButton', "processing....", true);
        setTimeout(function(){
            if(checkForErrors(".help-block")>0){
                smartSubmit('.disableButton', getText, false);
            }
        }, 500);
        smartSubmit('.disableButton', getText, false, 50000);
    });

});

$(".select2-container").css({
    width:"auto"
});
JS;


$this->registerJs($script);


?>