<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\bootstrap\Modal;
use yii\helpers\Url;

?>
<h1>Question Libraries</h1>
<?= Html::beginForm(['/'], 'post', ['id' => 'checkBoxForm']) ?>
<?= Html::input('hidden', 'checkedValue', '', ['id' => 'checkedValue']) ?>
<?= Html::endForm() ?>

<?= Html::button('<span class="glyphicon glyphicon-export"></span> Import', ['data-url' => Url::to(['import-individual-question']), 'class' => 'checkbox-action btn btn-danger pull-right showModal']); ?>
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => [
        ['class' => 'yii\grid\CheckboxColumn'],
        ['class' => 'yii\grid\SerialColumn'],
        [
                'attribute' => 'question',
                'header' => 'Question',
                'format' => 'raw',
                'value' => function($model){
                    return Html::a($model->question, 'javascript:void(0)', ['class' => 'questionPrev', 'data-url' => Url::to(['question-prev']),'data-id' => Yii::$app->samparq->encryptUserData($model->id)]);
                }
        ]
    ],
]); ?>


<?php  Modal::begin([
    'header' => '<h2>Import Questions</h2>',
    'id' => 'modalCont1'
]);

echo '<div class="modalInner"></div>';

Modal::end();


Modal::begin([
    'header' => '<h2>Question Preview</h2>',
    'id' => 'modalCont2'
]);

echo '<div class="questionPrev"></div>';

Modal::end();

?>


<?php

$script = <<<JS

   $("#modalCont1").removeAttr("tabindex");
   $(document).on('click',".showModal", function() {
       var getUlr = $(this).data("url");
        var getCheckedBoxes = $("#w0").yiiGridView('getSelectedRows').toString();
  
       $("#modalCont1")
       .modal("show")
       .find(".modalInner")
       .load(getUlr+"?id="+getCheckedBoxes);
   });


   

   $("#modalCont2").removeAttr("tabindex");
   $(document).on('click',".questionPrev", function() {
       var getUlr = $(this).data("url"),
            id = $(this).data('id');
       $("#modalCont2")
       .modal("show")
       .find(".questionPrev")
       .load(getUlr+'?quid='+id);
   });
   



JS;


$this->registerJs($script);


?>

