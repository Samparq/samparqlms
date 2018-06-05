<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 28/9/17
 * Time: 5:33 PM
 */

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

?>

        <div class="panel panel-info">
                <div class="panel-heading">
                    <h3><?= Yii::$app->samparq->getTrainingTitle($questions["tid"]) ?></h3>
                </div>
                <div class="panel-body" style="min-height: 600px;">
                    <?php $form = ActiveForm::begin(["id" => "surveyForm"]); ?>
                    <?= $form->field($model, 'training_id')->hiddenInput(['value' => $questions["tid"]])->label(false); ?>
                    <?= $form->field($model, 'question_id')->hiddenInput(['value' => $questions["id"]])->label(false); ?>
                    <h2><?= $questions["Question"] ?></h2>
                    <?= $form->field($model, 'option_id')->radioList( $questions["options"])->label(false); ?>
                    <div class="col-md-12">
                        <?= Html::submitButton('Next', ['class' => 'btn btn-info pull-right'])?>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
                <div class="panel-footer">
                    <p class="pull-right">Powered by QDegrees</p>
                    <div class="clearfix"></div>
                </div>
        </div>


<?php
$script = <<< JS
history.pushState(null, null, document.title);
window.addEventListener('popstate', function () {
    history.pushState(null, null, document.title);
});
        
JS;
?>