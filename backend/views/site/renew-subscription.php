<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 7/6/18
 * Time: 10:36 AM
 */

use yii\widgets\ActiveForm;
use yii\helpers\Html;

?>
<div class="row">
    <div class="col-md-12">
        <div class="col-md-6">
            <h4>Subscription Details</h4>
            <div class="panel panel-default">
                <div class="panel-body">

                    <?php $form = ActiveForm::begin(); ?>
                    <div class="form-group field-client_name required">
                        <label class="control-label" for="client_name">Client Name</label>
                        <?= Html::textInput('client_name', Yii::$app->session->get('client'), ['class' => 'form-control', 'readOnly' => true]) ?>
                    </div>
                    <?= $form->field($model, 'no_of_months')->textInput(['placeholder' => 'Months'])->label('No. of months') ?>
                    <?= $form->field($model, 'no_of_users')->textInput(['placeholder' => 'Users'])->label('No. of Users') ?>
                    <?= Html::submitButton('Renew Now', ['class' => 'btn btn-success']); ?>
                    <?php ActiveForm::end(); ?>

                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="row">
                <div class="col-md-12">
                    <h4>Total Estimated License Cost</h4>
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th>Item</th>
                            <th>Cost</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>License cost (Cost per user * no of users)</td>
                            <td class="total_cost">-</td>
                        </tr>
                        <tr>
                            <td>Gross Amount</td>
                            <td class="gross_cost">-</td>
                        </tr>
                        <tr>
                            <td>Add GST 18%</td>
                            <td class="gst_amount">-</td>
                        </tr>
                        <tr>
                            <td>Total Payable Amount</td>
                            <td class="net_amount">-</td>
                        </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>



<?php

$script = <<<JS

function estimatedCost(total_amount,cpu){
    var total_cost = total_amount*cpu;
    var gross_cost = total_amount*cpu;
    var gst_amount = ((total_cost*18)/100);
    $(".total_cost").text(total_cost);
    $(".gross_cost").text(gross_cost);
    $(".gst_amount").text(gst_amount);
    $(".net_amount").text(gst_amount+total_cost);
}

$("#client-cost_per_user").change(function() {
    var total_amount = $("#client-no_of_users").val();
    var cpu = $(this).val();
    
    if(total_amount != '' && cpu != ''){
        estimatedCost(total_amount,cpu);
    }
});

$("#client-no_of_users").change(function() {
    var cpu = $("#client-cost_per_user").val();
    var total_amount = $(this).val();
    
    if(total_amount != '' && cpu != ''){
        estimatedCost(total_amount,cpu);
    }
});


JS;

$this->registerJs($script);


?>