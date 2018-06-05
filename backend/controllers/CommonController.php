<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 4/9/17
 * Time: 3:28 PM
 */

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;

class CommonController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'error', 'sync-data'],
                        'allow' => true,
                    ],
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];

    }

    public function beforeAction($action)
    {

        if(isset($_POST['LoginForm']) && !empty($_POST['LoginForm']['client_code'])){
            $db = strtolower(str_replace('-','_',$_POST['LoginForm']['client_code']));
            Yii::$app->session->set('dbName', $db);
        }
        \Yii::$app->dbDynamic->close();
        \Yii::$app->dbDynamic->dsn = 'mysql:host=192.168.4.9;dbname='.Yii::$app->session->get('dbName');
        \Yii::$app->dbDynamic->username = 'mridul';
        \Yii::$app->dbDynamic->password = '123456';
        \Yii::$app->dbDynamic->charset = 'utf8';

        if (parent::beforeAction($action)) {
            if (Yii::$app->user->isGuest) {
                if ($action->id == 'error') $this->layout = 'login-view';
            }
            return true;
        } else {
            return false;
        }
    }

    public function checkBehaviour()
    {
        return true;
    }
}