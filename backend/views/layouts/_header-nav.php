<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 31/8/17
 * Time: 5:15 PM
 */

use yii\helpers\Url;
use yii\helpers\Html;
use kartik\widgets\Select2;

?>
<div class="top_nav">
    <div class="nav_menu">
        <nav>
            <div class="nav toggle">
                <a id="menu_toggle"><i class="fa fa-bars"></i></a>
            </div>


            <ul class="nav navbar-nav navbar-right">

                <li class="">
                    <a href="javascript:;" class="user-profile dropdown-toggle" data-toggle="dropdown"
                       aria-expanded="false">
                        <img src="<?= Yii::$app->params['file_url'] . Yii::$app->user->identity->image_name ?>" alt=""">
                        Hi <?= Yii::$app->user->identity->name ?>
                    </a>
                </li>
                <?php if(Yii::$app->user->can('admin')): ?>
                <li style="padding-top: 10px; width: 240px">
                    <?= Html::beginForm(['site/client-chart'], 'post', ['id' => 'client_chart']) ?>
                    <?= Select2::widget([
                        'name' => 'client',
                        'value' => Yii::$app->session->get('client'),
                        'data' => Yii::$app->samparq->getClientList(),
                        'options' => [
                            'id' => 'client_list',
                            'placeholder' => 'Search client ...',
                        ],
                    ]); ?>
                    <?= Html::endForm(); ?>
                </li>
                <?php endif; ?>

                <li role="presentation" class="dropdown">

                    <ul id="menu1" class="dropdown-menu list-unstyled msg_list" role="menu">
                        <li>
                            <a href="<?= Url::toRoute(['/site/index']) ?>">
                                <span class="image"><img
                                            src="<?= Yii::$app->params['file_url'] . Yii::$app->user->identity->image_name ?>"
                                            alt="Profile Image"></span>
                                <span>
                          <span>John Smith</span>
                          <span class="time">3 mins ago</span>
                        </span>
                                <span class="message">
                          Film festivals used to be do-or-die moments for movie makers. They were where...
                        </span>
                            </a>
                        </li>
                        <li>
                            <a>
                                <span class="image"><img src="images/img.jpg" alt="Profile Image"></span>
                                <span>
                          <span>John Smith</span>
                          <span class="time">3 mins ago</span>
                        </span>
                                <span class="message">
                          Film festivals used to be do-or-die moments for movie makers. They were where...
                        </span>
                            </a>
                        </li>
                        <li>
                            <a>
                                <span class="image"><img src="images/img.jpg" alt="Profile Image"></span>
                                <span>
                          <span>John Smith</span>
                          <span class="time">3 mins ago</span>
                        </span>
                                <span class="message">
                          Film festivals used to be do-or-die moments for movie makers. They were where...
                        </span>
                            </a>
                        </li>
                        <li>
                            <a>
                                <span class="image"><img src="images/img.jpg" alt="Profile Image"></span>
                                <span>
                          <span>John Smith</span>
                          <span class="time">3 mins ago</span>
                        </span>
                                <span class="message">
                          Film festivals used to be do-or-die moments for movie makers. They were where...
                        </span>
                            </a>
                        </li>
                        <li>
                            <div class="text-center">
                                <a>
                                    <strong>See All Alerts</strong>
                                    <i class="fa fa-angle-right"></i>
                                </a>
                            </div>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>
    </div>
</div>

<?php

$script = <<<JS


$("#client_list").change(function() {
    $("#client_chart").submit();  
});

JS;


$this->registerJs($script);

?>
