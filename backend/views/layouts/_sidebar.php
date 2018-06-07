<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 31/8/17
 * Time: 4:13 PM
 */

use yii\helpers\Url;

?>

<div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
    <div class="menu_section">
        <ul class="nav side-menu">
            <li><a href="<?= Url::toRoute(['/site/index'])?>"><i class="fa fa-home"></i> Dashboard </span></a></li>
            <?php if(Yii::$app->user->can("admin")):?>
            <li><a><i class="fa fa-users"></i> Client management <span class="fa fa-chevron-down"></span></a>
                <ul class="nav child_menu" style="">
                <?php if(Yii::$app->user->can("admin")){?>
                        <li><a href="<?= Url::toRoute(['/user/create-client'])?>">Add new client</a></li>
                        <li><a href="<?= Url::toRoute(['/user/view-client'])?>">Client Management</a></li>
                 <?php } ?>
                </ul>
            </li>
            <?php endif; ?>

            <?php if(Yii::$app->user->can("admin") || Yii::$app->user->can("monitor") || Yii::$app->user->can("instructor")):?>
                <li><a><i class="fa fa-user"></i> User management <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu" style="">
                        <?php if(Yii::$app->user->can("admin") || Yii::$app->user->can("monitor")):?>
                            <li><a href="<?= Url::toRoute(['/user/index'])?>">Manage Users</a></li>
                            <li><a href="<?= Url::toRoute(['/user/role-view'])?>">Role Management</a></li>
                            <li><a href="<?= Url::toRoute(['/user/bulk-registration'])?>">Bulk Registeration</a></li>
                        <?php endif; ?>
                            <li><a href="<?= Url::toRoute(['/user/group-management'])?>">Group Management</a></li>

                    </ul>
                </li>
            <?php endif; ?>

            <?php if(Yii::$app->user->can('monitor') || Yii::$app->user->can("admin") || Yii::$app->user->can("instructor")): ?>
            <li><a><i class="fa fa-graduation-cap" aria-hidden="true"></i>Training Management <span class="fa fa-chevron-down"></span></a>
                <ul class="nav child_menu" style="">
                    <li><a href="<?= Url::toRoute(['/training/index'])?>">Trainings</a></li>
                    <li><a href="<?= Url::toRoute(['/training/chat'])?>">Trainees Query</a></li>
                    <li><a href="javascript:void(0)" data-href="<?= Url::toRoute(['/training/training-modal','type' => 'preview'])?>" class="show_assessment">Assessment Preview</a></li>
                    <li><a href="<?= Url::toRoute(['/training/library'])?>">Question Library</a></li>
                    <li><a href="<?= Url::toRoute(['/training/trainees-view'])?>">Results</a></li>
                </ul>
            </li>
            <?php endif; ?>

            <?php if(Yii::$app->user->can('monitor') || Yii::$app->user->can("admin") || Yii::$app->user->can("instructor")): ?>
                <li><a><i class="fa fa-bell"></i>Push Notification <span class="fa fa-chevron-down"></span></a>
                    <ul class="nav child_menu" style="">
                        <li><a href="<?= Url::toRoute(['/notification/index'])?>">Send Notification</a></li>
                    </ul>
                </li>
            <?php endif; ?>
        </ul>


    </div>

</div>