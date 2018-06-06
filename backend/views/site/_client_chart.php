<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 6/6/18
 * Time: 4:55 PM
 */

use dosamigos\chartjs\ChartJs;

$clientData = Yii::$app->samparq->getClientChartData();
$active = $clientData['active'];
$total = $clientData['total'];
$inactive = $total - $active;

?>

<div class="whitebox" style="margin-top: 15px;">
    <h3>Client Graph</h3>
    <?php if($clientData['total'] == 0){ ?>
        No data to display
    <?php } else { ?>
        <div>
            <br>
            <h4>Total <?= $clientData['total'] ?> Clients</h4>

            <div class="col-lg-8" style="z-index: 9;"><span class="small-circle active-circle"> </span> <span>Active</span> <span>(<?= $active ?>)</span></div>
            <div class="col-lg-4" style="z-index: 9;"><span class="small-circle inactive-circle"></span> <span>Inactive</span> <span>(<?= $inactive ?>)</span></div>
            <div id="clientt_chart" style="width: 100%; min-height: 300px;"></div>
        </div>
    <?php } ?>
</div>
<?php

$script = <<<JS

 google.charts.load("current", {packages:["corechart"]});
      google.charts.setOnLoadCallback(drawChartaa);
      function drawChartaa() {
        var dataaa = google.visualization.arrayToDataTable([
          ['Detail', 'Total clients'],
          ['Active',     $active],
          ['Inactive',   $inactive]
        ]);

        var optionsss = {
           legend: 'none',
          pieHole: 0.4,
          slices: {0: {color: '#40c884'}, 1:{color: '#31a6d5'}}
        };

        var chartss = new google.visualization.PieChart(document.getElementById('clientt_chart'));
        chartss.draw(dataaa, optionsss);
      }

JS;


$this->registerJs($script);

?>


