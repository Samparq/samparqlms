<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 6/10/17
 * Time: 10:49 AM
 */
namespace console\controllers;

use Yii;
use yii\console\Controller;

class RbacController extends Controller
{
    public function actionInit(){
        $auth = Yii::$app->authManager;
    }

}