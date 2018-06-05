<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\Training */

$this->title = 'Create Training';
$this->params['breadcrumbs'][] = ['label' => 'Trainings', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="training-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
