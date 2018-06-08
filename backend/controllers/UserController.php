<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 1/9/17
 * Time: 9:22 AM
 */

namespace backend\controllers;

use backend\controllers\CommonController;
use backend\models\CdUser;
use backend\models\CdUserSearch;
use backend\models\Client;
use backend\models\ClientSearch;
use backend\models\Group;
use backend\models\GroupMembers;
use backend\models\GroupSearch;
use backend\models\TeamQds;
use backend\models\TeamQdsSearch;
use backend\models\Training;
use console\models\AuthAssignmentSearch;
use common\models\UpdateUser;
use console\models\AuthAssignment;
use console\models\AuthItem;
use kartik\widgets\ActiveForm;
use Yii;
use common\models\User;
use common\models\UserSearch;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\validators\Validator;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\web\UploadedFile;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends CommonController
{
    /**
     * @inheritdoc
     */

    public function actionIndex()
    {


        if (Yii::$app->user->can("admin") || Yii::$app->user->can("monitor")) {
            $searchModel = new UserSearch();



            $dataProvider = $searchModel->search(Yii::$app->request->queryParams, Yii::$app->request->post('action'));
            if (Yii::$app->request->post('hasEditable')) {

                $uid = Yii::$app->request->post('editableKey');
                $model = User::findOne($uid);

                $out = Json::encode(['output' => '', 'message' => '']);

                $posted = current($_POST['User']);
                $post = ['User' => $posted];


                $attributeName = $_POST['editableAttribute'];

                if ($model->load($post)) {

                    $output = '';


                    if (isset($posted[$attributeName])) {

                        if ($attributeName === 'user_type') {

                            if ($posted[$attributeName] == 1) {
                                $type = "PMO";
                            } elseif ($posted[$attributeName] == 2) {
                                $type = "IT";
                            } elseif ($posted[$attributeName] == 3) {
                                $type = "HR";
                            } elseif ($posted[$attributeName] == 4) {
                                $type = "FINANCE";
                            } elseif ($posted[$attributeName] == 5) {
                                $type = "EMPLOYEE";
                            }
                            $model->update_status = 1;
                            $output = $type;

                        } elseif ($attributeName === 'flag') {
                            if ($posted[$attributeName] === "BLOCKED") {
                                $type = "BLOCKED";
                                $model->update_status = 1;
                                $model->key = 0;
                                $model->super_admin = 0;
                                Yii::$app->mailer->compose('thanksTemplate', ['username' => $model->name, 'email' => $model->email, 'dob' => $model->dob, 'department' => Yii::$app->samparq->getDepartmentName($model->user_type), 'uid' => Yii::$app->utility->encryptUserData($model->id), 'confirmed' => 'true', 'type' => 'blocked'])
                                    ->setFrom('samparq@qdegrees.com')
                                    ->setTo($model->email)
                                    ->setSubject('Account Blocked')
                                    ->send();
                            } elseif ($posted[$attributeName] === "ACTIVE") {
                                $model->update_status = 1;
                                $type = "ACTIVE";
                                Yii::$app->mailer->compose('thanksTemplate', ['username' => $model->name, 'email' => $model->email, 'dob' => $model->dob, 'department' => Yii::$app->samparq->getDepartmentName($model->user_type), 'uid' => Yii::$app->utility->encryptUserData($model->id), 'confirmed' => 'true', 'type' => 'activated'])
                                    ->setFrom('samparq@qdegrees.com')
                                    ->setTo($model->email)
                                    ->setSubject('Account Activated')
                                    ->send();
                            } elseif ($posted[$attributeName] === "INACTIVE") {
                                $type = "INACTIVE";
                            } elseif ($posted[$attributeName] === "PENDING") {
                                $type = "PENDING";
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

            return $this->render('index', [
                'time' => date('s'),
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        } else {
            throw new ForbiddenHttpException('Access Denied, You don\'t have permission to perform this action');
        }
    }



    public function actionView($id)
    {

        $id = Yii::$app->samparq->decryptUserData($id);
        $userModel = User::findOne(['id' => $id]);
        if (empty($userModel)) {
            throw new BadRequestHttpException('Invalid request');
        }



        if (Yii::$app->user->can("admin") || Yii::$app->user->can("monitor")) {
            return $this->render('view', [
                'model' => $userModel,
                'completed' => Yii::$app->samparq->getCompletedTrainingCount($id),
                'total' => Yii::$app->samparq->getAllTrainingCount($id)
            ]);
        } else {
            throw new ForbiddenHttpException('Access Denied, You don\'t have permission to perform this action');
        }
    }

    protected function findModel($id)
    {
        if (Yii::$app->user->can("admin")) {
            if (($model = User::findOne($id)) !== null) {
                return $model;
            } else {
                throw new NotFoundHttpException('The requested page does not exist.');
            }
        } else {
            throw new ForbiddenHttpException('Access Denied, You don\'t have permission to perform this action');
        }
    }


    public function actionAssignRole()
    {
        if (Yii::$app->user->can("admin") || Yii::$app->user->can("monitor")) {
            $model = new AuthAssignment();
            $model->scenario = 'create-assignment';
            if ($model->load(Yii::$app->request->post())) {
                $itmArr = $model->item_name;
                $userArr = $model->user_id;
                foreach ($itmArr as $item) {
                    foreach ($userArr as $userid) {
                        $newModel = new AuthAssignment();
                        $alreadyExisted = AuthAssignment::find()->where(['user_id' => $userid])->count();
                        if ($alreadyExisted == 0) {
                            $newModel->item_name = $item;
                            $newModel->user_id = $userid;
                            $newModel->save();
                        }
                    }
                }

                Yii::$app->session->setFlash('rolesAssigned', "Role has been assigned successfully");
                return $this->redirect(['role-view']);

            }

            return $this->render('assign-role', [
                'model' => $model,
                'userlist' => Yii::$app->samparq->getUList(),
                'roleList' => Yii::$app->samparq->getRole()
            ]);
        } else {
            throw new ForbiddenHttpException('Access Denied, You don\'t have permission to perform this action');
        }
    }

    public function actionAccAct()
    {
        if (Yii::$app->user->can("admin") || Yii::$app->user->can("monitor")) {
            Yii::$app->samparq->getAccActAlertsReceiver();
            $id = $_POST['id'];
            $status = $_POST['status'];
            $model = $this->findModel($id);
            if (!empty($model)) {
                $model->ac_act_alert = $status;
                if ($model->save(false)) {
                    $response = [
                        'status' => false,
                        'message' => 'success'
                    ];
                } else {
                    $response = [
                        'status' => false,
                        'message' => 'something went wrong'
                    ];
                }
            } else {
                $response = [
                    'status' => false,
                    'message' => 'something went wrong'
                ];
            }

            echo Json::encode($response);
            die;
        } else {
            throw new ForbiddenHttpException('Access Denied, You don\'t have permission to perform this action');
        }


    }

    public function actionUpdate($id)
    {
        if (Yii::$app->user->can("admin") || Yii::$app->user->can("monitor")) {
            $model = User::findOne($id);
            if (Yii::$app->request->post('User')) {
                $postedData = $_POST['User'];
                $model->name = $postedData['name'];
                $model->mobile_no = $postedData['mobile_no'];

                if ($postedData['right_status']) {
                    $assignMentModel = AuthAssignment::findOne(['user_id' => $id]);
                    if(empty($assignMentModel)){
                        $assignMentModel = new  AuthAssignment();
                        $assignMentModel->item_name = $postedData['right_status'];
                        $assignMentModel->user_id = $id;
                        $assignMentModel->save(false);
                    } else {
                        $assignMentModel->item_name = $postedData['right_status'];
                        $assignMentModel->save(false);

                    }
                }

                if($model->save(false)){
                    $id = Yii::$app->samparq->encryptUserData($id);
                    return $this->redirect(['view', 'id' => $id]);
                }
            } else {
                return $this->renderAjax('update', [
                    'model' => $model,
                    'roleList' => Yii::$app->samparq->getRole()
                ]);
            }
        } else {
            throw new ForbiddenHttpException('Access Denied, You don\'t have permission to perform this action');
        }
    }

    public function actionDelete($id)
    {
        if (Yii::$app->user->can("admin") || Yii::$app->user->can("monitor")) {

            $this->findModel($id)->delete();

            return $this->redirect(['index']);
        } else {
            throw new ForbiddenHttpException('Access Denied, You don\'t have permission to perform this action');
        }
    }

    public function actionChangePassword($id)
    {
        if (Yii::$app->user->can("admin") || Yii::$app->user->can("monitor")) {
            $model = User::findOne($id);
            return $this->render('change-password', [
                'model' => $model
            ]);
        } else {
            throw new ForbiddenHttpException('Access Denied, You don\'t have permission to perform this action');
        }
    }

    public function actionRoleView()
    {
        if (Yii::$app->user->can("admin") || Yii::$app->user->can("monitor")) {
            $searchModel = new AuthAssignmentSearch();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams, Yii::$app->request->post('action'));


            return $this->render('role-view', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        } else {
            throw new ForbiddenHttpException('Access Denied, You don\'t have permission to perform this action');
        }
    }


    public function actionUserRoleView($item_name, $uid)
    {
        $uid = Yii::$app->samparq->decryptUserData($uid);
        $userModel = User::findOne(['id' => $uid]);
        if (empty($userModel)) {
            throw new BadRequestHttpException('Invalid request');
        }

        if (Yii::$app->user->can("admin") || Yii::$app->user->can("monitor")) {
            $model = new AuthAssignment();
            $checkedListStr = "";
            $assignments = AuthAssignment::findAll(["user_id" => $uid]);
            foreach ($assignments as $assignment) {
                $checkedListStr .= $assignment->item_name . ",";

            }
            $checkedListArr = explode(",", $checkedListStr);
            return $this->render('role-management', [
                'model' => $model,
                'roles' => Yii::$app->samparq->getRole(),
                'uid' => $uid,
                'checkedList' => array_filter($checkedListArr)
            ]);
        } else {
            throw new ForbiddenHttpException('Access Denied, You don\'t have permission to perform this action');
        }
    }

    public function actionSaveRole()
    {
        if (Yii::$app->user->can("admin") || Yii::$app->user->can("monitor")) {
            $model = new AuthAssignment();
            $userid = $_POST["userid"];
            $role = $_POST["role"];
            $checkIfRecordExist = AuthAssignment::find()->where(['user_id' => $userid, 'item_name' => $role])->count();
            if ($checkIfRecordExist == 0) {
                $model->user_id = $userid;
                $model->item_name = $role;
                if ($model->save(false)) {

                    if ($role == "web-login") {
                        $userModel = User::findOne(['id' => $userid]);
                        $userModel->allow_login = 1;
                        $userModel->save(false);
                    }

                    $response = [
                        'status' => true,
                        'message' => 'success'
                    ];
                } else {
                    $response = [
                        'status' => false,
                        'message' => 'error'
                    ];
                }
            } else {
                $response = [
                    'status' => true,
                    'message' => 'success'
                ];
            }


            echo Json::encode($response);
        } else {
            throw new ForbiddenHttpException('Access Denied, You don\'t have permission to perform this action');
        }
    }

    public function actionDeleteRole()
    {
        if (Yii::$app->user->can("admin") || Yii::$app->user->can("monitor")) {
            $userid = $_POST["userid"];
            $role = $_POST["role"];
            $checkIfRecordExist = AuthAssignment::find()->where(['user_id' => $userid, 'item_name' => $role])->count();
            if ($checkIfRecordExist > 0) {
                $model = AuthAssignment::findOne(['user_id' => $userid, 'item_name' => $role]);
                if ($model->delete(false)) {
                    if ($role === "web-login") {
                        $userModel = User::findOne(['id' => $userid]);
                        $userModel->allow_login = 0;
                        $userModel->save(false);
                    }
                    $response = [
                        'status' => true,
                        'message' => 'success',
                        'count' => $checkIfRecordExist
                    ];
                } else {
                    $response = [
                        'status' => false,
                        'message' => 'error'
                    ];
                }
            } else {
                $response = [
                    'status' => true,
                    'message' => 'success',
                    'count' => $checkIfRecordExist
                ];
            }


            echo Json::encode($response);
        } else {
            throw new ForbiddenHttpException('Access Denied, You don\'t have permission to perform this action');
        }
    }


    public function actionTeamQds()
    {
        if (Yii::$app->user->can("team-qds") || Yii::$app->user->can("admin")) {

            $searchModel = new TeamQdsSearch();
            $teamQdsModel = new TeamQds();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

            $query = TeamQds::find()->orderBy('id DESC');

            if (Yii::$app->request->post('hasEditable')) {

                $uid = Yii::$app->request->post('editableKey');
                $model = TeamQds::findOne($uid);

                $out = Json::encode(['output' => '', 'message' => '']);

                $posted = current($_POST['TeamQds']);
                $post = ['TeamQds' => $posted];


                $attributeName = $_POST['editableAttribute'];

                if ($model->load($post)) {

                    $output = '';


                    if (isset($posted[$attributeName])) {

                        $model->$attributeName = $posted[$attributeName];
                        $model->save(false);
                    }

                    $out = Json::encode(['output' => $output, 'message' => '']);
                }
                echo $out;
                return;
            }
            $exportDataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => -1,
                ],
            ]);

            if ($teamQdsModel->load(Yii::$app->request->post())) {
                $emailArr = explode(',', $teamQdsModel->email);
                if (Yii::$app->request->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return ActiveForm::validate($teamQdsModel);
                }
                foreach ($emailArr as $email) {
                    $checkIfRecordExist = TeamQds::findOne(['email' => $email]);
                    if (count($checkIfRecordExist) > 0) {
                        $model = $checkIfRecordExist;
                    } else {
                        $model = new TeamQds();
                    }

                    $model->email = $email;
                    $model->created_by = Yii::$app->user->id;
                    $model->save();
                }
                Yii::$app->session->setFlash('success', 'Email has been added successfully!');
                return $this->redirect(Yii::$app->request->referrer);
            }

            return $this->render('team-qds', [
                'searchModel' => $searchModel,
                'teamQdsModel' => $teamQdsModel,
                'dataProvider' => $dataProvider,
                'exportDataProvider' => $exportDataProvider
            ]);

        } else {
            throw new ForbiddenHttpException("Access Denied, You don't have permission to perform this action");
        }
    }

    public function actionSyncData()
    {
        if (isset($_POST['checkedValue'])) {
            $ids = @explode(',', $_POST['checkedValue']);
            $eids = [];
            $uModel = User::findAll(['id' => $ids]);
            if (!empty($uModel)) {
                foreach ($uModel as $ems) {
                    $eids[] = $ems->employee_id;
                }
            }

            $userDataModel = CdUser::findAll(['employee_id' => $eids]);

        } else {
            $userDataModel = CdUser::find()->all();
        }


        foreach ($userDataModel as $userData) {
            if (!empty($userData->email)) {
                $userModel = User::findOne(['email' => $userData->email]);
                if (!empty($userModel)) {
                    $userModel->employee_id = $userData->employee_id;
                    $userModel->name = $userData->name;
                    $userModel->gender = $userData->gender;
                    $userModel->email = $userData->email;
                    $userModel->aadhar_no = $userData->aadhar_no;
                    $userModel->mobile_no = $userData->phone;
                    $userModel->pan = $userData->pan;
                    $userModel->employement_status = $userData->active;
                    if ($userModel->flag != "PENDING") {
                        $userModel->flag = $userData->active == 1 ? "ACTIVE" : "INACTIVE";
                    }
                    $userModel->dob = $userData->dob;
                    $userModel->joining_date = $userData->joining_date;
                    $userModel->confirmation_date = $userData->confirmation_date;
                    $userModel->marital_status = $userData->marital_status;
                    $userModel->branch = $userData->branch;
                    $userModel->department = $userData->department;
                    $userModel->designation = $userData->designation;
                    $userModel->category = $userData->category;
                    $userModel->reporting_in_charge1 = $userData->reporting_in_charge1;
                    $userModel->reporting_in_charge2 = $userData->reporting_in_charge2;
                    $userModel->save(false);
                }

            }

        }

        Yii::$app->session->setFlash('success', 'Data synchronized successfully');
        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionForceLogout()
    {
        if (isset($_POST['checkedValue'])) {
            $ids = @explode(',', $_POST['checkedValue']);
            $models = User::findAll(['flag' => 'ACTIVE', 'id' => $ids]);
        } else {
            $models = User::findAll(['flag' => 'ACTIVE']);
        }


        foreach ($models as $model) {
            $model->update_status = 1;
            $model->save(false);
        }
        Yii::$app->session->setFlash('success', 'All app users has been logged out forcefully');
        return $this->redirect(Yii::$app->request->referrer);
    }


    public function actionDeleteTeam($id)
    {
        $findModel = TeamQds::findOne(['id' => $id]);
        if ($findModel->delete()) {
            Yii::$app->session->setFlash('success', 'Email has been deleted successfully!');
            return $this->redirect(['team-qds']);
        }

    }


    //Group management


    public function actionGroupManagement()
    {
        $searchModel = new GroupSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('group-management', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider
        ]);
    }

    public function actionCreateGroup()
    {
        $model = new Group();

        if ($model->load(Yii::$app->request->post())) {

            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            }

            $model->save();
            Yii::$app->session->setFlash('success', 'Group has been created successfully');
            return $this->redirect(['group-management']);
        }

        return $this->render('create-group', [
            'model' => $model
        ]);

    }

    public function actionAddGroupMembers($gid = false, $tid = false)
    {


        if ((isset($gid) && !empty($gid))) {
            $gid = Yii::$app->samparq->decryptUserData($gid);
            $groupModel = Group::findOne(['id' => $gid]);
            if (empty($groupModel)) {
                throw new BadRequestHttpException('Invalid request');
            }
        }

        if ((isset($tid) && !empty($tid))) {
            $tid = Yii::$app->samparq->decryptUserData($tid);
            $trainingModel = Training::findOne(['id' => $tid]);
            if (empty($trainingModel)) {
                throw new BadRequestHttpException('Invalid request');
            }
        }


        if (empty($gid) || empty($groupModel)) {
            $groupModel = new Group();
        }

        $model = new GroupMembers();

        if (!empty($gid)) {
            $checkIfRecordExist = GroupMembers::findOne(['group_id' => $gid]);
            if (count($checkIfRecordExist) > 0) {
                $model = $checkIfRecordExist;
            }
        }

        if ($model->load(Yii::$app->request->post()) && $groupModel->load(Yii::$app->request->post())) {


            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ArrayHelper::merge(
                    ActiveForm::validate($model),
                    ActiveForm::validate($groupModel)
                );
            }

            if ($groupModel->save(false)) {
                $model->group_id = $groupModel->id;
                $model->user_id = implode(',', $model->user_id);
                if ($model->save()) {
                    if (isset($tid) && !empty($tid)) {
                        return $this->redirect(['training/create-training', 'tid' => Yii::$app->samparq->encryptUserData($model->id)]);
                    } else
                        Yii::$app->session->setFlash('success', 'Group has been created successfully');
                    return $this->redirect(['group-management']);
                }
            }

        }

        return $this->render('add-group-members', [
            'model' => $model,
            'tid' => isset($tid) && !empty($tid) ? $tid : "",
            'groupModel' => $groupModel,
            'groupList' => ArrayHelper::map(Group::findAll(['status' => 1, 'created_by' => Yii::$app->user->id]), 'id', "name"),
            'memberList' => ArrayHelper::map(User::find()->where(['flag' => 'ACTIVE'])->andWhere(['!=', 'id', 13])->all(), 'id', 'email')
        ]);

    }

    public function actionBulkRegistration()
    {

        $userModel = new User();

        if ($userModel->load(Yii::$app->request->post())) {


            $string = str_shuffle('!123456789@#$ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz_');

            $getLicenseCount = Client::findOne(['code' => Yii::$app->session->get('client')])->no_of_users;

            $emailArr = [];

            $csvFile = UploadedFile::getInstance($userModel, 'email_csv');


            if (!empty($csvFile)) {

                $emailArr = file($csvFile->tempName);


            } elseif (!empty($userModel->email_arr)) {
                $emailArr = $userModel->email_arr;
            } else {
                return $this->redirect(['index']);
            }


            $emailArr = array_unique($emailArr);

            if (!empty($emailArr)) {
                foreach ($emailArr as $email) {
                    $userCount = User::find()->where(['flag' => 'ACTIVE'])->andWhere(['!=','id',13])->count();
                    $model = new User();
                    $model->username = $email;
                    $model->email = $email;
                    $model->client_code = Yii::$app->session->get('client');
                    $getRandomstring = substr($string, 0, 8);

                    $model->password_hash = Yii::$app->security->generatePasswordHash($getRandomstring);
                    $model->flag = "ACTIVE";
                    if($getLicenseCount >= $userCount){
                        //$model->save(false);
                            Yii::$app->samparq->createUserAppPasswordHash($getRandomstring, $model->id);

                    } else {
                        $limitExceeded = (count($emailArr) - $getLicenseCount) < 0 ? count($emailArr) : (count($emailArr) - $getLicenseCount);
                        Yii::$app->session->setFlash('userexceeded', $limitExceeded.' has been skipped due to your license limit has exceeded, please extend license to proceed further registrations.');
                        return $this->redirect(['index']);
                    }
                }
            }
        }

        return $this->render('_bulk_registration', [
            'model' => $userModel
        ]);

    }

    public function actionCreateClient()
    {
        if(Yii::$app->user->can('admin')){
            $model = new Client();

            if ($model->load(Yii::$app->request->post())) {

                $client_email = $model->email;
                $client_name = ucfirst($model->name);
                $code = strtoupper($model->code);

                if (Yii::$app->request->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return ActiveForm::validate($model);
                }

                $model->status = 'ACTIVE';
                $model->created_by = Yii::$app->user->id;
                $endDate = strtotime(date("Y-m-d H:i:s", strtotime($model->subscription_sd)) . " +".$model->months." month");
                $model->subscription_ed = date("Y-m-d H:i:s",$endDate);
                if($model->save()){
                    $fname = strtolower(str_replace('-','_', $model->code));

                    Yii::$app->samparq->createDbConfigFile($fname);

                    $default_image = "default.png";
                    $string = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*(*(()";
                    $shuffle = str_shuffle($string);
                    $randPassword = substr($shuffle, 0 , 8);
                    $pwd = Yii::$app->security->generatePasswordHash($randPassword);

                    $db_name = strtolower(str_replace('-', '_', $model->code));

                  Yii::$app->samparq->sendEmail([
                      'client_name' => $model->name,
                      'client_email' => $model->email,
                      'client_pwd' => $randPassword,
                      'client_code' => $db_name

                  ], $model->email, 'Account activation alert');
                    Yii::$app->db->createCommand('CREATE DATABASE ' . $db_name . '')->execute();

                    $createTable = "
create table " . $db_name . ".`auth_rule`
(
`name` varchar(64) not null,
`data` text,
`created_at` integer,
`updated_at` integer,
    primary key (`name`)
) engine InnoDB;

create table " . $db_name . ".`auth_item`
(
`name` varchar(64) not null,
`type` integer not null,
`description` text,
`rule_name` varchar(64),
`data` text,
`created_at` integer,
`updated_at` integer,
primary key (`name`),
foreign key (`rule_name`) references `auth_rule` (`name`) on delete set null on update cascade,
key `type` (`type`)
) engine InnoDB;

create table " . $db_name . ".`auth_item_child`
(
`parent` varchar(64) not null,
`child` varchar(64) not null,
primary key (`parent`, `child`),
foreign key (`parent`) references `auth_item` (`name`) on delete cascade on update cascade,
foreign key (`child`) references `auth_item` (`name`) on delete cascade on update cascade
) engine InnoDB;

create table " . $db_name . ".`auth_assignment`
(
`item_name` varchar(64) not null,
`user_id` varchar(64) not null,
`created_at` integer,
primary key (`item_name`, `user_id`),
foreign key (`item_name`) references `auth_item` (`name`) on delete cascade on update cascade
) engine InnoDB;



CREATE TABLE " . $db_name . ".`group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) DEFAULT NULL,
  `status` enum('1','0') DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE " . $db_name . ".`group_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` text,
  `group_id` int(11) DEFAULT NULL,
  `status` enum('1','0') DEFAULT '1',
  `added_by` int(11) DEFAULT NULL,
  `added_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE " . $db_name . ".`login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ipaddress` varchar(100) CHARACTER SET latin1 DEFAULT NULL,
  `username` varchar(100) CHARACTER SET latin1 DEFAULT NULL,
  `password` varchar(100) CHARACTER SET latin1 DEFAULT NULL,
  `attempt_time` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_by` varchar(45) CHARACTER SET latin1 DEFAULT 'SYSTEM',
  `status` int(1) DEFAULT '0' COMMENT '0 = Unblocked, 1 = Blocked',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE " . $db_name . ".`login_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) DEFAULT NULL,
  `username` varchar(100) CHARACTER SET latin1 DEFAULT NULL,
  `login_time` datetime DEFAULT NULL,
  `logout_time` datetime DEFAULT NULL,
  `total_time` varchar(45) CHARACTER SET latin1 DEFAULT NULL,
  `last_activity` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_by` varchar(45) CHARACTER SET latin1 DEFAULT 'SYSTEM',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE " . $db_name . ".`notification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type_id` int(11) DEFAULT NULL,
  `post_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `message` varchar(255) DEFAULT NULL,
  `status` int(1) DEFAULT '1',
  `read_status` int(1) DEFAULT '0',
  `seen_status` int(1) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE " . $db_name . ".`permission` (
  `id` int(11) NOT NULL,
  `profile_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `status` int(11) DEFAULT '1',
  `created_by` int(11) DEFAULT NULL,
  `created_on` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE " . $db_name . ".`push_notification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_ids` text,
  `text` text,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE " . $db_name . ".`t_chat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `training_id` int(11) DEFAULT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text,
  `read_status` tinyint(1) NOT NULL DEFAULT '0',
  `status` tinyint(1) DEFAULT '1',
  `attachment_status` tinyint(1) DEFAULT '0',
  `attachment_type` tinyint(1) DEFAULT NULL,
  `original_filename` varchar(255) DEFAULT NULL,
  `new_filename` varchar(45) DEFAULT NULL,
  `file_path` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE " . $db_name . ".`trainees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `training_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `start_time` datetime DEFAULT NULL,
  `status` int(11) DEFAULT '0' COMMENT '0 = inactive, 1 = active,2= submitted',
  `certificate_download` int(1) DEFAULT '0',
  `feedback` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_by` int(11) DEFAULT NULL,
  `training_sd` datetime DEFAULT NULL,
  `training_ed` datetime DEFAULT NULL,
  `notification_status` int(1) DEFAULT '0',
  `type` int(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE " . $db_name . ".`training` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `trainer_name` varchar(100) DEFAULT NULL,
  `client_code` varchar(45) DEFAULT NULL,
  `training_title` varchar(255) DEFAULT NULL,
  `description` text,
  `file_new_name` varchar(255) DEFAULT NULL,
  `file_original_name` varchar(255) DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `status` int(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `training_sd` datetime DEFAULT NULL,
  `training_ed` datetime DEFAULT NULL,
  `welcome_template` longtext,
  `allow_prev` int(1) DEFAULT '0',
  `instructions` longtext,
  `thanks_template` longtext,
  `enable_otp` int(1) DEFAULT '0',
  `allow_print_answersheet` int(1) DEFAULT '0',
  `show_answersheet` int(1) DEFAULT '0',
  `web_status` int(1) DEFAULT '0',
  `download_report` int(1) DEFAULT '0',
  `training_type` int(1) DEFAULT '0',
  `training_with_assessment` int(1) DEFAULT '0',
  `assessment_type` int(1) DEFAULT '0',
  `show_result` int(1) DEFAULT '0',
  `youtube_url` varchar(255) DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `training_question_status` int(1) DEFAULT '0',
  `shuffle_question` int(1) DEFAULT '0',
  `feedback_required` int(1) DEFAULT '0',
  `feedback_message` varchar(255) DEFAULT NULL,
  `pass_score` int(11) DEFAULT NULL,
  `time_control` tinyint(4) DEFAULT NULL,
  `certificate_status` int(1) DEFAULT '0',
  `availability_status` tinyint(4) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE " . $db_name . ".`training_material` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `training_id` int(11) DEFAULT NULL,
  `original_name` varchar(100) DEFAULT NULL,
  `new_name` varchar(100) DEFAULT NULL,
  `path` varchar(100) DEFAULT NULL,
  `type` int(1) DEFAULT NULL COMMENT '0 = pdf, 1 = video',
  `status` int(1) DEFAULT '1',
  `download_status` int(1) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE " . $db_name . ".`training_notification` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `title` varchar(80) DEFAULT NULL,
  `description` varchar(250) DEFAULT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `read_status` int(11) DEFAULT '0' COMMENT '''0=Unread\n''1=Read''',
  `status` int(11) DEFAULT '1' COMMENT '''0''=removed\n''1''=Active',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE " . $db_name . ".`training_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tquestion_id` int(11) DEFAULT NULL,
  `option_value` text,
  `is_answer` int(1) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE " . $db_name . ".`training_question` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `training_id` int(11) DEFAULT NULL,
  `ref_id` int(11) DEFAULT NULL,
  `question` text,
  `type` int(11) DEFAULT NULL,
  `marks` float DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `has_negative` int(1) DEFAULT NULL,
  `negative_mark` int(11) DEFAULT '0',
  `status` int(11) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `created_by` int(11) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `is_required` int(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE " . $db_name . ".`training_submission` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `training_id` int(11) DEFAULT NULL,
  `question_type` int(11) DEFAULT NULL,
  `question_id` int(11) DEFAULT NULL,
  `option_id` int(11) DEFAULT NULL,
  `training_submitted_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `comment_box` text,
  `other` varchar(45) DEFAULT NULL,
  `original_file_name` text,
  `new_file_name` text,
  `file_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE " . $db_name . ".`upload_files` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `mail_to` int(11) DEFAULT NULL,
  `mail_from` int(11) DEFAULT NULL,
  `process_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `file_name` varchar(250) CHARACTER SET latin1 DEFAULT NULL,
  `file_path` varchar(250) CHARACTER SET latin1 DEFAULT NULL,
  `file_script` varchar(250) CHARACTER SET latin1 DEFAULT NULL,
  `orignal_filename` varchar(250) CHARACTER SET latin1 DEFAULT NULL,
  `ext` varchar(100) CHARACTER SET latin1 DEFAULT NULL,
  `upload_filescol` varchar(45) CHARACTER SET latin1 DEFAULT NULL,
  `inbox_id` bigint(20) DEFAULT NULL,
  `sent_id` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE " . $db_name . ".`user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `client_code` varchar(45) COLLATE utf8_bin DEFAULT NULL,
  `auth_key` varchar(32) CHARACTER SET utf8 DEFAULT NULL,
  `password_hash` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `password_hash_app` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `password_reset_token` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `status` smallint(6) NOT NULL DEFAULT '10',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `user_type` int(11) DEFAULT NULL,
  `name` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `last_name` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
  `flag` enum('ACTIVE','INACTIVE','BLOCKED','PENDING') CHARACTER SET utf8 DEFAULT 'ACTIVE',
  `mobile_permission` int(11) DEFAULT NULL,
  `imei_app` varchar(45) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `app_regid` longtext CHARACTER SET utf8,
  `employee_id` varchar(45) COLLATE utf8_bin DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `image_path` varchar(250) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `image_name` varchar(250) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `mobile_no` bigint(20) DEFAULT NULL,
  `key` int(11) DEFAULT '0',
  `update_status` int(11) DEFAULT '0',
  `form_submit` int(11) DEFAULT '0',
  `email_conf` int(11) DEFAULT '0',
  `super_admin` int(11) DEFAULT '0' COMMENT '''0''=> Not Super admin\n''1''=> Super Admin',
  `lastlogin_time` datetime DEFAULT NULL,
  `test` varchar(45) CHARACTER SET utf8 DEFAULT NULL,
  `auth_token` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `otp_status` int(1) DEFAULT '0',
  `ac_act_alert` int(1) DEFAULT NULL,
  `remark` varchar(250) CHARACTER SET utf8 DEFAULT NULL,
  `allow_login` int(1) DEFAULT NULL,
  `changed_password` int(1) DEFAULT '0',
  `gender` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `aadhar_no` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `pan` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `employement_status` tinyint(4) DEFAULT '0',
  `joining_date` date DEFAULT NULL,
  `confirmation_date` date DEFAULT NULL,
  `marital_status` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `branch` varchar(200) CHARACTER SET utf8 DEFAULT NULL,
  `department` varchar(200) CHARACTER SET utf8 DEFAULT NULL,
  `designation` varchar(200) CHARACTER SET utf8 DEFAULT NULL,
  `category` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `reporting_in_charge1` varchar(45) CHARACTER SET utf8 DEFAULT NULL,
  `reporting_in_charge2` varchar(45) CHARACTER SET utf8 DEFAULT NULL,
  `sync_status` int(1) DEFAULT NULL,
  `email_flag_update` int(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `password_reset_token` (`password_reset_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
";

                    $tableData = "INSERT INTO " . $db_name . ".`auth_item` (`name`, `type`, `description`, `data`, `created_at`, `updated_at`) VALUES ('admin', '2', 'admin rights', '?', '2017-10-06 17:42:50', '2018-03-31 09:53:35');
INSERT INTO " . $db_name . ".`auth_item` (`name`, `type`, `description`, `data`, `created_at`) VALUES ('instructor', '2', 'trainer rights', '?', '2018-03-31 09:53:35');
INSERT INTO " . $db_name . ".`auth_item` (`name`, `type`, `description`, `data`, `created_at`, `updated_at`) VALUES ('monitor', '1', 'Have control over system', '?', '2017-10-06 17:26:46', '2018-03-31 09:53:35');

INSERT INTO " . $db_name . ".`auth_assignment` (`item_name`, `user_id`, `created_at`) VALUES ('admin', '13', '2018-05-16 11:52:16');
INSERT INTO " . $db_name . ".`auth_assignment` (`item_name`, `user_id`, `created_at`) VALUES ('monitor', '1', '2018-05-16 11:52:23');

INSERT INTO " . $db_name . ".`user` (`username`, `client_code`, `password_hash`, `password_hash_app`, `email`, `status`, `created_at`, `updated_at`, `user_type`, `name`, `flag`, `imei_app`, `app_regid`, `employee_id`, `dob`, `image_path`, `image_name`, `mobile_no`, `key`, `update_status`, `form_submit`, `email_conf`, `super_admin`, `lastlogin_time`, `auth_token`, `otp_status`, `ac_act_alert`, `allow_login`, `changed_password`, `gender`, `aadhar_no`, `pan`, `employement_status`, `joining_date`, `confirmation_date`, `marital_status`, `branch`, `department`, `designation`, `category`, `reporting_in_charge1`, `reporting_in_charge2`, `email_flag_update`) VALUES ('" . $client_email . "', '" . $code . "', '" . $pwd . "', 'sha1$27aa7e15$1$5e264sdfsdfsdf8a912e8ebd58610f5a553b94e9994b0e86b', '" . $client_email . "', '10', '2017-09-11 16:16:13', '', '2', '" . $client_name . "', 'ACTIVE', '', '', '', '', 'http://samparq.qdegrees.com/Upload_Files/', '" . $default_image . "', '', '1', '0', '1', '0', '1', '0000-00-00 00:00:00', '', '0', '1', '1', '0', '', '', '', '', '', '', '', '', '', '', '', '', '', '0');
INSERT INTO " . $db_name . ".`user` (`id`,`username`, `client_code`, `password_hash`, `password_hash_app`, `email`, `status`, `created_at`, `updated_at`, `user_type`, `name`, `flag`, `imei_app`, `app_regid`, `employee_id`, `dob`, `image_path`, `image_name`, `mobile_no`, `key`, `update_status`, `form_submit`, `email_conf`, `super_admin`, `lastlogin_time`, `auth_token`, `otp_status`, `ac_act_alert`, `allow_login`, `changed_password`, `gender`, `aadhar_no`, `pan`, `employement_status`, `joining_date`, `confirmation_date`, `marital_status`, `branch`, `department`, `designation`, `category`, `reporting_in_charge1`, `reporting_in_charge2`, `email_flag_update`) VALUES (13,'saurabh.rai@qdegrees.com', 'QDEG-123456', '" . Yii::$app->security->generatePasswordHash('saurabh89') . "', 'sha1$27aa7e15$1$5e264sdfsdfsdf8a912e8ebd58610f5a553b94e9994b0e86b', 'saurabh.rai@qdegrees.com', '10', '2017-09-11 16:16:13', '', '2', 'Saurabh Rai', 'ACTIVE', '', '', '', '', 'http://samparq.qdegrees.com/Upload_Files/', '" . $default_image . "', '', '1', '0', '1', '0', '1', '0000-00-00 00:00:00', '', '0', '1', '1', '1', '', '', '', '', '', '', '', '', '', '', '', '', '', '0');
";
                    Yii::$app->db->createCommand($createTable)->execute();
                    Yii::$app->db->createCommand($tableData)->execute();

                    Yii::$app->session->setFlash('ClientSuccess', 'Client has been added successfully');

                    return $this->redirect(['view-client']);
                }
            }


            return $this->render('add-client', [
                'model' => $model,
                'list' => Yii::$app->samparq->getLicenseList()
            ]);
        } else {
            throw new ForbiddenHttpException('Access denied!');
        }
    }

    public function actionViewClient()
    {
       if(Yii::$app->user->can('admin')){
           $modelSearch = new ClientSearch();
           $dataProvider = $modelSearch->search(Yii::$app->request->queryParams);

           if (Yii::$app->request->post('hasEditable')) {

               $uid = Yii::$app->request->post('editableKey');
               $model = Client::findOne($uid);

               $out = Json::encode(['output' => '', 'message' => '']);

               $posted = current($_POST['Client']);
               $post = ['Client' => $posted];


               $attributeName = $_POST['editableAttribute'];

               if ($model->load($post)) {

                   $output = '';


                   if (isset($posted[$attributeName])) {

                       if ($attributeName === 'status') {
                           if ($posted[$attributeName] === "Pending") {
                               $type = "Pending";
                           } elseif ($posted[$attributeName] === "Active") {
                               $type = "Active";
                           } elseif ($posted[$attributeName] === "Expired") {
                               $type = "Expired";
                           } elseif ($posted[$attributeName] === "Renewal Pending") {
                               $type = "Renewal Pending";
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

           return $this->render('view-client', [
               'dataProvider' => $dataProvider,
               'modelSearch' => $modelSearch,
           ]);
       } else {
           throw new ForbiddenHttpException('Access denied!');
       }
    }


}