<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 27/4/18
 * Time: 5:47 PM
 */
?>
<?php


$tid = Yii::$app->samparq->decryptUserData($tid);
if($type == 1){

    $data = Yii::$app->samparq->getCompletionData($tid);

    if(!empty($data)){ ?>

        <div id="submission_chart" style="margin-right:10px; width: 30%; min-height: 200px; float: left"></div>
        <div id="submission_chart2" style="margin-right:10px; width: 30%; min-height: 200px; float: left"></div>
        <div id="submission_chart3" style="width: 30%; min-height: 200px; float: left"></div>
        <div class="clearfix"></div>
        <br/>

    <?php }  ?>

    <?php

    $data2 = Yii::$app->samparq->getFailedQuestion($tid);
    $data3 = Yii::$app->samparq->getAverageScore($tid);



    $total = $data["totalSubmission"];
    $completed = $data["totalSubmission"];
    $totalMarks = $data3["totalMarks"];
    $averageMarks = $data3["averageMarks"];


    $script = <<<JS
      google.charts.load("current", {packages:["corechart"]});
      google.charts.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
          ['Training', 'Submission'],
          ['Total Trainees',   $total],
          ['Completed',   $completed]
        ]);

         var options = {
          title: 'Training Submission',
          pieHole: 0.2,
          slices: {0: {color: '#2a91cd'}, 1:{color: '#3f0aa4'}}
        };
         
        
  
        var data2 = google.visualization.arrayToDataTable($data2);

         var options2 = {
          title: 'Most Failed Question',
          pieHole: 0.2,
             slices: {0: {color: '#a8184b'}, 1:{color: '#f99902'},2:{color: '#66b133'},3:{color: '#870cb0'},4:{color: '#0e46fe'}}
        };
       
        
        var data3 = google.visualization.arrayToDataTable([
            ['Score', 'Average score'],
            ['Total', $totalMarks],
            ['Average', $averageMarks]
        ]);

        var options3 = {
          title: 'Average Score',
          pieHole: 0.2,
            slices: {0: {color: '#f9bb04'}, 1:{color: '#f7530c'}}
        };

        var chart = new google.visualization.PieChart(document.getElementById('submission_chart'));
        chart.draw(data, options);
        
        var chart2 = new google.visualization.PieChart(document.getElementById('submission_chart2'));
        chart2.draw(data2, options2);
        
        var chart3 = new google.visualization.PieChart(document.getElementById('submission_chart3'));
        chart3.draw(data3, options3);
      }

JS;

} else {




    $surveyData = Yii::$app->samparq->getSurveyResponse($tid);
    $surveyResponseData = Yii::$app->samparq->getSurveyeeDetails($tid);
    $totalSurveyee = $surveyData['totalCount'];
    $submitted = $surveyData['submitted'];
    $dropOuts = $surveyData['dropOuts'];

?>
<?php   if(Yii::$app->samparq->getTraineesCount($tid) > 0){?>
        <div id="submission_chart" style="width: 100%; min-height: 350px;"></div>
    <?php } ?>


<?php

    $script = <<<JS
      google.charts.load("current", {packages:["corechart"]});
      google.charts.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
          ['Training', 'Submission'],
          ['Dropouts',   $dropOuts],
          ['Completed',   $submitted]
        ]);

         var options = {
          title: 'Survey Details ( Total Trainees $submitted)',
          pieHole: 0.2,
          slices: {0: {color: '#2a91cd'}, 1:{color: '#3f0aa4'}}
        };
         
        var chart = new google.visualization.PieChart(document.getElementById('submission_chart'));
        chart.draw(data, options);
        
      
      }

JS;



}

$this->registerJs($script);


?>



