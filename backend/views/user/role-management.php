<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 6/10/17
 * Time: 11:29 AM
 */
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

?>
    <!-- <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        <strong>Holy guacamole!</strong> You should check in on some of those fields below.
    </div> -->
 
            <h1>Role View</h1>
 
<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h4><i class="fa fa-lock" aria-hidden="true"></i> Manage roles for <?= Yii::$app->samparq->getUsernameById($uid)?></h4>
                <div class="clearfix"></div>

            </div>
            <?php
                $form = ActiveForm::begin();
                $model->item_name = $checkedList;
            ?>
                    <?= $form->field($model, 'item_name')->inline()->checkboxList($roles, ['class' => 'saveForm','data-userid' => $uid])->label("Role"); ?>

        <?php ActiveForm::end(); ?>

        </div>
    </div>

</div>
    <div id="successMessage">

    </div>


<?php
$surl = Url::toRoute(['save-role']);
$durl = Url::toRoute(['delete-role']);

$script = <<<JS
    $(".checkbox-inline input").click(function() {
        if(this.checked){
            var role = $(this).val();
            var getName = $(this).attr("name");
            var getUserId = $("#authassignment-item_name").data("userid");
            $.ajax({
                url:"$surl",
                type:"POST",
                dataType:"json",
                data:{
                    userid:getUserId,
                    role:role
                },
                success:function(res) {
                  if(res.status === true){
                      $("#successMessage").html("");
                      $("#successMessage").html('<div class="alert alert-success alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong>Success!</strong> Role has been added successfully.</div>');
                      setTimeout(function() {
                        $("#successMessage .alert-success").fadeOut();
                      }, 3000);
                  }
                },
                error:function() {
                  console.log("something went wrong please try again later!");
                }
            });
        } else {
            var role = $(this).val();
            var getName = $(this).attr("name");
            var getUserId = $("#authassignment-item_name").data("userid");
            $.ajax({
                url:"$durl",
                type:"POST",
                dataType:"json",
                data:{
                    userid:getUserId,
                    role:role,
                    type:"delete"
                },
                success:function(res) {
                  if(res.status === true){
                      $("#successMessage").html("");
                      $("#successMessage").html('<div class="alert alert-warning alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong>Success!</strong> Role has been removed successfully.</div>');
                      setTimeout(function() {
                        $("#successMessage .alert-warning").fadeOut();
                      }, 3000);
                  }
                },
                error:function() {
                    console.log("something went wrong please try again later!");
                }
            });
        }
    });
JS;

$this->registerJs($script);

?>