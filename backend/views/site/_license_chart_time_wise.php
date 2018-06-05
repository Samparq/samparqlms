<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 4/6/18
 * Time: 4:16 PM
 */


$dataaa = Yii::$app->samparq->getLicenseUsageGraph($client_code);
$totalData = $dataaa['total'];
$usageData = $dataaa['current'];


?>


<div class="whitebox" style="margin-top: 15px;">
    <h3>License Graph Time Wise</h3>
    <?php if($usageData == 0){ ?>
        <div id="licenseChart" style="width: 100%; min-height: 400px;">
            No data to display
        </div>

    <?php } elseif ($usageData < 0) { ?>
        <div id="licenseChart" style="width: 100%; min-height: 400px;">
            License expired
        </div>

    <?php } else { ?>
        <div id="licenseChartTimewise" style="width: 100%; min-height: 400px;"></div>
    <?php } ?>

</div>
<?php


$script = <<<JS
       

 google.charts.load("current", {packages:["corechart"]});
      google.charts.setOnLoadCallback(drawChartt);
      function drawChartt() {
          
        var data = google.visualization.arrayToDataTable([
          ['Detail', 'License'],
          ['Balance',     $usageData],
          ['Usage',     $totalData]
        ]);

        var options = {
          pieHole: 0.4,
          legend:"none",
          slices: {0: {color: '#3fbb71'}, 1:{color: '#f88f12'}}
        };

        var chart = new google.visualization.PieChart(document.getElementById('licenseChartTimewise'));
        chart.draw(data, options);
        
        
      }
      

JS;


$this->registerJs($script);

?>



