<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 28/9/17
 * Time: 5:33 PM
 */
?>

         
            <h1>Assessment Preview</h1>
        
<?php if(empty($modelQuestion)){
    echo "Assessment has not been prepared for this training.";
} else { ?>
        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel whitebox">
                    <div class="x_title">
                        <h4><i class="fa fa-info-circle" aria-hidden="true"></i> Assessment for <?= $trainingTitle ?></h4>

                        <div class="clearfix"></div>
                    </div>
                    <?php $i = 0; foreach ($modelQuestion as $key => $mq):
                            $i++;
                            $options = $mq->options;

                        ?>

                            <strong>Q<?= $i ?>. </strong><?= $mq->question ?>
                        <ol type="A">
                            <?php $a = 0; foreach ($options as $key => $option){ $a++; ?>
                                <li>
                                    <?= $option->option_value ?> <?php echo $option->is_answer == 1 ?'<span style="color: green">(<i>Correct</i>)</span>':''; ?>
                                </li>
                            <?php } ?>
                        </ol>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
<?php } ?>