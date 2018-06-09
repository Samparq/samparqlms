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
        <div>            
            <h5 class="total">Total <?= $totalUsers ?> Users</h5>
            <div class="col-lg-6" style="z-index: 1;"><span class="small-circle active-circle"> </span> <span>Used</span> <span>(<?= $activeData ?>)</span></div>
            <div class="col-lg-6" style="z-index: 1;"><span class="small-circle inactive-circle"></span> <span>Balance</span> <span>(<?= $balance ?>)</span></div>
            
            <br>
            <div id="licenseChartUserwise" style="width: 100%; min-height: 300px;"></div>
        </div>
        
        
        
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
          pieHole: 0.4,
          legend:"none",
          slices: {0: {color: '#30a2d5'}, 1:{color: '#f56572'}}
        };

        var chart = new google.visualization.PieChart(document.getElementById('licenseChartUserwise'));
        chart.draw(data, options);
        
        
      }
      

JS;


$this->registerJs($script);

?>


