<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 30/3/18
 * Time: 9:56 AM
 */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Group Management';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="trainees-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php if(Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <strong>Congrats! </strong> <?= Yii::$app->session->getFlash('success') ?>.
        </div>

    <?php endif; ?>

    <?php if(Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <strong>Oops! </strong> <?= Yii::$app->session->getFlash('error') ?>.
        </div>

    <?php endif; ?>

    <?= Html::a('Add New Group', ['user/create-group'], ['class' => 'btn btn-primary'])?>

    <div class="clearfix"></div>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'id',
            'name',
            [
                'attribute' => 'members',
                'header' => 'Total Members',
                'value' => function($model){
                    $userArr = empty($model->members[0]) ? [] :explode(',',$model->members[0]->user_id);
                    return count($userArr);
                }
            ],

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{add-member}',
                'buttons' => [
                    'add-member' => function($url, $model, $key) {
                        return Html::a('<button class="btn btn-danger btn-xs"><i class="fa fa-plus"></i> <i class="fa fa-users"></i></button>', ['user/add-group-members', 'gid' => Yii::$app->samparq->encryptUserData($model->id)]);
                }
                ]
            ]

        ],
    ]); ?>
</div>