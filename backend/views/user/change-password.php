<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 1/9/17
 * Time: 5:32 PM
 */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>

<div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Change Password</h2>

            <div class="clearfix"></div>
        </div>
        <div class="x_content">
            <br>
            <?php $form = ActiveForm::begin([
                    'id' => 'change-password',
                    'class' => 'form-horizontal form-label-left'
            ]);?>

                <div class="form-group col-md-12">

                    <div class="col-md-4 col-sm-6 col-xs-12">
                        <?= $form->field($model, 'password')->passwordInput(['placeholder' => 'Password']);?>
                    </div>
                </div>
            <div class="form-group col-md-12">

                <div class="col-md-4 col-sm-6 col-xs-12">
                    <?= $form->field($model, 'confirm_password')->passwordInput(['placeholder' => 'Confirm Password']);?>
                </div>
            </div>

                <div class="form-group col-md-12">
                    <div class="col-md-6 col-sm-6 col-xs-12">
                        <button class="btn btn-primary" type="reset">Reset</button>
                        <?= Html::submitButton('Submit', ['class' => 'btn btn-danger'])?>
                    </div>
                </div>

           <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
