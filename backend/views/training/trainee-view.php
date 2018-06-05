<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 29/9/17
 * Time: 1:48 PM
 */


use yii\helpers\Html;
use yii\widgets\Pjax;
use kartik\export\ExportMenu;
use kartik\grid\GridView;
use kartik\form\ActiveForm;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\TraineesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Trainees Results';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="trainees-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>


    <div class="clearfix"></div>
    <div class="pull-right">
        <?=Html::beginForm(['trainees-view'],'get', ['data-pjax' => '','id' => 'testt']);?>
        <?=Html::textInput('global',isset($_GET['global']) ? $_GET['global'] : '',['class'=>'form-control','id' => 'globalSearch' ,'placeholder' => 'Global Search'])?>
        <?= Html::endForm();?>
    </div>
    <?php  $gridColumns = [
        ['class' => 'yii\grid\SerialColumn'],
        [
            'header' => 'Submitted By',
            'value' => function($model){
                return $model->userDetails->name;
            }
        ],
        [
            'header' => 'Trainer name',
            'value' => function($model){
                return Yii::$app->samparq->getTrainerName($model->training_id);
            }
        ],
        [
            'header' => 'Training title',
            'value' => function($model){
                return Yii::$app->samparq->getTrainingTitle($model->training_id);
            }
        ],
        [
            'header' => 'Assessment Type',
            'value' => function($model){
                return $model->trainingDetails->assessment_type == 2 ? "Survey" : "Quiz";
            }
        ],
        [
            'header' => 'Start Time',
            'value' => function($model){
                return Yii::$app->samparq->getStartSubmissionTime($model->training_id, $model->training_submitted_by);
            }
        ],
        [
            'header' => 'End Time',
            'value' => function($model){
                return Yii::$app->samparq->getEndSubmissionTime($model->training_id,$model->training_submitted_by);
            }
        ],
        [
            'header' => 'Total marks',
            'value' => function($model){
                return $model->trainingDetails->assessment_type == 1 ? Yii::$app->samparq->calculateTotalMarks($model->training_id) : "N/A";
            }
        ],
        [
            'header' => 'Total time',
            'value' => function($model){
                return Yii::$app->samparq->getSubmissionTime($model->training_id,$model->training_submitted_by);
            }
        ],
        [
            'header' => 'Average time',
            'value' => function($model){
                return Yii::$app->samparq->getSubmissionAverageTime($model->training_id,$model->training_submitted_by);
            }
        ],
        [
            'header' => 'Marks obtained',
            'value' => function($model){
                return $model->trainingDetails->assessment_type == 1 ? Yii::$app->samparq->getMarksObtained($model->training_id,$model->training_submitted_by) : "N/A";
            }
        ],
        [
            'header' => 'Overall %',
            'format' => 'raw',
            'value' => function($model){
                if($model->trainingDetails->assessment_type == 1){
                    $percentage = Yii::$app->samparq->calculateTotalMarks($model->training_id) == 0 ?  0 : round(Yii::$app->samparq->getMarksObtained($model->training_id,$model->training_submitted_by)/Yii::$app->samparq->calculateTotalMarks($model->training_id)*100, 2);
                    return  $percentage>=50?"<span style='color:green;'>".$percentage."%</span>":"<span style='color: red'>".$percentage."%</span>";
                } else {
                    return "N/A";
                }
            }
        ],

        ['class' => 'yii\grid\ActionColumn',
            'header' => 'Action',
            'contentOptions' => ['style' => 'width: 8.7%'],
            'buttons'=>[
                'view'=>function ($url, $model) {
                    return Html::a('<i class="fa fa-eye" aria-hidden="true"></i>', ['training/trainee-result', 'tid' => Yii::$app->samparq->encryptUserData($model->training_id),'trainee_id' => Yii::$app->samparq->encryptUserData($model->training_submitted_by)], ['class' => 'btn btn-danger btn-xs']);
                },
                'update'=>function ($url, $model) {
                    return false;
                },
                'delete'=>function ($url, $model) {
                    return false;
                },
            ],
        ]
    ]?>

    <?= ExportMenu::widget([
        'filterModel' => $searchModel,
        'dataProvider' => $dataProvider,
        'columns' => $gridColumns,
    ]); ?>

    <?= GridView::widget([
        'filterModel' => $searchModel,
        'dataProvider' => $dataProvider,
        'columns' => $gridColumns,
        'pjax' => true
    ]); ?>
</div>


<?php

$script = <<<JS
$(".filters").remove();
function getLimit(val){
    $('optgroup').contents().unwrap();
    $("#gLimit").val(val);
}

$('optgroup').contents().unwrap();

$('#globalSearch').focusout(function() {
  $("#testt").submit();
});

$('body').on('change',"#gLimit", function() {
    var currentVal = $(this).val();
    
    $("#test").submit();
    $(document).on('ready pjax:success', function() {
        console.log(currentVal);
         getLimit(currentVal);
    });
});

JS;

$this->registerJs($script);

?>