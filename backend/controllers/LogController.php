<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 6/9/17
 * Time: 12:20 PM
 */
namespace backend\controllers;

use backend\models\AppUpdates;
use backend\models\LoginAttempts;
use backend\models\LoginAttemptsSearch;
use backend\models\PostLogSearch;
use Yii;
use backend\controllers\CommonController;
use backend\models\LoginLogSearch;
use yii\helpers\Json;
use yii\web\ForbiddenHttpException;

class LogController extends CommonController
{
    public function actionLogin()
    {
        if(Yii::$app->user->can("admin")) {
            $searchModel = new LoginLogSearch();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

            return $this->render('login', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        } else {
            throw new ForbiddenHttpException("Access Denied, You don't have permission to perform this action");
        }
    }

    public function actionFailedAttempt()
    {
        if(Yii::$app->user->can("admin")) {
            $searchModel = new LoginAttemptsSearch();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

            if (Yii::$app->request->post('hasEditable')) {
                $uid = Yii::$app->request->post('editableKey');
                $model = LoginAttempts::findOne($uid);

                $out = Json::encode(['output' => '', 'message' => '']);

                $posted = current($_POST['LoginAttempts']);
                $post = ['LoginAttempts' => $posted];


                $attributeName = $_POST['editableAttribute'];

                if ($model->load($post)) {

                    $output = '';


                    if (isset($posted[$attributeName])) {

                        if ($attributeName === 'status') {

                            if ($posted[$attributeName] == 0) {
                                $type = "Unblocked";
                            } elseif ($posted[$attributeName] == 1) {
                                $type = "Blocked";
                            }
                            $output = $type;

                        } else {

                            $output = $model->$attributeName;

                        }

                        $model->$attributeName = $posted[$attributeName];
                        $model->save(false);


                    }

                    $out = Json::encode(['output' => $output, 'message' => '']);
                }
                echo $out;
                return;
            }

            return $this->render('attempt', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        } else {

        }
    }


    public function actionPost()
    {
        if(Yii::$app->user->can("admin")) {
            $searchModel = new PostLogSearch();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

            return $this->render('post', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        } else {
            throw new ForbiddenHttpException("Access Denied, You don't have permission to perform this action");
        }
    }

    public function actionUpdateVersion(){
        if(Yii::$app->user->can("admin")) {
            $UpdateModel = AppUpdates::findOne(['id' => 1]);
            $versionName = @explode('.', $UpdateModel->version_name);
            if (empty($UpdateModel)) {
                $UpdateModel = new AppUpdates();
            }
            if ($UpdateModel->load(Yii::$app->request->post())) {
                $UpdateModel->version_name = $UpdateModel->firstDig . '.' . $UpdateModel->secondDig . '.' . $UpdateModel->thirdDig;
                if ($UpdateModel->save()) {
                    Yii::$app->session->setFlash('successMsg', "Android version has been successfully updated");
                }
            }
            return $this->render('android', [
                'model' => $UpdateModel,
                'firstDig' => empty($versionName) ? 000 : $versionName[0],
                'secondDig' => empty($versionName) ? 000 : $versionName[1],
                'thirdDig' => empty($versionName) ? 000 : $versionName[2],
            ]);
        } else {
            throw new ForbiddenHttpException("Access Denied, You don't have permission to perform this action");
        }
    }
}