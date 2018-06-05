<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;

/**
 * Site controller
 */
class SiteController extends CommonController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'error', 'client-chart'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index', 'chat'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {


        
        return $this->render('index',
            ['client_code' => Yii::$app->samparq->checkClient()]
        );
    }

    public function actionPrivacyPolicy()
    {

        $this->layout = 'policy-layout';
        return $this->render('policy');
    }

    public function actionChat()
    {
        return $this->render('chat');
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {

        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }


        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
                Yii::$app->samparq->createLoginLog(Yii::$app->user->identity->username, Yii::$app->user->getId(), date('Y-m-d H:i:s'));
                return $this->goBack();

        } else {
            $this->layout = 'login-view';
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }


    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }


    public function actionClientChart()
    {

        if (Yii::$app->request->post()) {
            $client = Yii::$app->request->post('client');
            Yii::$app->session->set('client', $client);
            Yii::$app->session->set('dbName', strtolower(str_replace('-','_', $client)));
        }

        return $this->redirect(Yii::$app->request->referrer);

    }
}
