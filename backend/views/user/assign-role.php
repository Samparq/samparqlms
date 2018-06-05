<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 6/10/17
 * Time: 11:32 AM
 */

use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\Html;

?>
<section class="content">
        <div class="mailboxadiin">
            <div class="row">
                <div class="col-md-12 col-xs-12">
                    <div class="mailboxsidebzr whitebox">
                        <?php $form = ActiveForm::begin([
    'id' => 'composeForm',
]); ?>
<?= $form->field($model, 'item_name')->widget(Select2::classname(), [
    'data' => $roleList,
    'options' => ['placeholder' => 'Role', 'multiple' => true],
    'pluginOptions' => [
        'tokenSeparators' => [',', '']
    ],
])->label('Role'); ?>
<?= $form->field($model, 'user_id')->widget(Select2::classname(), [
    'data' => $userlist,
    'options' => ['placeholder' => 'Assign To', 'multiple' => true],
    'pluginOptions' => [
        'tags' => true,
        'tokenSeparators' => [',', '']
    ],
])->label('Assign To'); ?>



<div class="form-group" style="margin-top: 20px;">
    <?= Html::submitButton('Assign', ['class' => 'btn btn-primary btnorange marginright']) ?>
</div>

<?php ActiveForm::end(); ?>

</div>
</div>
</div>
<div class="clearfix"></div>
</div>
</section>