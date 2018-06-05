<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \common\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Login';
$this->params['breadcrumbs'][] = $this->title;


?>
<style>
    .field-loginform-rememberme {
        display: inline-block;
        margin-right: 40px;
    }
</style>

<div>
    <div class="login_wrapper">
        <div class="form login_form">
            <section class="login_content">
                <?php $form = ActiveForm::begin(); ?>
                <h1> <img src="<?= Yii::getAlias('@web/images/samparq-logo.png')?>" style="max-width: 200px;margin-top: -15px"/></h1>
                <div style="text-align: left">
                    <?= $form->field($model, 'client_code')->textInput(['autofocus' => false, 'class' => 'form-control'])->label(false) ?>

                </div>
                <div style="text-align: left">
                        <?= $form->field($model, 'username')->textInput(['autofocus' => false, 'class' => 'form-control'])->label(false) ?>

                    </div>
                    <div style="text-align: left">
                        <?= $form->field($model, 'password')->passwordInput(['class' => 'form-control'])->label(false) ?>
                    </div>
                    <div>
                        <?= Html::submitButton('Login', ['class' => 'btn btn-danger', 'name' => 'login-button' ]) ?>

                        <?= $form->field($model, 'rememberMe')->checkbox() ?>
                    </div>

                    <div class="clearfix"></div>

                    <div class="separator">

                        <div>
                            <p>© <?= date('Y') ?> All Rights Reserved.Powered by <a href="http:www.qdegrees.com" target="_blank">QDegrees</a>. Privacy and Terms</p>
                        </div>
                    </div>
                <?php ActiveForm::end(); ?>
            </section>
        </div>

    </div>
</div>