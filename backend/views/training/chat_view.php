<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 23/5/18
 * Time: 12:34 PM
 */

use yii\widgets\ActiveForm;
use yii\helpers\Html;

?>


<div class="row">
    <div class="chatback">


        <div class="contentbox">

            <?php foreach ($chats as $chat): ?>
                <div class="chatdiv">
                    <div class="<?= Yii::$app->user->id == $chat->sender_id ? 'right_bubble' : 'left_bubble'?>">
                        <?= $chat->message; ?>
                    </div>
                </div>

            <?php endforeach; ?>


        </div>


    </div>

    <footer class="chatfooter">
        <?php $form = ActiveForm::begin(); ?>
        <?= $form->field($model, 'message')->textarea(['placeholder' =>'Type here']) ?>
        <?= Html::submitButton('<img src="../images/send.png" alt="">',['class'])?>

        <?php ActiveForm::end(); ?>
    </footer>

</div>

<?php

$script = <<<JS


$(".field-chat-message").addClass('textareabox')

JS;


$this->registerJs($script)
?>