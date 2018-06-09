<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 25/9/17
 * Time: 9:20 AM
 */

use yii\bootstrap\ActiveForm;
use dosamigos\ckeditor\CKEditor;
use kartik\widgets\Select2;
use kartik\file\FileInput;
use yii\helpers\Url;
use yii\helpers\Html;
use dosamigos\fileupload\FileUploadUI;

?>
    <div class="row">
        <div class="col-md-12">
            <ul class="cd-breadcrumb">
                <li class="next">
                    <a href="javascript:void(0)">Training</a></li>
                <li class="active"><a
                            href="<?= Url::toRoute(['user/add-group-members', 'tid' => Yii::$app->samparq->encryptUserData($tid)]) ?>">Group
                        Management</a></li>
                <li class="current"><a href="javascript:void(0)">Step 3: ADD Trainees/Attachment</a></li>
                <li class="next"><a>Assessment/Quiz Setting</a></li>
                <li class="next"><a>Add/Update Questions</a></li>
            </ul>
            <h1>Step 3: Add Trainees/Add attachment</h1>
        </div>

        <div class="col-md-12">
            <div class="whitebox">
                <section class="content">
                    <div class="mailboxadiin">
                        <div class="row">
                            <div class="col-md-9">
                                <div class="mailboxsidebzr">
                                    <?php $form = ActiveForm::begin([
                                        'id' => 'composeForm',
                                    ]); ?>
                                    <?php
                                    $traineeList = Yii::$app->samparq->getTraineesList($tid);
                                    $traineeModel->user_id = $traineeList;
                                    ?>
                                    <div class="row">
                                        <div class="col-lg-4">
                                            <?= $form->field($traineeModel, 'user_id')->widget(Select2::classname(), [
                                                'data' => $uList,
                                                'options' => ['placeholder' => 'Add Trainees', 'multiple' => true],
                                                'pluginOptions' => [
                                                    'id' => 'senderList',
                                                    'tokenSeparators' => [',', '']
                                                ],
                                            ])->label('Add Trainees'); ?>
                                        </div>
                                    </div>

                                    <label class="control-label" for="notification-message">Add Material</label>
                                    <?= FileInput::widget([
                                        'model' => $tMaterial,
                                        'attribute' => 'new_name',
                                        'options' => [
                                            'multiple' => true,
                                        ],
                                        'pluginOptions' => [
                                            'uploadUrl' => Url::to(['/training/upload-files/']),
                                            'showRemove' => true,
                                            'initialPreviewShowDelete' => true,
                                            'uploadExtraData' => new \yii\web\JsExpression("function (previewId, index) {
                                                        return {
                                                            tid: $tid,
                                                        };
                                                         }"),
                                            'allowedFileExtensions' => ['mp4', 'pdf', 'xlsx', 'xls', 'doc', 'docx', 'ppt', 'pptx'],
                                            'initialPreviewAsData' => true,
                                            'showPreview' => true,
                                        ]
                                    ]); ?>
                                    <div class="error-msg" style="color: red"></div>
                                    <div class="clearfix"></div>

                                    <div class="alert alert-success fade in"
                                         style="background: #bce8f1; border: #bce8f1; color: #31708f">
                                        <strong>Note: </strong> Please consider bold keyword as Youtube Url ID,
                                        https://www.youtube.com/watch?v=<strong>PmFWzWbziz0</strong>
                                    </div>

                                    <?php $trainingModel->youtube_url = explode(',', $trainingModel->youtube_url) ?>
                                    <br>
                                    <?= $form->field($trainingModel, 'youtube_url')->widget(Select2::classname(), [
                                        'options' => ['placeholder' => 'Example: ruHMqQJ6PNE, bxuHJtQJ8XFG etc..', 'multiple' => true],
                                        'pluginOptions' => [
                                            'tokenSeparators' => [',', ''],
                                            'tags' => true,
                                            'allowClear' => true
                                        ],
                                        'pluginEvents' => [
                                            "select2:select" => "function() {tt()}",
                                            "select2:unselecting" => "function() {tt()}",
                                        ]
                                    ])->label('Add Youtube Ids'); ?>
                                    <div class="alert alert-danger hide" id="errorMessage" role="alert">
                                        <strong>Oops!</strong> Invalid youtube url id.
                                    </div>
                                    <?php if (!empty($material)): ?>
                                        <div class="attachment">
                                            <p>
                                                <span><i class="fa fa-paperclip"></i> <?= count($material); ?>
                                                    attachments with this training </span>
                                            </p>
                                            <br>


                                                    <?php foreach ($material as $mat): ?>


                                                        <?php if ($mat->type == 0) {  //pdf ?>
                                                            <div class="border-box">

                                                                <span> <img src="<?= Yii::getAlias('@web/images/pdf.png') ?>" alt="img"> pdf  </span>

                                                                <ul>
                                                                    <li id="box-<?= $mat->id ?>">
                                                                        <a href="#" class="atch-thumb">
                                                                            <i class="fa fa-paperclip" aria-hidden="true"></i>
                                                                            <?= $mat->original_name; ?>
                                                                            <?= Html::button('<i class="fa fa-trash-o" aria-hidden="true"></i> ', ['class' => 'btn btn-danger btn-xs box-remove', 'id' => $mat->id]) ?>
                                                                        </a>

                                                                    </li>
                                                                </ul>


                                                            </div>
                                                        <?php } elseif ($mat->type == 2) { //xls ?>
                                                            <div class="border-box">

                                                                <span> <img src="<?= Yii::getAlias('@web/images/pdf.png') ?>" alt="img"> xls/xlx  </span>

                                                                <ul>
                                                                    <li id="box-<?= $mat->id ?>">
                                                                        <a href="#" class="atch-thumb">
                                                                            <i class="fa fa-paperclip" aria-hidden="true"></i>
                                                                            <?= $mat->original_name; ?>
                                                                            <?= Html::button('<i class="fa fa-trash-o" aria-hidden="true"></i> ', ['class' => 'btn btn-danger btn-xs box-remove', 'id' => $mat->id]) ?>
                                                                        </a>

                                                                    </li>
                                                                </ul>


                                                            </div>
                                                        <?php } elseif ($mat->type == 3) { //doc ?>
                                                            <div class="border-box">

                                                                <span> <img src="<?= Yii::getAlias('@web/images/pdf.png') ?>" alt="img"> doc/docx  </span>

                                                                <ul>
                                                                    <li id="box-<?= $mat->id ?>">
                                                                        <a href="#" class="atch-thumb">
                                                                            <i class="fa fa-paperclip" aria-hidden="true"></i>
                                                                            <?= $mat->original_name; ?>
                                                                            <?= Html::button('<i class="fa fa-trash-o" aria-hidden="true"></i> ', ['class' => 'btn btn-danger btn-xs box-remove', 'id' => $mat->id]) ?>
                                                                        </a>

                                                                    </li>
                                                                </ul>


                                                            </div>
                                                        <?php } elseif ($mat->type == 4) { //ppt ?>
                                                            <div class="border-box">

                                                                <span> <img src="<?= Yii::getAlias('@web/images/pdf.png') ?>" alt="img"> ppt/pptx  </span>

                                                                <ul>
                                                                    <li id="box-<?= $mat->id ?>">
                                                                        <a href="#" class="atch-thumb">
                                                                            <i class="fa fa-paperclip" aria-hidden="true"></i>
                                                                            <?= $mat->original_name; ?>
                                                                            <?= Html::button('<i class="fa fa-trash-o" aria-hidden="true"></i> ', ['class' => 'btn btn-danger btn-xs box-remove', 'id' => $mat->id]) ?>
                                                                        </a>

                                                                    </li>
                                                                </ul>


                                                            </div>
                                                        <?php } elseif ($mat->type == 1) { //mp4 ?>
                                                            <div class="border-box">

                                                                <span> <img src="<?= Yii::getAlias('@web/images/pdf.png') ?>" alt="img"> mp4  </span>

                                                                <ul>
                                                                    <li id="box-<?= $mat->id ?>">
                                                                        <a href="#" class="atch-thumb">
                                                                            <i class="fa fa-paperclip" aria-hidden="true"></i>
                                                                            <?= $mat->original_name; ?>
                                                                            <?= Html::button('<i class="fa fa-trash-o" aria-hidden="true"></i> ', ['class' => 'btn btn-danger btn-xs box-remove', 'id' => $mat->id]) ?>
                                                                        </a>

                                                                    </li>
                                                                </ul>


                                                            </div>
                                                        <?php } ?>

                                                    <?php endforeach; ?>


                                            <br>
                                        </div>
                                        <div class="alert alert-success hide" id="deletedMessageBox" role="alert">
                                            <strong>Hurray!</strong> Image deleted successfully.
                                        </div>

                                    <?php endif; ?>


                                    <?= Html::submitButton('Save & Next', ['class' => 'btn btn-primary btnorange marginright btnsubmit', 'name' => 'tt', 'value' => 'start_training', 'data-type' => 'sent']) ?>
                                    <?= Html::a('Close & Exit', ['view', 'id' => Yii::$app->samparq->encryptUserData($tid)], ['class' => 'btn btn-danger']) ?>


                                    <?php ActiveForm::end(); ?>

                                </div>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                </section>
            </div>
            <br>
        </div>


    </div>

<?php
$url = Url::toRoute(['image-delete']);
$script = <<<JS

var values = new Array();
$('.select2-selection__rendered').children('li').each(function() {
  var text = $(this).text();
  if (values.indexOf(text) === -1) {
    values.push(text);
  } else {
    //  Its a duplicate
    $(this).find('li').remove()
  }
  
  console.log(values);
});

$('#trainees-user_id').select2({
  
  templateResult: function(item){
        if(typeof item.children != 'undefined') {
            var i = $(item.element).find('option').length - $(item.element).find('option:selected').length;
            var e = $('<span class="trainees-user_id_optgroup'+(i ? '' : ' trainees-user_id_optgroup_selected')+'">'+item.text+'</span>');
    
            e.click(function() {
                $('#trainees-user_id').find('optgroup[label="' + $(this).text() + '"] option').prop(
                    'selected',
                    $(item.element).find('option').length - $(item.element).find('option:selected').length
                );
                $('#trainees-user_id').change();
                $('#trainees-user_id').select2('close');
            });
    
            return e;
           }
        return item.text;
    }
   
});

function tt(){
            $(".field-training-youtube_url").find(".select2-selection__rendered li").each(function(i,e){
                var urlPattern =  /(http|https):\/\/(\w+:{0,1}\w*)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%!\-\/]))?/; 
                var getTitle = $(this).attr("title");
                if(urlPattern.test(getTitle)){
                    $(this).remove();
                    $("#errorMessage").removeClass("hide");
                    setTimeout(function() {
                      $("#errorMessage").addClass("hide");
                    }, 3500);
                }
                if(getTitle == ''){
                            $(this).remove();
                    }
                });
            }
            
            
            $(".select2-selection__rendered").keyup(function() {
               tt();
            });

            $(".select2").change(function() {
               tt();
            });
        $('.btn').click(function() {
            var type = $(this).attr('data-type');
            if(type == 'sent'){
                $('#status').val(1);
            } else {
                $('#status').val(3);
            }
         });

$('#composeForm').on('beforeSubmit', function(event, jqXHR, settings) {
              tt();  
});
  
    $('#trainingmaterial-new_name').on('filebatchselected', function(event, file, previewId, index, reader) {
       var senderList = $('#inbox-mail_to').val();
       if(senderList === null || senderList === 'undefined'){
           $('.fileinput-remove-button').trigger('click');
           $('.error-msg').html('Please select To first');
            return false;
       } else {
            $('.error-msg').html('');
           $('.fileinput-upload-button').trigger('click');
       }
     
    });
    
    
    $('#trainingmaterial-new_name').on('fileuploaded', function(event, data, previewId, index) {
       var senderList = $('#inbox-mail_to').val();
       if(senderList === null || senderList === 'undefined'){
            $('.fileinput-remove-button').trigger('click');
             $('.error-msg').html('Please select To first');
           return false;
       } else {
         $('.error-msg').html('');
         
         $(".input-group").find(".file-caption-name").remove();
          
     setTimeout(function() {
           $('.fileinput-remove-button').hide();
         }, 200);
        $('.fileinput-upload').hide();
        var filename = $('#'+previewId).find('.file-footer-caption').attr('title');
        $('#'+previewId).find('.kv-file-remove').attr('id',filename);
       }
     
    });
    
     $('#trainingmaterial-new_name').on('filebatchselected', function(event, file, previewId, index, reader) {
         setTimeout(function() {
           $('.fileinput-remove-button').hide();
         }, 200);
        $('.fileinput-upload').hide();
       $('.fileinput-upload-button').trigger('click');
    });
    
    $('#trainingmaterial-new_name').on('fileuploaded', function(event, data, previewId, index) {
     setTimeout(function() {
           $('.fileinput-remove-button').hide();
         }, 200);
        $('.fileinput-upload').hide();
        var filename = $('#'+previewId).find('.file-footer-caption').attr('title');
        $('#'+previewId).find('.kv-file-remove').attr('id',jQuery.parseJSON(data.jqXHR.responseText)['files'][0].id);
    });
    
   
  
  
       $('body').on('click','.kv-file-remove',function() {
           $("#deletedMessageBox").addClass("hide");
           var id =  $(this).attr('id');
           $.ajax({
            type:'post',
            url:"$url",
            dataType:"json",
            data:{
                id:id
            },
            success:function() {
              $("#deletedMessageBox").removeClass("hide");
                setTimeout(function() {
                      $("#deletedMessageBox").addClass("hide");
                }, 2000);
            }
       
           });
        
       });
       
        $('body').on('click','.box-remove',function() {
            $("#deletedMessageBox").addClass("hide");
           var id =  $(this).attr('id');
           var boxId = $("#box-"+id);
           $.ajax({
            type:'post',
            url:"$url",
            dataType:"json",
            data:{
                id:id
            },
            success:function() {
                boxId.fadeOut(500);
                 $("#deletedMessageBox").removeClass("hide");
                setTimeout(function() {
                      $("#deletedMessageBox").addClass("hide");
                }, 2000);
            }
       
           });
        
       });
JS;


$this->registerJs($script);

?>