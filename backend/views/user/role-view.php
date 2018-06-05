<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 6/10/17
 * Time: 12:59 PM
 */


use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\TraineesSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Role Management';
$this->params['breadcrumbs'][] = $this->title;
?>
    <div class="trainees-index">

        <h1><?= Html::encode($this->title) ?></h1>
        <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

        <div class="pull-left ">
            <?= Html::a('Assign Role', ['assign-role'], ['class' => 'btn gradient-shadow-btn btn-primary']) ?>

        </div>

        <?php Pjax::begin(); ?>
        <div class="pull-left">
            <?=Html::beginForm(['role-view'],'post', ['data-pjax' => '','id' => 'test','class'=>'slectroll']);?>
            <?php
            $arr = ["all" => "All"];
            $newArr = Yii::$app->samparq->getRole(1);


            ?>
            <?=Html::dropDownList('action','',[$arr,$newArr],['class'=>'form-control', 'id' => 'gLimit','prompt' => 'Select Role'])?>
            <?= Html::endForm();?>
        </div>
        <div class="clearfix"></div>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                [
                    'attribute' => 'name',
                    'value' => function($model){
                        return $model->user->name;
                    }
                ],

                ['class' => 'yii\grid\ActionColumn',
                    'header' => 'Action',
                    'contentOptions' => ['style' => 'width: 8.7%'],
                    'buttons'=>[
                        'view'=>function ($url, $model) {
                            return Html::a('<i class="fa fa-eye" aria-hidden="true"></i>', ['user-role-view', 'item_name' => Yii::$app->samparq->encryptUserData($model->item_name),'uid' => Yii::$app->samparq->encryptUserData($model->user_id)], ['class' => 'btn btn-danger btn-xs']);
                        },
                        'update'=>function ($url, $model) {
                            return false;
                        },
                        'delete'=>function ($url, $model) {
                            return false;
                        },
                    ],
                ]
            ],
        ]); ?>
        <?php Pjax::end(); ?>
    </div>


<?php

$script = <<<JS

function getLimit(val){
    $('optgroup').contents().unwrap();
    $("#gLimit").val(val);
}

$('optgroup').contents().unwrap();

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