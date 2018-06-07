<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 24/9/17
 * Time: 6:05 PM
 */


use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use wbraganca\dynamicform\DynamicFormWidget;
use kartik\select2\Select2;
use yii\helpers\Url;
use yii\grid\GridView;
use kartik\date\DatePicker;


?>
<div class="col-md-12">
    <ul class="cd-breadcrumb">
        <li class="next">
            <a>Training</a></li>
        <li class="next"><a>Group Management</a></li>
        <li class="next" > <a>ADD TRAINEES/ADD ATTACHMENT</a></li>
        <li class="next" > <a href="<?= Url::toRoute(['assessment-setup', 'tid' => $tid])?>">Training Setting</a></li>
        <li class="current" > <a href="javascript:void(0)">Step 5: Add/Create Questions</a></li>
    </ul>
    <h1>Step 5: Add/Create Questions</h1>
    <div class="customer-form">
        <?= Html::a('Cancel & Exit',Url::toRoute(['training/view', 'id' => Yii::$app->samparq->encryptUserData($tid)]), ['class' => 'btn btn-danger pull-right']) ?>
        <?= Html::a('Add new',Url::toRoute(['create-question', 'tid' => Yii::$app->samparq->encryptUserData($tid)]), ['class' => 'btn btn-danger pull-right']) ?>

        <div class="clearfix"></div>
        <?php $form = ActiveForm::begin([
            'id' => 'dynamic-form'

        ]); ?>

        <?= $form->field($modelQuestion, 'is_submitted')->hiddenInput()->label(false); ?>
        <?= $form->field($trainingModel, 'training_question_status')->hiddenInput(['value' => 0])->label(false); ?>

        <div class="msg_box_cont">

        </div>
        <div class="panel panel-default">
            <div class="panel-heading"><h4>Add New Question</h4></div>
            <div class="panel-body">
                <div class="row">
                <div class="col-xs-12 col-md-3">
                    <?= $form->field($modelQuestion, 'type')->widget(Select2::classname(), [
                        'data' => $questionTypeListing,
                        'options' => ['placeholder' => 'Question Type'],
                        'pluginOptions' => [
                            'id' => 'senderList',
                        ],
                    ])->label('Select Question Type'); ?>

                </div>
                </div>
                <div class="row">
                <div class="col-xs-12 col-md-6">
                    <?= $form->field($modelQuestion, 'question')->textarea(['rows' => 2]); ?>
                </div>
                    </div>
            
                <div class="dynamicFromReq">
                    <?php DynamicFormWidget::begin([
                        'widgetContainer' => 'dynamicform_wrapper', // required: only alphanumeric characters plus "_" [A-Za-z0-9_]
                        'widgetBody' => '.container-items', // required: css class selector
                        'widgetItem' => '.item', // required: css class
                        'limit' => 4, // the maximum times, an element can be cloned (default 999)
                        'min' => 1, // 0 or 1 (default 1)
                        'insertButton' => '.add-item', // css class
                        'deleteButton' => '.remove-item', // css class
                        'model' => $modelsOption[0],
                        'formId' => 'dynamic-form',
                        'formFields' => [
                            'full_name',
                            'address_line1',
                            'address_line2',
                            'city',
                            'state',
                            'postal_code',
                        ],
                    ]); ?>

                    <div class="container-items"><!-- widgetContainer -->
                        <?php foreach ($modelsOption as $i => $modelOption): ?>
                            <div class="clearfix"></div>

                            <div class="item">
                                <div class="col-sm-12">
                                    <div style="border: solid 1px #eee; padding:15px 15px 10px 15px; margin-bottom: 15px;">
                                        <?php
                                        // necessary for update action.
                                        if (!$modelOption->isNewRecord) {
                                            echo Html::activeHiddenInput($modelOption, "[{$i}]id");
                                        }
                                        ?>
                                        <?= $form->field($modelOption, "[{$i}]max_marks")->hiddenInput(['value' => 1])->label(false); ?>
                                        <div class="col-sm-6 text_type">
                                            <?= $form->field($modelOption, "[{$i}]option_value")->textInput(['maxlength' => true])->label(false) ?>
                                        </div>


                                        <div class="col-sm-6" style="padding-top: 5px;">
                                            <button type="button" class="add-item btn btn-danger btn-xs"><i
                                                        class="glyphicon glyphicon-plus"></i></button>
                                            <button type="button" class="remove-item btn btn-danger btn-xs"><i
                                                        class="glyphicon glyphicon-minus"></i></button>

                                            <div class="clearfix"></div>
                                        </div>
                                        <div class="clearfix"></div>
                                            <div class="col-sm-4 chkforans" style="margin-top: -10px;">
                                                <?= $form->field($modelOption, "[{$i}]is_answer")->checkbox(['class' => 'is_answer','checked' => 'checked']) ?>
                                            </div>
                                        <div class="clearfix"></div>

                                    </div>

                                </div>

                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php DynamicFormWidget::end(); ?>

                </div>
 
                <div class="col-md-2 image_type hide">
                    <?=  $form->field($modelQuestion, 'image')->fileInput(); ?>
                </div>
               
                <?php if($trainingModel->assessment_type == 1): ?>
                        
                <div class="row">
                <div class="col-xs-12 col-md-2">

                        <div>
                            <?=  $form->field($modelQuestion, 'marks', [
                                'inputTemplate' => '<div class="input-group"><span class="input-group-addon indeBtn" data-type="dec" style="font-weight: bolder;cursor: pointer">-</span>{input}<span class="input-group-addon indeBtn" data-type="inc" style="font-weight: bolder;cursor: pointer">+</span></div>',
                                'inputOptions' => [
                                    'value' => 1,
                                ]
                            ]); ?>

                        </div>
                        </div>
                                </div>


                        <div>
                            <?= $form->field($modelQuestion, "has_negative")->checkbox(['class' => 'is_negative','checked' => 'checked']) ?>
                        </div>
                                
                                <div class="row">
                <div class="col-xs-12 col-md-2">                
                    <div class="tt hide">
                        <?=  $form->field($modelQuestion, "negative_mark", [
                            'inputTemplate' => '<div class="input-group"><span class="input-group-addon indeBtn" data-type="dec" style="font-weight: bolder;cursor: pointer">-</span>{input}<span class="input-group-addon indeBtn" data-type="inc" style="font-weight: bolder;cursor: pointer">+</span></div>',
                            'inputOptions' => [
                                'value' => 0,
                            ]
                        ]); ?>
                    </div>
                            </div>
                            </div>
                <?php endif; ?>
                          

            </div>

        </div>

        <?php if($trainingModel->assessment_type == 2): ?>

            <?=  $form->field($modelQuestion, "is_required")->checkbox([1 => 'Mandatory', 0 => 'Not Mandatory']); ?>
        <?php endif; ?>
        <div class="form-group pull-left">
            <?php if(isset($_GET['quid']) && !empty($_GET['quid']) && $showPrevButton === true){ ?>
                <?= Html::a('Previous',['create-question', 'quid' => (Yii::$app->samparq->encryptUserData(Yii::$app->samparq->decryptUserData($_GET['quid']) - 1)), 'tid' => Yii::$app->samparq->encryptUserData($tid), 'isUpdate' => "true"], ['class' => 'btn btn-primary final-submit']) ?>
            <?php } ?>
        </div>
        <div class="form-group pull-right">
            <?= Html::submitButton('Save & Next', ['class' => 'btn btn-primary ffg final-submit minOptions']) ?>
        </div>

        <br/>
        <br/>
        <br/>
        <div class="clearfix"></div>

        <?php ActiveForm::end(); ?>

    </div>
</div>
<div class="col-md-12">
    <div class="panel panel-default">
        <div class="panel-heading"><h4>Questions listing</h4></div>
        <div class="panel-body">
            <?php if(!empty($questions)){ ?>
                <div class="training-index">

                    <?= GridView::widget([
                        'dataProvider' => $dataProviderQuestion,
                        'filterModel' => $searchModelQuestion,
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],
                            'question',

                            ['class' => 'yii\grid\ActionColumn',
                                'header' => 'Action',
                                'contentOptions' => ['style' => 'width: 15%'],
                                'buttons'=>[
                                    'view'=>function ($url, $model) {

                                        return $model->status == 1?Html::a('Active', "javascript:void(0)", ['data-id' => $model->id,'id' => 'chngStatus-question-'.$model->id, 'data-status'=>'active','data-type' => 'question',  'class' => 'btn btn-danger btn-xs change-training-stat']):Html::a('Inactive', "javascript:void(0)", ['data-id' => $model->id,'id' => 'chngStatus-question-'.$model->id,'data-status'=>'inactive','data-type' => 'question',  'class' => 'btn btn-danger btn-xs change-training-stat']);

                                    },
                                    'update'=>function ($url, $model) {
                                        return Html::a('Edit', ["create-question", "tid" => Yii::$app->samparq->encryptUserData($model->training_id), "quid" => Yii::$app->samparq->encryptUserData($model->id), 'isUpdate' => 1], ['class' => 'btn btn-danger btn-xs']);

                                    },
                                    'delete'=>function ($url, $model) {
                                        return false;
                                    },
                                ],
                            ]
                        ],
                    ]); ?>
                </div>
            <?php } else { ?>
                <span>Questions not found</span>
            <?php } ?>
        </div>
    </div>
</div>
<?php

$trainingModel = $trainingModel->assessment_type;
$script = <<<JS

$(".final_submit").click(function() {
    $("#training-training_question_status").val(1);
});

$(".ffg").click(function() {
    $("#training-training_question_status").val(0);
    
});

function checkRquiredFields() {
   $('#dynamic-form').on('beforeSubmit', function (event, jqXHR, settings) {
    var tt = false;
    var getLength = $('input:checkbox:checked').length;
    var getOptionsLenght = $('.item').length;
          if(getLength>0){
              if(getOptionsLenght>1){
                 $(".msg_box_cont").html("");   
              }
              tt = true;
          } else {
              $(".msg_box_cont").html("");
              $(".msg_box_cont").html('<div class="alert alert-danger" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong>Warning!</strong> At least one answer is required to proceed further.</div>');
            
               $('html, body').animate({
                    scrollTop: $(".msg_box_cont").offset().top
                }, 200);
              tt = false;
              setTimeout(function() {
               $('.msg_box_cont').fadeOut(500, function() {
                    $(this).empty().show();
                });
              },2500);
          }
    
          if(getOptionsLenght>1){
             if(getLength>0){
                 $(".msg_box_cont").html("");   
             }
              tt = true;
          } else {
              $(".msg_box_cont").html("");
              $(".msg_box_cont").html('<div class="alert alert-danger" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong>Warning!</strong> Please add at least two options to proceed further.</div>');
            
               $('html, body').animate({
                    scrollTop: $(".msg_box_cont").offset().top
                }, 200);
              tt = false;
              setTimeout(function() {
               $('.msg_box_cont').fadeOut(500, function() {
                    $(this).empty().show();
                });
              },2500);
          }
          
          if(getOptionsLenght>1 && getLength >0){
              tt = true;
          } else {
              tt= false;
          }
    return tt;
    });

  if($(".is_negative").is(":checked")){  
            $(".tt").removeClass("hide");
        } 
function getId(checkLength,seq) {
    if(checkLength == 1){
           var id = "#neg";
       } else {
          var id = "#neg"+seq;
       }
       
       return id;

}


$("body").on("click",".indeBtn", function() {
    var getSeq = $(this).siblings("input").attr("id").replace ( /[^\d.]/g, '' );
    var curVal = $(this).siblings("input").val();
    var type = $(this).data("type");
    if(type==="inc"){
         var newVal = curVal === null || curVal === "" || curVal === "undefined"?parseInt(0)+1:parseInt(curVal)+1;   
    } else {
            var newVal = curVal === null || curVal === "" || curVal === "undefined"?parseInt(0)-1:parseInt(curVal)-1;
    }
    if(newVal === -1 || newVal < 0){
        newVal =0;
    }
    $(this).siblings("input").val(newVal);
    var setVal = $("#trainingquestion-marks").val();
    $("#options-0-max_marks").val(setVal);
    $("#options-"+getSeq+"-max_marks").val($("#options-0-max_marks").val());
});


function hideSeek(id,current,numberId,seq) {
        if($(current).is(":checked")){  

            $(".field-options-"+seq+"-negative_mark").parent(".tt").removeClass("hide");
        } else {
        
            $(".field-options-"+seq+"-negative_mark").parent(".tt").addClass("hide");
        
        }
}

$(".dynamicform_wrapper").on("limitReached", function(e, item) {
    alert("Max option limit is 4");
});



$("body").on("click",".is_negative",function() {
   if(this.checked){
       $(".tt").removeClass("hide");
        $('.tt').fadeIn(500, function() {
        $(this).show();
    });   
   } else {
        $('.tt').fadeOut(500, function() {
        $(this).hide();
    }); 
   }
    
});
}

function removeDynafield() {

  $(".col-sm-12").each(function() {
    $(".remove-item").trigger("click");
  })
}

if($("#trainingquestion-type").val() == 1 || $("#trainingquestion-type").val() == 3){
   

             $(".dynamicFromReq").removeClass("hide");  
                   checkRquiredFields();
  } else {
  $(".dynamicFromReq").addClass("hide");
        removeDynafield();
  }
  
  
$("#trainingquestion-type").change(function() {
  var val = $(this).val();

  if(val == 1 || val == 3){
      $(".dynamicFromReq").removeClass("hide"); 
          checkRquiredFields();
  } else {
      $(".dynamicFromReq").addClass("hide");
      removeDynafield();
  }
  
 
});

 if($trainingModel == 2){
    $(".image_type").removeClass("hide");   
  } else {
    $(".image_type").addClass("hide");      
  }

$('form').on('submit', function() {
        $('.is_submit').attr("disabled","disabled");
        var buttonVal = $(".is_submit").text();
       
        var form = $(this);
        setTimeout(function () {
            if(form.find('.has-error').length > 0) {
                $('.is_submit').removeAttr("disabled");
            }

        }, 5000);

       
        setTimeout(function () {
            if(form.find('.has-error').length == 0) {

                $('.is_submit').attr("disabled","disabled");
                $('.is_submit').text("processing");
            }

        }, 500000);

        setTimeout(function () {
            $('.is_submit').removeAttr("disabled");
            $('.is_submit').text(buttonVal);
        }, 500000);

    });
        
$(".is_submit").click(function() {
  $("#trainingquestion-is_submitted").val("1");
  $("#dynamic-form").submit();
});

$(".final-submit").click(function() {
  $("#trainingquestion-is_submitted").val("");  
});

if($trainingModel == 1){
   checkRquiredFields();
}






JS;


$this->registerJs($script);

?>