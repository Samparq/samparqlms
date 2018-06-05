<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 5/6/18
 * Time: 12:25 PM
 */

?>

<div class="panel panel-default">
    <div class="panel-heading">Question: <?= $questionModel->question ?></div>
    <div class="panel-body">
        <ol start="A" type="A">
            <?php foreach ($questionModel->options as $options): ?>
                <li><?= $options->option_value?> <?= $options->is_answer == 1 ? '<i class="fa fa-check" style="color: green"></i>' : ''?></li>
            <?php endforeach; ?>
        </ol>
    </div>

</div>


