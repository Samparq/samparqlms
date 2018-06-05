  <?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 12/9/17
 * Time: 4:57 PM
 */


use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\TblPost */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="tbl-post-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput() ?>

    <?= $form->field($model, 'mobile_no')->textInput() ?>

    <?php
        if($model->key == 1 && $model->super_admin == 1){
            $model->right_status = 1;
        } else {
            $model->right_status = 2;
        }
    ?>
    <?= $form->field($model, 'right_status')->dropDownList($roleList, ['prompt' => 'User right']) ?>


    <div class="form-group">
        <?= Html::submitButton( 'Update', ['class' => 'btn btn-success' ]) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
