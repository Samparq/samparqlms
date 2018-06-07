<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 30/3/18
 * Time: 10:50 AM
 */



use yii\widgets\ActiveForm;
use yii\helpers\Html;
use kartik\select2\Select2;
use yii\helpers\Url;

?>

<div class="row">
    <?php if((isset($_GET['tid'])) && !empty($_GET['tid'])){ ?>
    <div class="col-md-12">

<ul class="cd-breadcrumb">
<li id="pointer">
<a>Create Training</a></li>
<li class="current"><a href="#0">Step 2: Group Management</a></li>
    <li class="next" > <a>ADD TRAINEES/ATTACHMENT</a></li>
    <li class="next" > <a>Assessment/Quiz Setting</a></li>
    <li class="next" > <a >Add/Update Questions</a></li>
</ul>

    <h1>Step 2: Add Trainees</h1>
        </div>
    <?php } else { ?>
        <h1>Group Management</h1>
    <?php } ?>

         <div class="col-md-6 col-xs-12">
            <!-- <div class="panel-heading"><h2>Step 2: Add Trainees</h2></div> -->
            <div class="whitebox">

                <section class="content">

                    <div class="mailboxadiin">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mailboxsidebzr">
                                    <?php $form = ActiveForm::begin([
                                        'id' => 'composeForm',
                                        'enableAjaxValidation' => true
                                    ]); ?>


                                    <?= $form->field($groupModel, 'name')->textInput()->label('Group Name<sup style="color: red">*</sup>') ?>

                                    <?php $model->user_id = explode(',',$model->user_id)?>
                                    <?= $form->field($model, 'user_id')->widget(Select2::classname(), [
                                        'data' => $memberList,
                                        'options' => ['placeholder' => 'Add Members', 'multiple' => true],
                                        'pluginOptions' => [    
                                            'id' => 'senderList',
                                            'tokenSeparators' => [',', '']
                                        ],
                                    ])->label('Group Members<sup style="color: red">*</sup>'); ?>
                                        <br>
                                    <?= Html::submitButton('Save & Next', ['class' => 'btn btn-primary'])?>
                                    <?php if(isset($tid) && !empty($tid)): ?>
                                    <?= Html::a('Skip', Url::toRoute(['/training/create-training', 'tid' => $tid]), ['class' => 'btn btn-danger'])?>
                                    <?php endif; ?>
                                    <?php ActiveForm::end(); ?>

                                </div>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                </section>
            </div>
        </div>
    
    </div>
       
    
</div>
