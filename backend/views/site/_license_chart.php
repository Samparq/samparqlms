<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 18/5/18
 * Time: 4:48 PM
 */

$activeData = Yii::$app->samparq->getChartData('ACTIVE',$client_code);
$totalUsers = Yii::$app->samparq->getTotalLicencse($client_code);
$balance = ($totalUsers - $activeData);

?>

<style>
    .pd{
        font-size: 400px;
        background: red;
    }
</style>

<div class="whitebox" style="margin-top: 15px;">
    <h3>License Graph User Wise</h3>
    <?php if($totalUsers == 0){ ?>
        No data to display
    <?php } else { ?>
        <div id="licenseChartUserwise" style="width: 100%; min-height: 400px;"></div>
    <?php } ?>

</div>
<?php


$script = <<<JS
    
 google.charts.load("current", {packages:["corechart"]});
      google.charts.setOnLoadCallback(drawChart);
      function drawChart() {
          
        var data = google.visualization.arrayToDataTable([
          ['Detail', 'License'],
          ['Balance',     $balance],
          ['Usage',     $activeData]
        ]);

        var options = {
          allowHtml: true,
          cssClassNames: cssClassNames,
          pieHole: 0.4,
          title:"Total $totalUsers Users",
          slices: {0: {color: '#30a2d5'}, 1:{color: '#f56572'}}
        };

        var chart = new google.visualization.PieChart(document.getElementById('licenseChartUserwise'));
        chart.draw(data, options);
        
        
      }
      

JS;


$this->registerJs($script);

?>


