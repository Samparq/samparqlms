<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 30/3/18
 * Time: 9:48 AM
 */


use yii\widgets\ActiveForm;
use yii\helpers\Html;

?>

<div class="row">
    <div class="col-md-12">
        <h1>Create Group</h1>
        <div class="panel panel-info">
            <div class="panel-body">

                <section class="content">

                    <div class="mailboxadiin">
                        <div class="row">
                            <div class="col-md-9">
                                <div class="mailboxsidebzr">
                                    <?php $form = ActiveForm::begin([
                                        'id' => 'composeForm',
                                        'enableAjaxValidation' => true
                                    ]); ?>

                                    <?= $form->field($model, 'name')?>

                                    <?= Html::submitButton('Create', ['class' => 'btn btn-primary'])?>

                                    <?php ActiveForm::end(); ?>

                                </div>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>