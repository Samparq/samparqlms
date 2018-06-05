<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 1/9/17
 * Time: 9:24 AM
 */


use yii\helpers\Html;
use kartik\grid\GridView;
use kartik\export\ExportMenu;
use yii\helpers\Url;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel common\models\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Users';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="user-index">
   
    <?php if(Yii::$app->session->hasFlash('rolesAssigned')): ?>
        <div class="alert alert-success alert-dismissible show hideAlertBox" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <strong>Congrats! </strong> Role has been assigned successfully.
        </div>

    <?php endif; ?>
    <?php if(Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success alert-dismissible show hideAlertBox" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <strong>Congrats! </strong> <?= Yii::$app->session->getFlash('success') ?>
        </div>

    <?php endif; ?>
    <h1><?= Html::encode($this->title) ?></h1>
    <?= Html::beginForm(['/'], 'post', ['id' => 'checkBoxForm']) ?>
    <?= Html::input('hidden', 'checkedValue', '', ['id' => 'checkedValue']) ?>
    <?= Html::endForm() ?>

    <div class="clearfix"></div>


    <?php


    $gridColumns = [
        ['class' => 'kartik\grid\CheckboxColumn'],
        ['class' => 'yii\grid\SerialColumn'],
        [
            'class'=>'kartik\grid\EditableColumn',
            'attribute'=>'employee_id',

            'editableOptions'=>[
                'header'=>'Employee Id',
                'inputType'=>\kartik\editable\Editable::INPUT_TEXT,
            ],
        ],
        [
            'class'=>'kartik\grid\EditableColumn',
            'attribute'=>'username',

            'editableOptions'=>[
                'header'=>'Username',
                'inputType'=>\kartik\editable\Editable::INPUT_TEXT,
            ],
        ],[
            'class'=>'kartik\grid\EditableColumn',
            'attribute'=>'client_code',

            'editableOptions'=>[
                'header'=>'Client Name',
                'inputType'=>\kartik\editable\Editable::INPUT_TEXT,
            ],
        ],
        [
            'class'=>'kartik\grid\EditableColumn',
            'attribute'=>'email',

            'editableOptions'=>[
                'header'=>'email',
                'inputType'=>\kartik\editable\Editable::INPUT_TEXT,
            ],
        ],
        [
            'class'=>'kartik\grid\EditableColumn',
            'attribute'=>'remark',
            'label' => 'Remark',
            'value' => function($model){
                    return empty($model->remark) === true ? "no remarks" : $model->remark;
            },
            'editableOptions'=>[
                    'header' => 'Remark',
                'inputType'=>\kartik\editable\Editable::INPUT_TEXTAREA,

            ],
        ],
        [
            'class'=>'kartik\grid\EditableColumn',
            'attribute'=>'flag',
            'label' => 'Status',
            'value' => function($model){
                $type = '';
                if($model->flag === "BLOCKED"){ $type = "BLOCKED"; }
                elseif($model->flag === "ACTIVE"){ $type = "ACTIVE"; }
                elseif($model->flag === "INACTIVE"){ $type = "INACTIVE"; }
                elseif($model->flag === "PENDING"){ $type = "PENDING"; }
                return $type;
            },
            'editableOptions'=>[
                'data' => Yii::$app->samparq->getUserStatusList(),
                'header'=>'flag',
                'inputType'=>\kartik\editable\Editable::INPUT_DROPDOWN_LIST,
            ],
        ],

        'branch',
        'department',
        'designation',
        ['class' => 'yii\grid\ActionColumn',
            'header' => 'Action',
            'contentOptions' => ['style' => 'width: 8.7%'],
            'buttons'=>[
                'view'=>function ($url, $model) {
                    return Html::a(
                            '<i class="fa fa-eye" aria-hidden="true"></i>', 
                            Url::to(['/user/view', 'id' => Yii::$app->samparq->encryptUserData($model->id), 'type' => Yii::$app->samparq->encryptUserData('ls')]),
                            ['class' => 'btn btn-danger btn-xs']);
                },
                'update'=>function ($url, $model) {
                    return false;
                },
                'delete'=>function ($url, $model) {
                    return false;
                },
            ],
        ],
        // 'mobile_permission',
        // 'imei_app',
        // 'app_regid:ntext',
        // 'employee_id',
        // 'dob',
        // 'image_path',
        // 'image_name',
        // 'mobile_no',
        // 'key',
        // 'update_status',
        // 'form_submit',
        // 'email_conf:email',
        // 'super_admin',
        // 'lastlogin_time',
        // 'test',
        // 'auth_token:ntext',
    ];

    echo ExportMenu::widget([
        'dataProvider' => $dataProvider,
        'columns' => $gridColumns
    ]);

    echo GridView::widget([
        'pjax'=>true,
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => $gridColumns
    ]);
?>
</div>

<?php

$script = <<<JS

$(".hideAlertBox").fadeOut(3000, function() {
  $(this).remove();
});

$(".checkbox-action").click(function() {
    var getCheckedBoxes = $("#w4").yiiGridView('getSelectedRows').toString();
    var formAction = $(this).data('url');
    console.log(formAction);
    $("#checkBoxForm").attr("action", formAction)
    $("#checkedValue").val(getCheckedBoxes);
    $("#checkBoxForm").submit();
});

function getLimit(val){
    $("#gLimit").val(val);
}


$('body').on('change',"#gLimit", function() {
    var currentVal = $(this).val();
    
    $("#test").submit();
    $(document).on('ready pjax:success', function() {
         getLimit(currentVal);
    });
});



JS;

$this->registerJs($script);

?>
