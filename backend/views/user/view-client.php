<?php


use kartik\grid\GridView;
use kartik\export\ExportMenu;
use yii\helpers\Url;
use yii\helpers\Html;
?>
<?php if(Yii::$app->session->hasFlash('ClientSuccess')): ?>
    <div class="alert alert-success">
        <strong>Success!</strong> <?= Yii::$app->session->getFlash('ClientSuccess')?>.
    </div>
<?php endif; ?>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">


                        <h1>Clients Listing</h1>

                        <?php
                        $gridColumns = [
                            ['class' => 'yii\grid\SerialColumn'],
                            [
                                'class'=>'kartik\grid\EditableColumn',
                                'attribute'=>'name',

                                'editableOptions'=>[
                                    'header'=>'Client Name',
                                    'inputType'=>\kartik\editable\Editable::INPUT_TEXT,
                                ],
                            ],
                            [
                                'class'=>'kartik\grid\EditableColumn',
                                'attribute'=>'no_of_users',

                                'editableOptions'=>[
                                    'header'=>'Number of users',
                                    'inputType'=>\kartik\editable\Editable::INPUT_TEXT,
                                ],
                            ],
                            [
                                'class'=>'kartik\grid\EditableColumn',
                                'attribute'=>'status',
                                'label' => 'Status',
                                'value' => function($model){
                                    $type = '';
                                    if($model->status === "Pending"){ $type = "Pending"; }
                                    elseif($model->status === "Active"){ $type = "Active"; }
                                    elseif($model->status === "Expired"){ $type = "Expired"; }
                                    elseif($model->status === "Renewal Pending"){ $type = "Renewal Pending'"; }
                                    return $type;
                                },
                                'editableOptions'=>[
                                    'data' => Yii::$app->samparq->getClientStatusList(),
                                    'header'=>'status',
                                    'inputType'=>\kartik\editable\Editable::INPUT_DROPDOWN_LIST,
                                ],
                            ],
                            [
                                'class'=>'kartik\grid\EditableColumn',
                                'header'=>'Subscription end date',
                                'attribute'=>'subscription_ed',

                                'editableOptions'=>[
                                    'header'=>'Subscription end date',
                                    'inputType'=>\kartik\editable\Editable::INPUT_DATETIME,
                                ],
                            ],
                            [
                                'class'=>'kartik\grid\EditableColumn',
                                'header'=>'Subscription start date',
                                'attribute'=>'subscription_sd',

                                'editableOptions'=>[
                                    'header'=>'Subscription start date',
                                    'inputType'=>\kartik\editable\Editable::INPUT_DATETIME,
                                ],
                            ]
                        ];

                        echo ExportMenu::widget([
                            'dataProvider' => $dataProvider,
                            'columns' => $gridColumns
                        ]);

                        echo GridView::widget([
                            'pjax'=>true,
                            'dataProvider' => $dataProvider,
                            'filterModel' => $modelSearch,
                            'columns' => $gridColumns
                        ]);
                        ?>
                    </div>
        </div>
    </section>
