<?php
/**
 * Created by PhpStorm.
 * User: saurabh
 * Date: 2/9/17
 * Time: 11:57 AM
 */

namespace common\components;

use backend\models\CdUser;
use backend\models\Chat;
use backend\models\Client;
use backend\models\DepartmentModel;
use backend\models\GroupMembers;
use backend\models\License;
use backend\models\LoginLog;
use backend\models\Notification;
use backend\models\Options;
use backend\models\PolicyTypeMaster;
use backend\models\PostLog;
use backend\models\QuestionType;
use backend\models\Trainees;
use backend\models\Training;
use backend\models\TrainingOptionType;
use backend\models\TrainingQuestion;
use backend\models\TrainingSubmission;
use backend\models\UploadFiles;
use backend\models\WebcastViewers;
use Codeception\Lib\Generator\Group;
use Codeception\Module\Cli;
use common\models\TblChatgroup;
use common\models\TblChatgroupMembers;
use common\models\TblChatgroupReadstatus;
use common\models\TblFeedbackMember;
use common\models\TblFeedbackReadstatus;
use common\models\User;
use console\models\AuthItem;
use Yii;
use backend\models\TblPost;
use yii\base\Component;
use yii\base\ErrorException;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\ForbiddenHttpException;

class Samparq extends Component
{
    public function getComments($id)
    {
        $getPost = $this->findPost($id);
        $getPostComments = $getPost->comments;
        $getAttachmentComments = $getPost->attachmentComments;
        return array_merge($getPostComments, $getAttachmentComments);

    }

    public function findPost($id)
    {
        $model = TblPost::findOne(['id' => $id]);
        return $model;
    }

    public function encryptUserData($data)
    {
        $cryptKey = 'abCDjdskf48756125SFxvB';
        $encodedData = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($cryptKey), $data, MCRYPT_MODE_CBC, md5(md5($cryptKey))));
        return ($encodedData);
    }

    function decryptUserData($data)
    {
        $cryptKey = 'abCDjdskf48756125SFxvB';
        $decodedData = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($cryptKey), base64_decode($data), MCRYPT_MODE_CBC, md5(md5($cryptKey))), "\0");
        return ($decodedData);
    }

    public function getLikeCount($id)
    {
        $getPost = $this->findPost($id);
        $getPostCount = $getPost->likes;
        return count($getPostCount);

    }

    public function getAttachment($id)
    {
        $getPost = $this->findPost($id);
        $getAttachments = $getPost->attachments;
        return $getAttachments;
    }

    public function getActiveList()
    {
        $activeList = [0 => 'Inactive', 1 => 'Active'];
        return $activeList;
    }

    public function getPolicyTypes()
    {
        $model = PolicyTypeMaster::findAll(['flag' => 'ACTIVE']);
        $typeList = ArrayHelper::map($model, 'id', 'name');
        return $typeList;
    }

    public function getUserStatusList()
    {
        $statusList = ['ACTIVE' => 'ACTIVE', 'INACTIVE' => 'INACTIVE', 'BLOCKED' => 'BLOCKED', 'PENDING' => 'PENDING'];
        return $statusList;
    }

    public function getClientStatusList()
    {
        $statusList = ['Pending' => 'Pending', 'Active' => 'Active', 'Expired' => 'Expired', 'Renewal Pending' => 'Renewal Pending'];
        return $statusList;
    }

    public function getUsersByType($typeArr, $format = false)
    {
        $userIds = [];
        $dtArr = [];
        foreach ($typeArr as $arr) {
            $userModel = User::findAll(['user_type' => $arr, 'flag' => 'ACTIVE']);
            foreach ($userModel as $um) {

                $userIds[] = [
                    'id' => $um->id,
                    'type_id' => $um->user_type,
                ];
            }
        }

        if ($format === 'string') {

            $singleArray = array();

            foreach ($userIds as $key => $value) {
                $singleArray[$key] = $value['id'];
            }
            foreach ($typeArr as $tr) {
                $dtArr[] = $this->getDepartmentName($tr);
            }
            $users = @implode(',', $singleArray);
            $typeName = @implode(',', $dtArr);
            $typeIds = @implode(',', $typeArr);
            $userIds = [
                'id' => $users,
                'typeIds' => $typeIds,
                'typeNames' => $typeName,
            ];
        }


        return $userIds;
    }

    public function sendEmailNotification($receiverid, $senderid, $usertype, $inboxid, $subject)
    {

        $userModel = User::find()
            ->where(['id' => $receiverid, 'user_type' => $usertype, 'flag' => 'ACTIVE'])
            ->one();

        if (!empty($userModel)) {
            if (!empty($userModel->app_regid)) {
                $reg_id = $userModel->app_regid;
                $registrationIds = array($reg_id);
                $msg = [
                    'process_type' => 'email_type',
                    'inbox_id' => $inboxid,
                    'subject' => $subject,
                    'Sendername' => $this->getUsernameById($senderid)
                ];
                $fields = array
                (
                    'registration_ids' => $registrationIds,
                    'data' => $msg
                );
                $headers = array
                (
                    'Authorization: key=' . Yii::$app->params['API_ACCESS_KEY'],
                    'Content-Type: application/json'
                );
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
                curl_exec($ch);
                curl_close($ch);
            }

        }


    }

    public function getUsernameById($id)
    {
        $userModel = User::findOne(['id' => $id]);
        return empty($userModel) ? "-" : $userModel->name;
    }

    public function sendNotificationToAll($type, $postId, $typeId)
    {
        $users = [];
        $userModel = User::findAll(['flag' => 'ACTIVE']);

        foreach ($userModel as $key => $um) {
            echo $key . "<br/>";
            if (Yii::$app->user->id != $um->id) {
                $notificationModel = new Notification();
                $notificationModel->message = $type['message'];
                $notificationModel->type_id = $typeId;
                $notificationModel->user_id = $um->id;
                $notificationModel->post_id = $postId;
                $notificationModel->created_at = date('Y-m-d H:i:s');
                $notificationModel->sender_id = Yii::$app->user->id;
                $notificationModel->save(false);
                if (!empty($um->app_regid)) {
                    $users[] = [
                        'username' => $um->username,
                        'app_regid' => $um->app_regid,
                    ];
                }
            }

        }


        $this->sendNotification($users, $type);
    }

    public function sendNotification($users, $typeArr)
    {


        foreach ($users as $user) {
            $reg_id = $user['app_regid'];
            $registrationIds = array($reg_id);
            $msg = $typeArr;
            $fields = array
            (
                'registration_ids' => $registrationIds,
                'notification' => array('title' => $typeArr['Title'], 'body' => $typeArr['Body'], 'sound' => 'default'),
                'data' => $msg
            );

            $headers = array
            (
                'Authorization: key=' . Yii::$app->params['API_ACCESS_KEY'],
                'Content-Type: application/json'
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            curl_exec($ch);
            curl_close($ch);
        }
    }

    public function sendSingleChatNotification($data)
    {
        $users = [];
        $userModel = User::findOne(['id' => $data['receiver_id']]);

        $notificationModel = new Notification();
        $notificationModel->message = $data['message'];
        $notificationModel->type_id = 16;
        $notificationModel->user_id = $userModel->id;
        $notificationModel->post_id = $data['sender_id'];
        $notificationModel->created_at = date('Y-m-d H:i:s');
        $notificationModel->sender_id = Yii::$app->user->id;
        $notificationModel->save(false);


        if (!empty($userModel->app_regid)) {
            $users[] = [
                'username' => $userModel->username,
                'app_regid' => $userModel->app_regid,
            ];
        }


        $this->sendNotification($users, $data);
    }

    public function getTraineesList($tid)
    {

        $traineeList = ArrayHelper::map(Trainees::findAll(['training_id' => $tid]), 'user_id', 'user_id');
        return $traineeList;
    }

    public function sendNotificationToTrainees($type, $tid)
    {
        $users = [];
        $userModel = Trainees::findAll(['status' => 1, 'training_id' => $tid]);
        foreach ($userModel as $um) {
            $users[] = [
                'username' => $um->username,
                'app_regid' => $this->getRegId($um->user_id),
            ];
        }

        $this->sendNotification($users, $type);
    }

    public function getRegId($id)
    {
        $model = User::findOne(['id' => $id]);
        return !empty($model) ? $model->app_regid : "";
    }

    public function sendDynamicNotification($users, $type)
    {
        foreach ($users as $um) {
            $userss[] = [
                'app_regid' => $this->getRegId($um),
            ];
        }

        $this->sendNotification($userss, $type);

    }

    public function getDateForMail($date)
    {
        date_default_timezone_set('Asia/Calcutta');
        $currentTimeStamp = strtotime(date("Y-m-d H:i:s"));
        $oldTimeStamp = strtotime($date);
        $timeStampDiff = $currentTimeStamp - $oldTimeStamp;
        if ($timeStampDiff >= 86400) {
            return $date;
        } else {
            return Yii::$app->formatter->asRelativeTime($oldTimeStamp);
        }
    }

    public function getUserEmployeeId($id)
    {
        $userModel = User::findOne(['id' => $id]);
        return $userModel->employee_id;
    }

    public function getUserRightList()
    {
        $list = [1 => 'Admin', 2 => 'User'];
        return $list;
    }

    public function getQuestionType($type)
    {

        if ($type == 'SURVEY') {
            $model = QuestionType::find()
                ->where(['status' => 'ACTIVE'])
                ->orWhere(['type' => 'COMMON'])
                ->orderBy(['type' => 'SURVEY'])
                ->all();
        } else {
            $model = QuestionType::findAll(['status' => 'ACTIVE', 'type' => $type]);
        }

        return empty($model) ? [] : ArrayHelper::map($model, 'id', 'name');
    }

    public function getAccActAlertsReceiver()
    {
        $model = User::findAll(['ac_act_alert' => 1]);
        if (empty($model)) {
            $receivers = ['saurabh.rai@qdegrees.com'];
        } else {
            foreach ($model as $recvrs) {
                $receivers[] = $recvrs->email;
            }
        }

        return $receivers;
    }

    public function getPolicyTypeName($id)
    {
        $policyTypeModel = PolicyTypeMaster::findOne(['id' => $id]);
        if (empty($policyTypeModel)) {
            return 'unknown';
        } else {
            return $policyTypeModel->name;
        }
    }

    public function createLoginLog($username, $userid, $login_time = false, $logout_time = false, $last_activity = false)
    {
        $model = LoginLog::findOne(['userid' => $userid, 'logout_time' => null]);

        if (empty($model)) {
            $model = new LoginLog();
        }

        if (!empty($username)) {
            $model->username = $username;
        }
        if (!empty($userid)) {
            $model->userid = $userid;
        }
        if (!empty($login_time)) {
            $model->login_time = $login_time;
        } else {
            $model->logout_time = $logout_time;
        }
        if (empty(($model->logout_time))) {
            $model->last_activity = $last_activity;
            $model->logout_time = null;
        }

        if (!empty(($model->logout_time) && !empty(($model->logout_time)))) {
            $loginTimeStamp = strtotime($model->login_time);
            $logoutTimeStamp = strtotime($model->logout_time);
            $timeDiff = $logoutTimeStamp - $loginTimeStamp;
            $totaTime = round($timeDiff / 60);
            $model->total_time = $totaTime . " Min (approx)";
        }

        $model->save(false);
    }

    public function createPostLog($username, $userid, $postid, $comment_id = false, $description = false)
    {

        $model = new PostLog();
        $model->username = $username;
        $model->userid = $userid;
        $model->post_id = $postid;
        if ($comment_id != false) {
            $model->comment_id = $comment_id;
        }
        $model->description = $description;
        $model->save(false);
    }

    public function getMailAttachments($id, $filename)
    {

        $model = UploadFiles::find()->select(['file_name', 'orignal_filename', 'file_name'])->where([$filename => $id])->distinct()->all();
        $fileName = [];
        if (empty($model)) {
            $fileName = [];
        } else {
            foreach ($model as $md) {
                $fileName[] = [
                    'filename' => $md->file_name,
                    'originalName' => $md->orignal_filename,
                    'download_path' => Yii::$app->params['file_url'] . $md->file_name
                ];
            }
        }

        return $fileName;
    }

    public function sendFeedbackNotification($fId, $typeArr, $senderId)
    {
        $userModel = TblFeedbackMember::findAll(['status' => 1, 'feedback_id' => $fId]);
        foreach ($userModel as $user) {
            $statusModel = new TblFeedbackReadstatus();
            if ($user->user_id != $senderId) {
                $statusModel->status = 0;
                $statusModel->user_id = $user->user_id;
                $statusModel->message_id = $fId;
                $statusModel->save();
                $users = User::findOne(['id' => $user->user_id]);
                $userArr[] = [
                    'username' => $users->username,
                    'app_regid' => $users->app_regid,
                ];
            }

        }

        $this->sendNotification($userArr, $typeArr);
    }

    public function getGroupCount($model, $mid, $uid)
    {
        if ($model === "chat") {
            $countModel = TblChatgroupReadstatus::find()->where(['user_id' => $uid, 'group_id' => $mid, 'status' => 0])->count();
        } elseif ($model === "feedback") {
            $countModel = TblFeedbackReadstatus::find()->where(['user_id' => $uid, 'feedback_id' => $mid, 'status' => 0])->count();
        } else {
            $countModel = 0;
        }

        return $countModel;
    }

    public function sendChatGroupNotification($groupId, $typeArr, $senderId)
    {
        $userModel = TblChatgroupMembers::findAll(['status' => 1, 'group_id' => $groupId]);
        $userArr = [];
        foreach ($userModel as $user) {
            $readModel = new TblChatgroupReadstatus();
            if ($user->user_id != $senderId) {
                $readModel->message_id = $typeArr['message_id'];
                $readModel->group_id = $groupId;
                $readModel->user_id = $user->user_id;
                $readModel->status = 0;
                $readModel->save();
                $users = User::findOne(['id' => $user->user_id]);

                $userArr[] = [
                    'username' => $users->username,
                    'app_regid' => $users->app_regid,
                ];
            }

        }

        $this->sendNotification($userArr, $typeArr);
    }

    public function getActive()
    {
        return User::find()->where(['flag' => 'ACTIVE'])->count();
    }

    public function getDashbaordData($dType, $client_code)
    {
        switch ($dType) {
            case "total-users":
                echo User::find()
                    ->where(['client_code' => $client_code])
                    ->andWhere(['!=', 'id', '13'])
                    ->count();
                break;
            case "active-users":
                echo User::find()
                    ->where(['flag' => 'ACTIVE', 'client_code' => $client_code])
                    ->andWhere(['!=', 'id', '13'])
                    ->count();
                break;
            case "pending-users":
                echo User::find()
                    ->where(['flag' => 'PENDING', 'client_code' => $client_code])
                    ->andWhere(['!=', 'id', '13'])
                    ->count();
                break;
            case "inactive-users":
                echo User::find()
                    ->where(['flag' => 'INACTIVE', 'client_code' => $client_code])
                    ->andWhere(['!=', 'id', '13'])
                    ->count();
                break;
            case "blocked-users":
                echo User::find()
                    ->where(['flag' => 'BLOCKED', 'client_code' => $client_code])
                    ->andWhere(['!=', 'id', '13'])
                    ->count();
                break;


        }
    }

    public function getChartData($dType, $client_code)
    {

        if ($dType === "ACTIVE") {
            return User::find()
                ->where(['flag' => 'ACTIVE', 'client_code' => $client_code])
                ->andWhere(['!=', 'id', '13'])
                ->count();
        } elseif ($dType === "PENDING") {
            return User::find()
                ->where(['flag' => 'PENDING', 'client_code' => $client_code])
                ->andWhere(['!=', 'id', '13'])
                ->count();
        } elseif ($dType === "INACTIVE") {
            return User::find()
                ->where(['flag' => 'INACTIVE', 'client_code' => $client_code])
                ->andWhere(['!=', 'id', '13'])
                ->count();
        } elseif ($dType === "BLOCKED") {
            return User::find()
                ->where(['flag' => 'BLOCKED', 'client_code' => $client_code])
                ->andWhere(['!=', 'id', '13'])
                ->count();
        }
    }

    public function getUserArr()
    {
        $activeStatus = false;
        $inactiveStatus = false;
        $pendingStatus = false;
        $blockedStatus = false;
        $totalCount = User::find()->count();
        $activeCount = User::find()->where(['flag' => 'ACTIVE'])->count();
        $inactiveCount = User::find()->where(['flag' => 'PENDING'])->count();
        $pendingCount = User::find()->where(['flag' => 'INACTIVE'])->count();
        $blockedCount = User::find()->where(['flag' => 'BLOCKED'])->count();
        $highestCount = max($activeCount, $inactiveCount, $pendingCount, $blockedCount);
        if ($activeCount == $highestCount) {
            $activeStatus = true;
        } elseif ($inactiveCount == $highestCount) {
            $inactiveStatus = true;
        } elseif ($pendingCount == $highestCount) {
            $pendingStatus = true;
        } elseif ($blockedCount == $highestCount) {
            $blockedStatus = true;
        }
        $active = ['y' => $activeCount / $totalCount * 100, 'name' => 'Active Users', 'exploded' => $activeStatus];
        $pending = ['y' => $pendingCount / $totalCount * 100, 'name' => 'Pending Users', 'exploded' => $pendingStatus];
        $inactive = ['y' => $inactiveCount / $totalCount * 100, 'name' => 'Inactive Users', 'exploded' => $inactiveStatus];
        $blocked = ['y' => $blockedCount / $totalCount * 100, 'name' => 'Blocked Users', 'exploded' => $blockedStatus];
        return [$active, $pending, $inactive, $blocked];
    }

    public function getSendPostNotification($id)
    {
        $users = User::findAll(['flag' => 'ACTIVE']);
        $postModel = TblPost::findOne(['id' => $id]);

        foreach ($users as $user) {
            if ($user->id != $postModel->post_userid) {
                Yii::$app->mailer->compose('newPostTemplate',
                    [
                        'username' => $this->getUsernameById($postModel->post_userid),
                        'uimage' => $this->getUserimageById($postModel->post_userid),
                        'postImage' => "defaultPost.jpg",
                        'pcontent' => $this->truncate($postModel->post_description, 100),
                    ])
                    ->setFrom('samparq@qdegrees.com')
                    ->setTo($user->email)
                    ->setSubject($this->getUsernameById($postModel->post_userid) . ' added new post')
                    ->send();
            }
        }
    }

    public function getUserimageById($id, $userName = false)
    {
        if (isset($userName) && !empty($userName)) {
            $userModel = User::findOne(['name' => $userName]);
        } else {
            $userModel = User::findOne(['id' => $id]);
        }

        return empty($userModel) ? 'unknown' : $userModel->image_name;
    }

    function truncate($text, $length)
    {
        $length = abs((int)$length);
        if (strlen($text) > $length) {
            $text = preg_replace("/^(.{1,$length})(\s.*|$)/s", '\\1...', $text);
        }

        return ($text);
    }

    public function sendNewTrainingNotification($users, $trainerName, $title, $type = false, $training_id = false)
    {

        $tt = [];


        if (empty($type)) {
            foreach ($users as $arr) {
                $idArr[] = $arr->user_id;
                $arr = array_merge($tt, $idArr);
            }
        } else {
            $arr = $users;
        }

        if (!empty($arr)) {
            foreach ($arr as $user) {
                Yii::$app->mailer->compose('newTrainingNotification',
                    [
                        'username' => $trainerName,
                        'uimage' => $this->getUserimageById(false, $trainerName),
                        'postImage' => "defaultTraining.jpg",
                        'title' => $title,
                        'type' => !empty($type) ? true : false,
                        //'trainingUrl' => !empty($type)?"http://localhost/samparqad/api/render-survey-form?tid=".Yii::$app->utility->encryptUserData($training_id)."&current=".Yii::$app->utility->encryptUserData(1):false,
                        'trainingUrl' => "#",
                    ])
                    ->setFrom('samparq@qdegrees.com')
                    ->setTo($this->getUserEmailById($user))
                    ->setSubject('New training notification')
                    ->send();

                $trainingModel = Trainees::findOne(['user_id' => $user, 'training_id' => $training_id]);
                $trainingModel->notification_status = 1;
                $trainingModel->save(false);

            }
        }
    }

    public function getUserEmailById($id)
    {
        $userModel = User::findOne(['id' => $id]);
        return empty($userModel) ? "unknown" : $userModel->email;
    }

    public function totalSessionTime($total)
    {
        if ($total / 60 >= 24) {
            return round($total / 60 / 24, 2) . " Days";
        } elseif ($total >= 60) {
            return round($total / 60, 2) . " Hours";
        } else {
            return $total;
        }
    }

    public function getReceiverList()
    {
        $registeredUserList = $this->getRegisteredUserList();
        $unregisteredUserList = $this->getUnregisteredUserList();
        $incompleteUserList = $this->getIncompleteUserList();
        if (empty($registeredUserList)) {
            $PList = [
                'Unregistered Users' => 'Unregistered Users',
                'Incomplete Registration Users' => 'Incomplete Registration Users',
            ];
        } elseif (empty($unregisteredUserList)) {
            $PList = [
                'Registered Users' => 'Registered Users',
                'Incomplete Registration Users' => 'Incomplete Registration Users',
            ];
        } elseif (empty($incompleteUserList)) {
            $PList = [
                'Registered Users' => 'Registered Users',
                'Unregistered Users' => 'Unregistered Users',
            ];
        } else {
            $PList = [
                'Registered Users' => 'Registered Users',
                'Unregistered Users' => 'Unregistered Users',
                'Incomplete Registration Users' => 'Incomplete Registration Users',
            ];
        }


        return $PList;
    }

    public function getRegisteredUserList()
    {
        $userModel = User::find()->orderBy('id DESC')->all();

        foreach ($userModel as $uModel) {
            $registEmails[] = $uModel->email;
        }

        return empty($registEmails) ? [] : $registEmails;
    }

    public function getUnregisteredUserList()
    {
        $userModel = User::find()->orderBy('id DESC')->all();

        $getFile = file_get_contents(Yii::$app->params['file_url'] . 'email.txt');
        $allEmail = explode(',', $getFile);
        foreach ($userModel as $uModel) {
            $registEmails[] = $uModel->email;
        }


        $unRegEmail = array_diff($allEmail, $registEmails);

        return empty($unRegEmail) ? [] : $unRegEmail;
    }

    public function getIncompleteUserList()
    {
        $userModel = User::find()->where(['<>', 'form_submit', 1])->orderBy('id DESC')->all();

        foreach ($userModel as $uModel) {
            $registEmails[] = $uModel->email;
        }

        return empty($registEmails) ? [] : $registEmails;
    }

    public function getUserByCategory($category)
    {
        $registeredUserList = in_array('Registered Users', $category) ? $this->getRegisteredUserList() : [];
        $unregisteredUserList = in_array('Unregistered Users', $category) ? $this->getUnregisteredUserList() : [];
        $incompleteUserList = in_array('Incomplete Registration Users', $category) ? $this->getIncompleteUserList() : [];
        if (empty($registeredUserList)) {
            $PList = [
                'Unregistered Users' => $unregisteredUserList,
                'Incomplete Registration Users' => $incompleteUserList,
            ];
        } elseif (empty($unregisteredUserList)) {
            $PList = [
                'Registered Users' => $registeredUserList,
                'Incomplete Registration Users' => $incompleteUserList,
            ];
        } elseif (empty($incompleteUserList)) {
            $PList = [
                'Registered Users' => $registeredUserList,
                'Unregistered Users' => $unregisteredUserList,
            ];
        } else {
            $PList = [
                'Registered Users' => $registeredUserList,
                'Unregistered Users' => $unregisteredUserList,
                'Incomplete Registration Users' => 'Incomplete Registration Users',
            ];
        }


        return $PList;
    }

    public function getUList()
    {
        $uModel = User::find()->where(['!=','id',13])->andWhere(['flag' => 'ACTIVE'])->all();
        return ArrayHelper::map($uModel, 'id', 'email');
    }

    public function getGroupWiseUList()
    {
        $groupModel = GroupMembers::findAll(['status' => 1]);
        $userArr = [];
        foreach ($groupModel as $group) {
            $groupMemberArr = explode(',', $group->user_id);
            foreach ($groupMemberArr as $member) {
                $userArr[$this->getGroupDetailsById('name', $group->group_id)][$member] = $this->getUserEmailById($member);
            }
        }

        return $userArr;
    }

    public function getGroupDetailsById($reqData, $id)
    {
        $model = \backend\models\Group::findOne(['id' => $id]);
        return empty($model) ? "Unknown" : $model->$reqData;
    }

    public function getTrainingList($status)
    {
        if (!isset($status) && empty($status)) {
            $tModel = Training::find()->all();
        } else {
            $tModel = Training::findAll(['status' => $status]);
        }
        return empty($tModel) ? [] : ArrayHelper::map($tModel, 'id', 'training_title');
    }

    public function getTrainingsToImport($id)
    {

        $tModel = Training::find()
            ->where(['status' => 1])
            ->andWhere(['availability_status' => 0])
            ->andWhere(['!=', 'id', $id])
            ->andWhere(['>=', 'end_date', new Expression('NOW()')])
            ->all();
        return empty($tModel) ? [] : ArrayHelper::map($tModel, 'id', 'training_title');
    }

    public function getTrainingOptionType()
    {
        $model = TrainingOptionType::findAll(['status' => 1]);
        return ArrayHelper::map($model, 'id', 'type');
    }

    public function getTrainingTitle($tid)
    {

        $model = Training::findOne(['id' => $tid]);
        return empty($model->training_title) ? "-" : $model->training_title;
    }

    public function getTrainingType($tid)
    {
        $model = Training::findOne(['id' => $tid]);
        return empty($model) ? 0 : $model->assessment_type;
    }

    public function getTrainerName($tid)
    {
        $model = Training::findOne(['id' => $tid]);
        return empty($model->trainer_name) ? "-" : $model->trainer_name;
    }

    public function getAssessmentSummary($tid, $uid)
    {
        $trainingModel = Training::findOne(['id' => $tid]);

        $qaSet = [];

        if (!empty($trainingModel)) {
            $getTrainingType = $trainingModel->assessment_type;

            if ($getTrainingType == 2) {

                $models = TrainingQuestion::findAll(['training_id' => $tid, 'status' => 1]);
                foreach ($models as $model) {
                    $optionModel = Options::findOne(['tquestion_id' => $model->id, 'is_answer' => 1]);

                    if (empty($optionModel)) {
                        $optionId = "";
                    } else {
                        $optionId = $optionModel->id;
                    }


                    $submissionModel = TrainingSubmission::findOne(['training_id' => $tid, 'question_id' => $model->id, 'training_submitted_by' => $uid]);
                    if (!empty($submissionModel->option_id)) {
                        $answerGiven = $this->getOptionValue($submissionModel->option_id);
                    } elseif (!empty($submissionModel->comment_box)) {
                        $answerGiven = $submissionModel->comment_box;
                    } elseif (!empty($submissionModel->other)) {
                        $answerGiven = $submissionModel->other;
                    } else {
                        $answerGiven = "Skipped";
                    }
                    $qaSet[] = [
                        "question" => $model->question,
                        "right_answer" => $optionId,
                        "answer_given" => $answerGiven,
                        "attachment" => !empty($submissionModel->new_file_name) ? $submissionModel->new_file_name : ""
                    ];
                }

            } else {

                $models = TrainingQuestion::findAll(['training_id' => $tid, 'status' => 1]);
                foreach ($models as $model) {
                    $optionModel = Options::findOne(['tquestion_id' => $model->id, 'is_answer' => 1]);
                    $submissionModel = TrainingSubmission::findOne(['training_id' => $tid, 'question_id' => $model->id, 'training_submitted_by' => $uid]);
                    $qaSet[] = [
                        "question" => $this->getQuestion($model->id),
                        "right_answer" => $this->getOptionValue($optionModel->id),
                        "negative_mark" => $model->negative_mark,
                        "positive_marks" => $model->marks,
                        "correct" => $optionModel->id == $submissionModel->option_id ? "(<span><i style='color: green'>Correct</i></span>)" : "(<span><i style='color: red'>Incorrect</i></span>)",
                        "answer_given" => $this->getOptionValue($submissionModel->option_id)
                    ];
                }
            }


        }


        return $qaSet;


    }

    public function getOptionValue($oid)
    {
        $optionTable = Options::findOne(['id' => $oid]);

        return !empty($optionTable) ? $optionTable->option_value : "unknown";
    }

    public function getQuestion($qid)
    {
        $model = TrainingQuestion::findOne(['id' => $qid]);
        return empty($model->question) ? "-" : $model->question;
    }

    public function getActiveInactiveList()
    {
        $arr = [
            0 => "Inactive",
            1 => "Active",
        ];

        return $arr;
    }

    public function getTraineeResult($tid, $uid)
    {
        $models = TrainingQuestion::findAll(['training_id' => $tid, 'status' => 1]);
        $qaSet = [];
        $correctAnswer = 0;
        $totalmarks = 0;
        $marksObtained = 0;
        $negativeMarking = 0;
        foreach ($models as $model) {

            $optionModel = Options::findOne(['tquestion_id' => $model->id, 'is_answer' => 1]);
            if (!empty($optionModel)) {
                $submissionModel = TrainingSubmission::findOne(['training_id' => $tid, 'question_id' => $model->id, 'training_submitted_by' => $uid]);
                if (!empty($submissionModel)) {
                    $qaSet[] = [
                        "question" => $model->id,
                        "right_answer" => $optionModel->id,
                        "negative_mark" => $model->negative_mark,
                        "positive_marks" => $model->marks,
                        "answer_given" => $submissionModel->option_id,
                    ];
                }
            }

        }


        foreach ($qaSet as $item) {
            $totalmarks += $item['positive_marks'];
            if ($item['answer_given'] == $item['right_answer']) {
                $correctAnswer++;
                $marksObtained += $item['positive_marks'];
            } else {
                $marksObtained -= $item['negative_mark'];
                $negativeMarking += $item['negative_mark'];
            }
        }

        $result = [
            'totalMarks' => $totalmarks,
            'marksObtained' => $marksObtained > 0 ? $marksObtained : 0,
            'totalQuestion' => count($qaSet),
            'correctQuestion' => $correctAnswer,
            'incorrectQuestion' => count($qaSet) - $correctAnswer,
            'negative_marks' => $negativeMarking,
        ];

        return $result;
    }

    public function getRole()
    {

        return Yii::$app->user->can('monitor') ? ArrayHelper::map(AuthItem::find()->where(['!=', 'name', 'admin'])->all(), 'name', 'name') : ArrayHelper::map(AuthItem::find()->all(), 'name', 'name');
    }

    public function getTrainingUserList()
    {
        $uModel = User::find()->where(['flag' => 'ACTIVE'])->andWhere(['!=', 'id', 13])->all();
        return ArrayHelper::map($uModel, 'email', 'name');
    }

    public function getLiveViewers()
    {
        $allUsersCount = User::find()->where(['flag' => 'ACTIVE'])->count();
        $liveUsersCount = WebcastViewers::find()->where(['view_status' => 1])->count();

        $users = [
            'all' => $allUsersCount,
            'live' => $liveUsersCount
        ];

        return $users;
    }

    public function getOptions($qid)
    {
        $options = Options::findAll(['tquestion_id' => $qid]);
        return empty($options) ? [] : ArrayHelper::map($options, 'id', 'option_value');

    }

    public function createQuestionPagination($current, $arr)
    {
        $data = @$arr[$current];
        return $data;
    }

    public function checkIfQuestionExist($id)
    {
        $question = TrainingQuestion::findOne(['id' => $id]);
        return empty($question) ? false : true;
    }

    public function assessmentSubmitted($tid, $uid)
    {

        $trainingModel = Training::findOne(['id' => $tid]);
        if ($trainingModel->assessment_type == 1) {
            $model = Trainees::findOne(['training_id' => $tid, 'user_id' => $uid]);
            if (empty($model)) {
                return "N/A";
            } elseif ($model->status == 2) {

                return round((($this->getMarksObtained($tid, $uid) / $this->calculateTotalMarks($tid)) * 100), 2) . "%";
            } else {
                return "Not Submitted/Attempted";
            }
        } else {
            return "Survey";
        }
    }

    public function getMarksObtained($tid, $uid)
    {
        $trainingModel = Training::findOne(['id' => $tid]);
        if ($trainingModel->assessment_type == 1) {
            $models = TrainingQuestion::findAll(['training_id' => $tid, 'status' => 1]);
            $marks = 0;
            foreach ($models as $model) {
                $optionModel = Options::findOne(['tquestion_id' => $model->id, 'is_answer' => 1]);
                $submissionModel = TrainingSubmission::findOne(['training_id' => $tid, 'question_id' => $model->id, 'training_submitted_by' => $uid]);
                $qaSet[] = [
                    "question" => $model->id,
                    "right_answer" => $optionModel->id,
                    "negative_mark" => $model->negative_mark,
                    "positive_marks" => $model->marks,
                    "answer_given" => $submissionModel->option_id,
                ];

            }

            foreach ($qaSet as $item) {
                if ($item['answer_given'] == $item['right_answer']) {
                    $marks += $item["positive_marks"];
                } else {
                    $marks -= $item["negative_mark"];
                }
            }

            return $marks > 0 ? $marks : 0;
        } else {
            return 0;
        }

    }

    public function calculateTotalMarks($tid)
    {
        $models = TrainingQuestion::findAll(['training_id' => $tid, 'status' => 1]);
        $marks = 0;
        foreach ($models as $model) {
            $marks += $model->marks;
        }

        return $marks;

    }

    public function checkIFCertification($tid, $uid)
    {
        $trainingModel = Training::findOne(['id' => $tid]);
        if (!empty($trainingModel)) {
            if ($trainingModel->assessment_type == 1) {
                $traineesModel = Trainees::findOne(['user_id' => $uid, 'training_id' => $tid]);
                if (empty($traineesModel)) {
                    return 'N/A';
                } else {
                    return $traineesModel->certificate_download == 1 ? '<i class="fa fa-check"></i>' : '<i class="fa fa-close"></i>';
                }
            } else {
                return 'N/A';
            }
        } else {
            return "N/A";
        }
    }

    public function checkDisability($date, $type = false)
    {
        $endDate = strtotime($date);


        $currentDate = strtotime(date('Y-m-d H:i:s'));
        $getDiffrence = $currentDate - $endDate;

        if ($type == 2) {
            if (($getDiffrence > 0)) {
                return true;

            } else {
                return false;
            }
        } else {
            if ($getDiffrence > (-3600)) {
                return true;
            } else {
                return false;
            }

        }

    }

    public function getTrainingStartDate($date, $raw = false)
    {
        $currentTime = strtotime(date('Y-m-d H:i:s'));
        if ($raw == true) {
            return (strtotime($date) - $currentTime);

        } else {
            return date('M d Y, g:i:s A', strtotime($date));
        }

    }

    Public function getCosecNameById($eid)
    {
        if (empty($eid)) {
            return '';
        }
        $model = CdUser::findOne(['employee_id' => $eid]);
        return empty($model) ? 'Unknown' : $model->name;
    }

    public function getSubmissionTime($tid, $uid)
    {

        $query = TrainingSubmission::find()
            ->where(['training_id' => $tid, 'training_submitted_by' => $uid]);
        $startTime = $query->orderBy(['created_at' => SORT_ASC])->one()->created_at;
        $endTime = $query->orderBy(['created_at' => SORT_DESC])->one()->created_at;

        $difference = strtotime($endTime) - strtotime($startTime);

        return "Approx: (" . round($difference / 60, 2) . " Min.)";

    }

    public function getStartSubmissionTime($tid, $uid)
    {

        $query = TrainingSubmission::find()
            ->where(['training_id' => $tid, 'training_submitted_by' => $uid]);
        $startTime = $query->orderBy(['created_at' => SORT_ASC])->one()->created_at;

        return date("Y") == date("Y", strtotime($startTime)) ? date("M-d h:i:s A", strtotime($startTime)) : date("Y M d h:i:s A", strtotime($startTime));

    }

    public function getEndSubmissionTime($tid, $uid)
    {

        $query = TrainingSubmission::find()
            ->where(['training_id' => $tid, 'training_submitted_by' => $uid]);
        $endTime = $query->orderBy(['created_at' => SORT_DESC])->one()->created_at;

        return date("Y") == date("Y", strtotime($endTime)) ? date("M-d h:i:s A", strtotime($endTime)) : date("Y M d h:i:s A", strtotime($endTime));

    }

    public function getSubmissionAverageTime($tid, $uid)
    {
        $totalQuestion = $this->getTotalQuestionCount($tid);

        $query = TrainingSubmission::find()
            ->where(['training_id' => $tid, 'training_submitted_by' => $uid]);
        $startTime = $query->orderBy(['created_at' => SORT_ASC])->one()->created_at;
        $endTime = $query->orderBy(['created_at' => SORT_DESC])->one()->created_at;

        $difference = strtotime($endTime) - strtotime($startTime);


        return "Approx: (" . round(($difference / 60) / $totalQuestion, 2) . " Min.)";


    }

    public function getTotalQuestionCount($tid)
    {
        $count = TrainingQuestion::find()->where(['training_id' => $tid])->count();
        return $count;
    }

    public function getYearlyData()
    {
        $models = Training::find()
            ->joinWith('user')
            ->where(['>', 'training.created_at', new Expression('DATE_SUB(NOW(), INTERVAL 12 MONTH)')])
            ->andWhere(['user.client_code' => $this->checkClient()])
            ->all();

        $mm = [];
        $dd = [];
        $ff = "";
        $months = [
            date('Y') . ' Jan',
            date('Y') . ' Feb',
            date('Y') . ' Mar',
            date('Y') . ' Apr',
            date('Y') . ' May',
            date('Y') . ' Jun',
            date('Y') . ' Jul',
            date('Y') . ' Aug',
            date('Y') . ' Sep',
            date('Y') . ' Oct',
            date('Y') . ' Nov',
            date('Y') . ' Dec',
        ];

        foreach ($months as $key => $month) {
            foreach ($models as $model) {
                if ($month == date('Y M', strtotime($model->created_at))) {
                    $dd[] = $model->id;
                }
            }

            $count = count($dd);

            $ff .= "[new Date(" . date('Y') . ", " . "$key), $count],";

            $count = 0;
            $dd = [];

        }

        return "[" . trim($ff, ",") . "]";


    }

    public function checkClient()
    {

        if (Yii::$app->user->can('admin')) {
            if (Yii::$app->session->get('client')) {
                return Yii::$app->session->get('client');
            } else {
                return 'QDEG-123456';
            }
        } else {
            return Yii::$app->user->identity->client_code;
        }


    }

    public function getProgressBar($tid, $uid)
    {
        $qCount = $this->getTotalQuestionCount($tid);
        $submitted = TrainingSubmission::find()->where(['training_id' => $tid, 'training_submitted_by' => $uid])->count();
        $bar = $qCount == 0 ? 0 : round(($submitted / $qCount) * 100, 2);
        return $bar;

    }

    public function getCompletionData($tid)
    {

        $trainingModel = Training::findOne(['id' => $tid]);

        if (empty($trainingModel)) {
            throw new ForbiddenHttpException('Invalid request');
        }

        $totalSubmission = count($trainingModel->submissions);
        $totalTrainees = count($trainingModel->trainees);

        return [
            'totalSubmission' => $totalSubmission,
            'totalTrainees' => $totalTrainees,
        ];

    }

    public function getAverageScore($tid)
    {


        $models = TrainingQuestion::findAll(['training_id' => $tid]);
        $qaSet = [];
        $correctAnswer = 0;
        $totalmarks = 0;
        $trainees = [];
        $marksObtained = 0;
        $negativeMarking = 0;
        foreach ($models as $model) {

            $optionModel = Options::findOne(['tquestion_id' => $model->id, 'is_answer' => 1]);
            if (!empty($optionModel)) {
                $submissionModel = TrainingSubmission::findAll(['training_id' => $tid, 'question_id' => $model->id]);
                $uniqueSubmission = TrainingSubmission::find()->where(['training_id' => $tid])->groupBy('training_submitted_by')->all();
                if (!empty($submissionModel)) {
                    foreach ($submissionModel as $submission) {
                        $trainees[] = [
                            'trainee_id' => $submission->training_submitted_by
                        ];

                        $qaSet[] = [
                            "question" => $model->id,
                            "right_answer" => $optionModel->id,
                            "negative_mark" => $model->negative_mark,
                            "positive_marks" => $model->marks,
                            "answer_given" => $submission->option_id,
                        ];
                    }
                }
            }

        }


        foreach ($qaSet as $item) {
            $totalmarks += $item['positive_marks'];
            if ($item['answer_given'] == $item['right_answer']) {
                $correctAnswer++;
                $marksObtained += $item['positive_marks'];
            } else {
                $marksObtained -= $item['negative_mark'];
                $negativeMarking += $item['negative_mark'];
            }
        }

        $result = [
            'totalMarks' => $totalmarks,
            'averageMarks' => $marksObtained > 0 ? round(((($marksObtained) / count($uniqueSubmission)) / $totalmarks) * 100, 2) : 0,
        ];

        return $result;
    }

    public function getFailedQuestion($tid)
    {
        $models = TrainingQuestion::findAll(['training_id' => $tid]);
        $qaSet = [];
        $failedQuestions = [];
        $finalData = "";
        $trainees = [];
        foreach ($models as $model) {

            $optionModel = Options::findOne(['tquestion_id' => $model->id, 'is_answer' => 1]);
            if (!empty($optionModel)) {
                $submissionModel = TrainingSubmission::findAll(['training_id' => $tid, 'question_id' => $model->id]);
                $uniqueSubmission = TrainingSubmission::find()->where(['training_id' => $tid])->groupBy('training_submitted_by')->all();
                if (!empty($submissionModel)) {
                    foreach ($submissionModel as $submission) {

                        $trainees[] = [
                            'trainee_id' => $submission->training_submitted_by
                        ];

                        $qaSet[] = [
                            "question" => $model->id,
                            "right_answer" => $optionModel->id,
                            "answer_given" => $submission->option_id,
                        ];
                    }
                }
            }

        }


        foreach ($qaSet as $item) {
            if ($item['answer_given'] != $item['right_answer']) {
                $failedQuestions[] = $item['question'];
            }
        }

        $questionCount = array_count_values($failedQuestions);

        arsort($questionCount);

        $mostFailed = array_slice($questionCount, 0, 5, true);

        foreach ($mostFailed as $key => $data) {
            $finalData .= "['Q.Id " . $key . "',   " . $data . "],";
        }


        return "[['Training', 'Most Failed Question']," . $finalData . "]";


    }

    public function getSurveyResponse($tid)
    {
        $totalCount = Trainees::find()->where(['training_id' => $tid])->count();
        $submitted = TrainingSubmission::find()
            ->where(['training_id' => $tid])
            ->groupBy('training_submitted_by')
            ->orderBy('id desc')
            ->count();
        $droptOuts = $totalCount - $submitted;

        return [
            'totalCount' => $totalCount,
            'submitted' => $submitted,
            'dropOuts' => $droptOuts
        ];
    }

    public function getSurveyeeDetails($tid)
    {
        $trainingSubmission = TrainingSubmission::findAll(['training_id' => $tid]);

    }

    public function getTraineesCount($tid)
    {
        $traineesModel = Trainees::find()->where(['training_id' => $tid])->count();
        return empty($traineesModel) ? 0 : $traineesModel;

    }

    public function getTotalLicencse($clientCode)
    {

        $clientCode = Client::findOne(['code' => $clientCode]);
        return empty($clientCode) ? 0 : $clientCode->no_of_users;

    }

    public function getQueryCount($tid, $uid)
    {
        return Chat::find()->where(['training_id' => $tid, 'sender_id' => $uid])->count();
    }

    public function getUnreadQueryCount($tid, $uid)
    {
        return Chat::find()->where(['training_id' => $tid, 'sender_id' => $uid, 'read_status' => 0])->count();
    }

    public function getAllTrainingCount($uid)
    {
        $model = Trainees::find()->where(['user_id' => $uid])->count();
        return $this->getCompletedTrainingCount($uid) == 0 ? "N/A" : $model;
    }

    public function getCompletedTrainingCount($uid)
    {
        $model = Trainees::find()->where(['user_id' => $uid, 'status' => 2])->count();
        return $model == 0 ? "N/A" : $model;
    }

    public function getLicenseList()
    {
        $arrayModel = License::findAll(['status' => 1]);
        return ArrayHelper::map($arrayModel, 'id', 'name');

    }

    public function getClientList()
    {

        $arrayModel = Client::findAll(['status' => 'Active']);
        return ArrayHelper::map($arrayModel, 'code', 'name');

    }

    public function dynamicDb()
    {

        $config = [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host = localhost;dbname = helloworld',
            'username' => 'vladimir',
            'password' => '12345',
            'charset' => 'utf8',
        ];

        return Yii::createObject($config);

    }

    public function sendEmail($data, $receiver, $subject)
    {
        Yii::$app->mailer->compose('newClientTemplate',
            [
                'data' => $data
            ])
            ->setFrom(['samparq@qdegrees.com' => 'SamparQ LMS'])
            ->setTo($receiver)
            ->setSubject($subject)
            ->send();
    }

    public function createDbConfigFile($filename)
    {
        $remote_file = Yii::getAlias('@frontend/web/' . $filename.'js');

        $content = '/**
 * Created by Saurabh Rai on ' . date("d/m/Y") . '.
 */

const sequelize = require("sequelize");


const connection = new sequelize("' . $filename . '", "root", "$ecurity@123", {
    host:"45.114.141.55",
    dialect:"mysql",
    timezone : "Asia/Kolkata",
});


const connection2 = new sequelize("qdegrees_cosec", "qdegrees_cosec", "cosec@123$", {
    host:"103.231.209.72",
    dialect:"mysql",
    timezone : "Asia/Kolkata",
});

module.exports = {
    Sequelize:sequelize,
    Connection:connection,
    Connection2:connection2
};';
        $fp = fopen($remote_file, "wb");
        fwrite($fp, $content);
        fclose($fp);

        $ftp_host = '45.114.141.55';
        $ftp_user_name = 'saurabh';
        $ftp_user_pass = 'SamparqTest@123';

        $local_file = '/var/www/lms/server/db/' . $filename.'js';

        $connect_it = ftp_connect($ftp_host);

        $login_result = ftp_login($connect_it, $ftp_user_name, $ftp_user_pass);

        if (ftp_put($connect_it, $local_file, $remote_file, FTP_ASCII)) {
            echo "Successfully written to $local_file\n";
        } else {
            echo "There was a problem\n";
        }

        ftp_close($connect_it);

    }


    public function getTrainingRating($uid){
        $totalTraining = Trainees::find()->where(['user_id' => $uid])->count();
        $completed = Trainees::find()->where(['status' => 2, 'user_id' => $uid])->count();
        if($totalTraining == 0){
            return "N/A";
        }
        $rating = round((($completed/$totalTraining)*10), 2);
        return $rating;

    }

    public function getLicenseUsageGraph($code){
        $clientModel = Client::findOne(['code' => $code]);
        $startTimeStamp = strtotime($clientModel->subscription_sd);
        $endTimeStamp = strtotime($clientModel->subscription_ed);
        $currentTimeStamp = strtotime(date('Y-m-d H:i:s'));
        $total = $endTimeStamp - $startTimeStamp;
        $usage = $endTimeStamp - $currentTimeStamp;




        $data =  [
            'total' => round($total/(60*60),2),
            'current' => round($usage/(60*60), 2)
        ];

        return $data;
    }

    public function createUserAppPasswordHash($password,$uid){

        $config_file = Yii::$app->session->get('dbName').".js";
        $client_code = Yii::$app->session->get('client');
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, Yii::$app->params['api_url']);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_POSTFIELDS, "password=$password&client_code=$client_code&config_file=$config_file&uid=$uid");
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            "accept: application/json",
            "Content-Type: application/x-www-form-urlencoded"

        ));

        curl_exec($curl);
        curl_close($curl);

    }

    public function getClientName(){
        $model = Client::findOne(['code' => Yii::$app->session->get('client')]);
        return $model->name;


    }

    public function getClientChartData(){
        $total = Client::find()->count();
        $active = Client::find()->where(['status' => 'ACTIVE'])->count();

        return  [
            'active' => $active,
            'total' => $total
        ];
    }

    public function getClientDetails($field){
        $model = Client::findOne(['code' => Yii::$app->session->get('client')]);
        return $model->$field;
    }


    public function getSubscriptionTimeFormat($totalData, $divisionBy){
        $dattaa = explode('.',round($totalData/$divisionBy, 2));
        return count($dattaa) ==  2 ? $dattaa[0].' Days <span style="font-size:16px; color:#0c6d92"> '.ceil(((24*($dattaa[1]))/100)).' hrs </span>' : $dattaa[0]." Days";
    }

    public function getSubsriptionTime(){
        //$startDate = strtotime($this->getClientDetails('subscription_sd'));
        $startDate = strtotime(date('Y-m-d H:i:s'));
        $endDate = strtotime($this->getClientDetails('subscription_ed'));

        return $this->getSubscriptionTimeFormat(($endDate - $startDate), (60*60*24));
    }




}