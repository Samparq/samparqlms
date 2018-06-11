<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use yii\grid\GridView;
use kartik\widgets\DateTimePicker;

/* @var $this yii\web\View */
/* @var $model backend\models\Training */

$this->title = Yii::$app->samparq->getTrainingTitle($model->id);
$this->params['breadcrumbs'][] = ['label' => 'Trainings', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="training-view">


    <div id="training_status_message"></div>
    <?php if (Yii::$app->session->hasFlash('importSuccess')): ?>
        <div class="alert alert-success alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <strong>Success!</strong> <?= Yii::$app->session->getFlash('importSuccess') ?>.
        </div>
    <?php endif; ?>
    <h1>Training Details</h1>
    <div class="chart-area">
        <?= $this->render('_question_submission_chart', [
            'tid' => Yii::$app->samparq->encryptUserData($tid),
            'type' => $model->assessment_type
        ]) ?>
    </div>
    
    <?php if ($disability == true) { ?>

        <?= Html::a("<i class='fa fa-ban'></i>  Expired ", "javascript:void(0)", ['class' => 'btn btn-danger ', 'disabled' => "disabled"]) ?>
        <?= Html::a("<i class='fa fa-graduation-cap'></i> " . $title, "javascript:void(0)", ['class' => 'btn btn-danger ', 'disabled' => true]) ?>
        <?= Html::a("<i class='fa fa-users'></i> Add Trainees/Study Materials", "javascript:void(0)", ['class' => 'btn btn-danger ', 'disabled' => true]) ?>
        <?= Html::a("<i class='fa fa-cogs'></i> Setting", "javascript:void(0)", ['class' => 'btn btn-danger ', 'disabled' => true]) ?>

    <?php } elseif ($availability == 0 || $availability == 2) { ?>

        <?php Html::a("<i class='fa fa-bullhorn'></i>  Publish Training ", ['manage-training', 'tid' => Yii::$app->samparq->encryptUserData($tid), 'type' => 7], ['class' => 'btn btn-danger  check_training_status']) ?>
        <?= Html::a("<i class='fa fa-bullhorn'></i>  Publish Training ","javascript:void(0)", ['data-id' => Yii::$app->samparq->encryptUserData($tid), 'class' => 'btn btn-danger  check_training_status']) ?>
        <?= Html::a("<i class='fa fa-graduation-cap'></i> " . $title, ['create-question', 'tid' => Yii::$app->samparq->encryptUserData($tid)] , ['class' => 'btn btn-danger ']) ?>
        <?= Html::a("<i class='fa fa-users'></i> Add Trainees/Study Materials", ["create-training", "tid" => Yii::$app->samparq->encryptUserData($tid), 'isUpdate' => true], ['class' => 'btn btn-danger ']) ?>
        <?= Html::a("<i class='fa fa-cogs'></i> Setting", ["assessment-setup", "tid" => Yii::$app->samparq->encryptUserData($tid), "fromView" => true], ['class' => 'btn btn-danger ']) ?>


    <?php } elseif ($availability == 1) { ?>

        <?= Html::a("<i class='fa fa-bullhorn'></i>  Stop Training ","javascript:void(0)", ['data-id' => Yii::$app->samparq->encryptUserData($tid), 'class' => 'btn btn-danger check_training_status pull-right']) ?>
        <?= Html::a("<i class='fa fa-graduation-cap'></i> " . $title, "javascript:void(0)", ['class' => 'btn btn-danger pull-right', 'disabled' => true]) ?>
        <?= Html::a("<i class='fa fa-users'></i> Add Trainees/Study Materials", "javascript:void(0)", ['class' => 'btn btn-danger pull-right', 'disabled' => true]) ?>
        <?= Html::a("<i class='fa fa-cogs'></i> Setting", "javascript:void(0)", ['class' => 'btn btn-danger pull-right', 'disabled' => true]) ?>


    <?php } ?>

<br><br>
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'trainer_name',
            'training_title',
            [
                'attribute' => 'start_date',
                'label' => 'Training start date',
                'value' => function ($data) {
                    return date("M d Y h:i:s A", strtotime($data->start_date));
                }
            ],
            [
                'attribute' => 'end_date',
                'format' => 'raw',
                'label' => 'Training end date',
                'value' => function ($data) {
                    return "<div class='dateColumn row' id='showDateField1'><div id='newDate1' class='col-md-3'>" . date("M d Y h:i:s A", strtotime($data->end_date)) . "</div> <div class='col-md-4'>" . Html::button('Edit', ['class' => 'btn btn-xs btn-danger updateDateField', 'data-field' => 'showDateField1', 'data-name' => 'end_date', 'data-id' => $data->id]) . "</div></div><div class='updateDate col-md-6 hide row' id='editDateField1'>" . DateTimePicker::widget([
                            //return "<div class='dateColumn row' id='showDateField'><div id='newDate' class='col-md-3'>".date("M d Y h:i:s A", strtotime($data->end_date))."</div> <div class='col-md-4'>".Html::button('Edit', ['class' => 'btn btn-xs btn-danger updateDateField','data-field' => 'showDateField', 'data-id' => $data->id, "disabled" => Yii::$app->samparq->checkDisability($data->end_date, 2) === true ? true : false])."</div></div><div class='updateDate col-md-6 hide row' id='editDateField'>".DateTimePicker::widget([
                            'name' => 'updateDate1',
                            'value' => $data->end_date,
                            'type' => DateTimePicker::TYPE_INPUT,
                            'pluginOptions' => [
                                'autoclose' => true
                            ]
                        ]) . "<br/> " . Html::a('Update', 'Javascript:void(0)', ['class' => 'btn btn-xs btn-danger updateDateField', 'data-name' => 'end_date', 'data-id' => Yii::$app->samparq->encryptUserData($data->id), 'data-field' => 'editDateField1']) . "</div>";
                }
            ],
            [
                'attribute' => 'training_sd',
                'format' => 'raw',
                'label' => 'Assessment start date',
                'value' => function ($data) {
                    return "<div class='dateColumn row' id='showDateField2'><div id='newDate2' class='col-md-3'>" . date("M d Y h:i:s A", strtotime($data->training_sd)) . "</div> <div class='col-md-4'>" . Html::button('Edit', ['class' => 'btn btn-xs btn-danger updateDateField', 'data-field' => 'showDateField2', 'data-name' => 'training_sd', 'data-id' => $data->id]) . "</div></div><div class='updateDate col-md-6 hide row' id='editDateField2'>" . DateTimePicker::widget([
                            //return "<div class='dateColumn row' id='showDateField'><div id='newDate' class='col-md-3'>".date("M d Y h:i:s A", strtotime($data->end_date))."</div> <div class='col-md-4'>".Html::button('Edit', ['class' => 'btn btn-xs btn-danger updateDateField','data-field' => 'showDateField', 'data-id' => $data->id, "disabled" => Yii::$app->samparq->checkDisability($data->end_date, 2) === true ? true : false])."</div></div><div class='updateDate col-md-6 hide row' id='editDateField'>".DateTimePicker::widget([
                            'name' => 'updateDate2',
                            'value' => $data->training_sd,
                            'type' => DateTimePicker::TYPE_INPUT,
                            'pluginOptions' => [
                                'autoclose' => true
                            ]
                        ]) . "<br/> " . Html::a('Update', 'Javascript:void(0)', ['class' => 'btn btn-xs btn-danger updateDateField', 'data-name' => 'training_sd', 'data-id' =>Yii::$app->samparq->encryptUserData($data->id), 'data-field' => 'editDateField2']) . "</div>";
                }
            ],
            [
                'attribute' => 'training_ed',
                'format' => 'raw',
                'label' => 'Assessment end date',
                'value' => function ($data) {
                    return "<div class='dateColumn row' id='showDateField3'><div id='newDate3' class='col-md-3'>" . date("M d Y h:i:s A", strtotime($data->training_ed)) . "</div> <div class='col-md-4'>" . Html::button('Edit', ['class' => 'btn btn-xs btn-danger updateDateField', 'data-field' => 'showDateField3', 'data-name' => 'training_ed', 'data-id' => $data->id]) . "</div></div><div class='updateDate col-md-6 hide row' id='editDateField3'>" . DateTimePicker::widget([
                            //return "<div class='dateColumn row' id='showDateField'><div id='newDate' class='col-md-3'>".date("M d Y h:i:s A", strtotime($data->end_date))."</div> <div class='col-md-4'>".Html::button('Edit', ['class' => 'btn btn-xs btn-primary updateDateField','data-field' => 'showDateField', 'data-id' => $data->id, "disabled" => Yii::$app->samparq->checkDisability($data->end_date, 2) === true ? true : false])."</div></div><div class='updateDate col-md-6 hide row' id='editDateField'>".DateTimePicker::widget([
                            'name' => 'updateDate3',
                            'value' => $data->training_ed,
                            'type' => DateTimePicker::TYPE_INPUT,
                            'pluginOptions' => [
                                'autoclose' => true
                            ]
                        ]) . "<br/> " . Html::a('Update', 'Javascript:void(0)', ['class' => 'btn btn-xs btn-danger updateDateField', 'data-id' => Yii::$app->samparq->encryptUserData($data->id), 'data-name' => 'training_ed', 'data-field' => 'editDateField3']) . "</div>";
                }
            ],
            [
                'attribute' => 'created_at',
                'value' => function ($data) {
                    return date("M d Y H:i:s A", strtotime($data->created_at));
                }
            ],
        ],
    ]) ?>

</div>

<div class="panel panel-default">

    <div class="panel panel-body">

        <div class="" role="tabpanel" data-example-id="togglable-tabs">
            <ul id="myTab" class="nav nav-tabs bar_tabs" role="tablist">
                <li role="presentation" class="active"><a href="#tab_content1" id="home-tab" role="tab"
                                                          data-toggle="tab" aria-expanded="true">Trainees List</a>
                </li>
                <li role="presentation" class=""><a href="#tab_content2" role="tab" id="profile-tab" data-toggle="tab"
                                                    aria-expanded="false">Material Provided</a>
                </li>
                <li role="presentation" class=""><a href="#tab_content3" role="tab" id="profile-tab" data-toggle="tab"
                                                    aria-expanded="false">Questions</a>
                </li>
            </ul>
            <div id="myTabContent" class="tab-content">
                <div role="tabpanel" class="tab-pane fade active in" id="tab_content1" aria-labelledby="home-tab">
                    <?php if (!empty($trainees)) { ?>

                        <div class="training-index">

                            <?= GridView::widget([
                                'dataProvider' => $dataProvider,
                                'filterModel' => $searchModel,
                                'columns' => [
                                    ['class' => 'yii\grid\SerialColumn'],
                                    'username',
                                    [
                                        'attribute' => 'progress',
                                        'format' => 'raw',
                                        'value' => function ($model) {
                                            return Yii::$app->samparq->getProgressBar($model->training_id, $model->user_id) == 0 ? 'N/A' : '<div class="bar-outer" style="width: 100%; height:20px; border:2px solid #3c763d;"><div class="bar-inner" style="width: ' . Yii::$app->samparq->getProgressBar($model->training_id, $model->user_id) . '%; background: #38b99a; height: 100%; text-align: center; color: white">' . Yii::$app->samparq->getProgressBar($model->training_id, $model->user_id) . '%</div></div>';
                                        }
                                    ],
                                    [
                                        'attribute' => 'result',
                                        'label' => 'Score',
                                        'value' => function ($model) {
                                            return Yii::$app->samparq->assessmentSubmitted($model->training_id, $model->user_id);
                                        }
                                    ],
                                    [
                                        'attribute' => 'certificate_download',
                                        'label' => 'Certificate',
                                        'format' => 'raw',
                                        'value' => function ($model) {
                                            return Yii::$app->samparq->checkIFCertification($model->training_id, $model->user_id);
                                        }
                                    ],
                                    ['class' => 'yii\grid\ActionColumn',
                                        'header' => 'Action',
                                        'contentOptions' => ['style' => 'width: 15%'],
                                        'buttons' => [
                                            'view' => function ($url, $model) {
                                                if ($model->status == 2) {
                                                    return Html::a('Submitted', "javascript:void(0)", ['class' => 'btn btn-info btn-xs', 'disabled' => true]);
                                                } else {
                                                    return $model->status == 1 ? Html::a('Active', "javascript:void(0)", ['data-id' => $model->id, 'id' => 'chngStatus-trainee-' . $model->id, 'data-status' => 'active', 'data-type' => 'trainee', 'class' => 'btn btn-danger btn-xs change-training-stat']) : Html::a('Inactive', "javascript:void(0)", ['data-id' => $model->id, 'id' => 'chngStatus-trainee-' . $model->id, 'data-status' => 'inactive', 'data-type' => 'trainee', 'class' => 'btn btn-danger btn-xs change-training-stat']);
                                                }
                                            },
                                            'update' => function ($url, $model) {
                                                return false;
                                            },
                                            'delete' => function ($url, $model) {
                                                return false;
                                            },
                                        ],
                                    ]
                                ],
                            ]); ?>
                        </div>
                    <?php } else { ?>
                        <span>Trainees not found</span>
                    <?php } ?>

                </div>
                <div role="tabpanel" class="tab-pane fade" id="tab_content2" aria-labelledby="profile-tab">
                    <?php if (!empty($materials)) { ?>
                        <ul class="messages">
                            <table class="data table table-striped no-margin">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Attachment Name</th>
                                    <th>Download</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php $count = 0;
                                foreach ($materials as $key => $material): $count++ ?>
                                    <tr>
                                        <td><?= $count ?></td>
                                        <td><?= $material->original_name ?></td>
                                        <td> <?= Html::checkbox('download_permission', $material->download_status, ['class' => 'download_permission', 'data-id' => $material->id]) ?></td>
                                        <td> <?= $material->status == 1 ? Html::a('Active', "javascript:void(0)", ['data-id' => $material->id, 'id' => 'chngStatus-material-' . $material->id, 'data-status' => 'active', 'data-type' => 'material', 'class' => 'btn btn-danger btn-xs change-training-stat']) : Html::a('Inactive', "javascript:void(0)", ['data-id' => $material->id, 'id' => 'chngStatus-material-' . $material->id, 'data-status' => 'inactive', 'data-type' => 'material', 'class' => 'btn btn-danger btn-xs change-training-stat']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </ul>
                    <?php } else { ?>
                        <span>Material not found</span>
                    <?php } ?>


                </div>
                <div role="tabpanel" class="tab-pane fade" id="tab_content3" aria-labelledby="question-tab">
                    <?php if (!empty($questions)) { ?>
                        <div class="training-index">

                            <?= GridView::widget([
                                'dataProvider' => $dataProviderQuestion,
                                'filterModel' => $searchModelQuestion,
                                'columns' => [
                                    ['class' => 'yii\grid\SerialColumn'],
                                    'id',
                                    'question',

                                    ['class' => 'yii\grid\ActionColumn',
                                        'header' => 'Action',
                                        'contentOptions' => ['style' => 'width: 15%'],
                                        'buttons' => [
                                            'view' => function ($url, $model) {

                                                return $model->status == 1 ? Html::a('Active', "javascript:void(0)", ['data-id' => $model->id, 'id' => 'chngStatus-question-' . $model->id, 'data-status' => 'active', 'data-type' => 'question', 'class' => 'btn btn-danger btn-xs change-training-stat']) : Html::a('Inactive', "javascript:void(0)", ['data-id' => $model->id, 'id' => 'chngStatus-question-' . $model->id, 'data-status' => 'inactive', 'data-type' => 'question', 'class' => 'btn btn-danger btn-xs change-training-stat']);

                                            },
                                            'update' => function ($url, $model) {
                                                return Html::a('Edit', ["create-question", "tid" => Yii::$app->samparq->encryptUserData($model->training_id), "quid" => Yii::$app->samparq->encryptUserData($model->id), 'isUpdate' => 1], ['class' => 'btn btn-danger btn-xs']);

                                            },
                                            'delete' => function ($url, $model) {
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

    </div>
</div>

<?php
Modal::begin([
    'header' => '<h4><i class="fa fa-eye" aria-hidden="true"></i> Preview</h4>',
    'id' => 'modal',
    'size' => 'modal-lg',
]);

echo "<div id='modalContent'></div>";
Modal::end();
?>


<?php

$pdfPreviewPath = Yii::$app->params['file_url'];
$url = Url::toRoute(['change-status']);
$dateUrl = Url::toRoute(['update-date']);
$checkStatusUrl = Url::toRoute(['check-training-status']);
$updateDownloadStatus = Url::toRoute(['update-download-status']);
$script = <<<JS

$(".download_permission").change(function() {
  var id = $(this).data('id');
  
   $.ajax({
        url:"$updateDownloadStatus",
        type:"POST",
        dataType:"json",
        data:{
            id:id,
            status:$(this).is(":checked") === true ? 1 : 0 
        },
        success:function(res) {
            console.log(res);
        },
        error:function() {
          console.log("something went wrong please try again later!");
        }
    });
  
 
});


$(".check_training_status").click(function() {
  var getId = $(this).data("id");
  
  $.ajax({
    url:"$checkStatusUrl",
    dataType:"json",
    type:"POST",
    data:{
        id:getId
    },
    success:function(data) {
      if(data.status == true){       
          $("#training_status_message").html('<div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong>danger!</strong>'+data.message+'</div>')
    
      } else {
          console.log("Something went wrong please try again later!");
      }
    }, error:function() {
      console.log("Something went wrong please try again later!");
    }
  });
});

$(".updateDateField").click(function() {
    

    var getId = $(this).data("field"),
        getKey = getId.replace(/[^0-9]/g, ''),
        field = $(this).data("name");
    
     console.log(field);
    if(getId === "showDateField"+getKey){
        $("#"+getId).addClass("hide");
        $("#editDateField"+getKey).removeClass("hide")
    } else {
                $("#"+getId).addClass("hide");
        $("#showDateField"+getKey).removeClass("hide");
          var updatedDate = $("input[name=updateDate"+getKey+"]").val();
  var id = $(this).data("id");
 
  $.ajax({
        url:"$dateUrl",
        type:"POST",
        dataType:"json",
        data:{
            id:id,
            date:updatedDate,
            field:field
        },
        success:function(res) {
            if(res.status === true){
                $("#newDate").html(res.newDate);
                  $("#dateSuccess").removeClass("hide");                
                setTimeout(function() {
                  $("#dateSuccess").addClass("hide");
                }, 7500);
            }
        },
        error:function() {
          console.log("something went wrong please try again later!");
        }
    });
    }

});

$('.showModelDetails').click(function ()
{
    var pdfFile = $(this).data('file');
    if(pdfFile)
    $('#modal').modal('show').find('#modalContent').html("<embed src='$pdfPreviewPath"+pdfFile+"#zoom=100' width='100%' height='400'></embed>");

});

$("body").on('click','.change-training-stat',function() {
  var currentStatus = $(this).text();
  var getDataId = $(this).data("id");
  var getDataType = $(this).data("type");
  var newStatus = "";
  var getId = $(this).attr("id");
  if(currentStatus === "Active"){
      newStatus = 0;
  } else {
      newStatus = 1;
    
  }
 
  
      $.ajax({
        url:"$url",
        type:"POST",
        dataType:"json",
        data:{
            id:getDataId,
            type:getDataType,
            status:newStatus
        },
        success:function(res) {
            console.log(res);
            if(res.st === true){
                if(res.newstat == "0"){
                         $("#"+getId).removeClass("btn-success");
                        $("#"+getId).addClass("btn-danger");
                        $("#"+getId).text("Inactive");
                } else {
                        $("#"+getId).removeClass("btn-danger");
                        $("#"+getId).addClass("btn-danger");
                        $("#"+getId).text("Active");
                }
                     
            } else {
                console.log("error");
            }
        },
        error:function() {
          console.log("something went wrong please try again later!");
        }
    });
  
});

JS;


$this->registerJs($script);

?>
