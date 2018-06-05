<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 30/4/18
 * Time: 10:56 AM
 */

use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\Html;

?>

<section class="content">
    <div class="mailboxadiin">
        <div class="row" >
        <div class="col-md-12">
        <h1>bulk registration</h1>
        </div>
            
            <div class="col-md-9">
                <div class="mailboxsidebzr whitebox">
                    <?php $form = ActiveForm::begin([
                        'id' => 'composeForm',
                    ]); ?>

                    <?= $form->field($model, 'email_arr')->widget(Select2::classname(), [
                        'data' => [],
                        'options' => ['placeholder' => 'Bulk Email', 'multiple' => true],
                        'pluginOptions' => [
                            'tags' => true,
                            'tokenSeparators' => [',', '']
                        ],
                    ])->label('Bulk Email (Email)'); ?>
                    <h4>OR</h4>
                    <?= $form->field($model, 'email_csv')->fileInput()->label('Bulk Email (CSV file)'); ?>

            </div> 

                    <div class="form-group" style="margin-top: 20px;">
                        <?= Html::submitButton('Submit', ['class' => 'btn btn-primary btnorange marginright']) ?>
                    </div>

                    <?php ActiveForm::end(); ?>

                </div>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>
</section>
