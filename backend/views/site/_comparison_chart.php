<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 15/9/17
 * Time: 2:26 PM
 */
?>
<br>
<?php if(Yii::$app->user->can('admin') || Yii::$app->user->can('monitor')):?>
<div class="row">
    <div class="col-lg-3"><?= $this->render('_client_chart');?></div>
    <div class="col-lg-3"><?= $this->render('_chart', [
       'client_code'  => $client_code
    ]);?></div>
<div class="col-lg-3"><?= $this->render('_license_chart', [
        'client_code'  => $client_code
    ]);?>
</div>
<div class="col-lg-3"><?= $this->render('_license_chart_time_wise', [
        'client_code'  => $client_code
    ]);?>
</div>

    <?php endif; ?>
    <div class="col-lg-12" style="margin-top: 15px">
        <div class="whitebox">
            <h3>Training Comparison Graph <span>(Month wise)</span></h3>
            <div id="comparison_chart" style="min-height: 400px;"></div>
        </div>
    </div>

<?php

$data = Yii::$app->samparq->getYearlyData();


$script = <<<JS
google.charts.load('current', {packages: ['corechart', 'line']});
google.charts.setOnLoadCallback(drawBackgroundColor);

function drawBackgroundColor() {
      var data = new google.visualization.DataTable();
      data.addColumn('date', 'Month');
      data.addColumn('number', 'Trainings');

       data.addRows($data);

      var options = {
        hAxis: {
          title: 'Month'
        },
        vAxis: {
          title: 'Increment'
        }
      };

      var chart = new google.visualization.LineChart(document.getElementById('comparison_chart'));
      chart.draw(data, options);
    }

JS;

$this->registerJs($script);


?>


