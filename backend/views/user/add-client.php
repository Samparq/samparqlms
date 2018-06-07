<?php
/**
 * Created by PhpStorm.
 * User: QD0482
 * Date: 5/23/2018
 * Time: 12:01 PM
 */

use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;
use kartik\date\DatePicker;
use kartik\datetime\DateTimePicker;
use dosamigos\ckeditor\CKEditor;
use kartik\depdrop\DepDrop;
Use yii\helpers\Url;

?>

    <section class="content">
        <div class="mailboxadiin">
            <div class="row">
                <div class="col-md-12 col-xs-12">
                    <h1>Add new client</h1>
                    <div class="whitebox">

                        <?php $form = ActiveForm::begin([
                            'id' => 'client-form',
                            'enableAjaxValidation' => true
                        ]); ?>
                        <div class="row">

                            <div class="col-md-12">
                                <div class="col-md-8">

                                    <div class="row">
                                        <div class="col-md-12">

                                            <div class="col-md-6">

                                                <div class="form-group field-candidatedetails-date_of_birth required">
                                                    <label class="control-label" for="candidatedetails-date_of_birth">Name
                                                        <span style=color:red>*</span></label>
                                                    <?= $form->field($model, 'name')->textInput(['id' => "getNameCode"])->label(false) ?>
                                                    <div class="help-block"></div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-12">

                                            <div class="col-md-6">

                                                <div class="form-group field-candidatedetails-date_of_birth required">
                                                    <label class="control-label" for="candidatedetails-date_of_birth">Email
                                                        <span style=color:red>*</span></label>
                                                    <?= $form->field($model, 'email')->textInput(['placeholder' => 'Email'])->label(false) ?>
                                                    <div class="help-block"></div>
                                                </div>
                                            </div>
                                        </div>


                                        <!--<div class="col-md-3">
                                            </div>-->
                                        <div class="col-md-12">
                                            <div class="col-md-6">
                                                <div class="form-group field-candidatedetails-gender required">
                                                    <label class="control-label" for="candidatedetails-gender">Code
                                                        <span style=color:red>*</span></label>
                                                    <?= $form->field($model, 'code')->textInput(['prompt' => 'select Qualification', 'id' => 'getcode', 'readOnly' => true])->label(false) ?>
                                                    <div class="help-block"></div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-12">


                                            <!--<div class="col-md-3">
                                                 </div>-->
                                            <div class="col-md-6">

                                                <div class="form-group field-candidatedetails-date_of_birth required">
                                                    <label class="control-label" for="candidatedetails-date_of_birth">License
                                                        id <span style=color:red>*</span></label>
                                                    <?= $form->field($model, 'license_id')->dropDownList($list, ['prompt' => 'select...'])->label(false) ?>
                                                    <div class="help-block"></div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <div class="col-md-6">
                                                <div class="form-group field-candidatedetails-gender required">
                                                    <label class="control-label" for="candidatedetails-gender">No of
                                                        users <span style=color:red>*</span></label>
                                                    <?= $form->field($model, 'no_of_users')->textInput(['prompt' => 'select'])->label(false) ?>
                                                    <div class="help-block"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="col-md-6">
                                                <div class="form-group field-candidatedetails-gender required">
                                                    <label class="control-label" for="candidatedetails-gender">Cost per
                                                        user <span style=color:red>*</span></label>
                                                    <?= $form->field($model, 'cost_per_user')->textInput()->label(false) ?>
                                                    <div class="help-block"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="col-md-6">
                                                <div class="form-group field-candidatedetails-gender required">
                                                    <label class="control-label" for="candidatedetails-gender">No of
                                                        months <span style=color:red>*</span></label>
                                                    <?= $form->field($model, 'months')->textInput(['placeholder' => 'No of months'])->label(false); ?>
                                                    <div class="help-block"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">

                                        <!--<div class="col-md-3">
                                            </div>-->
                                        <div class="col-md-12">
                                            <div class="col-md-6">
                                                <div class="form-group field-candidatedetails-gender required">
                                                    <label class="control-label" for="candidatedetails-gender">Subscription
                                                        start date <span style=color:red>*</span></label>
                                                    <?= $form->field($model, 'subscription_sd')->widget(DateTimePicker::classname(), [
                                                        'options' => ['placeholder' => ''],
                                                        'pluginOptions' => [
                                                            'autoclose' => true,
                                                            'todayHighlight' => true,
                                                            'startDate' => '0d'

                                                        ]
                                                    ])->label(false); ?>
                                                    <div class="help-block"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="col-md-4">

                                    <div class="row">
                                        <div class="col-md-12">
                                            <h4>Total Estimated License Cost</h4>
                                            <table class="table table-bordered">
                                                <thead>
                                                <tr>
                                                    <th>Item</th>
                                                    <th>Cost Per Month</th>
                                                    <th>Cost Per Annum</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <tr>
                                                    <td>License cost (Cost per user * no of users)</td>
                                                    <td class="total_cost">-</td>
                                                    <td class="total_cost_pa">-</td>
                                                </tr>
                                                <tr>
                                                    <td>Gross Amount</td>
                                                    <td class="gross_cost">-</td>
                                                    <td class="gross_cost_pa">-</td>
                                                </tr>
                                                <tr>
                                                    <td>Add GST 18%</td>
                                                    <td class="gst_amount">-</td>
                                                    <td class="gst_amount_pa">-</td>
                                                </tr>
                                                <tr>
                                                    <td>Total Payable Amount</td>
                                                    <td class="net_amount">-</td>
                                                    <td class="net_amount_pa">-</td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                    </div>

                                </div>
                            </div>
                        </div>


                        <div class="form-group">
                            <?= Html::submitButton($model->isNewRecord ? 'Create client' : 'Update', ['class' => 'btn btn-primary']) ?>
                        </div>

                        <?php ActiveForm::end(); ?>


                    </div>
                </div>
            </div>
            <div class="clearfix"></div>
        </div>
    </section>
<?php
$script = <<<JS


function estimatedCost(total_amount,cpu){
    var total_cost = total_amount*cpu;
    var gross_cost = total_amount*cpu;
    var gst_amount = ((total_cost*18)/100);
    var getMonths = $("#client-months").val();
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

$("#client-months").change(function() {
    var getMonths = $(this).val();
    var cpu = $("#client-cost_per_user").val();
    var total_amount = $("#client-no_of_users").val();
    var total_cost = cpu*total_amount;
    var gst_amount = ((total_cost*18)/100);
     $(".total_cost_pa").text(total_cost*getMonths);
     $(".gross_cost_pa").text(total_cost*getMonths);
     $(".gst_amount_pa").text(gst_amount*getMonths);
     $(".net_amount_pa").text((gst_amount+total_cost)*getMonths);
});

$("#client-no_of_users").change(function() {
    var cpu = $("#client-cost_per_user").val();
    var total_amount = $(this).val();
    
    if(total_amount != '' && cpu != ''){
        estimatedCost(total_amount,cpu);
    }
});

$("#getNameCode").on('change',function() {
  var getVal = $("#getNameCode").val();
  
  if(getVal.length == 0){
      $("#getcode").val('');
  } else
  
  if(getVal.length >= 4){
      var res = getVal.slice(0,4);
      var getCode = res+'-'+Math.floor(100000 + Math.random() * 900000);
      $("#getcode").val(getCode);
  }else {
      var getCode = getVal+'-'+Math.floor(100000 + Math.random() * 900000);
      $("#getcode").val(getCode);
  }
  
  console.log(getCode);
})
JS;
$this->registerJs($script);
?>