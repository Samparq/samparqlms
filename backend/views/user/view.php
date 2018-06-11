<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 12/9/17
 * Time: 4:28 PM
 */

use yii\bootstrap\Modal;
use yii\helpers\Url;

?>

<div class="col-md-12 col-sm-12 col-xs-12 profile_left">
   <div class="profilebox">
    <div class="profile_img">
        <div>
        <div id="crop-avatar">
            <!-- Current avatar -->
            <img class="img-responsive avatar-view" src="<?= Yii::$app->params['file_url'].$model->image_name ?>" alt="Avatar"   title="Change the avatar">
            
        </div>
        </div>
    </div>
    
    <div class="col-md-6 col-md-offset-2 detail-penal">
    <h3><?= $model->name ?></h3>
    <h5>Ui designer</h5>

    <span class="ranking">Rating: <?= Yii::$app->samparq->getTrainingRating($model->id) ?></span>
    <img src="../images/star.jpg" alt="">
    <br><br><br>
    <ul class="list-unstyled user_data">

        <li>
            <i> Email </i> <?= $model->email ?>
        </li>
        
        <li>
            <i> Phone </i> <?= $model->mobile_no ?>
            <span>&nbsp;</span>
        </li>

        <li>
            <i>Status</i> <span style="color:#6e6e6e"><?= $model->flag ?></span>
        </li>

        <li><input style="vertical-align: text-bottom;" type="checkbox" data-status="<?= $model->ac_act_alert ?>" <?= $model->ac_act_alert == 1? "Checked":"" ?> data-id="<?= $model->id ?>" id="accountAlert">Account Activation Alert
            <a class="editprofile" id="editPro" data-url="<?= Url::toRoute(['/user/update','id' => $model->id])?>">  Edit Profile</a>    </li>

    </ul>
    <div class="activalert">


</div>
</div>

	   
<div class="col-lg-3" style="    margin-top: -50px;">
    <div class="training_summary">
        <span><?= $completed ?></span>
        <span><?= $total ?></span>
    </div>
    <div class="trainingtext">
    training summary  
    </div>
</div>
	   
</div>
</div>
<?php

Modal::begin([
    'header' => '<h2>'.$model->name.' Information</h2>',
    'id' => 'modal'
]);

echo '<div class="userInfoBox">While Loading please wait...</div>';

Modal::end();
?>
<?php

$url = Url::toRoute(['/user/acc-act']);

$script = <<<JS

    $("#accountAlert").click(function() {
        var id = $(this).data("id"),
        status = $(this).data("status");
        if(status == 0){
            status = 1;
        } else {
            status = 0;
        }
        $.ajax({
        url:"$url",
        type:"post",
        dataType:"json",
        data:{
            id:id,
            status:status
        },
        success:function() {
          console.log("success");
        },
        error:function() {
           console.log("something went wrong, please try again later");
        }
       
      });
    });
    
    $('#editPro').click(function() {
        var url = $(this).data('url');
        $('#modal').modal().find('.userInfoBox').load(url);
    });
JS;

$this->registerJs($script);


?>

