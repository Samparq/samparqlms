<?php

Namespace backend\controllers;

/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 13/3/18
 * Time: 11:01 AM
 */

use backend\models\Notification;
use backend\models\PushNotification;
use backend\models\PushNotificationSearch;
use Yii;
use backend\controllers\CommonController;
use yii\web\ForbiddenHttpException;
use yii\web\User;

class NotificationController extends CommonController
{

    public function actionIndex()
    {
        if (Yii::$app->user->can("admin") || Yii::$app->user->can("monitor")) {
            $model = new PushNotification();

            if ($model->load(Yii::$app->request->post())) {
                $idArr = $model->user_ids;
                $idStr = implode(',',$model->user_ids);
                $model->user_ids = $idStr;
                if($model->save()){
                    foreach ($idArr as $id){
                        $notificationModel = new Notification();
                        $notificationModel->post_id = $model->id;
                        $notificationModel->user_id = $id;
                        $notificationModel->type_id = 7;
                        $notificationModel->message = $model->text;
                        $notificationModel->sender_id = Yii::$app->user->id;
                        $notificationModel->save(false);
                    }
                    $users = [];
                    $userModel = \common\models\User::findAll(['id' => $idArr]);
                    foreach ($userModel as $um){

                        if(Yii::$app->user->id != $um->id){
                            $users[] = [
                                'username' => $um->username,
                                'app_regid' => $um->app_regid,
                            ];

                        }

                    }


                    Yii::$app->samparq->sendNotification($users,[
                        'Title' => Yii::$app->user->identity->name,
                        'sender_name' => Yii::$app->user->identity->name,
                        'message' => $model->text,
                        'Body' => "New notification from ". Yii::$app->user->identity->name,
                        'process_type' => 'pushmessage_type',
                    ]);

                    Yii::$app->session->setFlash('notification', 'Notification has been sent successfully!');
                    return $this->redirect(['index']);
                }
            }

            return $this->render('index', [
                'model' => $model,
                'uList' => Yii::$app->samparq->getGroupWiseUList(),
            ]);

        } else {
            throw new ForbiddenHttpException("Access Denied, You don't have permission to perform this action");
        }
    }

    public function actionLogs()
    {
        if (Yii::$app->user->can("admin")) {
            $searchModel = new PushNotificationSearch();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

            return $this->render('logs', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        } else {
            throw new ForbiddenHttpException("Access Denied, You don't have permission to perform this action");
        }
    }
}