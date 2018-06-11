<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 24/9/17
 * Time: 2:50 PM
 */

namespace backend\controllers;

use backend\models\AssessmentSearch;
use backend\models\Chat;
use backend\models\ChatSearch;
use backend\models\Options;
use backend\models\Trainees;
use backend\models\TraineesSearch;
use backend\models\Training;
use backend\models\TrainingMaterial;
use backend\models\TrainingNotification;
use backend\models\TrainingQuestion;
use backend\models\TrainingQuestionSearch;
use common\models\Model;
use common\models\User;
use FFMpeg\Coordinate\Dimension;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\Driver\FFProbeDriver;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Yii;
use backend\models\TrainingSearch;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;
use yii\db\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\Response;
use yii\web\UploadedFile;
use yii\widgets\ActiveForm;
use kartik\mpdf\Pdf;
use yii\imagine\Image;


class TrainingController extends CommonController
{


    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        if (Yii::$app->user->can("monitor") || Yii::$app->user->can("admin") || Yii::$app->user->can("instructor")) {

            $searchModel = new TrainingSearch();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

            $query = Training::find()->orderBy('id DESC');


            $exportDataProvider = new ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => -1,
                ],
            ]);


            return $this->render('index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'exportDataProvider' => $exportDataProvider
            ]);

        } else {
            throw new ForbiddenHttpException("Access Denied, You don't have permission to perform this action");
        }
    }

    public function actionView($id)
    {

        $id = Yii::$app->samparq->decryptUserData($id);

        if (Yii::$app->user->can("monitor") || Yii::$app->user->can("admin") || Yii::$app->user->can("instructor")) {

            $trainingModel = Training::findOne(['id' => $id]);

            if (empty($trainingModel)) {
                throw new BadRequestHttpException('Invalid request');
            }


            $materials = $trainingModel->materials;

            $trainees = $trainingModel->trainees;
            $questions = $trainingModel->questions;

            $searchModel = new TraineesSearch();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $id);
            $searchModelQuestion = new TrainingQuestionSearch();
            $dataProviderQuestion = $searchModelQuestion->search(Yii::$app->request->queryParams, $id);


            return $this->render('view', [
                'tid' => $id,
                'disability' => Yii::$app->samparq->checkDisability($trainingModel->end_date, false),
                'availability' => $trainingModel->availability_status,
                'expired' => Yii::$app->samparq->checkDisability($trainingModel->end_date, 2, $trainingModel->availability_status),
                'model' => $trainingModel,
                'materials' => $materials,
                'trainees' => $trainees,
                'trUrl' => $trainingModel->training_type == 0 ? ['assessment-setup', 'tid' => $trainingModel->id] : ['create-training', 'tid' => $trainingModel->id],
                'title' => $trainingModel->training_type == 0 ? "Add Assessment" : "Add Question",
                'questions' => $questions,
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'searchModelQuestion' => $searchModelQuestion,
                'dataProviderQuestion' => $dataProviderQuestion,
            ]);

        } else {
            throw new ForbiddenHttpException("Access Denied, You don't have permission to perform this action");
        }
    }

    public function actionCreate()
    {
        if (Yii::$app->user->can("monitor") || Yii::$app->user->can("admin") || Yii::$app->user->can("instructor")) {
            $model = new Training();
            if ($model->load(Yii::$app->request->post())) {
                if (Yii::$app->request->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return ActiveForm::validate($model);
                }
                $path = Yii::getAlias('@frontend/web/Upload_Files/');
                $image = UploadedFile::getInstance($model, 'file_new_name');
                if (!empty($image)) {
                    $model->file_original_name = $image->name;
                    $newName = time() . "." . $image->extension;
                    $model->file_new_name = $newName;
                    $image->saveAs($path . $newName);
                    Image::thumbnail($path . $newName, 333, 200)->save(Yii::getAlias($path . $newName), ['quality' => 80]);

                }


                $model->created_by = Yii::$app->user->id;
                $model->status = 0;
                $model->client_code = Yii::$app->session->get('client');

                if ($model->save(false)) {
                    return $this->redirect(['add-trainees', 'tid' => Yii::$app->samparq->encryptUserData($model->id)]);
                }
            }
            return $this->render('create', [
                'model' => $model
            ]);
        } else {
            throw new ForbiddenHttpException("Access Denied, You don't have permission to perform this action");
        }
    }

    public function actionCreateTraining($tid, $isUpdate = false)
    {
        if (Yii::$app->user->can("monitor") || Yii::$app->user->can("admin") || Yii::$app->user->can("instructor")) {


            if (!isset($tid)) {
                return $this->redirect(['index']);
            } else {

                $tid = Yii::$app->samparq->decryptUserData($tid);

                $trainingModel = Training::findOne(['id' => $tid]);

                if (empty($trainingModel)) {
                    throw new BadRequestHttpException('Invalid request');
                }

                $trainingQuery = Training::find()->where(['id' => $tid]);
                $trainingExist = $trainingQuery->count();

                if ($trainingExist == 0) {
                    return $this->redirect(['index']);
                }

            }


            $tMaterialModel = new TrainingMaterial();
            $traineeModel = new Trainees();
            $material = TrainingMaterial::findAll(['training_id' => $tid]);

            $trainingModel = $trainingQuery->one();

            if ($traineeModel->load(Yii::$app->request->post()) && $trainingModel->load(Yii::$app->request->post())) {

                if (!empty($trainingModel->youtube_url)) {
                    $trainingModel->youtube_url = implode(',', array_filter($trainingModel->youtube_url));
                }

                $userIds = $traineeModel->user_id;

                foreach ($userIds as $uid) {
                    $checkIfRecordExist = Trainees::find()->where(['user_id' => $uid, 'training_id' => $tid])->count();

                    if ($checkIfRecordExist == 0) {


                        $tmodel = new Trainees();
                        $tmodel->user_id = $uid;
                        $tmodel->username = Yii::$app->samparq->getUsernameById($uid);
                        $tmodel->training_id = $tid;
                        $tmodel->status = 1;
                        $tmodel->save();
                    }


                }

                $trainingModel->status = 1;

                if ($trainingModel->save(false)) {
                    return $this->redirect(['assessment-setup', 'tid' => Yii::$app->samparq->encryptUserData($tid)]);
                }

            }

            return $this->render('create-training', [
                'material' => $material,
                'trainingModel' => $trainingModel,
                'tid' => $tid,
                'isUpdate' => $isUpdate,
                'uList' => Yii::$app->samparq->getGroupWiseUList(),
                'traineeModel' => $traineeModel,
                'tMaterial' => $tMaterialModel,
                'modelsOption' => (empty($modelsOption)) ? [new Options()] : $modelsOption
            ]);

        } else {
            throw new ForbiddenHttpException("Access Denied, You don't have permission to perform this action");
        }
    }

    public function actionAssessmentSetup($tid, $fromView = false)
    {

        $tid = Yii::$app->samparq->decryptUserData($tid);

        $trainingModel = Training::findOne(['id' => $tid]);

        if (empty($trainingModel)) {
            throw new BadRequestHttpException('Invalid request');
        }

        $surveyType = [
            1 => 'Conduct Quiz',
            2 => 'Conduct Survey'
        ];

        if ($trainingModel->load(Yii::$app->request->post())) {

            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($trainingModel);
            }

            if ($trainingModel->save(false)) {
                if (isset($fromView) && $fromView != false) {
                    return $this->redirect(['view', 'id' => Yii::$app->samparq->encryptUserData($tid)]);
                } else {
                    return $this->redirect(['create-question', 'tid' => Yii::$app->samparq->encryptUserData($tid)]);
                }

            }
        }

        return $this->render('assessment-setup', [
            'model' => $trainingModel,
            'surveyType' => $surveyType
        ]);
    }

    public function actionUploadFiles()
    {
        if (Yii::$app->user->can("monitor") || Yii::$app->user->can("admin") || Yii::$app->user->can("instructor")) {
            $attachmentModel = new TrainingMaterial();
            $imageFile = UploadedFile::getInstance($attachmentModel, 'new_name');
            $directory = Yii::getAlias('@frontend/web/Upload_Files') . DIRECTORY_SEPARATOR;


            if (!is_dir($directory)) {
                FileHelper::createDirectory($directory);
            }
            if ($imageFile) {

                $tid = $_POST['tid'];
                $attModelProject = new TrainingMaterial();
                $uid = time();
                $fileName = 't' . $uid . 'material.' . $imageFile->extension;


                if ($imageFile->extension == "mp4") {
                    $attModelProject->type = 1;

                } else if ($imageFile->extension == "pdf") {

                    $attModelProject->type = 0;
                } else if ($imageFile->extension == "xlsx" || $imageFile->extension == "xls") {

                    $attModelProject->type = 2;
                } else if ($imageFile->extension == "doc" || $imageFile->extension == "docx") {

                    $attModelProject->type = 3;
                } else if ($imageFile->extension == "ppt" || $imageFile->extension == "pptx") {

                    $attModelProject->type = 4;
                }


                $attModelProject->new_name = $fileName;
                $attModelProject->training_id = $tid;
                $attModelProject->original_name = $imageFile->name;
                $attModelProject->training_id = $_POST['tid'];
                $filePath = $directory . $fileName;
                $attModelProject->path = $filePath;
                $attModelProject->save(false);

                if ($imageFile->saveAs($filePath)) {
                    $path = '/Upload_Files/' . DIRECTORY_SEPARATOR . $fileName;
                    return Json::encode([
                        'files' => [
                            [
                                'id' => $attModelProject->id,
                                'name' => $fileName,
                                'size' => $imageFile->size,
                                'url' => $path,
                                'thumbnailUrl' => $path,
                                'deleteUrl' => 'image-delete?name=' . $fileName,
                                'deleteType' => 'POST',
                            ],
                        ],
                    ]);
                }
            }

            return '';
        } else {
            throw new ForbiddenHttpException("Access Denied, You don't have permission to perform this action");
        }
    }

    public function actionImageDelete()
    {

        $id = Yii::$app->samparq->decryptUserData($_REQUEST['id']);
        $attModel = TrainingMaterial::findOne(['id' => $id]);
        $directory = Yii::getAlias('@frontend/web/Upload_Files');
        if (is_file($directory . DIRECTORY_SEPARATOR . $attModel->new_name)) {
            unlink($directory . DIRECTORY_SEPARATOR . $attModel->new_name);
            if ($attModel->delete()) {
                echo Json::encode([
                    "staus" => true,
                    "message" => 'successfully deleted'
                ]);
            }
        } else {
            echo Json::encode([
                'status' => false,
                'message' => 'No such file found'
            ]);
        }

    }

    public function actionCreateQuestion($tid, $quid = false, $isUpdate = false)
    {
        if (Yii::$app->user->can("monitor") || Yii::$app->user->can("admin") || Yii::$app->user->can("instructor")) {

            $tid = Yii::$app->samparq->decryptUserData($tid);

            $tQueryModel = Training::findOne(['id' => $tid]);

            if (empty($tQueryModel)) {
                throw new BadRequestHttpException('Invalid requestssss');
            }


            if (!isset($tid)) {
                return $this->redirect(['index']);
            }



            if ((isset($quid) && !empty($quid))) {
                $quid = Yii::$app->samparq->decryptUserData($quid);

            }
            if ((isset($quid) && !empty($quid)) && !empty($isUpdate)) {


                $tQModel = TrainingQuestion::findOne(['id' => $quid]);

                if (empty($tQModel)) {
                    throw new BadRequestHttpException('Invalid requesat');
                }
            }



            $tQuery = Training::find()->where(['id' => $tid]);


            if (isset($finalSubmit) && $finalSubmit == true) {
                $updateModel = $tQuery->one();
                $updateModel->training_question_status = 1;
                $updateModel->save();
            }


            $searchModelQuestion = new TrainingQuestionSearch();
            $dataProviderQuestion = $searchModelQuestion->search(Yii::$app->request->queryParams, $tid);

            $checkIfRecordExist = $tQuery->count();
            $trainingModel = $tQuery->one();
            $questions = $trainingModel->questions;
            if ($trainingModel->assessment_type == 0) {
                return $this->redirect(['assessment-setup', 'tid' => Yii::$app->samparq->encryptUserData($tid)]);
            }


            if ($trainingModel->assessment_type == 1) {
                $questionType = Yii::$app->samparq->getQuestionType('COMMON');
            } else {
                $questionType = Yii::$app->samparq->getQuestionType('SURVEY');
            }

            if ($checkIfRecordExist == 0) {
                return $this->redirect(['index']);
            }
            $showPrevButton = TrainingQuestion::findOne(['id' => ($quid - 1), 'training_id' => $tid]);

            if (isset($quid) && !empty($quid) && isset($isUpdate) && !empty($isUpdate)) {
                $modelQuestion = TrainingQuestion::findOne(['id' => $quid]);
                $modelsOption = empty($modelQuestion->options) ? [new Options()] : $modelQuestion->options;
            } else {
                $modelQuestion = new TrainingQuestion();
                $modelsOption = [new Options()];
            }

            if ($modelQuestion->load(Yii::$app->request->post()) && $trainingModel->load(Yii::$app->request->post())) {

                if (isset($quid) && !empty($quid) && isset($isUpdate) && !empty($isUpdate)) {
                    $oldIDs = ArrayHelper::map($modelsOption, 'id', 'id');
                    $modelsOption = Model::createMultiple(Options::classname(), $modelsOption);
                    Model::loadMultiple($modelsOption, Yii::$app->request->post());
                    $deletedIDs = array_diff($oldIDs, array_filter(ArrayHelper::map($modelsOption, 'id', 'id')));
                } else {
                    $modelsOption = Model::createMultiple(Options::classname());
                    Model::loadMultiple($modelsOption, Yii::$app->request->post());
                }


                $image = UploadedFile::getInstance($modelQuestion, 'image');

                if ($image) {
                    $imageName = time() . '_' . $image->name;
                    $directory = Yii::getAlias('@frontend/web/Upload_Files') . DIRECTORY_SEPARATOR;
                    $filePath = $directory . $imageName;
                    if ($image->saveAs($filePath)) {
                        $modelQuestion->image = Yii::$app->params['file_url'] . $imageName;
                    }
                }


                if (Yii::$app->request->isAjax) {

                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return ArrayHelper::merge(
                        ActiveForm::validateMultiple($modelsOption),
                        ActiveForm::validate($modelQuestion)
                    );
                }

                if (Yii::$app->request->isAjax) {

                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return ArrayHelper::merge(
                        ActiveForm::validateMultiple($modelsOption),
                        ActiveForm::validate($modelQuestion)
                    );
                }

                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    $modelQuestion->created_by = Yii::$app->user->id;
                    $modelQuestion->training_id = $tid;
                    if ($trainingModel->assessment_type == 1) {
                        $modelQuestion->is_required = 1;
                    }

                    $trainingModel->save(false);

                    if ($flag = $modelQuestion->save(false)) {


                        if (!empty($deletedIDs)) {
                            Options::deleteAll(['id' => $deletedIDs]);
                        }

                        if ($modelQuestion->type == 1 || $modelQuestion->type == 3) {

                            foreach ($modelsOption as $modelOption) {
                                $modelOption->tquestion_id = $modelQuestion->id;
                                $modelOption->created_by = Yii::$app->user->id;
                                if (!($flag = $modelOption->save(false))) {
                                    $transaction->rollBack();
                                    break;
                                }
                            }
                        }
                    }
                    if ($flag) {
                        $transaction->commit();
                        if ($modelQuestion->is_submitted == 1) {

                            $Tmodel = Training::findOne(['id' => $tid]);
                            $Tmodel->status = 1;
                            $typeArr = [
                                'Title' => $Tmodel->training_title,
                                'Description' => "Training assigned by " . $Tmodel->trainer_name,
                                'Body' => "Training assigned by " . $Tmodel->trainer_name,
                                'process_type' => 'training_assign',
                            ];

                            if ($Tmodel->save(false)) {
                                $ttModel = Trainees::findAll(['training_id' => $tid]);

                                foreach ($ttModel as $tm) {
                                    $tmodel = Trainees::findOne(['id' => $tm->id]);
                                    $tmodel->status = 1;
                                    $tmodel->save(false);
                                }

                                $userModel = Trainees::findAll(['status' => 1, 'training_id' => $tid]);
                                Yii::$app->samparq->sendNotificationToTrainees($typeArr, $tid);
                                Yii::$app->samparq->sendNewTrainingNotification($userModel, $Tmodel->trainer_name, $Tmodel->training_title, false, $tid);
                                return $this->redirect(['view', 'id' => Yii::$app->samparq->encryptUserData($tid)]);
                            }

                        } else {
                            if (isset($isUpdate) && !empty($isUpdate)) {
                                if (Yii::$app->samparq->checkIfQuestionExist($modelQuestion->id + 1) === true) {
                                    return $this->redirect(['create-question', 'tid' => Yii::$app->samparq->encryptUserData($tid), 'quid' => Yii::$app->samparq->encryptUserData($modelQuestion->id + 1), 'isUpdate' => "true"]);
                                } else {
                                    return $this->redirect(['create-question', 'tid' => Yii::$app->samparq->encryptUserData($tid), 'quid' => Yii::$app->samparq->encryptUserData($modelQuestion->id + 1)]);
                                }
                            } else {
                                return $this->redirect(['create-question', 'tid' => Yii::$app->samparq->encryptUserData($tid), 'quid' => Yii::$app->samparq->encryptUserData($modelQuestion->id + 1)]);
                            }
                        }
                    }
                } catch (Exception $e) {
                    $transaction->rollBack();
                }
            }

            return $this->render('create-option', [
                'tid' => $tid,
                'showPrevButton' => empty($showPrevButton) ? false : true,
                'modelQuestion' => (empty($isUpdate)) ? new TrainingQuestion() : $modelQuestion,
                'trainingModel' => $trainingModel,
                'modelsOption' => (empty($isUpdate)) ? [new Options()] : $modelsOption,
                'questionTypeListing' => $questionType,
                'dataProviderQuestion' => $dataProviderQuestion,
                'searchModelQuestion' => $searchModelQuestion,
                'questions' => $questions,
            ]);
        } else {
            throw new ForbiddenHttpException("Access Denied, You don't have permission to perform this action");
        }
    }

    public function actionTrainingModal($type)
    {
        if (Yii::$app->user->can("monitor") || Yii::$app->user->can("admin") || Yii::$app->user->can("instructor")) {
            $model = new TrainingQuestion();
            if ($model->load(Yii::$app->request->post())) {
                Yii::$app->session->set('tid', $model->training_id);

                if (Yii::$app->request->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return ActiveForm::validate($model);
                }

                if ($type === "preview") {
                    return $this->redirect(['assessment-preview', 'tid' => Yii::$app->session->get('tid')]);
                } else {

                    return $this->redirect(['assessment-setup', 'tid' => Yii::$app->session->get('tid')]);
                }

            }
            return $this->renderAjax('assessment', [
                'model' => $model,
                'type' => $type === 'preview' ? null : 0
            ]);
        } else {
            throw new ForbiddenHttpException("Access Denied, You don't have permission to perform this action");
        }

    }

    public function actionAssessmentPreview($tid)
    {
        if (Yii::$app->user->can("monitor") || Yii::$app->user->can("admin") || Yii::$app->user->can("instructor")) {

            $tid = Yii::$app->samparq->decryptUserData($tid);

            $tQuery = Training::find()->where(['id' => $tid]);

            if (empty($tQuery)) {
                throw new BadRequestHttpException('Invalid request');
            }

            $modelQuestion = TrainingQuestion::findAll(['training_id' => $tid]);
            if (count($modelQuestion) == 0) {
                $modelQuestion = [];
            }

            return $this->render('preview', [
                'modelQuestion' => $modelQuestion,
                'trainingTitle' => Yii::$app->samparq->getTrainingTitle($tid)
            ]);
        } else {
            throw new ForbiddenHttpException("Access Denied, You don't have permission to perform this action");
        }
    }

    public function actionTraineesView()
    {
        if (Yii::$app->user->can("monitor") || Yii::$app->user->can("admin") || Yii::$app->user->can("instructor")) {
            $searchModel = new AssessmentSearch();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams, Yii::$app->request->post('action'));


            return $this->render('trainee-view', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,

            ]);

        } else {
            throw new ForbiddenHttpException("Access Denied, You don't have permission to perform this action");
        }
    }

    public function actionTraineeResult($tid, $trainee_id)
    {
        $tid = Yii::$app->samparq->decryptUserData($tid);
        $trainee_id = Yii::$app->samparq->decryptUserData($trainee_id);

        $tQuery = Training::findOne(['id' => $tid]);
        $uQuery = User::findOne(['id' => $trainee_id]);


        if (empty($tQuery) || empty($uQuery)) {
            throw new BadRequestHttpException('Invalid request');
        }

        if (Yii::$app->user->can("monitor") || Yii::$app->user->can("admin") || Yii::$app->user->can("instructor")) {
            $qModel = TrainingQuestion::find()->where(['training_id' => $tid]);
            $QuestionCount = clone $qModel;
            $pages = new Pagination(['totalCount' => $QuestionCount->count()]);
            $modelQuestion = $qModel->offset($pages->offset)
                ->limit($pages->limit)
                ->all();
            return $this->render('result', [
                'traineeName' => Yii::$app->samparq->getUsernameById($trainee_id),
                'answers' => Yii::$app->samparq->getAssessmentSummary($tid, $trainee_id),
                'modelQuestion' => $modelQuestion,
                'trainingTitle' => Yii::$app->samparq->getTrainingTitle($tid),
                'questionCount' => Yii::$app->samparq->getTotalQuestionCount($tid),
                'marksObtained' => Yii::$app->samparq->getMarksObtained($tid, $trainee_id),
                'totalMarks' => Yii::$app->samparq->calculateTotalMarks($tid),
                'pages' => $pages,
                'tid' => $tid,
                'uid' => $trainee_id
            ]);
        } else {
            throw new ForbiddenHttpException("Access Denied, You don't have permission to perform this action");
        }
    }

    public function actionReport($tid, $trainee_id)
    {


        if (Yii::$app->user->can("monitor") || Yii::$app->user->can("admin") || Yii::$app->user->can("instructor")) {
            $qModel = TrainingQuestion::find()->where(['training_id' => $tid]);
            $QuestionCount = clone $qModel;
            $pages = new Pagination(['totalCount' => $QuestionCount->count()]);
            $modelQuestion = $qModel->offset($pages->offset)
                ->limit($pages->limit)
                ->all();


            $content = $this->renderAjax('result', [
                'traineeName' => Yii::$app->samparq->getUsernameById($trainee_id),
                'answers' => Yii::$app->samparq->getAssessmentSummary($tid, $trainee_id),
                'modelQuestion' => $modelQuestion,
                'trainingTitle' => Yii::$app->samparq->getTrainingTitle($tid),
                'questionCount' => Yii::$app->samparq->getTotalQuestionCount($tid),
                'marksObtained' => Yii::$app->samparq->getMarksObtained($tid, $trainee_id),
                'totalMarks' => Yii::$app->samparq->calculateTotalMarks($tid),
                'pages' => $pages,
                'tid' => $tid,
                'uid' => $trainee_id


            ]);


            $pdf = new Pdf([

                'mode' => Pdf::MODE_UTF8,

                'format' => Pdf::FORMAT_A4,

                'orientation' => Pdf::ORIENT_PORTRAIT,

                'destination' => Pdf::DEST_BROWSER,

                'content' => $content,

                'cssFile' => '@vendor/kartik-v/yii2-mpdf/assets/kv-mpdf-bootstrap.min.css',
                'cssInline' => '.kv-heading-1{font-size:18px}',
                'options' => ['title' => Yii::$app->samparq->getUsernameById($trainee_id) . " Training Report"],
                'methods' => [
                    'SetHeader' => [Yii::$app->samparq->getTrainingTitle($tid)],
                    'SetFooter' => ['{PAGENO}'],
                ]
            ]);


            return $pdf->render();
        } else {
            throw new ForbiddenHttpException("Access Denied, You don't have permission to perform this action");
        }
    }

    public function actionChangeStatus()
    {
        if (Yii::$app->user->can("monitor") || Yii::$app->user->can("admin") || Yii::$app->user->can("instructor")) {
            $id = $_POST['id'];
            $type = $_POST['type'];
            $status = $_POST['status'];
            if ($type === "trainee") {
                $model = Trainees::findOne(['id' => $id]);
            } elseif ($type === "material") {
                $model = TrainingMaterial::findOne(['id' => $id]);
            } elseif ($type === "question") {
                $model = TrainingQuestion::findOne(['id' => $id]);
            } else {
                $res = [
                    'st' => false,
                    'message' => 'invalid type'
                ];
            }

            if (isset($model)) {
                $model->status = $status;
                if ($model->save(false)) {
                    $res = [
                        'newstat' => $model->status,
                        'st' => true,
                        'message' => 'success'
                    ];
                } else {
                    $res = [
                        'st' => false,
                        'message' => 'success'
                    ];
                }
            }

            echo Json::encode($res);
            die;
        } else {
            throw new ForbiddenHttpException("Access Denied, You don't have permission to perform this action");
        }
    }

    public function actionSendNotification()
    {
        $model = new TrainingNotification();
        if ($model->load(Yii::$app->request->post())) {
            foreach ($model->user_id as $uid) {
                $userArr[] = $uid;
                $tModel = new TrainingNotification();
                $tModel->user_id = $uid;
                $tModel->title = $model->title;
                $tModel->description = $model->description;
                $tModel->save(false);
            }
            $typeArr = [
                'Title' => $model->title,
                'Description' => $model->description,
                'Body' => $model->description,
                'process_type' => 'training_notification'
            ];

            Yii::$app->samparq->sendDynamicNotification($userArr, $typeArr);
        }

        return $this->render('send-notification', [
            'model' => $model,
            'userType' => Yii::$app->samparq->getUList()
        ]);
    }

    public function actionCreateWebTraining()
    {
        $trainingModel = new Training();
        $traineesModel = new Trainees();
        $tainingList = ArrayHelper::map(Training::find()->all(), 'id', 'training_title');

        if ($traineesModel->load(Yii::$app->request->post()) && $trainingModel->load(Yii::$app->request->post())) {
            $tModel = Training::findOne(['id' => $traineesModel->training_id]);
            $tModel->welcome_template = $trainingModel->welcome_template;
            $tModel->thanks_template = $trainingModel->thanks_template;
            $tModel->allow_prev = $trainingModel->allow_prev;
            $tModel->enable_otp = $trainingModel->enable_otp;
            $tModel->show_result = empty($trainingModel->show_result) ? 0 : $trainingModel->show_result;
            $tModel->show_answersheet = $trainingModel->show_answersheet;
            $tModel->allow_print_answersheet = $trainingModel->allow_print_answersheet;
            $tModel->web_status = 1;
            if ($tModel->save(false)) {
                $traineesArr = $traineesModel->user_id;
                Yii::$app->samparq->sendNewTrainingNotification($traineesArr, $tModel->trainer_name, $tModel->training_title, true, $traineesModel->training_id);
                foreach ($traineesArr as $trainee) {
                    $trModel = Trainees::findOne(['user_id' => $trainee, 'training_id' => $traineesModel->id]);
                    if (empty($trModel)) {
                        $trainee = new Trainees();
                        $trainee->user_id = $trainee;
                        $trainee->training_id = $traineesModel->training_id;
                        $trainee->username = Yii::$app->samparq->getUsernameById($trainee);
                        $trainee->status = 1;
                        $trainee->type = 1;
                        $trainee->save(false);
                    } else {
                        $trModel->type = 1;
                        $trModel->save(false);
                    }
                }
            }

        }
        return $this->render('create-web', [
            'trainingModel' => $trainingModel,
            'traineesModel' => $traineesModel,
            'trainingList' => $tainingList,
            'uList' => Yii::$app->samparq->getUList(),
        ]);
    }

    public function actionUpdateDate()
    {
        $id = Yii::$app->samparq->decryptUserData(Yii::$app->request->post('id'));
        $date = Yii::$app->request->post('date');
        $field = Yii::$app->request->post('field');
        $model = Training::findOne(['id' => $id]);
        if (empty($model)) {
            $response = [
                'status' => false,
                'message' => 'something went wrong please try again later'
            ];
        } else {
            $model->$field = $date;
            $model->save(false);
            $response = [
                'status' => true,
                'message' => 'Success',
                'newDate' => date("M d Y h:i:s A", strtotime($model->$field))
            ];
        }

        Yii::$app->session->setFlash('importSuccess', 'Date has been updated successfully');
        return $this->redirect(['view', 'id' => Yii::$app->samparq->encryptUserData($id)]);
    }

    //Training library management
    public function actionLibrary()
    {

        $searchModel = new TrainingQuestionSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, false, Yii::$app->user->id);

        return $this->render('import-all-question', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionAddTrainees($tid = false)
    {
        return $this->redirect(['user/add-group-members', 'tid' => $tid]);
    }

    public function actionImportIndividualQuestion($id)
    {
        $model = New TrainingQuestion();


        if (!empty($id)) {


            $trainingDetails = Training::findOne(['id' => $id]);

            if ($model->load(Yii::$app->request->post())) {

                $idsArr = @explode(',', $id);
                $modelQuestions = TrainingQuestion::findAll(['id' => $idsArr]);

                $importedQuestion = 0;


                if (Yii::$app->request->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return ActiveForm::validate($model);
                }

                foreach ($modelQuestions as $question) {
                    $newQuestionModel = new TrainingQuestion();
                    $newQuestionModel->training_id = $model->import_id;
                    $newQuestionModel->question = $question->question;
                    $newQuestionModel->type = $question->type;


                    $newQuestionModel->marks = $question->marks;
                    $newQuestionModel->has_negative = $question->has_negative;
                    $newQuestionModel->negative_mark = $question->negative_mark;
                    $newQuestionModel->status = $question->status;

                    $checkIfRecordExist = TrainingQuestion::find()->where(['question' => $question->question, 'training_id' => $model->import_id])->count();

                    if ($checkIfRecordExist == 0 && $trainingDetails->training_type == $question->type) {
                        $importedQuestion++;

                        if ($newQuestionModel->save(false)) {
                            if (!empty($question->options)) {
                                foreach ($question->options as $option) {
                                    $optionModel = new Options();
                                    $optionModel->tquestion_id = $newQuestionModel->id;
                                    $optionModel->option_value = $newQuestionModel->id;
                                    $optionModel->is_answer = $option->is_answer;
                                    $optionModel->save(false);
                                }
                            }
                        }
                    }

                }


                Yii::$app->session->setFlash('importSuccess', $importedQuestion . " questions has been imported successfully out of " . count($modelQuestions));

                return $this->redirect(['view', 'id' => Yii::$app->samparq->encryptUserData($model->import_id)]);
            }
        }

        return $this->renderAjax('_import-question', [
            'model' => $model,
            'trainingList' => Yii::$app->samparq->getTrainingsToImport($id)
        ]);
    }

    public function actionTrainingQuestion($id)
    {
        $model = New TrainingQuestion();

        $id = Yii::$app->samparq->decryptUserData($id);

        $trainingDetails = Training::findOne(['id' => $id]);

        if (empty($trainingDetails)) {

            throw new BadRequestHttpException('Invalid request');
        }

        if ($model->load(Yii::$app->request->post())) {


            $model->training_id = $id;

            $modelQuestions = TrainingQuestion::find()
                ->joinWith('options')
                ->where(['training_question.training_id' => $id])
                ->all();

            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            }


            foreach ($modelQuestions as $question) {
                $newQuestionModel = new TrainingQuestion();
                $newQuestionModel->ref_id = $id;
                $newQuestionModel->training_id = $model->import_id;
                $newQuestionModel->question = $question->question;
                $newQuestionModel->type = $question->type;
                $newQuestionModel->marks = $question->marks;
                $newQuestionModel->has_negative = $question->has_negative;
                $newQuestionModel->negative_mark = $question->negative_mark;
                $newQuestionModel->status = $question->status;
                if ($newQuestionModel->save(false)) {

                    if (!empty($question->options)) {
                        foreach ($question->options as $option) {
                            $optionModel = new Options();
                            $optionModel->tquestion_id = $newQuestionModel->id;
                            $optionModel->option_value = $newQuestionModel->id;
                            $optionModel->is_answer = $option->is_answer;
                            $optionModel->save(false);
                        }
                    }
                }
            }

            Yii::$app->session->setFlash('importSuccess', count($modelQuestions) . " questions has been imported successfully!");

            return $this->redirect(['view', 'id' => Yii::$app->samparq->encryptUserData($id)]);


        }

        return $this->renderAjax('_import-question', [
            'model' => $model,
            'trainingList' => Yii::$app->samparq->getTrainingsToImport($id)
        ]);
    }

    public function actionUpdateDownloadStatus()
    {
        if ((isset($_POST['id']) && !empty($_POST['id']) && (isset($_POST['status']) && !empty($_POST['status'])))) {
            $model = TrainingMaterial::findOne(['id' => $_POST['id']]);
            if (!empty($model)) {
                $model->download_status = $_POST['status'];

                if ($model->save(false)) {
                    echo Json::encode(['status' => true]);
                } else {
                    echo Json::encode(['status' => false]);
                }
            }

        } else {
            echo Json::encode(['status' => false]);
        }
    }

    public function actionManageTraining($tid, $type)
    {

        $tid = Yii::$app->samparq->decryptUserData($tid);

        if ((isset($tid) && !empty($tid)) && (isset($type) && !empty($type))) {

            $trainingModel = Training::findOne(['id' => $tid]);

            if (empty($trainingModel)) {
                throw new BadRequestHttpException('Invalid request');
            }

            if (!empty($trainingModel)) {

                if ($type == 5) {

                    $trainingModel->availability_status = 2;
                    $trainingModel->save(false);

                    Yii::$app->session->setFlash('importSuccess', 'Training has been paused successfully');

                } else {
                    $trainingModel->availability_status = 1;

                    $userModel = Trainees::findAll(['status' => 1, 'training_id' => $tid, 'notification_status' => 0]);

                    $typeArr = [
                        'Title' => $trainingModel->training_title,
                        'Description' => "Training assigned by " . $trainingModel->trainer_name,
                        'Body' => "Training assigned by " . $trainingModel->trainer_name,
                        'training_id' => $trainingModel->id,
                        'process_type' => 'training_assign',
                    ];

                    $trainingModel->save(false);

                    Yii::$app->samparq->sendNotificationToTrainees($typeArr, $tid);
                    Yii::$app->samparq->sendNewTrainingNotification($userModel, $trainingModel->trainer_name, $trainingModel->training_title, false, $tid);

                    Yii::$app->session->setFlash('importSuccess', 'Training has been processed successfully');
                }
                return $this->redirect(['view', 'id' => Yii::$app->samparq->encryptUserData($tid)]);
            }

        } else {
            throw new ForbiddenHttpException("Invalid arguments, please try again later");
        }


    }

    public function actionCheckTrainingStatus()
    {
        $id = Yii::$app->request->post('id');
        if (!isset($id) || empty($id)) {
            echo Json::encode([
                "status" => false
            ]);
        } else {
            $getQuestionCount = Yii::$app->samparq->getTotalQuestionCount($id);
            $getTraineesCount = Yii::$app->samparq->getTraineesCount($id);

            $trainingModel = Training::findOne(['id' => $id]);

            if ($getQuestionCount == 0) {
                echo Json::encode([
                    'status' => true,
                    'message' => ' Please add at least one question to publish training <a href="#">Add Question</a>'
                ]);
            } elseif ($getTraineesCount == 0) {
                echo Json::encode([
                    'status' => true,
                    'message' => ' Please add at least one trainee to publish training <a href="#">Add Trainee</a>'
                ]);
            } elseif ($trainingModel->assessment_type == 0) {
                echo Json::encode([
                    'status' => true,
                    'message' => ' Please configure training before publishing <a href="#">Setting</a>'
                ]);
            } else {

                if ($trainingModel->availability_status == 0) {
                    $message = ' You will not be able to do any modification after publishing training. <br/><a href="' . Url::toRoute(['manage-training', 'tid' => $id, 'type' => 7]) . '">Proceed</a> | <a href="#">Cancel</a>';
                } else {
                    $message = ' Training will not be able to available to the trainees. <br/><a href="' . Url::toRoute(['manage-training', 'tid' => $id, 'type' => 5]) . '">Proceed</a> | <a href="#">Cancel</a>';
                }

                echo Json::encode([
                    'status' => true,
                    'message' => $message
                ]);
            }
        }
    }

    public function actionChat()
    {


        $searchModel = new ChatSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('chat', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionChatView($tid, $uid, $rid)
    {

        $tid = Yii::$app->samparq->decryptUserData($tid);
        $uid = Yii::$app->samparq->decryptUserData($uid);
        $rid = Yii::$app->samparq->decryptUserData($rid);

        $chats = Chat::find()
            ->where(['or', ['sender_id' => $uid, 'receiver_id' => $rid], ['sender_id' => $rid, 'receiver_id' => $uid]])
            ->andWhere(['training_id' => $tid])
            ->all();

        $model = new Chat();

        if ($model->load(Yii::$app->request->post())) {

            $model->training_id = $tid;
            $model->sender_id = Yii::$app->user->id;
            $model->receiver_id = $rid;
            if ($model->save(false)) {

                $data = [
                    'receiver_id' => $rid,
                    'id' => $model->id,
                    'sender_id' => $tid,
                    'message' => "New message",
                    'file_status' => '0',
                    'file_name' => '',
                    'file_extension' => '',
                    'file_orignal_name' => '',
                    'file_path' => '',
                    'process_type' => 'ask_trainer',
                    'process_date' => date('Y-m-d H:i:s'),
                    'read_flag' => '0',
                    'sender_name' => Yii::$app->user->identity->name,
                    'Body' => 'Send new message'
                ];

                Yii::$app->samparq->sendSingleChatNotification($data);
                return $this->redirect(Yii::$app->request->referrer);
            }
        }
        return $this->render('chat_view', [
            'chats' => $chats,
            'model' => $model
        ]);
    }

    public function actionQuestionPrev($quid){
     
        $quid = Yii::$app->samparq->decryptUserData($quid);
        $questionModel = TrainingQuestion::findOne(['id' => $quid]);

        return $this->renderAjax('question-prev', [
            'questionModel' => $questionModel
        ]);
    }

}

