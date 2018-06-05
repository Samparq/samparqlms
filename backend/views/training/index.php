<?php

use yii\helpers\Html;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use kartik\daterange\DateRangePicker;
use kartik\export\ExportMenu;
use kartik\grid\GridView;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\TrainingSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Trainings';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="training-index">
 
    <?php if(Yii::$app->session->hasFlash('success')): ?>
    <div class="alert alert-success alert-dismissible" id="showAlertBox" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        <strong>Success!</strong> <?= Yii::$app->session->getFlash('success') ?>
    </div>
    <?php endif; ?>
    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Training', ['/training/create'], ['class' => 'btn btn-primary']) ?>
     </p>

    <?php
    $gridColumns = [
        //'id',
        'trainer_name',
        'training_title',
        [
            'attribute'=>'totalQuestion',
            'label'=>'Total Question',
            'format'=>'raw',
            'value'=>function($data){
                return Yii::$app->samparq->getTotalQuestionCount($data->id);
            }
        ],
        [
            'attribute'=>'start_date',
            'label'=>'Start Date',
            'filter' => DateRangePicker::widget([
                'model' => $searchModel,
                'attribute' => 'start_date',
                'convertFormat' => true,
                'pluginOptions' => [
                    'separator'=>'to',
                    'locale' => [
                        'format' => 'Y-m-d'
                    ],
                ],
            ]),
            'value'=>function($data){
                return date("M d Y h:i:s A", strtotime($data->start_date));
            }
        ],
        [
            'attribute'=>'end_date',
            'label'=>'End Date',
            'filter' => DateRangePicker::widget([
                'model' => $searchModel,
                'attribute' => 'end_date',
                'convertFormat' => true,
                'pluginOptions' => [
                    'separator'=>'to',
                    'locale' => [
                        'format' => 'Y-m-d'
                    ],
                ],
            ]),
            'value'=>function($data){
                return date("M d Y h:i:s A", strtotime($data->end_date));
            }
        ],
        [
            'attribute'=>'status',
            'label'=>'Status',
            'format'=>'raw',
            'value'=>function($data){
                if(Yii::$app->samparq->checkDisability($data->end_date, 2) === true){
                    $val = "<span style='color: red'>Expired</span>";
                } else {
                    if($data->status ==0){
                        $val = "<span style='color: blue'>New</span>";
                    } elseif ($data->status == 1) {
                        $val = "<span style='color: green'>Questions Added & Saved</span>";
                    } elseif ($data->status == 2){
                        $val = "<span style='color: yellow'>Completed</span>";
                    }
                }

                return $val;
            }
        ],

        ['class' => 'yii\grid\ActionColumn',
            'header' => 'Action',
            'contentOptions' => ['style' => 'width: 8.7%'],
            'template' => '{view}{export}',

            'buttons'=>[
                'view'=>function ($url, $model) {
                    return  Html::a('<span class="glyphicon glyphicon-eye-open"></span>', Url::to(['/training/view', 'id' => Yii::$app->samparq->encryptUserData($model->id)]), ['title' => 'View','class' => 'btn btn-danger btn-xs']);
                },
                'export'=>function ($url, $model) {
                    return  Html::button('<span class="glyphicon glyphicon-import"></span>', ['data-url' => Url::to(['/training/training-question', 'id' => Yii::$app->samparq->encryptUserData($model->id)]), 'title' => 'Import questions','class' => 'btn btn-danger btn-xs showModal']);
                }
            ],
        ]
    ]
    ?>
    <?= ExportMenu::widget([
        'dataProvider' => $exportDataProvider,
        'target'=> ExportMenu::TARGET_SELF,
        'showConfirmAlert'=>false,
    ]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => $gridColumns,
    ]); ?>

    <?php  Modal::begin([
        'header' => '<h2>Import Questions</h2>',
        'id' => 'modalCont'
    ]);

    echo '<div class="modalInner"></div>';

    Modal::end();

    ?>

</div>

<?php

$script = <<<JS

    $("#modalCont").removeAttr("tabindex");

   $(document).on('click',".showModal", function() {
       var getUlr = $(this).data("url");
 
       
       $("#modalCont")
       .modal("show")
       .find(".modalInner")
       .load(getUlr);
   });

JS;

$this->registerJs($script);



?>
