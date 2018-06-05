<?php

namespace frontend\controllers;

use common\models\AppLogin;
use Yii;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use frontend\models\User;
use yii\web\UploadedFile;
use yii\db\Expression;
use frontend\models\Profile;
use frontend\models\TblPost;
use frontend\models\TblPostAttachments;
use frontend\models\TblPostLike;
use frontend\models\TblPostComment;
use frontend\models\TblFavouritePost;
use frontend\models\TblAttachLike;
use frontend\models\TblAttachComment;
use frontend\models\PolicyTypeMaster;
use frontend\models\Policies;
use frontend\models\TblFeedback;
use frontend\models\Inbox;
use frontend\models\Sent;
use frontend\models\UploadFiles;
use frontend\models\Chat;
use yii\imagine\Image;

/**
 * Site controller
 */
class ApiController extends Controller {

//    public function beforeAction($action) {
//        $this->enableCsrfValidation = false;
//        return parent::beforeAction($action);
//    }


    public function beforeAction($action) {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    public function actionSendMailFunction() {
        if (Yii::$app->request->post()) {
            $token = $_REQUEST['token'];
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            if ($accessStatus['authentication'] === 1) {
                define('API_ACCESS_KEY', 'AIzaSyA3ACIrZoCQLyv7kGvfTgmy_0-H3UlpLlI');
                $File_Count = $_REQUEST['fileCount'];
                $User_id = $_REQUEST['user_id'];
                $To_Str = $_REQUEST['to'];
                $Subject_Str = str_replace("'", "\'", $_REQUEST['Subject']);
                $Message_Str = str_replace("'", "\'", $_REQUEST['Message']);
                $To_Details = '';
                if ($File_Count == '0') {
                    $ToModel = Yii::$app->db->createCommand("select type from profile where id in(" . $To_Str . ")")->queryAll();
                    foreach ($ToModel as $row) {
                        if ($To_Details == '') {
                            $To_Details = $row['type'];
                        } else {
                            $To_Details = $To_Details . ',' . $row['type'];
                        }
                    }
                    $Insert_SentBox = new Sent();
                    $Insert_SentBox->mail_to = $To_Str;
                    $Insert_SentBox->to_detail = $To_Details;
                    $Insert_SentBox->mail_from = $User_id;
                    $Insert_SentBox->subject = $Subject_Str;
                    $Insert_SentBox->message = $Message_Str;
                    $Insert_SentBox->file_status = 0;
                    if ($Insert_SentBox->save(false)) {
                        $sent_id = $Insert_SentBox->id;
                        $To_Array = array();
                        $To_Array = explode(',', $To_Str);
                        for ($i = 0; $i < sizeof($To_Array); $i++) {
                            $to_jisd = $To_Array[$i];
                            $Select_Username_query = Yii::$app->db->createCommand("select id,app_regid,(select name from user where id='" . $User_id . "')as SenderName from user where user_type='" . $to_jisd . "' and id!='" . $User_id . "' and flag='ACTIVE'")->queryAll();
                            foreach ($Select_Username_query as $row) {
                                $mail_to_userid = $row['id'];
                                $reg_id = $row['app_regid'];
                                $SenderName = $row['SenderName'];
                                $Insert_Inbox = new Inbox();
                                $Insert_Inbox->sent_id = $sent_id;
                                $Insert_Inbox->mail_to = $to_jisd;
                                $Insert_Inbox->mail_from = $User_id;
                                $Insert_Inbox->subject = $Subject_Str;
                                $Insert_Inbox->message = $Message_Str;
                                $Insert_Inbox->file_status = 0;
                                $Insert_Inbox->mail_to_userid = $mail_to_userid;
                                $Insert_Inbox->save(false);
                                $Inbox_id = $Insert_Inbox->id;
                                $fcmApiKey = 'AIzaSyA3ACIrZoCQLyv7kGvfTgmy_0-H3UlpLlI';


                                $registrationIds = array($reg_id);

                                $msg = array
                                    (
                                    'process_type' => 'email_type',
                                    'inbox_id' => $Inbox_id,
                                    'subject' => $Subject_Str,
                                    'Sendername' => $SenderName
                                );
                                $fields = array
                                    (
                                    'registration_ids' => $registrationIds,
                                    'data' => $msg
                                );
                                $headers = array
                                    (
                                    'Authorization: key=' . API_ACCESS_KEY,
                                    'Content-Type: application/json'
                                );
                                $ch = curl_init();
                                curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
                                curl_setopt($ch, CURLOPT_POST, true);
                                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
                                $result = curl_exec($ch);
                                curl_close($ch);
                            }
                        }
                        $Report = array();
                        $Report['success'] = 1;
                        $Report['message'] = 'Message sent Successfully!!';
                        echo json_encode($Report);
                        die();
                    } else {
                        $Report = array();
                        $Report['success'] = 0;
                        $Report['message'] = 'Message sent failed!!';
                        echo json_encode($Report);
                        die();
                    }
                } else if ($File_Count == '1') {

                    $Orignal_File1_Name = $_REQUEST['Orignal_Name'];
                    $File1_Extention = $_REQUEST['ext'];
                    $Get_To_Detail = Yii::$app->db->createCommand("select type from profile where id in(" . $To_Str . ")")->queryAll();
                    foreach ($Get_To_Detail as $row) {
                        if ($To_Details == '') {
                            $To_Details = $row['type'];
                        } else {
                            $To_Details = $To_Details . ',' . $row['type'];
                        }
                    }

                    $Insert_SentBox = new Sent();
                    $Insert_SentBox->mail_to = $To_Str;
                    $Insert_SentBox->to_detail = $To_Details;
                    $Insert_SentBox->mail_from = $User_id;
                    $Insert_SentBox->subject = $Subject_Str;
                    $Insert_SentBox->message = $Message_Str;
                    $Insert_SentBox->file_status = 1;

                    if ($Insert_SentBox->save(false)) {
                        $sent_id = $Insert_SentBox->id;
                        if (is_uploaded_file($_FILES['Filedata']['tmp_name'])) {
                            $pic_name = $_FILES['Filedata']['name'];
                            $tmp_name = $_FILES['Filedata']['tmp_name'];
                            $fname = Yii::getAlias('@frontend/web/Upload_Files/') . $pic_name;
                            move_uploaded_file($tmp_name, $fname);
                            $LocalUrl = Yii::getAlias('@frontend/web/Upload_Files/');

                            $To_Array = array();
                            $To_Array = explode(',', $To_Str);
                            $Inbox_id='';
                            for ($i = 0; $i < sizeof($To_Array); $i++) {
                                $to_jisd = $To_Array[$i];
                                $Select_Username_query = Yii::$app->db->createCommand("select id,app_regid,(select name from user where id='" . $User_id . "')as SenderName from user where user_type='" . $to_jisd . "' and id!='" . $User_id . "' and flag='ACTIVE'")->queryAll();
                                foreach ($Select_Username_query as $row) {
                                    $mail_to_userid = $row['id'];
                                    $reg_id = $row['app_regid'];
                                    $SenderName = $row['SenderName'];
                                    $Insert_Inbox = new Inbox();
                                    $Insert_Inbox->sent_id = $sent_id;
                                    $Insert_Inbox->mail_to = $to_jisd;
                                    $Insert_Inbox->mail_from = $User_id;
                                    $Insert_Inbox->subject = $Subject_Str;
                                    $Insert_Inbox->message = $Message_Str;
                                    $Insert_Inbox->file_status = 1;
                                    $Insert_Inbox->mail_to_userid = $mail_to_userid;
                                    $Insert_Inbox->save(false);
                                    $Inbox_id = $Insert_Inbox->id;
                                    $fcmApiKey = 'AIzaSyA3ACIrZoCQLyv7kGvfTgmy_0-H3UlpLlI'; //App API Key(This is google cloud messaging api key not web api key)
                                    // API access key from Google API's Console
                                   
                                    $registrationIds = array($reg_id);
                                    // prep the bundle
                                    $msg = array
                                        (
                                        'process_type' => 'email_type',
                                        'inbox_id' => $Inbox_id,
                                        'subject' => $Subject_Str,
                                        'Sendername' => $SenderName
                                    );
                                    $fields = array
                                        (
                                        'registration_ids' => $registrationIds,
                                        'data' => $msg
                                    );
                                    $headers = array
                                        (
                                        'Authorization: key=' . API_ACCESS_KEY,
                                        'Content-Type: application/json'
                                    );
                                    $ch = curl_init();
                                    curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
                                    curl_setopt($ch, CURLOPT_POST, true);
                                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
                                    $result = curl_exec($ch);
                                    curl_close($ch);
                                }
                                $inbx_id = $Inbox_id;
                                $Insert_UploadFiles = new UploadFiles();
                                $Insert_UploadFiles->mail_to = $to_jisd;
                                $Insert_UploadFiles->mail_from = $User_id;
                                $Insert_UploadFiles->file_name = $pic_name;
                                $Insert_UploadFiles->file_path = $fname;
                                $Insert_UploadFiles->orignal_filename = $Orignal_File1_Name;
                                $Insert_UploadFiles->ext = $File1_Extention;
                                $Insert_UploadFiles->inbox_id = $inbx_id;
                                $Insert_UploadFiles->sent_id = $sent_id;
                                $Insert_UploadFiles->save(false);
                            }
                            $Report = array();
                            $Report['success'] = 1;
                            $Report['message'] = 'Message sent Successfully!!';
                            echo json_encode($Report);
                            die();
                        } else {
                            $Report = array();
                            $Report['success'] = 0;
                            $Report['message'] = 'Message sent failed!!';
                            echo json_encode($Report);
                            die();
                        }
                    } else {
                        $Report = array();
                        $Report['success'] = 0;
                        $Report['message'] = 'Message sent failed!!';
                        echo json_encode($Report);
                        die();
                    }
                } else if ($File_Count == '2') {
                    $Orignal_File1_Name = $_REQUEST['Orignal_Name1'];
                    $Orignal_File2_Name = $_REQUEST['Orignal_Name2'];
                    $File1_Extention = $_REQUEST['ext1'];
                    $File2_Extention = $_REQUEST['ext2'];
                    $Get_To_Detail = Yii::$app->db->createCommand("select type from profile where id in(" . $To_Str . ")")->queryAll();
                    foreach ($Get_To_Detail as $row) {
                        if ($To_Details == '') {
                            $To_Details = $row['type'];
                        } else {
                            $To_Details = $To_Details . ',' . $row['type'];
                        }
                    }

                    $Insert_SentBox = new Sent();
                    $Insert_SentBox->mail_to = $To_Str;
                    $Insert_SentBox->to_detail = $To_Details;
                    $Insert_SentBox->mail_from = $User_id;
                    $Insert_SentBox->subject = $Subject_Str;
                    $Insert_SentBox->message = $Message_Str;
                    $Insert_SentBox->file_status = 1;

                    if ($Insert_SentBox->save(false)) {
                        $sent_id = $Insert_SentBox->id;
                        if (is_uploaded_file($_FILES['Filedata1']['tmp_name'])) {
                            $pic_name = $_FILES['Filedata1']['name'];
                            $tmp_name = $_FILES['Filedata1']['tmp_name'];
                            $fname = Yii::getAlias('@frontend/web/Upload_Files/') . $pic_name;
                            move_uploaded_file($tmp_name, $fname);
                            $LocalUrl = Yii::getAlias('@frontend/web/Upload_Files/');
                            if (is_uploaded_file($_FILES['Filedata2']['tmp_name'])) {
                                $tmp_name2 = $_FILES['Filedata2']['tmp_name'];
                                $pic_name2 = $_FILES['Filedata2']['name'];
                                $fname2 = Yii::getAlias('@frontend/web/Upload_Files/') . $pic_name2;
                                move_uploaded_file($tmp_name2, $fname2);
                                $To_Array = array();
                                $To_Array = explode(',', $To_Str);
                                 $Inbox_id='';
                                for ($i = 0; $i < sizeof($To_Array); $i++) {
                                    $to_jisd = $To_Array[$i];
                                    $Select_Username_query = Yii::$app->db->createCommand("select id,app_regid,(select name from user where id='" . $User_id . "')as SenderName from user where user_type='" . $to_jisd . "' and id!='" . $User_id . "' and flag='ACTIVE'")->queryAll();
                                    foreach ($Select_Username_query as $row) {
                                        $mail_to_userid = $row['id'];
                                        $reg_id = $row['app_regid'];
                                        $SenderName = $row['SenderName'];
                                        $Insert_Inbox = new Inbox();
                                        $Insert_Inbox->sent_id = $sent_id;
                                        $Insert_Inbox->mail_to = $to_jisd;
                                        $Insert_Inbox->mail_from = $User_id;
                                        $Insert_Inbox->subject = $Subject_Str;
                                        $Insert_Inbox->message = $Message_Str;
                                        $Insert_Inbox->file_status = 1;
                                        $Insert_Inbox->mail_to_userid = $mail_to_userid;
                                        $Insert_Inbox->save(false);
                                        $Inbox_id = $Insert_Inbox->id;
                                        $fcmApiKey = 'AIzaSyA3ACIrZoCQLyv7kGvfTgmy_0-H3UlpLlI'; //App API Key(This is google cloud messaging api key not web api key)
                                        // API access key from Google API's Console
                                        //define('API_ACCESS_KEY', 'AIzaSyA3ACIrZoCQLyv7kGvfTgmy_0-H3UlpLlI');
                                        $registrationIds = array($reg_id);
                                        // prep the bundle
                                        $msg = array
                                            (
                                            'process_type' => 'email_type',
                                            'inbox_id' => $Inbox_id,
                                            'subject' => $Subject_Str,
                                            'Sendername' => $SenderName
                                        );
                                        $fields = array
                                            (
                                            'registration_ids' => $registrationIds,
                                            'data' => $msg
                                        );
                                        $headers = array
                                            (
                                            'Authorization: key=' . API_ACCESS_KEY,
                                            'Content-Type: application/json'
                                        );
                                        $ch = curl_init();
                                        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
                                        curl_setopt($ch, CURLOPT_POST, true);
                                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
                                        $result = curl_exec($ch);
                                        curl_close($ch);
                                    }

                                    $inbx_id =  $Inbox_id;
                                    $Insert_UploadFiles = new UploadFiles();
                                    $Insert_UploadFiles->mail_to = $to_jisd;
                                    $Insert_UploadFiles->mail_from = $User_id;
                                    $Insert_UploadFiles->file_name = $pic_name;
                                    $Insert_UploadFiles->file_path = $fname;
                                    $Insert_UploadFiles->orignal_filename = $Orignal_File1_Name;
                                    $Insert_UploadFiles->ext = $File1_Extention;
                                    $Insert_UploadFiles->inbox_id = $inbx_id;
                                    $Insert_UploadFiles->sent_id = $sent_id;
                                    $Insert_UploadFiles->save(false);

                                    $Insert_UploadFiles2 = new UploadFiles();
                                    $Insert_UploadFiles2->mail_to = $to_jisd;
                                    $Insert_UploadFiles2->mail_from = $User_id;
                                    $Insert_UploadFiles2->file_name = $pic_name2;
                                    $Insert_UploadFiles2->file_path = $fname2;
                                    $Insert_UploadFiles2->orignal_filename = $Orignal_File2_Name;
                                    $Insert_UploadFiles2->ext = $File2_Extention;
                                    $Insert_UploadFiles2->inbox_id = $inbx_id;
                                    $Insert_UploadFiles2->sent_id = $sent_id;
                                    $Insert_UploadFiles2->save(false);
                                }
                                $Report = array();
                                $Report['success'] = 1;
                                $Report['message'] = 'Message sent Successfully!!';
                                echo json_encode($Report);
                                die();
                            } else {
                                $Report = array();
                                $Report['success'] = 0;
                                $Report['message'] = 'Message sent failed!!';
                                echo json_encode($Report);
                                die();
                            }
                        } else {
                            $Report = array();
                            $Report['success'] = 0;
                            $Report['message'] = 'Message sent failed!!';
                            echo json_encode($Report);
                            die();
                        }
                    } else {
                        $Report = array();
                        $Report['success'] = 0;
                        $Report['message'] = 'Message sent failed!!';
                        echo json_encode($Report);
                        die();
                    }
                } else if ($File_Count == '3') {
                    $Orignal_File1_Name = $_REQUEST['Orignal_Name1'];
                    $Orignal_File2_Name = $_REQUEST['Orignal_Name2'];
                    $Orignal_File3_Name = $_REQUEST['Orignal_Name3'];
                    $File1_Extention = $_REQUEST['ext1'];
                    $File2_Extention = $_REQUEST['ext2'];
                    $File3_Extention = $_REQUEST['ext3'];
                    $Get_To_Detail = Yii::$app->db->createCommand("select type from profile where id in(" . $To_Str . ")")->queryAll();
                    foreach ($Get_To_Detail as $row) {
                        if ($To_Details == '') {
                            $To_Details = $row['type'];
                        } else {
                            $To_Details = $To_Details . ',' . $row['type'];
                        }
                    }

                    $Insert_SentBox = new Sent();
                    $Insert_SentBox->mail_to = $To_Str;
                    $Insert_SentBox->to_detail = $To_Details;
                    $Insert_SentBox->mail_from = $User_id;
                    $Insert_SentBox->subject = $Subject_Str;
                    $Insert_SentBox->message = $Message_Str;
                    $Insert_SentBox->file_status = 1;
                    if ($Insert_SentBox->save(false)) {
                        $sent_id = $Insert_SentBox->id;
                        if (is_uploaded_file($_FILES['Filedata1']['tmp_name'])) {
                            $pic_name = $_FILES['Filedata1']['name'];
                            $tmp_name = $_FILES['Filedata1']['tmp_name'];
                            $fname = Yii::getAlias('@frontend/web/Upload_Files/') . $pic_name;
                            move_uploaded_file($tmp_name, $fname);
                            $LocalUrl = Yii::getAlias('@frontend/web/Upload_Files/');
                            if (is_uploaded_file($_FILES['Filedata2']['tmp_name'])) {
                                $tmp_name2 = $_FILES['Filedata2']['tmp_name'];
                                $pic_name2 = $_FILES['Filedata2']['name'];
                                $fname2 = Yii::getAlias('@frontend/web/Upload_Files/') . $pic_name2;
                                move_uploaded_file($tmp_name2, $fname2);
                                if (is_uploaded_file($_FILES['Filedata3']['tmp_name'])) {
                                    $tmp_name3 = $_FILES['Filedata3']['tmp_name'];
                                    $pic_name3 = $_FILES['Filedata3']['name'];
                                    $fname3 = Yii::getAlias('@frontend/web/Upload_Files/') . $pic_name3;
                                    move_uploaded_file($tmp_name3, $fname3);
                                    $To_Array = array();
                                    $To_Array = explode(',', $To_Str);
                                     $Inbox_id='';
                                    for ($i = 0; $i < sizeof($To_Array); $i++) {
                                        $to_jisd = $To_Array[$i];
                                        $Select_Username_query = Yii::$app->db->createCommand("select id,app_regid,(select name from user where id='" . $User_id . "')as SenderName from user where user_type='" . $to_jisd . "' and id!='" . $User_id . "' and flag='ACTIVE'")->queryAll();
                                        foreach ($Select_Username_query as $row) {
                                            $mail_to_userid = $row['id'];
                                            $reg_id = $row['app_regid'];
                                            $SenderName = $row['SenderName'];
                                            $Insert_Inbox = new Inbox();
                                            $Insert_Inbox->sent_id = $sent_id;
                                            $Insert_Inbox->mail_to = $to_jisd;
                                            $Insert_Inbox->mail_from = $User_id;
                                            $Insert_Inbox->subject = $Subject_Str;
                                            $Insert_Inbox->message = $Message_Str;
                                            $Insert_Inbox->file_status = 1;
                                            $Insert_Inbox->mail_to_userid = $mail_to_userid;
                                            $Insert_Inbox->save(false);
                                            $Inbox_id = $Insert_Inbox->id;
                                            $fcmApiKey = 'AIzaSyA3ACIrZoCQLyv7kGvfTgmy_0-H3UlpLlI'; //App API Key(This is google cloud messaging api key not web api key)
                                            // API access key from Google API's Console
                                            //define('API_ACCESS_KEY', 'AIzaSyA3ACIrZoCQLyv7kGvfTgmy_0-H3UlpLlI');
                                            $registrationIds = array($reg_id);
                                            // prep the bundle
                                            $msg = array
                                                (
                                                'process_type' => 'email_type',
                                                'inbox_id' => $Inbox_id,
                                                'subject' => $Subject_Str,
                                                'Sendername' => $SenderName
                                            );
                                            $fields = array
                                                (
                                                'registration_ids' => $registrationIds,
                                                'data' => $msg
                                            );
                                            $headers = array
                                                (
                                                'Authorization: key=' . API_ACCESS_KEY,
                                                'Content-Type: application/json'
                                            );
                                            $ch = curl_init();
                                            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
                                            curl_setopt($ch, CURLOPT_POST, true);
                                            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                                            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
                                            $result = curl_exec($ch);
                                            curl_close($ch);
                                        }
                                        $inbx_id = $Inbox_id;
                                        $Insert_UploadFiles = new UploadFiles();
                                        $Insert_UploadFiles->mail_to = $to_jisd;
                                        $Insert_UploadFiles->mail_from = $User_id;
                                        $Insert_UploadFiles->file_name = $pic_name;
                                        $Insert_UploadFiles->file_path = $fname;
                                        $Insert_UploadFiles->orignal_filename = $Orignal_File1_Name;
                                        $Insert_UploadFiles->ext = $File1_Extention;
                                        $Insert_UploadFiles->inbox_id = $inbx_id;
                                        $Insert_UploadFiles->sent_id = $sent_id;
                                        $Insert_UploadFiles->save(false);

                                        $Insert_UploadFiles2 = new UploadFiles();
                                        $Insert_UploadFiles2->mail_to = $to_jisd;
                                        $Insert_UploadFiles2->mail_from = $User_id;
                                        $Insert_UploadFiles2->file_name = $pic_name2;
                                        $Insert_UploadFiles2->file_path = $fname2;
                                        $Insert_UploadFiles2->orignal_filename = $Orignal_File2_Name;
                                        $Insert_UploadFiles2->ext = $File2_Extention;
                                        $Insert_UploadFiles2->inbox_id = $inbx_id;
                                        $Insert_UploadFiles2->sent_id = $sent_id;
                                        $Insert_UploadFiles2->save(false);

                                        $Insert_UploadFiles3 = new UploadFiles();
                                        $Insert_UploadFiles3->mail_to = $to_jisd;
                                        $Insert_UploadFiles3->mail_from = $User_id;
                                        $Insert_UploadFiles3->file_name = $pic_name3;
                                        $Insert_UploadFiles3->file_path = $fname3;
                                        $Insert_UploadFiles3->orignal_filename = $Orignal_File3_Name;
                                        $Insert_UploadFiles3->ext = $File3_Extention;
                                        $Insert_UploadFiles3->inbox_id = $inbx_id;
                                        $Insert_UploadFiles3->sent_id = $sent_id;
                                        $Insert_UploadFiles3->save(false);
                                    }
                                     $Report = array();
                                $Report['success'] = 1;
                                $Report['message'] = 'Message sent Successfully!!';
                                echo json_encode($Report);
                                die();
                                } else {
                                    $Report = array();
                                    $Report['success'] = 0;
                                    $Report['message'] = 'Message sent failed!!';
                                    echo json_encode($Report);
                                    die();
                                }
                            } else {
                                $Report = array();
                                $Report['success'] = 0;
                                $Report['message'] = 'Message sent failed!!';
                                echo json_encode($Report);
                                die();
                            }
                        } else {
                            $Report = array();
                            $Report['success'] = 0;
                            $Report['message'] = 'Message sent failed!!';
                            echo json_encode($Report);
                            die();
                        }
                    } else {
                        $Report = array();
                        $Report['success'] = 0;
                        $Report['message'] = 'Message sent failed!!';
                        echo json_encode($Report);
                        die();
                    }
                } else if ($File_Count == '4') {
                    $Orignal_File1_Name = $_REQUEST['Orignal_Name1'];
                    $Orignal_File2_Name = $_REQUEST['Orignal_Name2'];
                    $Orignal_File3_Name = $_REQUEST['Orignal_Name3'];
                    $Orignal_File4_Name = $_REQUEST['Orignal_Name4'];
                    $File1_Extention = $_REQUEST['ext1'];
                    $File2_Extention = $_REQUEST['ext2'];
                    $File3_Extention = $_REQUEST['ext3'];
                    $File4_Extention = $_REQUEST['ext4'];


                    $Get_To_Detail = Yii::$app->db->createCommand("select type from profile where id in(" . $To_Str . ")")->queryAll();
                    foreach ($Get_To_Detail as $row) {
                        if ($To_Details == '') {
                            $To_Details = $row['type'];
                        } else {
                            $To_Details = $To_Details . ',' . $row['type'];
                        }
                    }

                    $Insert_SentBox = new Sent();
                    $Insert_SentBox->mail_to = $To_Str;
                    $Insert_SentBox->to_detail = $To_Details;
                    $Insert_SentBox->mail_from = $User_id;
                    $Insert_SentBox->subject = $Subject_Str;
                    $Insert_SentBox->message = $Message_Str;
                    $Insert_SentBox->file_status = 1;
                    if ($Insert_SentBox->save(false)) {
                        $sent_id = $Insert_SentBox->id;
                        if (is_uploaded_file($_FILES['Filedata1']['tmp_name'])) {
                            $pic_name = $_FILES['Filedata1']['name'];
                            $tmp_name = $_FILES['Filedata1']['tmp_name'];
                            $fname = Yii::getAlias('@frontend/web/Upload_Files/') . $pic_name;
                            move_uploaded_file($tmp_name, $fname);
                            $LocalUrl = Yii::getAlias('@frontend/web/Upload_Files/');
                            if (is_uploaded_file($_FILES['Filedata2']['tmp_name'])) {
                                $tmp_name2 = $_FILES['Filedata2']['tmp_name'];
                                $pic_name2 = $_FILES['Filedata2']['name'];
                                $fname2 = Yii::getAlias('@frontend/web/Upload_Files/') . $pic_name2;
                                move_uploaded_file($tmp_name2, $fname2);
                                if (is_uploaded_file($_FILES['Filedata3']['tmp_name'])) {
                                    $tmp_name3 = $_FILES['Filedata3']['tmp_name'];
                                    $pic_name3 = $_FILES['Filedata3']['name'];
                                    $fname3 = Yii::getAlias('@frontend/web/Upload_Files/') . $pic_name3;
                                    move_uploaded_file($tmp_name3, $fname3);

                                    if (is_uploaded_file($_FILES['Filedata4']['tmp_name'])) {
                                        $tmp_name4 = $_FILES['Filedata4']['tmp_name'];
                                        $pic_name4 = $_FILES['Filedata4']['name'];
                                        $fname4 = Yii::getAlias('@frontend/web/Upload_Files/') . $pic_name4;
                                        move_uploaded_file($tmp_name4, $fname4);

                                        $To_Array = array();
                                        $To_Array = explode(',', $To_Str);
                                        $Inbox_id='';
                                        for ($i = 0; $i < sizeof($To_Array); $i++) {
                                            $to_jisd = $To_Array[$i];
                                            $Select_Username_query = Yii::$app->db->createCommand("select id,app_regid,(select name from user where id='" . $User_id . "')as SenderName from user where user_type='" . $to_jisd . "' and id!='" . $User_id . "' and flag='ACTIVE'")->queryAll();
                                            foreach ($Select_Username_query as $row) {
                                                $mail_to_userid = $row['id'];
                                                $reg_id = $row['app_regid'];
                                                $SenderName = $row['SenderName'];
                                                $Insert_Inbox = new Inbox();
                                                $Insert_Inbox->sent_id = $sent_id;
                                                $Insert_Inbox->mail_to = $to_jisd;
                                                $Insert_Inbox->mail_from = $User_id;
                                                $Insert_Inbox->subject = $Subject_Str;
                                                $Insert_Inbox->message = $Message_Str;
                                                $Insert_Inbox->file_status = 1;
                                                $Insert_Inbox->mail_to_userid = $mail_to_userid;
                                                $Insert_Inbox->save(false);
                                                $Inbox_id = $Insert_Inbox->id;
                                                $fcmApiKey = 'AIzaSyA3ACIrZoCQLyv7kGvfTgmy_0-H3UlpLlI'; //App API Key(This is google cloud messaging api key not web api key)
                                                // API access key from Google API's Console
                                                //define('API_ACCESS_KEY', 'AIzaSyA3ACIrZoCQLyv7kGvfTgmy_0-H3UlpLlI');
                                                $registrationIds = array($reg_id);
                                                // prep the bundle
                                                $msg = array
                                                    (
                                                    'process_type' => 'email_type',
                                                    'inbox_id' => $Inbox_id,
                                                    'subject' => $Subject_Str,
                                                    'Sendername' => $SenderName
                                                );
                                                $fields = array
                                                    (
                                                    'registration_ids' => $registrationIds,
                                                    'data' => $msg
                                                );
                                                $headers = array
                                                    (
                                                    'Authorization: key=' . API_ACCESS_KEY,
                                                    'Content-Type: application/json'
                                                );
                                                $ch = curl_init();
                                                curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
                                                curl_setopt($ch, CURLOPT_POST, true);
                                                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                                                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
                                                $result = curl_exec($ch);
                                                curl_close($ch);
                                            }
                                            $inbx_id =  $Inbox_id;
                                            $Insert_UploadFiles = new UploadFiles();
                                            $Insert_UploadFiles->mail_to = $to_jisd;
                                            $Insert_UploadFiles->mail_from = $User_id;
                                            $Insert_UploadFiles->file_name = $pic_name;
                                            $Insert_UploadFiles->file_path = $fname;
                                            $Insert_UploadFiles->orignal_filename = $Orignal_File1_Name;
                                            $Insert_UploadFiles->ext = $File1_Extention;
                                            $Insert_UploadFiles->inbox_id = $inbx_id;
                                            $Insert_UploadFiles->sent_id = $sent_id;
                                            $Insert_UploadFiles->save(false);

                                            $Insert_UploadFiles2 = new UploadFiles();
                                            $Insert_UploadFiles2->mail_to = $to_jisd;
                                            $Insert_UploadFiles2->mail_from = $User_id;
                                            $Insert_UploadFiles2->file_name = $pic_name2;
                                            $Insert_UploadFiles2->file_path = $fname2;
                                            $Insert_UploadFiles2->orignal_filename = $Orignal_File2_Name;
                                            $Insert_UploadFiles2->ext = $File2_Extention;
                                            $Insert_UploadFiles2->inbox_id = $inbx_id;
                                            $Insert_UploadFiles2->sent_id = $sent_id;
                                            $Insert_UploadFiles2->save(false);

                                            $Insert_UploadFiles3 = new UploadFiles();
                                            $Insert_UploadFiles3->mail_to = $to_jisd;
                                            $Insert_UploadFiles3->mail_from = $User_id;
                                            $Insert_UploadFiles3->file_name = $pic_name3;
                                            $Insert_UploadFiles3->file_path = $fname3;
                                            $Insert_UploadFiles3->orignal_filename = $Orignal_File3_Name;
                                            $Insert_UploadFiles3->ext = $File3_Extention;
                                            $Insert_UploadFiles3->inbox_id = $inbx_id;
                                            $Insert_UploadFiles3->sent_id = $sent_id;
                                            $Insert_UploadFiles3->save(false);

                                            $Insert_UploadFiles4 = new UploadFiles();
                                            $Insert_UploadFiles4->mail_to = $to_jisd;
                                            $Insert_UploadFiles4->mail_from = $User_id;
                                            $Insert_UploadFiles4->file_name = $pic_name4;
                                            $Insert_UploadFiles4->file_path = $fname4;
                                            $Insert_UploadFiles4->orignal_filename = $Orignal_File4_Name;
                                            $Insert_UploadFiles4->ext = $File4_Extention;
                                            $Insert_UploadFiles4->inbox_id = $inbx_id;
                                            $Insert_UploadFiles4->sent_id = $sent_id;
                                            $Insert_UploadFiles4->save(false);
                                        }
                                         $Report = array();
                                $Report['success'] = 1;
                                $Report['message'] = 'Message sent Successfully!!';
                                echo json_encode($Report);
                                die();
                                    } else {
                                        $Report = array();
                                        $Report['success'] = 0;
                                        $Report['message'] = 'Message sent failed!!';
                                        echo json_encode($Report);
                                        die();
                                    }
                                } else {
                                    $Report = array();
                                    $Report['success'] = 0;
                                    $Report['message'] = 'Message sent failed!!';
                                    echo json_encode($Report);
                                    die();
                                }
                            } else {
                                $Report = array();
                                $Report['success'] = 0;
                                $Report['message'] = 'Message sent failed!!';
                                echo json_encode($Report);
                                die();
                            }
                        } else {
                            $Report = array();
                            $Report['success'] = 0;
                            $Report['message'] = 'Message sent failed!!';
                            echo json_encode($Report);
                            die();
                        }
                    } else {
                        $Report = array();
                        $Report['success'] = 0;
                        $Report['message'] = 'Message sent failed!!';
                        echo json_encode($Report);
                        die();
                    }
                } else if ($File_Count == '5') {
                    $Orignal_File1_Name = $_REQUEST['Orignal_Name1'];
                    $Orignal_File2_Name = $_REQUEST['Orignal_Name2'];
                    $Orignal_File3_Name = $_REQUEST['Orignal_Name3'];
                    $Orignal_File4_Name = $_REQUEST['Orignal_Name4'];
                    $Orignal_File5_Name = $_REQUEST['Orignal_Name5'];
                    $File1_Extention = $_REQUEST['ext1'];
                    $File2_Extention = $_REQUEST['ext2'];
                    $File3_Extention = $_REQUEST['ext3'];
                    $File4_Extention = $_REQUEST['ext4'];
                    $File5_Extention = $_REQUEST['ext5'];
                    $Get_To_Detail = Yii::$app->db->createCommand("select type from profile where id in(" . $To_Str . ")")->queryAll();
                    foreach ($Get_To_Detail as $row) {
                        if ($To_Details == '') {
                            $To_Details = $row['type'];
                        } else {
                            $To_Details = $To_Details . ',' . $row['type'];
                        }
                    }

                    $Insert_SentBox = new Sent();
                    $Insert_SentBox->mail_to = $To_Str;
                    $Insert_SentBox->to_detail = $To_Details;
                    $Insert_SentBox->mail_from = $User_id;
                    $Insert_SentBox->subject = $Subject_Str;
                    $Insert_SentBox->message = $Message_Str;
                    $Insert_SentBox->file_status = 1;
                    if ($Insert_SentBox->save(false)) {
                        $sent_id = $Insert_SentBox->id;
                        if (is_uploaded_file($_FILES['Filedata1']['tmp_name'])) {
                            $pic_name = $_FILES['Filedata1']['name'];
                            $tmp_name = $_FILES['Filedata1']['tmp_name'];
                            $fname = Yii::getAlias('@frontend/web/Upload_Files/') . $pic_name;
                            move_uploaded_file($tmp_name, $fname);
                            $LocalUrl = Yii::getAlias('@frontend/web/Upload_Files/');
                            if (is_uploaded_file($_FILES['Filedata2']['tmp_name'])) {
                                $tmp_name2 = $_FILES['Filedata2']['tmp_name'];
                                $pic_name2 = $_FILES['Filedata2']['name'];
                                $fname2 = Yii::getAlias('@frontend/web/Upload_Files/') . $pic_name2;
                                move_uploaded_file($tmp_name2, $fname2);
                                if (is_uploaded_file($_FILES['Filedata3']['tmp_name'])) {
                                    $tmp_name3 = $_FILES['Filedata3']['tmp_name'];
                                    $pic_name3 = $_FILES['Filedata3']['name'];
                                    $fname3 = Yii::getAlias('@frontend/web/Upload_Files/') . $pic_name3;
                                    move_uploaded_file($tmp_name3, $fname3);

                                    if (is_uploaded_file($_FILES['Filedata4']['tmp_name'])) {
                                        $tmp_name4 = $_FILES['Filedata4']['tmp_name'];
                                        $pic_name4 = $_FILES['Filedata4']['name'];
                                        $fname4 = Yii::getAlias('@frontend/web/Upload_Files/') . $pic_name4;
                                        move_uploaded_file($tmp_name4, $fname4);
                                        if (is_uploaded_file($_FILES['Filedata5']['tmp_name'])) {
                                            $tmp_name5 = $_FILES['Filedata5']['tmp_name'];
                                            $pic_name5 = $_FILES['Filedata5']['name'];
                                            $fname5 = Yii::getAlias('@frontend/web/Upload_Files/') . $pic_name5;
                                            move_uploaded_file($tmp_name5, $fname5);
                                            $To_Array = array();
                                            $To_Array = explode(',', $To_Str);
                                             $Inbox_id='';
                                            for ($i = 0; $i < sizeof($To_Array); $i++) {
                                                $to_jisd = $To_Array[$i];
                                                $Select_Username_query = Yii::$app->db->createCommand("select id,app_regid,(select name from user where id='" . $User_id . "')as SenderName from user where user_type='" . $to_jisd . "' and id!='" . $User_id . "' and flag='ACTIVE'")->queryAll();
                                                foreach ($Select_Username_query as $row) {
                                                    $mail_to_userid = $row['id'];
                                                    $reg_id = $row['app_regid'];
                                                    $SenderName = $row['SenderName'];
                                                    $Insert_Inbox = new Inbox();
                                                    $Insert_Inbox->sent_id = $sent_id;
                                                    $Insert_Inbox->mail_to = $to_jisd;
                                                    $Insert_Inbox->mail_from = $User_id;
                                                    $Insert_Inbox->subject = $Subject_Str;
                                                    $Insert_Inbox->message = $Message_Str;
                                                    $Insert_Inbox->file_status = 1;
                                                    $Insert_Inbox->mail_to_userid = $mail_to_userid;
                                                    $Insert_Inbox->save(false);
                                                    $Inbox_id = $Insert_Inbox->id;
                                                    $fcmApiKey = 'AIzaSyA3ACIrZoCQLyv7kGvfTgmy_0-H3UlpLlI'; //App API Key(This is google cloud messaging api key not web api key)
                                                    // API access key from Google API's Console
                                                    //define('API_ACCESS_KEY', 'AIzaSyA3ACIrZoCQLyv7kGvfTgmy_0-H3UlpLlI');
                                                    $registrationIds = array($reg_id);
                                                    // prep the bundle
                                                    $msg = array
                                                        (
                                                        'process_type' => 'email_type',
                                                        'inbox_id' => $Inbox_id,
                                                        'subject' => $Subject_Str,
                                                        'Sendername' => $SenderName
                                                    );
                                                    $fields = array
                                                        (
                                                        'registration_ids' => $registrationIds,
                                                        'data' => $msg
                                                    );
                                                    $headers = array
                                                        (
                                                        'Authorization: key=' . API_ACCESS_KEY,
                                                        'Content-Type: application/json'
                                                    );
                                                    $ch = curl_init();
                                                    curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
                                                    curl_setopt($ch, CURLOPT_POST, true);
                                                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                                                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
                                                    $result = curl_exec($ch);
                                                    curl_close($ch);
                                                }
                                                $inbx_id =  $Inbox_id;
                                                $Insert_UploadFiles = new UploadFiles();
                                                $Insert_UploadFiles->mail_to = $to_jisd;
                                                $Insert_UploadFiles->mail_from = $User_id;
                                                $Insert_UploadFiles->file_name = $pic_name;
                                                $Insert_UploadFiles->file_path = $fname;
                                                $Insert_UploadFiles->orignal_filename = $Orignal_File1_Name;
                                                $Insert_UploadFiles->ext = $File1_Extention;
                                                $Insert_UploadFiles->inbox_id = $inbx_id;
                                                $Insert_UploadFiles->sent_id = $sent_id;
                                                $Insert_UploadFiles->save(false);

                                                $Insert_UploadFiles2 = new UploadFiles();
                                                $Insert_UploadFiles2->mail_to = $to_jisd;
                                                $Insert_UploadFiles2->mail_from = $User_id;
                                                $Insert_UploadFiles2->file_name = $pic_name2;
                                                $Insert_UploadFiles2->file_path = $fname2;
                                                $Insert_UploadFiles2->orignal_filename = $Orignal_File2_Name;
                                                $Insert_UploadFiles2->ext = $File2_Extention;
                                                $Insert_UploadFiles2->inbox_id = $inbx_id;
                                                $Insert_UploadFiles2->sent_id = $sent_id;
                                                $Insert_UploadFiles2->save(false);

                                                $Insert_UploadFiles3 = new UploadFiles();
                                                $Insert_UploadFiles3->mail_to = $to_jisd;
                                                $Insert_UploadFiles3->mail_from = $User_id;
                                                $Insert_UploadFiles3->file_name = $pic_name3;
                                                $Insert_UploadFiles3->file_path = $fname3;
                                                $Insert_UploadFiles3->orignal_filename = $Orignal_File3_Name;
                                                $Insert_UploadFiles3->ext = $File3_Extention;
                                                $Insert_UploadFiles3->inbox_id = $inbx_id;
                                                $Insert_UploadFiles3->sent_id = $sent_id;
                                                $Insert_UploadFiles3->save(false);

                                                $Insert_UploadFiles4 = new UploadFiles();
                                                $Insert_UploadFiles4->mail_to = $to_jisd;
                                                $Insert_UploadFiles4->mail_from = $User_id;
                                                $Insert_UploadFiles4->file_name = $pic_name4;
                                                $Insert_UploadFiles4->file_path = $fname4;
                                                $Insert_UploadFiles4->orignal_filename = $Orignal_File4_Name;
                                                $Insert_UploadFiles4->ext = $File4_Extention;
                                                $Insert_UploadFiles4->inbox_id = $inbx_id;
                                                $Insert_UploadFiles4->sent_id = $sent_id;
                                                $Insert_UploadFiles4->save(false);

                                                $Insert_UploadFiles5 = new UploadFiles();
                                                $Insert_UploadFiles5->mail_to = $to_jisd;
                                                $Insert_UploadFiles5->mail_from = $User_id;
                                                $Insert_UploadFiles5->file_name = $pic_name5;
                                                $Insert_UploadFiles5->file_path = $fname5;
                                                $Insert_UploadFiles5->orignal_filename = $Orignal_File5_Name;
                                                $Insert_UploadFiles5->ext = $File5_Extention;
                                                $Insert_UploadFiles5->inbox_id = $inbx_id;
                                                $Insert_UploadFiles5->sent_id = $sent_id;
                                                $Insert_UploadFiles5->save(false);
                                            }
                                             $Report = array();
                                $Report['success'] = 1;
                                $Report['message'] = 'Message sent Successfully!!';
                                echo json_encode($Report);
                                die();
                                        } else {
                                            $Report = array();
                                            $Report['success'] = 0;
                                            $Report['message'] = 'Message sent failed!!';
                                            echo json_encode($Report);
                                            die();
                                        }
                                    } else {
                                        $Report = array();
                                        $Report['success'] = 0;
                                        $Report['message'] = 'Message sent failed!!';
                                        echo json_encode($Report);
                                        die();
                                    }
                                } else {
                                    $Report = array();
                                    $Report['success'] = 0;
                                    $Report['message'] = 'Message sent failed!!';
                                    echo json_encode($Report);
                                    die();
                                }
                            } else {
                                $Report = array();
                                $Report['success'] = 0;
                                $Report['message'] = 'Message sent failed!!';
                                echo json_encode($Report);
                                die();
                            }
                        } else {
                            $Report = array();
                            $Report['success'] = 0;
                            $Report['message'] = 'Message sent failed!!';
                            echo json_encode($Report);
                            die();
                        }
                    } else {
                        $Report = array();
                        $Report['success'] = 0;
                        $Report['message'] = 'Message sent failed!!';
                        echo json_encode($Report);
                        die();
                    }
                }
            } else {
                $Response['status'] = 3;
                echo json_encode($Response);
                die;
            }
        }
    }

    public function actionSaveAttachment() {

        if (Yii::$app->request->post()) {

            $Post_Id = $_REQUEST['post_id'];
            $File_Count = (int) $_REQUEST['fileCount'];
            $OrignalName = $_REQUEST['orignal_name'];
            $AttachmentType = $_REQUEST['attachment_type'];
            $token = $_REQUEST['token'];
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            if ($accessStatus['authentication'] === 1) {
                if($AttachmentType=='1'){
                        $Insert_UploadFiles = new TblPostAttachments();
                        $pic_name = $_FILES['Filedata']['name'];
                        $tmp_name = $_FILES['Filedata']['tmp_name'];
                        $LocalUrl = Yii::getAlias('@frontend/web/Upload_Files/');
                        $thumbPath = Yii::getAlias('@frontend/web/thumb/');
                        $thumbName = time() . $pic_name;
                        $Report = array();
                        Image::frame($tmp_name, 5, '666', 0)->rotate(0)->save($thumbPath . $thumbName, ['quality' => 50]);
                        move_uploaded_file($tmp_name, $LocalUrl . $pic_name);

                        $Insert_UploadFiles->post_id = $Post_Id;
                        $Insert_UploadFiles->attachment_name =$pic_name;
                        $Insert_UploadFiles->orignal_name = $OrignalName;
                        $Insert_UploadFiles->attachment_type = $AttachmentType;
                        $Insert_UploadFiles->attach_path = Yii::$app->params['file_url'];
                        $Insert_UploadFiles->thumb_path = $thumbPath;
                        $Insert_UploadFiles->thumb_name = $thumbName;
                        if ($Insert_UploadFiles->save()) {
                            $AttachCountQuery = TblPostAttachments::find()->where(['post_id' => $Post_Id])->all();
                            $AttachCount = count($AttachCountQuery);
                            $Report['success'] = 1;
                            $Report['count'] = $AttachCount;
                        } else {
                            $AttachCountQuery = TblPostAttachments::find()->where(['post_id' => $Post_Id])->all();
                            $AttachCount = count($AttachCountQuery);
                            $Report['success'] = 0;
                            $Report['count'] = $AttachCount;
                        }
                        echo json_encode($Report);
                }else{
                        $Insert_UploadFiles = new TblPostAttachments();
                        $pic_name = $_FILES['Filedata']['name'];
                        $tmp_name = $_FILES['Filedata']['tmp_name'];
                        $LocalUrl = Yii::getAlias('@frontend/web/Upload_Files/');
//                        $thumbPath = Yii::getAlias('@frontend/web/thumb/');
//                        $thumbName = time() . $pic_name;
                        $Report = array();
//                        Image::frame($tmp_name, 5, '666', 0)->rotate(0)->save($thumbPath . $pic_name, ['quality' => 50]);
                        move_uploaded_file($tmp_name, $LocalUrl . $pic_name);

                        $Insert_UploadFiles->post_id = $Post_Id;
                        $Insert_UploadFiles->attachment_name =$pic_name;
                        $Insert_UploadFiles->orignal_name = $OrignalName;
                        $Insert_UploadFiles->attachment_type = $AttachmentType;
                        $Insert_UploadFiles->attach_path = Yii::$app->params['file_url'];
                        $Insert_UploadFiles->thumb_path = 'thumbPath';
                        $Insert_UploadFiles->thumb_name = 'thumbName';
                        if ($Insert_UploadFiles->save()) {
                            $AttachCountQuery = TblPostAttachments::find()->where(['post_id' => $Post_Id])->all();
                            $AttachCount = count($AttachCountQuery);
                            $Report['success'] = 1;
                            $Report['count'] = $AttachCount;
                        } else {
                            $AttachCountQuery = TblPostAttachments::find()->where(['post_id' => $Post_Id])->all();
                            $AttachCount = count($AttachCountQuery);
                            $Report['success'] = 0;
                            $Report['count'] = $AttachCount;
                        }
                        echo json_encode($Report);  
                }
                
                
                
                
                
                
            } else {
                $Report['success'] = 3;
                echo json_encode($Report);
                die;
            }
        }
    }

    public function actionUpdateRegId() {
        if (Yii::$app->request->post()) {
            $user_id = $_REQUEST['user_id'];
            $red_id = $_REQUEST['reg_id'];
            $UpdateUser = User::find()->where(['id' => $user_id])->one();
            $UpdateUser->app_regid = $red_id;
            $UpdateUser->save(false);
        }
    }

    public function actionUpdateContact() {
        if (Yii::$app->request->post()) {

            $userid = $_REQUEST['id'];
            $mobileno = $_REQUEST['mobile_no'];
            $token = $_REQUEST['token'];
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            if ($accessStatus['authentication'] === 1) {
                $model = User::find()->where(['id' => $userid])->one();
                $model->mobile_no = $mobileno;

                if ($model->save(FALSE)) {
                    $array = array('success' => 1, 'message' => 'Mobile No Updated Successfully');
                } else {
                    $array = array('success' => 0, 'message' => 'Error');
                }
                echo json_encode($array);die;
            } else {
                $Report = array();
                $Report['success'] = 3;
                $Report['message'] = 'Unauthorise Access.';
                echo json_encode($Report);die;
            }
        }
    }

    public function actionGetAllFeedback() {
        if (Yii::$app->request->post()) {
            $UserId = $_REQUEST['user_id'];
            $Limit_data = (int) $_REQUEST['limit'];
            $token = $_REQUEST['token'];
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            $Response = array();
            $Response['feedback_data'] = array();
            if ($accessStatus['authentication'] === 1) {
                $Response['status'] = 1;
                $post_query = TblFeedback::find()->orderBy(['id' => SORT_DESC])->limit($Limit_data)->all();
                foreach ($post_query as $value) {
                    $SenderId = $value['sender_id'];
                    $senderImageQuery = User::find()->select('image_path,image_name')->where(['id' => $SenderId])->all();
                    $sender_image = $senderImageQuery[0]->image_path . $senderImageQuery[0]->image_name;
                    //echo $sender_image;die();
                    $Report = array("id" => $value['id'], "sender_image" => $sender_image, "sender_id" => $SenderId, "sender_name" => $value['sender_name'], "feedback_str" => $value['feedback_str'], "date_time" => $value['date_time']);
                    array_push($Response['feedback_data'], $Report);
                }
                $Total_PostQuery = TblFeedback::find()->all();
                $Total_Post = count($Total_PostQuery);
                $Response['total_post'] = $Total_Post;
                echo json_encode($Response);
                die();
            } else {
                $Response['status'] = 3;
                echo json_encode($Response);
                die;
            }
        }
    }

    public function actionUpdateEmployeeRole() {
        if (Yii::$app->request->post()) {
            $user_id = $_REQUEST['user_id'];
            $employee_id = $_REQUEST['employee_id'];
            $employee_type_id = $_REQUEST['type_id'];
            $token = $_REQUEST['token'];
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            if ($accessStatus['authentication'] === 1) {
                date_default_timezone_set('Asia/Calcutta');
                $current_date_time = date('D,Y-m-d H:i:s');
                $Usermodel = User::find()->where(['id' => $employee_id])->one();
                $Usermodel->user_type = $employee_type_id;
                $Usermodel->updated_at = $current_date_time;
                $Usermodel->update_status = 1;
                if ($Usermodel->save(false)) {
                    $Report = array();
                    $Report['status'] = 1;
                    $Report['message'] = 'Updated Successfully!!';
                    echo json_encode($Report);
                    die;
                } else {
                    $Report = array();
                    $Report['status'] = 0;
                    $Report['message'] = 'Error!! please try again.';
                    echo json_encode($Report);
                    die;
                }
            } else {
                $Report = array();
                $Report['status'] = 3;
                $Report['message'] = 'Unauthorise Access';
                echo json_encode($Report);
                die;
            }
        }
    }

    public function actionGetEmployeeList() {
        if (Yii::$app->request->post()) {
            $user_id = $_REQUEST['user_id'];
            $token = $_REQUEST['token'];
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            $Response = array();
            $Response['employee_list'] = array();
            $Response['profile_list'] = array();
            if ($accessStatus['authentication'] === 1) {
                $Response['status'] = 1;
                $GetEmpModel = User::find()->where(['!=', 'id', $user_id])->andWhere(['flag' => 'ACTIVE'])->orderBy('name ASC')->all();
                $GetProfileModel = Profile::find()->where(['status' => 1])->all();
               
                
                foreach ($GetEmpModel as $emp) {
                    $Report = array();
                    $Report['id'] = $emp->id;
                    $Report['username'] = $emp->username;
                    $Report['email'] = $emp->email;
                    $Report['user_type'] = $emp->user_type;
                    $Report['name'] = $emp->name;
                    $Report['employee_id'] = $emp->employee_id;
                    array_push($Response['employee_list'], $Report);
                }

                foreach ($GetProfileModel as $pro) {
                    $Report1 = array();
                    $Report1['id'] = $pro->id;
                    $Report1['type'] = $pro->type;
                    $Report1['status'] = $pro->status;
                    $Report1['created_on'] = $pro->created_on;
                    array_push($Response['profile_list'], $Report1);
                }
            } else {
                $Response['status'] = 3;
            }

            echo json_encode($Response);
            die;
        }
    }

    public function actionUpdateChatStatus() {

        if (Yii::$app->request->post()) {

            $Sender_ID = $_REQUEST['sender_id'];
            $Receiver_Id = $_REQUEST['receiver_id'];
            $token = $_REQUEST['token'];
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            if ($accessStatus['authentication'] === 1) {

                $get_Unread_List = Yii::$app->db->createCommand("select id,(select app_regid from user where id = c.sender_id) as reg_id from chat c where sender_id='" . $Sender_ID . "' and receiver_id='" . $Receiver_Id . "' and read_flag=0")->queryAll();

                foreach ($get_Unread_List as $value) {

                    $chat_id = $value['id'];
                    $reg_id = $value['reg_id'];
                    $Update_Query = Chat::find()->where(['id' => $chat_id])->andWhere(['read_flag' => 0])->one();
                    $Update_Query->read_flag = 1;
                    $Update_Query->save(false);
                    $fcmApiKey = 'AIzaSyA3ACIrZoCQLyv7kGvfTgmy_0-H3UlpLlI'; //App API Key(This is google cloud messaging api key not web api key)
                    // API access key from Google API's Console
                    define('API_ACCESS_KEY', 'AIzaSyA3ACIrZoCQLyv7kGvfTgmy_0-H3UlpLlI');
                    $registrationIds = array($reg_id);

                    $msg = array('receiver_id' => $Receiver_Id, 'id' => $chat_id, 'sender_id' => $Sender_ID, 'process_type' => 'chat_read_type');
                    $fields = array('registration_ids' => $registrationIds, 'data' => $msg);
                    $headers = array('Authorization: key=' . API_ACCESS_KEY, 'Content-Type: application/json');

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
                    $result = curl_exec($ch);
                    curl_close($ch);
                }
                $Report = array();
                $Report['status'] = 1;
                echo json_encode($Report);
                die;
            }
        } else {
            $Report = array();
            $Report['status'] = 3;
            echo json_encode($Report);
            die;
        }
    }

    public function actionUpdateSingleChatStatus() {
        if (Yii::$app->request->post()) {
            $chat_id = $_REQUEST['chat_id'];
            $token = $_REQUEST['token'];
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            if ($accessStatus['authentication'] === 1) {
                $ChatModel = Chat::find()->where(['id' => $chat_id])->one();
                $ChatModel->read_flag = 1;
                if ($ChatModel->save(false)) {
                    $Report = array();
                    $Report['status'] = 1;
                    echo json_encode($Report);
                    $Sender_Id = $ChatModel->sender_id;
                    $Receiver_Id = $ChatModel->receiver_id;
                    $USermodel = User::find()->where(['id' => $Sender_Id])->one();
                    $Reg_id = $USermodel->app_regid;
                    $fcmApiKey = 'AIzaSyA3ACIrZoCQLyv7kGvfTgmy_0-H3UlpLlI';
                    //App API Key(This is google cloud messaging api key not web api key)
                    // API access key from Google API's Console
                    define('API_ACCESS_KEY', 'AIzaSyA3ACIrZoCQLyv7kGvfTgmy_0-H3UlpLlI');
                    $registrationIds = array($Reg_id);
                    // prep the bundle
                    $msg = array
                        (
                        'receiver_id' => $Receiver_Id,
                        'id' => $chat_id,
                        'sender_id' => $Sender_Id,
                        'process_type' => 'chat_read_type'
                    );
                    $fields = array
                        (
                        'registration_ids' => $registrationIds,
                        'data' => $msg
                    );
                    $headers = array
                        (
                        'Authorization: key=' . API_ACCESS_KEY,
                        'Content-Type: application/json'
                    );
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
                    $result = curl_exec($ch);
                    curl_close($ch);
                    die();
                } else {
                    $Report = array();
                    $Report['status'] = 0;
                    echo json_encode($Report);
                }
            } else {
                $Report = array();
                $Report['status'] = 3;
                echo json_encode($Report);
                die;
            }
        }
    }

    public function actionDeleteMultiChat() {
        if (Yii::$app->request->post()) {
            $User_Id = $_REQUEST['user_id'];
            $Chat_id = $_REQUEST['chat_id'];
            $token = $_REQUEST['token'];
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);

            if ($accessStatus['authentication'] === 1) {
                $RecieverChat = Chat::find()->where(['receiver_id' => $User_Id])->andWhere(['id' => $Chat_id])->one();
                $RecieverChat->receiver_flag = 0;
                $RecieverChat->save(false);

                $SenderChat = Chat::find()->where(['sender_id' => $User_Id])->andWhere(['id' => $Chat_id])->one();
                $SenderChat->sender_flag = 0;
                $SenderChat->save(false);

                $Report = array();
                $Report['success'] = 1;
                echo json_encode($Report);
                die();
            } else {
                $Report = array();
                $Report['success'] = 3;
                echo json_encode($Report);
                die();
            }
        }
    }

    public function actionUploadChatImage() {
        if (Yii::$app->request->post()) {
            $Sender_Id = $_REQUEST['sender_id'];
            $Receiver_Id = $_REQUEST['receiver_id'];
            $msg = $_REQUEST['message_str'];
            $MsgStr = str_replace("\\", "\\\\", $msg);
            $Message_Str = str_replace("'", "\'", $MsgStr);
            $Orignal_File1_Name = $_REQUEST['Orignal_Name'];
            $File1_Extention = $_REQUEST['ext'];
            $token = $_REQUEST['token'];
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            if ($accessStatus['authentication'] === 1) {
                date_default_timezone_set('Asia/Calcutta');
                $current_date_time = date('D,Y-m-d H:i:s');
                $InsertChat = new Chat();
                if (is_uploaded_file($_FILES['Filedata']['tmp_name'])) {
                    $pic_name = $_FILES['Filedata']['name'];
                    $tmp_name = $_FILES['Filedata']['tmp_name'];
                    $fname = Yii::getAlias('@frontend/web/Chat_Files/') . $pic_name;
                    move_uploaded_file($tmp_name, $fname);
                    $LocalUrl = Yii::getAlias('@frontend/web/Chat_Files/');
                    $InsertChat->sender_id = $Sender_Id;
                    $InsertChat->receiver_id = $Receiver_Id;
                    $InsertChat->message = $Message_Str;
                    $InsertChat->process_date = $current_date_time;
                    $InsertChat->file_status = 1;
                    $InsertChat->file_name = $pic_name;
                    $InsertChat->file_extention = $File1_Extention;
                    $InsertChat->file_orignal_name = $Orignal_File1_Name;
                    $InsertChat->file_path = $fname;
                    if ($InsertChat->save(false)) {
                        $Report = array();
                        $Report['success'] = 1;
                        $Report['message'] = 'Message sent Successfully!!';
                        $chat_id = $InsertChat->id;
                        $Report['id'] = $chat_id;
                        $Report['image_path'] = Yii::$app->params['chat_file_url'] . $pic_name;
                        echo json_encode($Report);

                        $SenderNameModel = User::find()->where(['id' => $Sender_Id])->one();
                        $Sender_name = $SenderNameModel->name;

                        $UserModel = User::find()->where(['id' => $Receiver_Id])->one();
                        $reg_id = $UserModel->app_regid;
                        $Receiver_name = $UserModel->name;

                        $fcmApiKey = 'AIzaSyA3ACIrZoCQLyv7kGvfTgmy_0-H3UlpLlI';
                        define('API_ACCESS_KEY', 'AIzaSyA3ACIrZoCQLyv7kGvfTgmy_0-H3UlpLlI');
                        $registrationIds = array($reg_id);
                        $msg = array
                            (
                            'receiver_id' => $Receiver_Id,
                            'id' => $chat_id,
                            'sender_id' => $Sender_Id,
                            'message' => $msg,
                            'file_status' => '1',
                            'file_name' => $pic_name,
                            'file_extention' => $File1_Extention,
                            'file_orignal_name' => $Orignal_File1_Name,
                            'file_path' => Yii::$app->params['chat_file_url'] . $pic_name,
                            'process_type' => 'chat_type',
                            'process_date' => $current_date_time,
                            'read_flag' => '0',
                            'receiver_name' => $Sender_name,
                            'sender_name' => $Receiver_name
                        );
                        $fields = array
                            (
                            'registration_ids' => $registrationIds,
                            'data' => $msg
                        );
                        $headers = array
                            (
                            'Authorization: key=' . API_ACCESS_KEY,
                            'Content-Type: application/json'
                        );
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
                        $result = curl_exec($ch);
                        curl_close($ch);
                        die();
                    } else {
                        $Report = array();
                        $Report['success'] = 0;
                        $Report['message'] = 'Message sent failed!!';
                        echo json_encode($Report);
                        die();
                    }
                } else {
                    $Report = array();
                    $Report['success'] = 0;
                    $Report['message'] = 'Message sent failed!!';
                    echo json_encode($Report);
                    die();
                }
            } else {
                $Report['status'] = 3;
                $Report['message'] = 'Unauthorise Access';
                echo json_encode($Report);
                die;
            }
        }
    }

    public function actionGetChatList() {

        if (Yii::$app->request->post()) {

            $Sender_Id = $_REQUEST['sender_id'];
            $Receiver_Id = $_REQUEST['receiver_id'];
            $token = $_REQUEST['token'];
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            $Response = array();
            $Response['chat_list'] = array();
            if ($accessStatus['authentication'] === 1) {
                $Response['status'] = 1;
                $GetChatList_Q = Chat::find()->where(['sender_id' => $Sender_Id])->andWhere(['receiver_id' => $Receiver_Id])->andWhere(['sender_flag' => 1])->orWhere(['and', ['sender_id' => $Receiver_Id], ['receiver_id' => $Sender_Id], ['receiver_flag' => 1]])->all();
                foreach ($GetChatList_Q as $value) {
                    $Report = array("id" => $value['id'], "sender_id" => $value['sender_id'], "receiver_id" => $value['receiver_id'], "message" => str_replace("\\\\", "\\", $value['message']), "file_status" => $value['file_status'], "file_name" => Yii::$app->params['chat_file_url'] . $value['file_name'], "file_extention" => $value['file_extention'], "file_orignal_name" => $value['file_orignal_name'], "file_path" =>  Yii::$app->params['chat_file_url'] . $value['file_name'], "process_date" => $value['process_date'], "read_flag" => $value['read_flag']);
                    array_push($Response['chat_list'], $Report);
                }
            } else {
                $Response['status'] = 3;
            }
            echo json_encode($Response);
        }
    }

    public function actionInsertChatMessage() {
        if (Yii::$app->request->post()) {
            $Sender_Id = $_REQUEST['sender_id'];
            $Receiver_Id = $_REQUEST['receiver_id'];
            $msg = $_REQUEST['message_str'];
            $token = $_REQUEST['token'];
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            if ($accessStatus['authentication'] === 1) {
                $MsgStr = str_replace("\\", "\\\\", $msg);
                $Message_Str = str_replace("'", "\'", $MsgStr);
                date_default_timezone_set('Asia/Calcutta');
                $current_date_time = date('D,Y-m-d H:i:s');
                $ChatModel = new Chat();
                $ChatModel->sender_id = $Sender_Id;
                $ChatModel->receiver_id = $Receiver_Id;
                $ChatModel->message = $Message_Str;
                $ChatModel->process_date = $current_date_time;
                if ($ChatModel->save(false)) {
                    $Report = array();
                    $Report['success'] = 1;
                    $Report['message'] = 'Message sent Successfully!!';
                    $chat_id = $ChatModel->id;
                    $Report['id'] = $chat_id;
                    echo json_encode($Report);

                    $SenderNameModel = User::find()->where(['id' => $Sender_Id])->one();
                    $Receiver_name = $SenderNameModel->name;

                    $UserModel = User::find()->where(['id' => $Receiver_Id])->one();
                    $reg_id = $UserModel->app_regid;

                    $fcmApiKey = 'AIzaSyA3ACIrZoCQLyv7kGvfTgmy_0-H3UlpLlI';
                    define('API_ACCESS_KEY', 'AIzaSyA3ACIrZoCQLyv7kGvfTgmy_0-H3UlpLlI');
                    $registrationIds = array($reg_id);
                    $msg = array
                        (
                        'receiver_id' => $Receiver_Id,
                        'id' => $chat_id,
                        'sender_id' => $Sender_Id,
                        'message' => $msg,
                        'file_status' => '0',
                        'file_name' => '',
                        'file_extention' => '',
                        'file_orignal_name' => '',
                        'file_path' => '',
                        'process_type' => 'chat_type',
                        'process_date' => $current_date_time,
                        'read_flag' => '0',
                        'receiver_name' => $Receiver_name
                    );
                    $fields = array
                        (
                        'registration_ids' => $registrationIds,
                        'data' => $msg
                    );
                    $headers = array
                        (
                        'Authorization: key=' . API_ACCESS_KEY,
                        'Content-Type: application/json'
                    );
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
                    $result = curl_exec($ch);
                    curl_close($ch);
                    die;
                } else {
                    $Report = array();
                    $Report['success'] = 0;
                    $Report['message'] = 'Message sent Failed!!';
                    echo json_encode($Report);
                    die;
                }
            } else {
                $Report = array();
                $Report['success'] = 3;
                $Report['message'] = 'Unauthorise Access';
                echo json_encode($Report);
                die;
            }
        }
    }

    public function actionGetChatContactList() {
        if (Yii::$app->request->post()) {
            $user_id = $_REQUEST['user_id'];
            $token = $_REQUEST['token'];
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            $Response = array();
            $Response['chat_contact_list'] = array();
            if ($accessStatus['authentication'] === 1) {
                $Response['status'] = 1;
                $GetContact_List = Yii::$app->db->createCommand('select id,name,image_path,image_name,(select type from profile where id=u.user_type)as department from user u where id !="' . $user_id . '" and flag="ACTIVE" order by name')
                        ->queryAll();
                foreach ($GetContact_List as $value) {
                    $Report = array("id" => $value['id'], "name" => $value['name'], "image" => $value['image_path'] . $value['image_name'], "department" => $value['department']);
                    //$Report[]=array("id"=>$value['id'],"name"=>$value['name'],"image"=>$value['image_path'].$value['image_name'],"department"=>$value['department']);
                    array_push($Response['chat_contact_list'], $Report);
                }
            } else {
                $Response['status'] = 3;
            }
            echo json_encode($Response);
        }
    }

    public function actionGetGroupChatList() {
        if (Yii::$app->request->post()) {
            $Receiver_id = $_REQUEST['receiver_id'];
            $token = $_REQUEST['token'];
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            $Response = array();
            $Response['chat_group_list'] = array();
            if ($accessStatus['authentication'] === 1) {
                $Response['status'] = 1;
                $My_New_Query = Yii::$app->db->createCommand("SELECT id,
		-- sender_id,receiver_id,
		MAX(message)as message,
		(case when (receiver_id='" . $Receiver_id . "')  THEN
				sender_id
		ELSE
				receiver_id
		END) as sender,

		(case when (receiver_id='" . $Receiver_id . "')  THEN
			
		(select name from user where id=c.sender_id)
		-- (select image_name from user where id=c.receiver_id)as image_name,     
		ELSE
		(select name from user where id=c.receiver_id)		
		END) as name,
		(case when (receiver_id='" . $Receiver_id . "')  THEN
			
		(select image_name from user where id=c.sender_id)
		-- (select image_name from user where id=c.receiver_id)as image_name,     
		ELSE
		(select image_name from user where id=c.receiver_id)		
		END) as image_name,
		-- (select count(*) as total from chat where sender_id=c.receiver_id and sender_id=c.sender_id and read_flag=0),
		(case when (receiver_id='" . $Receiver_id . "' )  THEN
		(select count(*) as total from chat where receiver_id='" . $Receiver_id . "' and sender_id=c.sender_id and read_flag=0)
				
		ELSE
		(select count(*) as total from chat where receiver_id='" . $Receiver_id . "' and sender_id=c.receiver_id and read_flag=0)
			
		END) as total

		-- (select sender_id from chat where  ) as sender_ida 
		FROM chat c
		where (sender_id='" . $Receiver_id . "' or receiver_id='" . $Receiver_id . "') 
		and sender_id not in (SELECT  receiver_id FROM chat where sender_id='" . $Receiver_id . "' group by receiver_id,sender_id order by id desc) 
		 group by receiver_id,sender_id order by id desc")->queryAll();

                foreach ($My_New_Query as $row) {
                    $Report = array();
                    $Report['id'] = $row['sender'];
                    $Report['Total'] = $row['total'];
                    $Report['message'] =  str_replace("\\\\", "\\", $row['message']);
                    $Report['name'] = $row['name'];
                    $Report['image_path'] = Yii::$app->params['file_url'] . $row['image_name'];
                    array_push($Response['chat_group_list'], $Report);
                }
            } else {
                $Response['status'] = 3;
            }
            echo json_encode($Response);
            die;
        }
    }

    public function actionGetInboxMailDetails() {

        if (Yii::$app->request->post()) {

            $user_id = $_REQUEST['user_id'];
            $inbox_id = $_REQUEST['inbox_id'];
            $token = $_REQUEST['token'];
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            $Response = array();
            $Response['my_inbox_detail'] = array();
            $Response['my_inbox_attach'] = array();

            if ($accessStatus['authentication'] === 1) {
                $Response['status'] = 1;
                $mySent_Details = Yii::$app->db->createCommand('select id,sent_id,mail_to,subject,message,flag,file_status,process_date,mail_to_userid,(select username from user where id=i.mail_from)as mail_from from inbox i where id="' . $inbox_id . '" and mail_to_userid="' . $user_id . '" and flag=1')
                        ->queryAll();
                foreach ($mySent_Details as $value) {

                    $Report['id'] = $value['id'];
                    $Report['mail_to'] = $value['mail_to'];
                    $Report['mail_from'] = $value['mail_from'];
                    $Report['subject'] = $value['subject'];
                    $Report['message'] = $value['message'];
                    $Report['file_status'] = $value['file_status'];
                    $Report['process_date'] = $value['process_date'];
                    $sent_id = $value['sent_id'];
                    array_push($Response['my_inbox_detail'], $Report);
                    if ($value['file_status'] == '1') {
                        $MySent_Attach = UploadFiles::find()->select(['file_name','file_path','ext','orignal_filename'])->distinct()->where(['sent_id' => $sent_id])->all();
                        foreach ($MySent_Attach as $val) {
                            $Report1['file_name'] = $val['file_name'];
                            $Report1['file_path'] = $val['file_path'];
                            $Report1['ext'] = $val['ext'];
                            $Report1['orignal_filename'] = $val['orignal_filename'];
                            $Report1['url'] = Yii::$app->params['file_url'];
                            array_push($Response['my_inbox_attach'], $Report1);
                        }
                    }
                }
            } else {
                $Response['status'] = 3;
            }
            echo json_encode($Response);
            die();
        }
    }

    public function actionSentBoxMail() {
        if (Yii::$app->request->post()) {

            $user_id = $_POST['user_id'];
            $token = $_REQUEST['token'];
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            $Response = array();
            if ($accessStatus['authentication'] === 1) {
                $Response['status'] = 1;
                $my_sent_q = Sent::find()->where(['mail_from' => $user_id])->andWhere(['flag' => 1])->orderBy(['id' => SORT_DESC])->all();
                $Response['my_sent_list'] = array();
                foreach ($my_sent_q as $value) {

                    $Report['id'] = $value['id'];
                    $Report['mail_to'] = $value['mail_to'];
                    $Report['to_detail'] = $value['to_detail'];
                    $Report['subject'] = $value['subject'];
                    $Report['message'] = $value['message'];
                    $Report['file_status'] = $value['file_status'];
                    $Report['process_date'] = $value['process_date'];
                    array_push($Response['my_sent_list'], $Report);
                }
            } else {
                $Response['status'] = 3;
            }
            echo json_encode($Response);
            die();
        }
    }

    public function actionGetSentMailDetails() {
        if (Yii::$app->request->post()) {
            $user_id = $_REQUEST['user_id'];
            $sent_id = $_REQUEST['sent_id'];
            $token = $_REQUEST['token'];
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            $Response = array();
            $Response['my_sent_detail'] = array();
            $Response['my_sent_attach'] = array();
            if ($accessStatus['authentication'] === 1) {
                $Response['status'] = 1;
                $SentModel = Sent::find()->where(['id' => $sent_id])->andWhere(['mail_from' => $user_id])->andWhere(['flag' => 1])->all();
                foreach ($SentModel as $row) {
                    $Report = array();
                    $Report['id'] = $row->id;
                    $Report['mail_to'] = $row->mail_to;
                    $Report['to_detail'] = $row->to_detail;
                    $Report['subject'] = $row->subject;
                    $Report['message'] = $row->message;
                    $fileStatus = $row->file_status;
                    $Report['file_status'] = $fileStatus;
                    $Report['process_date'] = $row->process_date;
                    array_push($Response['my_sent_detail'], $Report);
                    if ($fileStatus == '1') {
                        $AttachModel = UploadFiles::find()->select(['file_name','file_path','ext','orignal_filename'])->distinct()->where(['sent_id' => $sent_id])->all();
                        foreach ($AttachModel as $row1) {
                            $Report1 = array();
                            $Report1['file_name'] = $row1->file_name;
                            $Report1['file_path'] = $row1->file_path;
                            $Report1['ext'] = $row1->ext;
                            $Report1['orignal_filename'] = $row1->orignal_filename;
                            $Report1['url'] = Yii::$app->params['file_url'];
                            array_push($Response['my_sent_attach'], $Report1);
                        }
                    }
                }
            } else {
                $Response['status'] = 3;
            }
            echo json_encode($Response);
            die;
        }
    }

    public function actionDeleteSentMail() {
        if (Yii::$app->request->post()) {
            $user_id = $_REQUEST['user_id'];
            $sent_id = $_REQUEST['sent_id'];
            $token = $_REQUEST['token'];
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            $Report = array();
            if ($accessStatus['authentication'] === 1) {
                $SentModel = Sent::find()->where(['id' => $sent_id])->andWhere(['mail_from' => $user_id])->one();
                $SentModel->flag = 0;
                if ($SentModel->save(false)) {
                    $Report['status'] = 1;
                    $Report['message'] = 'Delete Successfully';
                    echo json_encode($Report);
                    die;
                } else {
                    $Report['status'] = 0;
                    $Report['message'] = 'Error! Please Try again';
                    echo json_encode($Report);
                    die;
                }
            } else {
                $Report['status'] = 3;
                $Report['message'] = 'Unauthorise Access';
                echo json_encode($Report);
                die;
            }
        }
    }

    public function actionInboxMail() {
        if (Yii::$app->request->post()) {

            $user_id = $_REQUEST['user_id'];
            $user_type = $_REQUEST['user_type'];
            $token = $_REQUEST['token'];
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            $Response = array();
            $Response['my_inbox_list'] = array();
            if ($accessStatus['authentication'] === 1) {
                $my_inbox_q = Yii::$app->db->createCommand('select id,sent_id,process_date,mail_from,(select username from user where id=i.mail_from)as Sendername,(select image_name from user where id=i.mail_from)as Senderimage,subject,message,file_status from inbox i where mail_to="' . $user_type . '" and mail_from!="' . $user_id . '" and flag=1 and mail_to_userid="' . $user_id . '" order by id DESC')
                        ->queryAll();

                foreach ($my_inbox_q as $value) {
                    $Report = array();
                    $Report['id'] = $value['id'];
                    $Report['sent_id'] = $value['sent_id'];
                    $Report['process_date'] = $value['process_date'];
                    $Report['mail_from'] = $value['mail_from'];
                    $Report['Sendername'] = $value['Sendername'];
                    $Report['subject'] = $value['subject'];
                    $Report['message'] = $value['message'];
                    $Report['Senderimage'] = Yii::$app->params['file_url'].$value['Senderimage'];
                    $Report['file_status'] = $value['file_status'];
                    array_push($Response['my_inbox_list'], $Report);
                }
                $Response['status'] = 1;
                echo json_encode($Response);
            } else {
                $Response['status'] = 3;
                echo json_encode($Response);
                die;
            }
        }
    }

    public function actionDeleteInboxMail() {
        if (Yii::$app->request->post()) {
            $user_id = $_REQUEST['user_id'];
            $inbox_id = $_REQUEST['inbox_id'];
            $user_type = $_REQUEST['user_type'];
            $token = $_REQUEST['token'];
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            $Reposrt = array();
            if ($accessStatus['authentication'] === 1) {
                $InboxModel = Inbox::find()->where(['id' => $inbox_id])->andWhere(['mail_to' => $user_type])->andWhere(['mail_to_userid' => $user_id])->one();
                $InboxModel->flag = 0;
                if ($InboxModel->save(false)) {
                    $Report['status'] = 1;
                    $Report['message'] = 'Delete Successfully';
                    echo json_encode($Report);
                    die;
                } else {
                    $Report['status'] = 0;
                    $Report['message'] = 'Error! Please Try again';
                    echo json_encode($Report);
                    die;
                }
            } else {
                $Report['status'] = 3;
                $Report['message'] = 'Unauthorise Access';
                echo json_encode($Report);
                die;
            }
        }
    }

    public function actionSubmitFeedback() {
        if (Yii::$app->request->post()) {
            $user_id = $_REQUEST['user_id'];
            $name = $_REQUEST['name'];
            $feedback = $_REQUEST['feedback'];
            $token = $_REQUEST['token'];
            $Report = array();
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            if ($accessStatus['authentication'] === 1) {
                $FeedbackInsert = new TblFeedback();
                $FeedbackInsert->sender_id = $user_id;
                $FeedbackInsert->sender_name = $name;
                $FeedbackInsert->feedback_str = $feedback;
                if ($FeedbackInsert->save(false)) {
                    $Report['status'] = 1;
                    $Report['message'] = 'Feedback submitted successfully!';
                    echo json_encode($Report);
                    define('API_ACCESS_KEY', 'AIzaSyA3ACIrZoCQLyv7kGvfTgmy_0-H3UlpLlI');
                    $UserModel = User::find()->where(['super_admin' => 1])->andWhere(['!=', 'app_regid', ' '])->andWhere(['is not', 'app_regid', null])->all();
                    foreach ($UserModel as $row) {
                        $reg_id = $row->app_regid;
                        $fcmApiKey = 'AIzaSyA3ACIrZoCQLyv7kGvfTgmy_0-H3UlpLlI';
                        $registrationIds = array($reg_id);
                        // prep the bundle
                        $msg = array
                            (
                            'process_type' => 'feedback_type',
                            'Sendername' => $name,
                            'Senderid' => $user_id,
                            'feedback' => $feedback
                        );
                        $fields = array
                            (
                            'registration_ids' => $registrationIds,
                            'data' => $msg
                        );
                        $headers = array
                            (
                            'Authorization: key=' . API_ACCESS_KEY,
                            'Content-Type: application/json'
                        );
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
                        $result = curl_exec($ch);
                        curl_close($ch);
                    }
                } else {
                    $Report['status'] = 0;
                    $Report['message'] = 'Something went wrong! please try again later.';
                    echo json_encode($Report);
                    die;
                }
            } else {
                $Report['status'] = 3;
                $Report['message'] = 'Unauthorise Access';
                echo json_encode($Report);
                die;
            }
        }
    }

    public function actionUploadPolicies() {
        if (Yii::$app->request->post()) {
            $user_id = $_REQUEST['user_id'];
            $DescriptionStr = $_REQUEST['description'];
            $policy_type_id = $_REQUEST['policy_type'];
            $policy_type_name = $_REQUEST['policy_type_name'];
            $TitleStr = $_REQUEST['title'];
            $FileSize = $_REQUEST['file_size'];
            $Orignal_File1_Name = $_REQUEST['Orignal_Name'];
            $File1_Extention = $_REQUEST['ext'];
            $token = $_REQUEST['token'];
            $Report = array();
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            if ($accessStatus['authentication'] === 1) {

                $InsertPolicy = new Policies();
                if (is_uploaded_file($_FILES['Filedata']['tmp_name'])) {
                    $pic_name = $_FILES['Filedata']['name'];
                    $tmp_name = $_FILES['Filedata']['tmp_name'];
                    $fname = Yii::getAlias('@frontend/web/Upload_Files/') . $pic_name;
                    move_uploaded_file($tmp_name, $fname);
                    $LocalUrl = Yii::getAlias('@frontend/web/Upload_Files/');

                    $InsertPolicy->file_name = $pic_name;
                    $InsertPolicy->file_path = $fname;
                    $InsertPolicy->orignal_filename = $Orignal_File1_Name;
                    $InsertPolicy->ext = $File1_Extention;
                    $InsertPolicy->policy_type = $policy_type_id;
                    $InsertPolicy->file_size = $FileSize;
                    $InsertPolicy->created_by = $user_id;
                    $InsertPolicy->description = $DescriptionStr;
                    $InsertPolicy->title = $TitleStr;
                    if ($InsertPolicy->save(false)) {
                        $policy_id = $InsertPolicy->id;
                        $Report['status'] = 1;
                        $Report['message'] = 'Policy Added Successfully!!';
                        echo json_encode($Report);
                        $UserModel = User::find()->where(['!=', 'id', $user_id])->andWhere(['!=', 'app_regid', ' '])->andWhere(['is not', 'app_regid', null])->all();
                        define('API_ACCESS_KEY', 'AIzaSyA3ACIrZoCQLyv7kGvfTgmy_0-H3UlpLlI');
                        foreach ($UserModel as $row) {
                            $reg_id = $row->app_regid;
                            $fcmApiKey = 'AIzaSyA3ACIrZoCQLyv7kGvfTgmy_0-H3UlpLlI';
                            $registrationIds = array($reg_id);
                            $msg = array
                                (
                                'id' => $policy_id,
                                'Title' => $TitleStr,
                                'Description' => $DescriptionStr,
                                'FilePath' => Yii::$app->params['file_url'] . $pic_name,
                                'policy_id' => $policy_type_id,
                                'policy_name' => $policy_type_name,
                                'process_type' => 'policies_type',
                                'OrignalName' => $Orignal_File1_Name,
                                'ext' => $File1_Extention
                            );
                            $fields = array
                                (
                                'registration_ids' => $registrationIds,
                                'data' => $msg
                            );
                            $headers = array
                                (
                                'Authorization: key=' . API_ACCESS_KEY,
                                'Content-Type: application/json'
                            );
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
                            curl_setopt($ch, CURLOPT_POST, true);
                            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
                            $result = curl_exec($ch);
                            curl_close($ch);
                        }
                    } else {
                        $Report['status'] = 0;
                        $Report['message'] = 'Policy upload failed';
                        echo json_encode($Report);
                        die;
                    }
                } else {
                    $Report['status'] = 0;
                    $Report['message'] = 'Policy upload failed';
                    echo json_encode($Report);
                    die;
                }
            } else {
                $Report['status'] = 3;
                $Report['message'] = 'Unauthorise Access';
                echo json_encode($Report);
                die;
            }
        }
    }

    public function actionGetPoliciesList() {
        if (Yii::$app->request->post()) {
            $user_id = $_REQUEST['user_id'];
            $typeid = $_REQUEST['typeid'];
            $token = $_REQUEST['token'];
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            $Response = array();
            $Response['policy_list'] = array();
            if ($accessStatus['authentication'] === 1) {
                $Response['status'] = 1;
                $PolicyModel = Policies::find()->where(['flag' => 1])->andWhere(['policy_type' => $typeid])->orderBy(['id' => SORT_DESC])->all();
                foreach ($PolicyModel as $row) {
                    $Report = array();
                    $Report['id'] = $row->id;
                    $Report['file_name'] = $row->file_name;
                    $Report['orignal_filename'] = $row->orignal_filename;
                    $Report['file_path'] = Yii::$app->params['file_url'] . $row->file_name;
                    $Report['ext'] = $row->ext;
                    $Report['file_size'] = $row->file_size;
                    $Report['created_by'] = $row->created_by;
                    $Report['process_date'] = $row->process_date;
                    $Report['profile_id'] = $row->profile_id;
                    $Report['description'] = $row->description;
                    $Report['title'] = $row->title;
                    $Report['url'] = Yii::$app->params['file_url'];
                    array_push($Response['policy_list'], $Report);
                }
            } else {
                $Response['status'] = 3;
            }
            echo json_encode($Response);
            die;
        }
    }

    public function actionPolicyTypeMaster() {
        if (Yii::$app->request->post()) {
            $user_id = $_REQUEST['user_id'];
            $token = $_REQUEST['token'];
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            $Response = array();
            $Response['data'] = array();
            if ($accessStatus['authentication'] === 1) {
                $Response['status'] = 1;
                $PolicyModel = PolicyTypeMaster::find()->where(['flag' => 'ACTIVE'])->all();
                foreach ($PolicyModel as $row) {
                    $Report = array();
                    $Report['id'] = $row->id;
                    $Report['name'] = $row->name;
                    $Report['flag'] = $row->flag;
                    array_push($Response['data'], $Report);
                }
            } else {
                $Response['status'] = 3;
            }

            echo json_encode($Response);
            die;
        }
    }

    public function actionGetContactList() {
        if (Yii::$app->request->post()) {
            $user_id = $_REQUEST['user_id'];
            $token = $_REQUEST['token'];
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            $Response = array();
            $Response['my_contact_list'] = array();
            if ($accessStatus['authentication'] === 1) {
                $Response['status'] = 1;
                $Usermodel = User::find()->where(['!=', 'id', $user_id])->andWhere(['flag' => 'ACTIVE'])->orderBy('name ASC')->all();
                foreach ($Usermodel as $row) {
                    $Report = array();
                    $Report['id'] = $row->id;
                    $Report['username'] = $row->username;
                    $Report['email'] = $row->email;
                    $Report['user_type'] = $row->user_type;
                    $Report['name'] = $row->name;
                    $Report['employee_id'] = $row->employee_id;
                    $Report['image_path'] = $row->image_path;
                    $Report['image_name'] = $row->image_name;
                    $Report['mobile_no'] = $row->mobile_no;
                    array_push($Response['my_contact_list'], $Report);
                }
            } else {
                $Response['status'] = 3;
            }
            echo json_encode($Response);
            die;
        }
    }

    public function actionGetprofileType() {
        if (Yii::$app->request->post()) {
            $user_id = $_REQUEST['user_id'];
            $username = $_REQUEST['username'];
            $token = $_REQUEST['token'];
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            $Response = array();
            $Response['my_profile_list'] = array();
            if ($accessStatus['authentication'] === 1) {
                $Response['status'] = 1;
                $Usermodel = Profile::find()->where(['status' => 1])->all();
                foreach ($Usermodel as $row) {
                    $Report = array();
                    $Report['id'] = $row->id;
                    $Report['type'] = $row->type;
                    $Report["status"] = $row->status;
                    array_push($Response['my_profile_list'], $Report);
                }
            } else {
                $Response['status'] = 3;
            }
            echo json_encode($Response);
            die;
        }
    }

    public function actionGetallFavourite() {
        if (Yii::$app->request->post()) {
            $user_id = $_REQUEST['user_id'];
            $limit = $_REQUEST['limit'];
            $token = $_REQUEST['token'];
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            $Response = array();
            $Response['post_data'] = array();
            if ($accessStatus['authentication'] === 1) {
                $Response['status'] = 1;
                $IdArray = array();
//                  $sqloption = "select group_concat(post_id) as val from tbl_favourite_post where user_id = ". $user_id . " and status=1";
//
//                  $countoption = \Yii::$app->db->createCommand($sqloption)->queryOne();
//
//                    $countoption_total_id = explode(",", $countoption['val']);

                $FavIdModel = TblFavouritePost::find()->where(['user_id' => $user_id])->andWhere(['status' => 1])->all();
                // print_r($FavIdModel);die;
                foreach ($FavIdModel as $row) {
                    $Id = $row->post_id;
                    array_push($IdArray, $Id);
                }
                $PostModel = TblPost::find()->where(['in', 'id', $IdArray])->andWhere(['post_status' => 1])->orderBy(['id' => SORT_DESC])->limit($limit)->all();
                foreach ($PostModel as $pRow) {
                    $Report = array();
                    $post_id = $pRow->id;
                    $post_UserId = $pRow->post_userid;
                    $Report['id'] = $post_id;
                    $Report['post_userid'] = $post_UserId;
                    $Report['post_sendername'] = $pRow->post_sendername;
                    $Report['post_description'] = $pRow->post_description;
                    $Report['post_datetime'] = $pRow->post_datetime;
                    $attachment = $pRow->attach_status;
                    $Report['attach_status'] = $attachment;
                    $Report['attachment_list'] = array();
                    if ($attachment != 0) {
                        $AttachModel = TblPostAttachments::find()->where(['post_id' => $post_id])->andWhere(['attach_status' => 1])->all();
                        foreach ($AttachModel as $aRow) {
                            $AttachReport = array();
                            $AttachReport['id'] = $aRow->id;
                            $AttachReport['post_id'] = $aRow->post_id;
                            $AttachReport['attachment_name'] = $aRow->attachment_name;
                            $AttachReport['orignal_name'] = $aRow->orignal_name;
                            $AttachReport['attachment_type'] = $aRow->attachment_type;
                            $AttachReport['attach_status'] = $aRow->attach_status;
                            $AttachReport['attach_path'] = $aRow->attach_path;
                            $AttachReport['attach_date'] = $aRow->attach_date;
                            $AttachReport['thumb_path'] = Yii::$app->params['thumb_url'];
                            $AttachReport['thumb_name'] = $aRow->thumb_name;
                            array_push($Report['attachment_list'], $AttachReport);
                        }
                    }
                    $likeCount = TblPostLike::find()->where(['post_id' => $post_id])->andWhere(['like_status' => 1])->count();
                    $CommentCount = TblPostComment::find()->where(['post_id' => $post_id])->andWhere(['comment_status' => 1])->count();
                    $LikedByYou = TblPostLike::find()->where(['post_id' => $post_id])->andWhere(['like_userid' => $user_id])->andWhere(['like_status' => 1])->count();
                    $Report['like_status'] = $LikedByYou;
                    if ($LikedByYou == 1) {
                        $lky = $likeCount - 1;
                        if($lky>0){
                            $Report['total_like'] = 'You and ' . $lky . ' other';

                        }else{
                            $Report['total_like'] = 'You';
                        }
                        //$Report['total_like'] = 'You and ' . $lky . ' other';
                    } else {
                        $Report['total_like'] = $likeCount;
                    }
                    $Report['total_comment'] = $CommentCount;
                    $SenderImage = User::find()->where(['id' => $post_UserId])->one();
                    $Report['sender_image'] = $SenderImage->image_path . $SenderImage->image_name;

                    if ($CommentCount > 0) {
                        $Report['Comment_Status'] = '1';
                        $LastComment = TblPostComment::find()->where(['post_id' => $post_id])->andWhere(['comment_status' => 1])->orderBy(['id' => SORT_DESC])->limit(1)->one();
                        $Report['comment_username'] = $LastComment->comment_username;
                        $CommentUserId = $LastComment->comment_userid;
                        $Report['comment_userid'] = $CommentUserId;
                        $Report['comment_text'] = $LastComment->comment_text;
                        $Report['comment_date'] = $LastComment->comment_date;
                        $CommentImage = User::find()->where(['id' => $CommentUserId])->one();
                        $Report['comment_sender_image'] = $CommentImage->image_path . $CommentImage->image_name;
                    } else {
                        $Report['Comment_Status'] = '0';
                        $Report['comment_username'] = '';
                        $Report['comment_userid'] = '';
                        $Report['comment_text'] = '';
                        $Report['comment_date'] = '';
                        $Report['comment_sender_image'] = '';
                    }
                    $favQuery = TblFavouritePost::find()->where(['post_id' => $post_id])->andWhere(['user_id' => $user_id])->andWhere(['status' => 1])->count();
                    if ($favQuery > 0) {
                        $Report['favourite_status'] = '1';
                    } else {
                        $Report['favourite_status'] = '0';
                    }
                    array_push($Response['post_data'], $Report);
                }

                $TotalPostQuery = TblFavouritePost::find()->where(['user_id' => $user_id])->andWhere(['status' => 1])->count();
                $Response['total_post'] = $TotalPostQuery;
            } else {
                $Response['status'] = 3;
            }
            echo json_encode($Response);
            die;
        }
    }

    public function actionCreateNewPost() {

        if (Yii::$app->request->post()) {

            $username = $_REQUEST['username'];
            $user_id = $_REQUEST['user_id'];
            $post_str = $_REQUEST['post_str'];
            $attach_status = $_REQUEST['attach_status'];
            $token = $_REQUEST['token'];
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            $Report = array();
            if ($accessStatus['authentication'] === 1) {
                $CreateQuery = new TblPost();
                $CreateQuery->post_userid = $user_id;
                $CreateQuery->post_sendername = $username;
                $CreateQuery->post_description = $post_str;
                $CreateQuery->attach_status = $attach_status;
                $CreateQuery->post_status = 0;

                if ($CreateQuery->save(false)) {
                    $Post_id = $CreateQuery->id;
                    $Report['post_id'] = $Post_id;
                    $Report['success'] = 1;
                } else {
                    $Report['success'] = 0;
                }
            } else {

                $Report['success'] = 3;
            }
            echo json_encode($Report);
            die();
        }
    }

    public function actionGenerateNewpostNotification() {
        if (Yii::$app->request->post()) {
            $post_id = $_REQUEST['post_id'];

            $PostModel = TblPost::find()->where(['id' => $post_id])->one();
            $PostModel->post_status = 1;
            $PostModel->save(false);
            Yii::$app->samparq->getSendPostNotification($PostModel->id);
            Yii::$app->samparq->createPostLog($PostModel->post_sendername,$PostModel->post_userid, $PostModel->id,false, 'Added new post');

            $postDetailModel = TblPost::find()->where(['id' => $post_id])->one();
            $SenderName = $postDetailModel->post_sendername;
            $Post_UserId = $postDetailModel->post_userid;

            $UserModel = User::find()->where(['!=', 'id', $Post_UserId])->andWhere(['!=', 'app_regid', ' '])->andWhere(['is not', 'app_regid', null])->all();
            define('API_ACCESS_KEY', 'AIzaSyA3ACIrZoCQLyv7kGvfTgmy_0-H3UlpLlI');
            foreach ($UserModel as $row) {
                $reg_id = $row->app_regid;
                $fcmApiKey = 'AIzaSyA3ACIrZoCQLyv7kGvfTgmy_0-H3UlpLlI';
                $registrationIds = array($reg_id);
                $msg = array
                    (
                    'process_type' => 'post_type',
                    'Sendername' => $SenderName
                );
                $fields = array
                    (
                    'registration_ids' => $registrationIds,
                    'data' => $msg
                );
                $headers = array
                    (
                    'Authorization: key=' . API_ACCESS_KEY,
                    'Content-Type: application/json'
                );
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
                $result = curl_exec($ch);
                curl_close($ch);
                echo $result;
            }
        }
    }

    public function actionTestTemplate(){
        $email = $_GET['email'];
        Yii::$app->mailer->compose('promotionTemplate')
            ->setFrom('samparq@qdegrees.com')
            ->setTo($email)
            ->setSubject('Samparq - Connecting Workforce')
            ->send();
    }

    public function actionPromoteSamparq(){
        $userModel = User::find()->all();

        $getFile = file_get_contents(Yii::$app->params['file_url'].'email.txt');
        $allEmail = explode(',', $getFile);
        foreach ($userModel as $uModel){
            $registEmails[] = $uModel->email;
        }
        $unRegEmail  = array_diff($allEmail, $registEmails);

        foreach ($unRegEmail as $email){
            Yii::$app->mailer->compose('promotionTemplate')
                ->setFrom('samparq@qdegrees.com')
                ->setTo($email)
                ->setSubject('Samparq - Connecting Workforce')
                ->send();
        }
    }

    public function actionGetuserProfile() {
        if (Yii::$app->request->post()) {
            $user_id = $_REQUEST['user_id'];
            $token = $_REQUEST['token'];
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            $Response = array();
            $Response['data'] = array();
            if ($accessStatus['authentication'] === 1) {
                $Response['success'] = 1;
              $UserModel = User::find()->where(['id' => $user_id])->one();
//                $Report = array();
                $UserType=$UserModel->user_type;
                $ProfileModel=  Profile::find()->where(['id'=>$UserType])->one();
                $Report = [];
                $Report['type']=$ProfileModel->type;
                $Report['image'] = $UserModel->image_path . $UserModel->image_name;
                $Report['name'] = $UserModel->name;
                $Report['email'] = $UserModel->email;
                $Report['mobile'] = $UserModel->mobile_no;
                $Report['birthday'] = $UserModel->dob;
                $Report['emp_id'] = $UserModel->employee_id;
                array_push($Response['data'], $Report);
            } else {
                $Response['success'] = 3;
            }
            echo json_encode($Response);
            die;
        }
    }

    public function actionRemovePostComment() {
        if (Yii::$app->request->post()) {

            $PostId = $_REQUEST['post_id'];
            $UserID = $_REQUEST['user_id'];
            $CommentId = $_REQUEST['comment_id'];
            $token = $_REQUEST['token'];
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            $Report = array();
            if ($accessStatus['authentication'] === 1) {
                $RemoveComment = TblPostComment::find()->where(['post_id' => $PostId])->andWhere(['id' => $CommentId])->one();
                $RemoveComment->comment_status = 0;
                $RemoveComment->remove_id = $UserID;
                if ($RemoveComment->save()) {

                    $Report['success'] = 1;
                } else {

                    $Report['success'] = 0;
                }
            } else {
                $Report['success'] = 3;
            }
            echo json_encode($Report);
            die();
        }
    }

    public function actionAddNewPostComment() {
        if (Yii::$app->request->post()) {

            $SenderId = $_REQUEST['sender_id'];
            $SenderName = $_REQUEST['sender_name'];
            $CommentText = $_REQUEST['comment_text'];
            $PostId = $_REQUEST['post_id'];
            $token = $_REQUEST['token'];
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            $Report = array();
            if ($accessStatus['authentication'] === 1) {
                $CommentInsertQuery = new TblPostComment();
                $CommentInsertQuery->post_id = $PostId;
                $CommentInsertQuery->comment_userid = $SenderId;
                $CommentInsertQuery->comment_username = $SenderName;
                $CommentInsertQuery->comment_text = $CommentText;
                $CommentInsertQuery->comment_status = 1;

                if ($CommentInsertQuery->save()) {
                    Yii::$app->samparq->createPostLog($CommentInsertQuery->comment_username,$CommentInsertQuery->comment_userid,$CommentInsertQuery->post_id, $CommentInsertQuery->id, 'Commented on post');
                    $Comment_id = $CommentInsertQuery->id;
                    $Report['comment_id'] = $Comment_id;
                    $Report['success'] = 1;
                } else {
                    $Report['success'] = 0;
                }
            } else {
                $Report['success'] = 3;
            }
            echo json_encode($Report);
        }
    }

    public function actionGetpostCommentlist() {
        if (Yii::$app->request->post()) {
            $post_id = $_REQUEST['post_id'];
            $user_id = $_REQUEST['user_id'];
            $limit = $_REQUEST['limit'];
            $token = $_REQUEST['token'];
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            $Response = array();
            $Response['comment_data'] = array();
            if ($accessStatus['authentication'] === 1) {
                $Response['status'] = 1;
                $CommentModel = TblPostComment::find()->where(['post_id' => $post_id])->andWhere(['comment_status' => 1])->limit($limit)->orderBy(['id' => SORT_DESC])->all();
                foreach ($CommentModel as $row) {
                    $Report = array();
                    $SenderId = $row->comment_userid;
                    $UserModel = User::find()->where(['id' => $SenderId])->one();
                    $Report['comment_sender_image'] = $UserModel->image_path . $UserModel->image_name;
                    $Report['sender_id'] = $SenderId;
                    $Report['comment_username'] = $row->comment_username;
                    $Report['comment_text'] = $row->comment_text;
                    $Report['comment_date'] = $row->comment_date;
                    $Report['id'] = $row->id;
                    array_push($Response['comment_data'], $Report);
                }
                $TotalComment = TblPostComment::find()->where(['post_id' => $post_id])->andWhere(['comment_status' => 1])->count();
                $Response['total_comment'] = $TotalComment;
            } else {
                $Response['status'] = 3;
            }
            echo json_encode($Response);
            die;
        }
    }

    public function actionRemoveAttachmentComment() {
        if (Yii::$app->request->post()) {
            $PostId = $_REQUEST['post_id'];
            $UserID = $_REQUEST['user_id'];
            $CommentId = $_REQUEST['comment_id'];
            $Attach_Id = $_REQUEST['attach_id'];
            $token = $_REQUEST['token'];
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            if ($accessStatus['authentication'] === 1) {
                $RemoveComment = TblAttachComment::find()->where(['post_id' => $PostId])->andWhere(['id' => $CommentId])->andWhere(['attach_id' => $Attach_Id])->one();
                $RemoveComment->comment_status = 0;
                $RemoveComment->remove_id = $UserID;
                if ($RemoveComment->save(FALSE)) {
                    $Report['success'] = 1;
                } else {
                    $Report['success'] = 0;
                }
            } else {
                $Report['success'] = 3;
            }
            echo json_encode($Report);
        }
    }

    public function actionAddNewAttachmentComment() {
        if (Yii::$app->request->post()) {

            $SenderId = $_REQUEST['sender_id'];
            $SenderName = $_REQUEST['sender_name'];
            $CommentText = $_REQUEST['comment_text'];
            $PostId = $_REQUEST['post_id'];
            $Attach_id = $_REQUEST['attach_id'];
            $token = $_REQUEST['token'];
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            $Report = array();
            if ($accessStatus['authentication'] === 1) {
                $CommentInsertQuery = new TblAttachComment();
                $CommentInsertQuery->attach_id = $Attach_id;
                $CommentInsertQuery->post_id = $PostId;
                $CommentInsertQuery->comment_userid = $SenderId;
                $CommentInsertQuery->comment_username = $SenderName;
                $CommentInsertQuery->comment_text = $CommentText;
                $CommentInsertQuery->comment_status = 1;
                if ($CommentInsertQuery->save(false)) {
                    Yii::$app->samparq->createPostLog($CommentInsertQuery->comment_username,$CommentInsertQuery->comment_userid,$CommentInsertQuery->post_id, $CommentInsertQuery->id,'commented on post attachment');
                    $Comment_id = $CommentInsertQuery->id;
                    $Report['comment_id'] = $Comment_id;
                    $Report['success'] = 1;
                } else {
                    $Report['success'] = 0;
                }
            } else {
                $Report['success'] = 3;
            }
            echo json_encode($Report);
            die();
        }
    }

    public function actionGetattachPostCommentlist() {
        if (Yii::$app->request->post()) {
            $post_id = $_REQUEST['post_id'];
            $user_id = $_REQUEST['user_id'];
            $attach_id = $_REQUEST['attach_id'];
            $limit = $_REQUEST['limit'];
            $token = $_REQUEST['token'];
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            $Response = array();
            $Response['comment_data'] = array();
            if ($accessStatus['authentication'] === 1) {
                $Response['status'] = 1;
                $CommentModel = TblAttachComment::find()->where(['attach_id' => $attach_id])->andWhere(['post_id' => $post_id])->andWhere(['comment_status' => 1])->limit($limit)->orderBy(['id' => SORT_DESC])->all();
                foreach ($CommentModel as $row) {
                    $Report = array();
                    $SenderId = $row->comment_userid;
                    $UserModel = User::find()->where(['id' => $SenderId])->one();
                    $Report['comment_sender_image'] = $UserModel->image_path . $UserModel->image_name;
                    $Report['sender_id'] = $SenderId;
                    $Report['comment_username'] = $row->comment_username;
                    $Report['comment_text'] = $row->comment_text;
                    $Report['comment_date'] = $row->comment_date;
                    $Report['id'] = $row->id;
                    array_push($Response['comment_data'], $Report);
                }
                $TotalComment = TblAttachComment::find()->where(['attach_id' => $attach_id])->andWhere(['post_id' => $post_id])->andWhere(['comment_status' => 1])->count();
                $Response['total_comment'] = $TotalComment;
            } else {
                $Response['status'] = 3;
            }
            echo json_encode($Response);
            die;
        }
    }

    public function actionOnAttachmentLike() {
        if (Yii::$app->request->post()) {
            $UserId = $_REQUEST['user_id'];
            $Username = $_REQUEST['user_name'];
            $PostId = $_REQUEST['post_id'];
            $Attachment_Id = $_REQUEST['attach_id'];
            $LikeStatus = $_REQUEST['like_status'];
            $token = $_REQUEST['token'];
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            $Report = array();
            if ($accessStatus['authentication'] === 1) {
                $StatusQuery = TblAttachLike::find()->where(['attach_id' => $Attachment_Id])->andWhere(['post_id' => $PostId])->andWhere(['like_userid' => $UserId])->one();
                $StatusCount = count($StatusQuery);

                if ($StatusCount > 0) {
                    $UpdateLikeQuery = TblAttachLike::find()->where(['attach_id' => $Attachment_Id])->andWhere(['post_id' => $PostId])->andWhere(['like_userid' => $UserId])->one();
                    $UpdateLikeQuery->like_status = $LikeStatus;
                    if ($UpdateLikeQuery->save(FALSE)) {
                        $Like_Count = TblAttachLike::find()->where(['attach_id' => $Attachment_Id])->andWhere(['post_id' => $PostId])->andWhere(['like_status' => 1])->one();

                        $TotalLike = count($Like_Count);

                        $likedByYou = TblAttachLike::find()->where(['attach_id' => $Attachment_Id])->andWhere(['post_id' => $PostId])->andWhere(['like_status' => 1])->andWhere(['like_userid' => $UserId])->one();
                        $Like_Status = count($likedByYou);
                        $Report['like_status'] = $Like_Status;
                        if ($Like_Status == 1) {
                            $lky = $TotalLike - 1;
                            if($lky>0){
                                $Report['total_like'] = 'You and ' . $lky . ' other';

                            }else{
                                $Report['total_like'] = 'You';
                            }
                            //$Report['total_like'] = 'You and ' . $lky . ' other';
                        } else {
                            $Report['total_like'] = $TotalLike;
                        }
                        $Report['success'] = 1;
                        //echo json_encode($Report);die();
                    } else {
                        $Report = array();
                        $Report['success'] = 0;
                        // echo json_encode($Report);die();
                    }
                } else {
                    $InserLikeStatus = new TblAttachLike();
                    $InserLikeStatus->attach_id = $Attachment_Id;
                    $InserLikeStatus->post_id = $PostId;
                    $InserLikeStatus->like_userid = $UserId;
                    $InserLikeStatus->like_username = $Username;
                    $InserLikeStatus->like_status = $LikeStatus;
                    if ($InserLikeStatus->save()) {

                        $Like_Count = TblAttachLike::find()->where(['attach_id' => $Attachment_Id])->andWhere(['post_id' => $PostId])->andWhere(['like_status' => 1])->one();
                        $TotalLike = count($Like_Count);
                        $likedByYou = TblAttachLike::find()->where(['attach_id' => $Attachment_Id])->andWhere(['post_id' => $PostId])->andWhere(['like_status' => 1])->andWhere(['like_userid' => $UserId])->one();
                        $Like_Status = count($likedByYou);
                        $Report['like_status'] = $Like_Status;
                        if ($Like_Status == 1) {
                            $lky = $TotalLike - 1;
                            if($lky>0){
                                $Report['total_like'] = 'You and ' . $lky . ' other';

                            }else{
                                $Report['total_like'] = 'You';
                            }
                            //$Report['total_like'] = 'You and ' . $lky . ' other';
                        } else {
                            $Report['total_like'] = $TotalLike;
                        }
                        $Report['success'] = 1;
                        //echo json_encode($Report);die();
                    } else {
                        $Report['success'] = 0;
                        //echo json_encode($Report);die();
                    }
                }
            } else {
                $Report['success'] = 3;
            }
            echo json_encode($Report);
            die();
        }
    }

    public function actionRemoveAttachment() {
        if (Yii::$app->request->post()) {
            $post_id = $_REQUEST['post_id'];
            $attach_id = $_REQUEST['attach_id'];
            $user_id = $_REQUEST['user_id'];
            $token = $_REQUEST['token'];
            $Report = array();
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            if ($accessStatus['authentication'] === 1) {

                $attachModel = TblPostAttachments::find()->where(['post_id' => $post_id])->andWhere(['id' => $attach_id])->one();
                $attachModel->attach_status = 0;
                $attachModel->remove_id = $user_id;
                if ($attachModel->save(false)) {
                    $Report['status'] = 1;
                } else {
                    $Report['status'] = 0;
                }
            } else {
                $Report['status'] = 3;
            }
            echo json_encode($Report);
            die();
        }
    }

    public function actionGetallAttachmentlist() {
        if (Yii::$app->request->post()) {
            $user_id = $_REQUEST['user_id'];
            $post_id = $_REQUEST['post_id'];
            $token = $_REQUEST['token'];
            $Response = array();
            $Response['attach_list'] = array();
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            if ($accessStatus['authentication'] === 1) {
                $Response['status'] = 1;
                $attachModel = TblPostAttachments::find()->where(['post_id' => $post_id])->andWhere(['attach_status' => 1])->all();
                foreach ($attachModel as $row) {
                    $Report = array();
                    $attachId = $row->id;
                    $Report['id'] = $attachId;
                    $Report['attachment_name'] = $row->attachment_name;
                    $Report['orignal_name'] = $row->orignal_name;
                    $Report['attachment_type'] = $row->attachment_type;
                    $Report['attach_path'] = $row->attach_path;
                    $Report['attach_date'] = $row->attach_date;
                    $Report['thumb_path'] = Yii::$app->params['thumb_url'];
                    $Report['thumb_name'] = $row->thumb_name;

                    $likeCount = TblAttachLike::find()->where(['attach_id' => $attachId])->andWhere(['post_id' => $post_id])->andWhere(['like_status' => 1])->count();
                    $CommentCount = TblAttachComment::find()->where(['attach_id' => $attachId])->andWhere(['post_id' => $post_id])->andWhere(['comment_status' => 1])->count();
                    $likeByYou = TblAttachLike::find()->where(['attach_id' => $attachId])->andWhere(['post_id' => $post_id])->andWhere(['like_status' => 1])->andWhere(['like_userid' => $user_id])->count();

                    $Report['like_status'] = $likeByYou;

                    if ($likeByYou == 1) {
                        $lky = $likeCount - 1;
                        if($lky>0){
                            $Report['total_like'] = 'You and ' . $lky . ' other';

                        }else{
                            $Report['total_like'] = 'You';
                        }
                        //$Report['total_like'] = 'You and ' . $lky . ' other';
                    } else {
                        $Report['total_like'] = $likeCount;
                    }
                    $Report['total_comment'] = $CommentCount;
                    if ($CommentCount > 0) {
                        $Report['Comment_Status'] = '1';

                        $LastComment = TblAttachComment::find()->where(['attach_id' => $attachId])->andWhere(['post_id' => $post_id])->andWhere(['comment_status' => 1])->limit(1)->orderBy(['id' => SORT_DESC])->one();

                        $Report['comment_username'] = $LastComment->comment_username;
                        $CommentUserId = $LastComment->comment_userid;
                        $Report['comment_userid'] = $CommentUserId;
                        $Report['comment_text'] = $LastComment->comment_text;
                        $Report['comment_date'] = $LastComment->comment_date;
                        $CommentImagemodel = User::find()->where(['id' => $CommentUserId])->one();
                        $Report['comment_sender_image'] = $CommentImagemodel->image_path . $CommentImagemodel->image_name;
                    } else {
                        $Report['Comment_Status'] = '0';
                        $Report['comment_username'] = '';
                        $Report['comment_userid'] = '';
                        $Report['comment_text'] = '';
                        $Report['comment_date'] = '';
                        $Report['comment_sender_image'] = '';
                    }
                    array_push($Response['attach_list'], $Report);
                }
            } else {
                $Response['status'] = 3;
            }
            echo json_encode($Response);
            die;
        }
    }

    public function actionRemovePost() {
        if (Yii::$app->request->post()) {
            $Post_ID = $_POST['post_id'];
            $User_Id = $_POST['user_id'];
            $token = $_REQUEST['token'];
            $Report = array();
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            if ($accessStatus['authentication'] === 1) {
                $RemovePost = TblPost::find()->where(['id' => $Post_ID])->one();
                $RemovePost->post_status = 0;
                $RemovePost->remove_id = $User_Id;
                if ($RemovePost->save()) {
                    $Report['success'] = 1;
                    //echo json_encode($Report);die();
                } else {
                    $Report['success'] = 0;
                    //echo json_encode($Report);die();
                }
            } else {
                $Report['success'] = 3;
            }
            echo json_encode($Report);
            die();
        }
    }

    public function actionAddFavourite() {
        if (Yii::$app->request->post()) {
            $user_id = $_REQUEST['user_id'];
            $post_id = $_REQUEST['post_id'];
            $Status = $_REQUEST['status'];
            $token = $_REQUEST['token'];
            $Report = array();

            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            if ($accessStatus['authentication'] === 1) {
                $SelectQuery = TblFavouritePost::find()->where(['post_id' => $post_id])->andWhere(['user_id' => $user_id])->one();
                $Count = count($SelectQuery);
                if ($Count > 0) {

                    $UpdateStatus = TblFavouritePost::find()->where(['post_id' => $post_id])->andWhere(['user_id' => $user_id])->one();
                    $SelectQuery->status = $Status;
                    if ($SelectQuery->save()) {
                        $Report['success'] = 1;
                    } else {

                        $Report['success'] = 0;
                    }
                } else {
                    $InsertStatus = new TblFavouritePost();
                    $InsertStatus->post_id = $post_id;
                    $InsertStatus->user_id = $user_id;
                    $InsertStatus->status = $Status;
                    if ($InsertStatus->save()) {
                        $Report['success'] = 1;
                    } else {
                        $Report['success'] = 0;
                    }
                }
            } else {
                $Report['success'] = 3;
            }
            echo json_encode($Report);
            die();
        }
    }

    public function actionGetallTrending() {
        if (Yii::$app->request->post()) {
            $user_id = $_REQUEST['user_id'];
            $limit = $_REQUEST['limit'];
            $token = $_REQUEST['token'];
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);

            if ($accessStatus['authentication'] === 1) {

                $Response = array();
                $Response['post_data'] = array();
                $model = TblPost::find()->where(['post_status' => 1])->limit($limit)->orderBy(['id' => SORT_DESC])->all();

                foreach ($model as $row) {
                    $Report = array();

                    $post_id = $row->id;
                    $postUser_id = $row->post_userid;
                    $Report['id'] = $post_id;
                    $Report['post_userid'] = $postUser_id;
                    $Report['post_sendername'] = $row->post_sendername;
                    $Report['post_description'] = $row->post_description;
                    $Report['post_datetime'] = $row->post_datetime;
                    $attachStatus = $row->attach_status;
                    $Report['attach_status'] = $attachStatus;
                    $Report['attachment_list'] = array();

                    if ($attachStatus == 1) {
                        $attachModel = TblPostAttachments::find()->where(['post_id' => $post_id])->andWhere(['attach_status' => 1])->all();
                        foreach ($attachModel as $attachrow) {
                            $AttachReport = array();
                            $AttachReport['id'] = $attachrow->id;
                            $AttachReport['post_id'] = $attachrow->post_id;
                            $AttachReport['attachment_name'] = $attachrow->attachment_name;
                            $AttachReport['orignal_name'] = $attachrow->orignal_name;
                            $AttachReport['attachment_type'] = $attachrow->attachment_type;
                            $AttachReport['attach_status'] = $attachrow->attach_status;
                            $AttachReport['attach_path'] = $attachrow->attach_path;
                            $AttachReport['attach_date'] = $attachrow->attach_date;
                            $AttachReport['thumb_path'] = Yii::$app->params['thumb_url'];
                            $AttachReport['thumb_name'] = $attachrow->thumb_name;
                            array_push($Report['attachment_list'], $AttachReport);
                        }
                    }



                    $likeCount = TblPostLike::find()->where(['post_id' => $post_id])->andWhere(['like_status' => 1])->count();
                    $commentCount = TblPostComment::find()->where(['post_id' => $post_id])->andWhere(['comment_status' => 1])->count();



                    $likedByYou = TblPostLike::find()->where(['post_id' => $post_id])->andWhere(['like_status' => 1])->andWhere(['like_userid' => $user_id])->count();
                    $Report['like_status'] = $likedByYou;

                    if ($likedByYou == 1) {
                        $lky = $likeCount - 1;
                        if($lky>0){
                            $Report['total_like'] = 'You and ' . $lky . ' other';

                        }else{
                            $Report['total_like'] = 'You';
                        }
                        //$Report['total_like'] = 'You and ' . $lky . ' other';
                    } else {

                        $Report['total_like'] = $likeCount;
                    }
                    $Report['total_comment'] = $commentCount;

                    /*                     * *****************Sender Profile*********************** */
                    $senderProfile = User::find()->where(['id' => $postUser_id])->one();

                    $Report['sender_image'] = $senderProfile->image_path . $senderProfile->image_name;

                    /*                     * *********************Last Comment********************** */
                    if ($commentCount > 0) {
                        $Report['Comment_Status'] = '1';
                        $LastComment = TblPostComment::find()->where(['post_id' => $post_id])->andWhere(['comment_status' => 1])->limit(1)->orderBy(['id' => SORT_DESC])->one();
                        $Report['comment_username'] = $LastComment->comment_username;
                        $CommentUserId = $LastComment->comment_userid;
                        $Report['comment_userid'] = $CommentUserId;
                        $Report['comment_text'] = $LastComment->comment_text;
                        $Report['comment_date'] = $LastComment->comment_date;
                        $commentProfile = User::find()->where(['id' => $CommentUserId])->one();
                        $Report['comment_sender_image'] = $commentProfile->image_path . $commentProfile->image_name;
                    } else {
                        $Report['Comment_Status'] = '0';
                        $Report['comment_username'] = '';
                        $Report['comment_userid'] = '';
                        $Report['comment_text'] = '';
                        $Report['comment_date'] = '';
                        $Report['comment_sender_image'] = '';
                    }
                    /*                     * *********************Favourite Status******************************************************* */
                    $FavModel = TblFavouritePost::find()->where(['post_id' => $post_id])->andWhere(['user_id' => $user_id])->andWhere(['status' => 1])->count();
                    if ($FavModel > 0) {
                        $Report['favourite_status'] = '1';
                    } else {
                        $Report['favourite_status'] = '0';
                    }
                    array_push($Response['post_data'], $Report);
                }

                $postModel = TblPost::find()->where(['post_status' => 1])->count();
                $Response['total_post'] = $postModel;
                $Response['status'] = 1;

            } else {
                $Response = [
                    'status' => 3,
                    'message' => 'Unauthorise access',
                    'post_data' => []
                ];
            }
            echo json_encode($Response);
            die;
        }
    }

    public function actionPostLike() {

        if (Yii::$app->request->post()) {

            $UserId = $_REQUEST['user_id'];
            $Username = $_REQUEST['user_name'];
            $PostId = $_REQUEST['post_id'];
            $LikeStatus = $_REQUEST['like_status'];
            $token = $_REQUEST['token'];
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            if ($accessStatus['authentication'] === 1) {
                $StatusQuery = TblPostLike::find()->where(['post_id' => $PostId, 'like_userid' => $UserId])->one();
                $StatusCount = count($StatusQuery);

                if ($StatusCount > 0) {

                    $UpdateLikeQuery = TblPostLike::find()->where(['post_id' => $PostId, 'like_userid' => $UserId])->one();
                    $UpdateLikeQuery->like_status = $LikeStatus;
                    if ($UpdateLikeQuery->save(FALSE)) {

                        $Report = array();
                        $Like_Count = TblPostLike::find()->where(['post_id' => $PostId])->andWhere(['!=', 'like_status', 0])->all();
                        $TotalLike = count($Like_Count);
                        $likedByYou = TblPostLike::find()->where(['post_id' => $PostId])->andWhere(['!=', 'like_status', 0])->andWhere(['like_userid' => $UserId])->one();
                        $Like_Status = count($likedByYou);
                        $Report['like_status'] = $Like_Status;
                        if ($Like_Status == 1) {

                            $lky = $TotalLike - 1;
                            if($lky>0){
                                $Report['total_like'] = 'You and ' . $lky . ' other';

                            }else{
                                $Report['total_like'] = 'You';
                            }
                            //$Report['total_like'] = 'You and ' . $lky . ' other';
                        } else {
                            $Report['total_like'] = $TotalLike;
                        }
                        $Report['success'] = 1;
                        echo json_encode($Report);
                        die();
                    } else {
                        $Report = array();
                        $Report['success'] = 0;
                        echo json_encode($Report);
                        die();
                    }
                } else {
                    $InserLikeStatus = new TblPostLike();
                    $InserLikeStatus->post_id = $_REQUEST['post_id'];
                    $InserLikeStatus->like_userid = $_REQUEST['user_id'];
                    $InserLikeStatus->like_username = $_REQUEST['user_name'];
                    $InserLikeStatus->like_status = $_REQUEST['like_status'];
                    if ($InserLikeStatus->save()) {

                        $Report = array();
                        $Like_Count = TblPostLike::find()->where(['post_id' => $PostId])->andWhere(['!=', 'like_status', 0])->all();
                        $TotalLike = count($Like_Count);
                        $likedByYou = TblPostLike::find()->where(['post_id' => $PostId])->andWhere(['!=', 'like_status', 0])->andWhere(['like_userid' => $UserId])->one();
                        $Like_Status = count($likedByYou);
                        $Report['like_status'] = $Like_Status;
                        if ($Like_Status == 1) {
                            $lky = $TotalLike - 1;
                            if($lky>0){
                                $Report['total_like'] = 'You and ' . $lky . ' other';

                            }else{
                                $Report['total_like'] = 'You';
                            }
                            //$Report['total_like'] = 'You and ' . $lky . ' other';
                        } else {
                            $Report['total_like'] = $TotalLike;
                        }
                        $Report['success'] = 1;
                        echo json_encode($Report);
                        die();
                    } else {

                        $Report = array();
                        $Report['success'] = 0;
                        echo json_encode($Report);
                        die();
                    }
                }
            } else {
                $Report = array();
                $Report['success'] = 3;
                echo json_encode($Report);
                die;
            }
        }
    }

    public function actionLogoutUser() {
        if (Yii::$app->request->post()) {
            $user_id = $_REQUEST['user_id'];
            $logoutModel = User::findOne(['id' => $user_id]);
            $logoutModel->app_regid = '';
            if ($logoutModel->save(false)) {
                date_default_timezone_set('Asia/Calcutta');
                Yii::$app->samparq->createLoginLog($logoutModel->username,$logoutModel->id,false,date('Y-m-d H:i:s'));
                $report = [
                    'status' => 1,
                    'message' => 'Successfully logout'
                ];
                echo json_encode($report);
                die;
            } else {
                $report = [
                    'status' => 0,
                    'message' => 'Error! please try again later.'
                ];
                echo json_encode($report);
                die;
            }
            }
        }


    public function actionSignupSecondform() {
        if (Yii::$app->request->post()) {
            $id = $_REQUEST['user_id'];
            $name = $_REQUEST['name_str'];
            $MobileNo = $_REQUEST['mobile_str'];
            $DepartmentId = $_REQUEST['department_id'];

            $model = User::findOne(['id' => $id]);
            $model->name = $name;
            $model->mobile_no = $MobileNo;
            $model->form_submit = 1;
            $model->user_type = $DepartmentId;
            if ($model->save(false)) {
                Yii::$app->mailer->compose('thanksTemplate', ['username' => $model->name])
                    ->setFrom('samparq@qdegrees.com')
                    ->setTo($model->email)
                    ->setSubject('Welcome to Samparq - Connecting Qdegrees')
                    ->send();

                $this->actionSendConfirmationMail(Yii::$app->samparq->getAccActAlertsReceiver(), $model);

                $response = [
                    'success' => 1,
                    'message' => 'Form submitted successfully.'
                ];
                echo json_encode($response);
                die;
            } else {
                $response = [
                    'success' => 0,
                    'message' => 'Error!! Please try again later'
                ];
                echo json_encode($response);
                die;
            }
        }
    }


    public function actionConfirmUser(){
        $uid = Yii::$app->utility->decryptUserData($_REQUEST['kjlfdsajkydf']);
        $department = Yii::$app->utility->decryptUserData($_REQUEST['dpttt']);
        $model = [];
        $alreadyActivated = "false";

        if(is_numeric($uid)){
            $model = User::findOne(['id' => $uid]);
            if($model->flag != "ACTIVE"){
                $model->user_type = Yii::$app->samparq->getDepartmentId($department);
                $model->flag = "ACTIVE";
                if($model->save(false)){
                    Yii::$app->mailer->compose('thanksTemplate', ['username' => $model->name,'email' => $model->email, 'dob' => $model->dob, 'department' => Yii::$app->samparq->getDepartmentName($model->user_type), 'uid' => Yii::$app->utility->encryptUserData($model->id), 'activation' => 'true'])
                        ->setFrom('samparq@qdegrees.com')
                        ->setTo($model->email)
                        ->setSubject('Account Confirmation')
                        ->send();
                }
            } else {
                $alreadyActivated = "true";
            }

        }

        return $this->render('confirmation-view', [
            'model' => $model,
            'activated' => $alreadyActivated
        ]);
    }

    public function actionSendConfirmationMail($receipients, $model){
        foreach ($receipients as $rec){
            Yii::$app->mailer->compose('pendingTemplate', ['username' => $model->name,'email' => $model->email, 'dob' => $model->dob, 'department' => Yii::$app->samparq->getDepartmentName($model->user_type), 'uid' => Yii::$app->utility->encryptUserData($model->id)])
                ->setFrom('samparq@qdegrees.com')
                ->setTo($rec)
                ->setSubject('New user request')
                ->send();
        }
    }

    public function actionGetdepartmentType() {
        if (Yii::$app->request->post()) {
            $user_id = $_REQUEST['user_id'];
            $username = $_REQUEST['username'];
            $model = Profile::findAll(['status' => 1]);
            $Response = array();
            $Response['my_profile_list'] = array();
            foreach ($model as $row) {
                $report = [
                    'id' => $row->id,
                    'type' => $row->type,
                    'status' => $row->status
                ];
                array_push($Response['my_profile_list'], $report);
            }
            echo json_encode($Response);
            die;
        }
    }

    public function actionAppChangePassword() {
        if (Yii::$app->request->post()) {
            $token = $_POST['token'];
            $newPassword = $_POST['newpassword'];
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            $response=array();
            if ($accessStatus['authentication'] === 1) {
                $userModel = User::findOne(['id' => $accessStatus['userid']]);
                $userModel->password_hash = Yii::$app->security->generatePasswordHash($newPassword);
                
                if ($userModel->save(false)) {
                    $response['status'] =1; 
                    $response['message'] = 'Password has been changed successfully';
                    $response['token'] = Yii::$app->utility->encryptUserData($userModel->username . '$#@!k' . $newPassword);
                  
                } else {
                    $response['status'] =0; 
                    $response['message'] = 'Something went wrong, please try again later!';
                   
                }
            } else {
                 $response['status'] =3; 
                 $response['message'] = 'unauthorised access';
                
            }

            echo json_encode($response);
            die;
        }
    }

    public function actionAppLogin() {
       
   
       if (Yii::$app->request->post()) {
            
            $username = $_REQUEST['username'];
            $password = $_REQUEST['password'];
            
            $model = new AppLogin();
            $model->username = $username;
            $model->password = $password;
            if ($model->login()) {

                if (\Yii::$app->user->identity->flag === "ACTIVE") {
                    $user = \common\models\User::findOne(\Yii::$app->user->identity->id);
                    $token = time() . "#@#" . rand(100000, 999999);
                    $userModel = User::findOne(Yii::$app->user->identity->id);
                    if (empty($userModel->auth_token)) {
                        $userModel->auth_token = $token;
                        $userModel->save(false);
                    }
                    $getTokeArr = explode('#@#', $userModel->auth_token);
                    $getTokeOnly = $getTokeArr[1];
                    $getTimeStamp = $getTokeArr[0];
                    date_default_timezone_set('Asia/Calcutta');
                    $currentTimeStamp = time();
                    $difference = $currentTimeStamp - $getTimeStamp;
                    if ($difference > 900) {
                        $userModel->auth_token = $token;
                        $userModel->save(false);
                        $getNewTokeArr = explode('#@#', $token);
                        $getNewTokeOnly = $getNewTokeArr[1];
                    Yii::$app->mailer->compose('tokenHtml', ['token' => $getNewTokeOnly])
                                ->setFrom('samparq@qdegrees.com')
                                ->setTo($user->email)
                                ->setSubject('User Authentication Token')
                                ->send();
                    } else {
                        Yii::$app->mailer->compose('tokenHtml', ['token' => $getTokeOnly])
                                ->setFrom('samparq@qdegrees.com')
                                ->setTo($user->email)
                                ->setSubject('User Authentication Token')
                                ->send();
                    }

                    $response = [
                        'status' => 1,
                        'message' => 'OTP has been sent to your registered email address'
                    ];
                } elseif(Yii::$app->user->identity->flag === "INACTIVE" || Yii::$app->user->identity->flag === "PENDING" || Yii::$app->user->identity->flag === "BLOCKED") {
                    $response = ['status' => 0, 'message' => 'Your account is '.strtolower(Yii::$app->user->identity->flag).', please contact to IT department'];
                } else {
                    $response = ['status' => 0, 'message' => 'Unauthorised user'];
                }
            } else {
                $response = ['status' => 0, 'message' => 'Username or password not matched'];
            }

            echo json_encode($response);
            die;
        }
    }

    public function actionResendToken() {
        if (Yii::$app->request->post()) {
            $username = $_REQUEST['username'];
            $password = $_REQUEST['password'];
            $model = new AppLogin();
            $model->username = $username;
            $model->password = $password;
            if ($model->login()) {

                $user = \common\models\User::findOne(\Yii::$app->user->identity->id);
                $userModel = User::findOne(Yii::$app->user->identity->id);
                $getTokeArr = explode('#@#', $userModel->auth_token);
                $getTokeOnly = $getTokeArr[1];
                $getTimeStamp = $getTokeArr[0];
                $currentTimeStamp = time();
                $difference = $currentTimeStamp - $getTimeStamp;
                if ($difference > 900) {
                    $token = time() . "#@#" . rand(100000, 999999);
                    $userModel = User::findOne(Yii::$app->user->identity->id);
                    $userModel->auth_token = $token;
                    $getTokeArr = explode('#@#', $userModel->auth_token);
                    $getTokeOnly = $getTokeArr[1];
                    $userModel->save(false);
                }
                Yii::$app->mailer->compose('tokenHtml', ['token' => $getTokeOnly])
                        ->setFrom('samparq@qdegrees.com')
                        ->setTo($user->email)
                        ->setSubject('User Authentication Token')
                        ->send();
                $response = [
                    'status' => 1,
                    'message' => 'OTP has been sent to your registered email address'
                ];
            } else {
                $response = [
                    'status' => 0,
                    'message' => 'Username or password not matched'
                ];
            }

            echo json_encode($response);
            die;
        }
    }

    public function actionAuthUser() {
        if (Yii::$app->request->post()) {
            $username = $_REQUEST['username'];
            $password = $_REQUEST['password'];
            $token = $_REQUEST['user_token'];
            $imei = $_REQUEST['IMEI'];
            $reg_id = $_REQUEST['app_regid'];
            $model = new AppLogin();
            $model->username = $username;
            $model->password = $password;
            $accessToken = Yii::$app->utility->encryptUserData($username . '$#@!k' . $password);
            if ($model->login()) {
                $user = \common\models\User::findOne(\Yii::$app->user->identity->id);
                $getTokenArr = explode('#@#', $user->auth_token);
                if ($getTokenArr[1] === $token) {
                     date_default_timezone_set('Asia/Calcutta');
                    $getTokenTimeStamp = $getTokenArr[0];
                    $getCurrentTimeStamp = time();
                    $getCurrentTimeStampDiff = $getCurrentTimeStamp - $getTokenTimeStamp;
                    if ($getCurrentTimeStampDiff < 900) {
                        if ($user->imei_app == null) {
                            Yii::$app->samparq->createLoginLog($user->username,$user->id,date('Y-m-d H:i:s'));
                            $currentTime = date('Y-m-d H:i:s');
                            $user->imei_app = $imei;
                            $user->app_regid = $reg_id;
                            $user->lastlogin_time = $currentTime;
                            $user->update_status = 0;
                            $user->save(false);
                            $response = [
                                "status" => 200,
                                "message" => "user successfully authenticated",
                                "id" => $user->id,
                                "username" => $user->username,
                                "email" => $user->email,
                                "created_at" => $user->created_at,
                                "updated_at" => $user->updated_at,
                                "user_type" => $user->user_type,
                                "name" => $user->name,
                                "last_name" => $user->last_name,
                                "flag" => $user->flag,
                                "employee_id" => $user->employee_id,
                                "dob" => $user->dob,
                                "image_path" => $user->image_path,
                                "image_name" => $user->image_name,
                                "mobile_no" => $user->mobile_no,
                                "key" => $user->key,
                                "form_status" => $user->form_submit,
                                'access_token' => $accessToken

//                            "id" => $user->id,
//                            "username" => $user->username,
//                            "email" => $user->email,
//                            "created_at" => $user->created_at,
//                            "updated_at" => $user->updated_at,
//                            "user_type" => $user->user_type,
//                            "name" => $user->name,
//                            "lastname" => "",
//                            "flag" => $user->flag,
//                            "user_status" => $user->status,
                            ];
                        } else {
                            Yii::$app->samparq->createLoginLog($user->username,$user->id,date('Y-m-d H:i:s'));
                            $currentTime = date('Y-m-d H:i:s');
                            $user->lastlogin_time = $currentTime;
                            $user->app_regid = $reg_id;
                            $user->update_status = 0;
                            $user->save(false);
                            if (Yii::$app->user->identity->imei_app == $imei) {
                                $response = [
//                                "status" => 1,
//                                "message" => "Login successfully",
//                                "id" => $user->id,
//                                "username" => $user->username,
//                                "email" => $user->email,
//                                "created_at" => $user->created_at,
//                                "updated_at" => $user->updated_at,
//                                "user_type" => $user->user_type,
//                                "name" => $user->name,
//                                "lastname" => "",
//                                "flag" => $user->flag,
//                                "user_status" => $user->status,
//                                'access_token' => $accessToken
                                    "status" => 200,
                                    "message" => "user successfully authenticated",
                                    "id" => $user->id,
                                    "username" => $user->username,
                                    "email" => $user->email,
                                    "created_at" => $user->created_at,
                                    "updated_at" => $user->updated_at,
                                    "user_type" => $user->user_type,
                                    "name" => $user->name,
                                    "last_name" => $user->last_name,
                                    "flag" => $user->flag,
                                    "employee_id" => $user->employee_id,
                                    "dob" => $user->dob,
                                    "image_path" => $user->image_path,
                                    "image_name" => $user->image_name,
                                    "mobile_no" => $user->mobile_no,
                                    "key" => $user->key,
                                    "form_status" => $user->form_submit,
                                    'access_token' => $accessToken
                                ];
                            } else {
                                $response = [
                                    'status' => 0,
                                    'message' => 'Invalid Device'
                                ];
                            }
                        }
                    } else {
                        $response = [
                            'status' => 0,
                            'message' => 'OTP has expired',
                        ];
                    }
                } else {
                    $response = [
                        'status' => 0,
                        'message' => 'Invalid OTP',
                    ];
                }
            } else {
                $response = [
                    'status' => 0,
                    'message' => 'Username or Password not matched'
                ];
            }
            echo json_encode($response);
            die;
        }
    }

    ////////*****************************************/////////////////**************************//////////////////

    public function actionSendMail($to, $subject, $msg) {

        $mail = Yii::$app->mailer->compose()
                ->setTo($to)
                ->setFrom('samparq@qdegrees.in')
                ->setSubject($subject)
                ->setHtmlBody($msg);

        $mail->send();
        return true;
    }

    public function actionCheckUpdateUserRole() {
        if (Yii::$app->request->post()) {
            $token = $_REQUEST['token'];
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            $user_id = $_REQUEST['user_id'];

            if ($accessStatus['authentication'] === 1) {
                 date_default_timezone_set('Asia/Calcutta');
                $currentTime = date('Y-m-d H:i:s');

                $model = User::findOne(['id' => $user_id]);

                $countUser = count($model);
                if ($countUser > 0) {

                    $logintime = $model->lastlogin_time;
                    $updateStatus = $model->update_status;
                    $curr = strtotime($currentTime);
                    $getOldTimeTotal = strtotime($logintime);
                    $timeDiff = $curr - $getOldTimeTotal;

                    if ($timeDiff >= (86400 * 7)) {
                        Yii::$app->samparq->createLoginLog($model->username,$model->id,false,date('Y-m-d H:i:s'));

                        $result = [
                            "status" => 200,
                            "message" => "Session Expired."
                        ];
                        echo json_encode($result);
                        die();
                    } else {
                        if ($updateStatus == 1) {
                            $result = [
                                "status" => 201,
                                "message" => "Profile Updated."
                            ];
                            echo json_encode($result);
                            die();
                        } else {
                            $result = [
                                "status" => 203,
                                "message" => "Profile  not Updated."
                            ];
                            echo json_encode($result);
                            die();
                        }
                    }
                } else {
                    $result = [
                        "status" => 204,
                        "message" => "Record Not Found."
                    ];
                    echo json_encode($result);
                    die();
                }
            } else {
                $response = [
                    'status' => 205,
                    'message' => 'unauthorised access'
                ];
                echo json_encode($response);
                die();
            }
        }
    }

    public function actionSignup() {
        $model = new User();
        if (Yii::$app->request->post()) {
            $email = $_REQUEST['mail_id'];
            //$name = $_REQUEST['name'];
            $birthday = $_REQUEST['birthday'];
            $employee_id = $_REQUEST['employee_id'];
            $password = $_REQUEST['password'];
            $Image_decoded = base64_decode($_REQUEST["image"]);
            $timeStamp = time();
            $imageName = $timeStamp . $employee_id . ".jpeg";
            //$mobile_number = $_POST['mobile_number'];
            //$departt_id = $_REQUEST['departt_id'];
            $model->email = $email;
            $model->dob = $birthday;
            $model->username = $email;
            //$model->name = $name;
            $model->flag = "PENDING";
            $model->employee_id = $employee_id;
            $model->password_hash = Yii::$app->security->generatePasswordHash($password);
            $model->auth_key = Yii::$app->security->generateRandomString();
            //$model->mobile_no = $mobile_number;
            $model->image_path = Yii::$app->params['file_url'];
            $model->image_name = $imageName;
            //$model->user_type = $departt_id;
            // $model->created_at = new Expression('NOW()');
            if ($model->save(false)) {
                $imgFile = fopen("Upload_Files/" . $imageName, 'w');
                fwrite($imgFile, $Image_decoded);
                fclose($imgFile);

                $result = [
                    "status" => 200,
                    "message" => "user registered successfully",
                    "id" => $model->id
                ];
            } else {
                $result = [
                    "status" => 401,
                    "message" => "username or email is alreay exists!"
                ];
            }
            echo json_encode($result);
            die;
        }
    }

    public function actionUpdateImage() {
        $id = $_REQUEST["id"];
        $model = User::find()->where(["id" => $id])->one();
        if (Yii::$app->request->post()) {
            $Image_decoded = base64_decode($_REQUEST["image"]);
            $timeStamp = time();
            $employee_id = $model->employee_id;
            $imageName = $timeStamp . $employee_id . ".jpeg";
            $model->image_path = Yii::$app->params['file_url'];
            $model->image_name = $imageName;
            if ($model->save(false)) {
                $imgFile = fopen("Upload_Files/" . $imageName, 'w');
                fwrite($imgFile, $Image_decoded);
                fclose($imgFile);
                $result = [
                    "status" => 200,
                    "message" => "user image updated successfully.",
                    "image_name" => $model->image_name
                ];
            } else {
                $result = [
                    "status" => 401,
                    "message" => "something went wrong, please try again later!"
                ];
            }
            echo json_encode($result);
            die;
        }
    }

    public function actionEmailVerification() {
        $key = Yii::$app->db->quoteValue($_REQUEST["key"]);
        $getUser = User::find()->where(["auth_key" => str_replace("'", "", $key)])->one();
        $countUser = count($getUser);
        if ($countUser > 0) {
            if ($getUser->email_conf == 1) {
                $message = '<h2 style="color:#42c662; font-size:24px;">Already Verified!</h2>
                         
                     You have already verified your email address.';
                $image = "https://www.qdegrees.com/frontend/web/images/success.png";
            } else {
                $getUser->email_conf = 1;
                $getUser->save();
                $message = '<h2 style="color:#42c662; font-size:24px;">Successfully verified!</h2>
                         
                      Congratulations! You have successfully verified your email address.  ';
            }
            $image = "https://www.qdegrees.com/frontend/web/images/success.png";
        } else {
            $message = '<h2 style="color:red; font-size:24px;">Invalid Key!</h2>
                         
                      Sorry! Invalid key. ';
            $image = "https://www.qdegrees.com/frontend/web/images/invalid.png";
        }

        return $this->render("email-verify", [
                    "message" => $message,
                    "image" => $image
        ]);
    }


    public function actionChangePassword() {
        $id = $_REQUEST["id"];
        $password = $_REQUEST['password'];
        $newpassword = $_REQUEST['newpassword'];
        $model = User::find()->where(['id' => $id])->one();
        $login = new AppLogin();
        $login->username = $model->username;
        $login->password = $password;

        if ($login->login()) {
            $model->password_hash = Yii::$app->security->generatePasswordHash($newpassword);
            if ($model->save(FALSE)) {
                $result = [
                    "status" => 200,
                    "message" => "Password has been successfully changed"
                ];
            } else {
                $result = [
                    "status" => 401,
                    "message" => "something went wrong please try again later"
                ];
            }
        } else {
            $result = [
                "status" => 401,
                "message" => "Password is not matched"
            ];
        }

        echo json_encode($result);
        die;
    }

    public function actionResetPassword() {
        $email = $_REQUEST["email"];
        $otp = $_REQUEST["otp"];
        $password = $_REQUEST["password"];
        $arr = ["email" => $email, "otp" => $otp, "password" => $password];
        foreach ($arr as $key => $value) {
            if (empty($arr[$key])) {
                $error = $key . " cannot be empty";
                $ekey = $key;
            }
        }
        if (empty($error)) {
            $user = User::find()->where(["username" => $email])->one();

            $currentTime = time();
            $getTokeArr = explode('#@#', $user->auth_token);
            $getOtp = $getTokeArr[1];
            $getTimeStamp = $getTokeArr[0];
            if ($otp === $getOtp) {
                $checkOtpExpire = $currentTime - $getTimeStamp;
                if ($checkOtpExpire > 60 * 60) {
                    $result = [
                        "status" => 401,
                        "message" => "Your OTP has been expired"
                    ];
                } else {
                    $user->password_hash = Yii::$app->security->generatePasswordHash($password);
                    $user->password_reset_token = null;
                    if ($user->save(false)) {
                        Yii::$app->mailer->compose('passwordReset', ['username' => $user->name])
                            ->setFrom('samparq@qdegrees.com')
                            ->setTo($user->email)
                            ->setSubject('Password reset successfully')
                            ->send();
                        $result = [
                            "status" => 200,
                            "message" => "Your Password has been reset successfully"
                        ];
                    } else {
                        $result = [
                            "status" => 401,
                            "message" => "something went wrong please try again later"
                        ];
                    }
                }
            } else {
                $result = [
                    "status" => 401,
                    "message" => "OTP not matched",
                ];
            }
        } else {
            $result = [
                "status" => 401,
                "message" => $ekey . " cannot be empty"
            ];
        }


        echo json_encode($result);
        die;
    }

    public function actionForgotPassword() {
        $email = $_REQUEST["email"];
        if (empty($email)) {
            $result = [
                "status" => 401,
                "message" => "email cannot be empty"
            ];
        } else {
            $user = User::find()->where(["email" => $email])->one();
            if (empty($user)) {
                $result = [
                    "status" => 401,
                    "message" => "User not registered with this email address"
                ];
            } else {
                if ($user->flag === "ACTIVE") {
                    $token = time() . "#@#" . rand(100000, 999999);
                    if (empty($user->auth_token)) {
                        $user->auth_token = $token;
                        $user->save(false);
                    }
                    $getTokeArr = explode('#@#', $user->auth_token);
                    $getTokeOnly = $getTokeArr[1];
                    $getTimeStamp = $getTokeArr[0];
                    date_default_timezone_set('Asia/Calcutta');
                    $currentTimeStamp = time();
                    $difference = $currentTimeStamp - $getTimeStamp;
                    if ($difference > 900) {
                        $user->auth_token = $token;
                        $user->save(false);
                        $getNewTokeArr = explode('#@#', $token);
                        $getNewTokeOnly = $getNewTokeArr[1];
                        Yii::$app->mailer->compose('tokenHtml', ['token' => $getNewTokeOnly])
                            ->setFrom('samparq@qdegrees.com')
                            ->setTo($user->email)
                            ->setSubject('User Authentication Token')
                            ->send();
                    } else {
                        Yii::$app->mailer->compose('tokenHtml', ['token' => $getTokeOnly])
                            ->setFrom('samparq@qdegrees.com')
                            ->setTo($user->email)
                            ->setSubject('User Authentication Token')
                            ->send();
                    }

                    $result = [
                        "status" => 200,
                        "message" => "One Time Password (OTP) has been sent to your registered email"
                    ];
                } else {
                    $result = [
                        "status" => 401,
                        "message" => "Your account is ".strtolower($user->flag).", please contact to IT department."
                    ];
                }
            }
        }


        echo json_encode($result);
        die;
    }

}
