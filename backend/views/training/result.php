<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 3/10/17
 * Time: 11:43 AM
 */
use yii\widgets\LinkPager;
use yii\helpers\ArrayHelper;

?>

<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">

         <h1> Survey submitted by <?= $traineeName ?></h1>

        <a class="btn btn-danger pull-right" aria-hidden="true" href="<?= \yii\helpers\Url::toRoute(['training/report', 'tid' => $tid, 'trainee_id' => $uid])?>" target="_blank" style="cursor: pointer" title="Download Report">
        <i class="glyphicon glyphicon-print"></i> Print
    </a>
  
        <div class="clearfix"></div>
        <br>
        <div class="whitebox">
        <?php if(Yii::$app->samparq->getTrainingType($tid) == 1): ?>
        <div class="panel panel-info">
            <div class="panel-heading"><?= $trainingTitle ?> Result Summary</div>
            <div class="panel-body">

                <div class="row">
                    <div class="col-md-12">
                        <div class="col-md-3">Total Marks</div>
                        <div class="col-md-2"><?= $totalMarks ?></div>
                    </div>
                    <div class="col-md-12">
                        <div class="col-md-3">Marks Obtained</div>
                        <div class="col-md-2"><?= $marksObtained ?></div>
                    </div>
                    <div class="col-md-12">
                        <div class="col-md-3">Overall Percentage</div>
                        <div class="col-md-2"><?= $totalMarks ==0 ? 0 : round($marksObtained/$totalMarks*100, 2) ?> %</div>
                    </div>

                </div>
            </div>
        </div>
        <?php endif; ?>
        <div class="x_panels">
          
                    <?php $i = 1; foreach ($answers as $key => $answer){ ?>
                      
                        <span> <strong>Q<?= $i++ ?>. </strong><?= $answer['question'] ?> </span>
                        <br/>
                        <strong>Ans. </strong><?php echo $answer['answer_given']; ?> <?php if(Yii::$app->samparq->getTrainingType($tid) == 1){ echo $answer['correct']; } ?>
                        <br><br>
                    <?php } ?>
            <?php
                echo LinkPager::widget([
                    'pagination' => $pages,
                ]);
            ?>
        </div>
        </div>
    </div>
</div>

