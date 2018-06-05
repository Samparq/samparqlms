<?php

/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 14/9/17
 * Time: 4:38 PM
 */

use dosamigos\chartjs\ChartJs;



$active = Yii::$app->samparq->getChartData('ACTIVE', $client_code);
$inactive = Yii::$app->samparq->getChartData('INACTIVE', $client_code);
$blocked = Yii::$app->samparq->getChartData('BLOCKED', $client_code);
$pending = Yii::$app->samparq->getChartData('PENDING', $client_code);
$total = $active + $inactive + $blocked + $pending;

?>

<div class="whitebox" style="margin-top: 15px;">
    <h3>User Registration Graph</h3>
    <?php if($total == 0){ ?>
        No data to display
    <?php } else { ?>
        <div id="donutcharts" style="width: 100%; min-height: 400px;"></div>
    <?php } ?>
</div>
<?php

$script = <<<JS
       

 google.charts.load("current", {packages:["corechart"]});
      google.charts.setOnLoadCallback(drawCharta);
      function drawCharta() {
        var dataa = google.visualization.arrayToDataTable([
          ['Detail', 'Total user registration'],
          ['Active',     $active],
          ['Inactive($inactive)',   $inactive],
          ['Blocked($blocked)',  $blocked],
          ['Pending($pending)', $pending]
        ]);

        var optionss = {
            tt:'red',
            legend: 'none',
          pieHole: 0.4,
          slices: {0: {color: '#40c884'}, 1:{color: '#31a6d5'},2: {color: '#f56572'}, 3:{color: '#f99917'}}
        };

        var charts = new google.visualization.PieChart(document.getElementById('donutcharts'));
        charts.draw(dataa, optionss);
      }

JS;


$this->registerJs($script);

?>

