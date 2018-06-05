<?php

namespace frontend\controllers;

use backend\models\CdUser;
use backend\models\Options;
use backend\models\TrainingQuestion;
use backend\models\TrainingSubmission;
use backend\models\Webcast;
use backend\models\WebcastQueries;
use backend\models\WebcastQuery;
use backend\models\WebcastViewers;
use common\models\AppLogin;
use common\models\TblChatgroupReadstatus;
use common\models\TblFeedbackReadstatus;
use common\models\UserTesting;
use function GuzzleHttp\Psr7\str;
use Yii;
use yii\base\InvalidParamException;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
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
use backend\models\AppUpdates;
use backend\models\Training;
use backend\models\Trainees;
use backend\models\TrainingNotification;
use backend\models\TrainingMaterial;
use common\models\TblChatgroup;
use common\models\TblChatgroupMembers;
use common\models\TblChatgroupMessage;
use common\models\TblChatgroupAttach;
use common\models\TblFeedbackMessage;
use common\models\TblFeedbackMember;
use common\models\TblFeedbackAdmins;
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
    /******************Notification Sending Testing Webservice****************************/
    
    public function actionTestNotificationApi(){

        
        $token='f91T87DcqAo:APA91bFEqhZXXQm4bf8MTfvOxEquJXbqYII0EeLAmphYVx0dDtqmXLxG73mHzsAkjYsLXYEJNy09WxbiDqqK5c6AOSJpkAUVJNhHZ8OCVygkHtxRUUvxZlfObIVG_6jLatqcEys2W6wN';
        $message='This is Testing Notification';
        
        $path_to_firebase_cm = 'https://fcm.googleapis.com/fcm/send';
        
        $fields = array(
            'to' => $token,
            'notification' => array('title' => ' Working Good', 'body' => 'That is all we want','sound'=>'default','icon'=>'qlogo'),
            'data' => array('message' => $message)
        );
 
        $headers = array(
            'Authorization:key=' . Yii::$app->params['API_ACCESS_KEY'],
            'Content-Type:application/json'
        );		
        $ch = curl_init();
 
        curl_setopt($ch, CURLOPT_URL, $path_to_firebase_cm); 
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 ); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
    
        $result = curl_exec($ch);
       
        curl_close($ch);

        echo $result;die;
    }
    
    /***************New Check Installation Update******************/
    public function actionCheckUpdateInstallationNew(){
            if (Yii::$app->request->post()) {
                    $VersionCode=$_REQUEST['version_code'];
                    $RegId=$_REQUEST['reg_id'];
                    $UserId=$_REQUEST['user_id'];
                    
                    if(!empty($RegId)&&$RegId!='null'){
                        $userModel=User::find()->where(['id'=>$UserId])->one();
                        $userModel->app_regid=$RegId;
                        $userModel->save(false);
                    }
                    
                    $UpdateModel=AppUpdates::find()->where(['status'=>1])->one();
                    $Oversion=$UpdateModel->version_code;
                    if($Oversion>$VersionCode){
                            $Response=array();
                            $Response['update']=1;
                            echo json_encode($Response);die;
                    }else{
                            $Response=array();
                            $Response['update']=0;
                            echo json_encode($Response);die;
                    }
            }
    }
    
    
    
    
    /*************************Feedback Model*********************************/
    
    
    
    
    /**********************Created by Sanjeev Poonia on Nov 13,2017************************/
    
    public function actionAssignFeedbackToAdmin(){
        if (Yii::$app->request->post()) {
            $FeedbackId=$_REQUEST['feedback_id'];
            $member_uid=$_REQUEST['member_uid'];
            $user_id=$_REQUEST['user_id'];
            $token=$_REQUEST['token'];
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            $Response=array();   
            if ($accessStatus['authentication'] === 1){
                $FeedbackMemModle=TblFeedbackMember::find()->where(['feedback_id'=>$FeedbackId])->andWhere(['user_id'=>$member_uid])->one();
                
                $Totla=count($FeedbackMemModle);
               
                if($Totla==1){
                    $alre=$FeedbackMemModle->status;
                     
                    if($alre==1){
                         $Response['status']=0;
                         $Response['message']='Already assigned to this user!';
                    }else{
                    $FeedbackMemModle->status=1;
                    if($FeedbackMemModle->save(false)){
                        
                        $MesModel=new TblFeedbackMessage();
                        $MesModel->feedback_id=$FeedbackId;
                        $MesModel->sender_id=$user_id;
                        $UserModewql=User::find()->where(['id'=>$user_id])->one();
                        $AdminName=$UserModewql->name;
                        $MesModel->sender_name=$AdminName;
                        $UserMo=User::find()->where(['id'=>$member_uid])->one();
                        $Memname=$UserMo->name;
                        $MesModel->feedback_message=$AdminName.' assigned this feedback to '.$Memname;
                        $MesModel->feedback_type=2;
                        $MesModel->save(false);
                        $Response['status']=1;
                        $Response['message']='Assigned Successfully!!';
                    }else{
                         $Response['status']=0;
                         $Response['message']='Something went wrong! Please try again.';
                        
                    }
                    }
                }else{
                    $NewMamModel= new TblFeedbackMember();
                    $NewMamModel->feedback_id=$FeedbackId;
                    $NewMamModel->user_id=$member_uid;
                    $UserModewql=User::find()->where(['id'=>$member_uid])->one();
                    $memName=$UserModewql->name;
                    $NewMamModel->user_name=$memName;
                    $NewMamModel->status=1;
                    if($NewMamModel->save(false)){
                        $UserMo=User::find()->where(['id'=>$user_id])->one();
                        $AdMemname=$UserMo->name;
                        $MesModel=new TblFeedbackMessage();
                        $MesModel->feedback_id=$FeedbackId;
                        $MesModel->sender_id=$user_id;
                        $MesModel->sender_name=$AdMemname;
                        $MesModel->feedback_message=$AdMemname.' assigned this feedback to '.$memName;
                        $MesModel->feedback_type=2;
                        $MesModel->save(false);
                        $Response['status']=1;
                        $Response['message']='Assigned Successfully!!';
                        
                    }else{
                         $Response['status']=0;
                         $Response['message']='Something went wrong! Please try again.';
                    }
                    
                    }
            }else{
                 $Response['status']=3;
                 $Response['message']='Unauthorise access!!';
            }
            echo json_encode($Response);die;
        }
    }
    
    public function actionAddNewFeedbackAdmin(){
         if (Yii::$app->request->post()) {
             $creater_id=$_REQUEST['user_id'];
             $user_id=$_REQUEST['newuser_id'];
             $user_name=$_REQUEST['user_name'];
             $token=$_REQUEST['token'];
             $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
             $Response=array();   
             if ($accessStatus['authentication'] === 1){
                 $AdminModel=TblFeedbackAdmins::find()->where(['user_id'=>$user_id])->one();
                 $total=count($AdminModel);
                 if($total==1){
                     $AdminModel->status=1;
                     
                     if($AdminModel->save(false)){
                         $UserModewql=User::find()->where(['id'=>$user_id])->one();
                          $UserEmail=$UserModewql->email;
                          $SenderImage=$UserModewql->image_path.$UserModewql->image_name;
                          $Response['mem_id']=$user_id;
                          $Response['user_image']=$SenderImage;
                          $Response['user_email']=$UserEmail;
                          $Response['status']=1;
                          $Response['message']='Created admin successfully.';
                     }else{
                          $Response['status']=0;
                          $Response['message']='Something went wrong!! Please try again.';
                     }
                 }else{
                     $NewAdminModel=new TblFeedbackAdmins();
                     $NewAdminModel->user_id=$user_id;
                     $NewAdminModel->user_name=$user_name;
                     $NewAdminModel->status=1;
                     $UserModel=User::find()->where(['id'=>$user_id])->one();
                     $UserEmail=$UserModel->email;
                     $SenderImage=$UserModel->image_path.$UserModel->image_name;
                     $isAdmin=$UserModel->key;
                     $suerAdmi=$UserModel->super_admin;
                     if($isAdmin==1 && $suerAdmi==1){
                         $NewAdminModel->is_admin=1;
                     }else{
                         $NewAdminModel->is_admin=0;
                     }
                     if($NewAdminModel->save(false)){
                          $Response['status']=1;
                          $Response['message']='Created admin successfully.';
                          $Response['mem_id']=$user_id;
                          $Response['user_image']=$SenderImage;
                          $Response['user_email']=$UserEmail;
                          
                     }else{
                          $Response['status']=0;
                          $Response['message']='Something went wrong!! Please try again.';
                     }
                 }
             }else{
                 $Response['status']=3;
                 $Response['message']='Something went wrong!! Please try again.';
             }
             echo json_encode($Response);die;
             
         }
    }
    public function actionGetFeedbackAdminList(){
         if (Yii::$app->request->post()) {
             $user_id=$_REQUEST['user_id'];
             $token=$_REQUEST['token'];
             $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
             $Response=array();
             $Response['admin_list']=array();      
             if ($accessStatus['authentication'] === 1){
                 $Response['status']=1;
                 $AdminModel=TblFeedbackAdmins::find()->where(['status'=>1])->all();
                 foreach($AdminModel as $ad){
                     $Report= array();
                     $admId=$ad->user_id;
                     $Report['user_id']=$admId;
                        $Report['user_name']=$ad->user_name;
                     $Report['is_admin']=$ad->is_admin;
                     $UserModel=User::find()->where(['id'=>$admId])->one();
                     $UserEmail=$UserModel->email;
                     $SenderImage=$UserModel->image_path.$UserModel->image_name;
                     $Report['user_image']=$SenderImage;
                     $Report['email']=$UserEmail;
                     array_push($Response['admin_list'], $Report);
               }
            }else{
                $Response['status']=3;
                $Response['message']='Something went wrong!! Please try again.';
            }
            echo json_encode($Response);die;
         }
    }
    public function actionSendSingleReplyFeedback(){
        if (Yii::$app->request->post()) {
            $user_id=$_REQUEST['user_id'];
            $Username=$_REQUEST['user_name'];
            $token=$_REQUEST['token'];
            $feedback_id=$_REQUEST['feedback_id'];
            $msg=$_REQUEST['feedback_message'];
            $MsgStr = str_replace("\\", "\\\\", $msg);
            $feedback_message = str_replace("'", "\'", $MsgStr);
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            $Response=array();
            if ($accessStatus['authentication'] === 1){
                $FeedbackMessageModel=new TblFeedbackMessage();
                $FeedbackMessageModel->feedback_id=$feedback_id;
                $FeedbackMessageModel->sender_id=$user_id;
                $FeedbackMessageModel->sender_name=$Username;
                $FeedbackMessageModel->feedback_message=$feedback_message;
                $FeedbackMessageModel->status=1;
                $FeedbackMessageModel->feedback_type=1;
                if($FeedbackMessageModel->save(false)){
                    $Response['status']=1;
                    $Response['message']='Reply submitted successfully!.';
                    $Response['feedback_message_id']=$FeedbackMessageModel->id;
                    $typeArr = [
                        'feedback_id' => $feedback_id,
                        'sender_id' => $user_id,
                        'sender_name' => $Username,
                        'Title' => $Username,
                        'Body' => 'New Feedback',
                        'feedback_message' => $msg,
                        'feedback_type' => '1',
                        'sender_image' => '',
                        'date_time' => date("M D, Y H:i:s A"),
                        'process_type' => 'groupfeedback_type',
                    ];

                    Yii::$app->samparq->sendFeedbackNotification($feedback_id,$typeArr,$user_id);
                }else{
                    $Response['status']=0;
                    $Response['message']='Something went wrong!! Please try again.';
                }
            }else{
                $Response['status']=3;
                $Response['message']='Unauthorise access!!';
            }
            echo json_encode($Response);die;
        }
    }
    /*************************Created by Sanjeev Poonia on Nov 11,2017***********************/
    public function actionGetAllFeedbackReplyList(){
        if (Yii::$app->request->post()) {
            $user_id=$_REQUEST['user_id'];
            $token=$_REQUEST['token'];
            $feedback_id=$_REQUEST['feedback_id'];
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            $Response=array();
            $Response['feedback_reply_list']=array();
            if ($accessStatus['authentication'] === 1){
                    $FeedbackModel=TblFeedback::find()->where(['id'=>$feedback_id])->andWhere(['status'=>1])->one();
                    $FeedbackSenderId=$FeedbackModel->sender_id;
                    $CreatedDate=$FeedbackModel->date_time;
                    $CreaterName=$FeedbackModel->sender_name;
                    $FeedbackTitle=$FeedbackModel->feedback_str;
                    $UserModel=User::find()->where(['id'=>$FeedbackSenderId])->one();
                    $SenderImage=$UserModel->image_path.$UserModel->image_name;
                    $Response['status']=1;
                    $Response['feedback_creater_id']=$FeedbackSenderId;
                    $Response['feedback_created']=$CreatedDate;
                    $Response['feedback_creater_name']=$CreaterName;
                    $Response['feedback_title']=$FeedbackTitle;
                    $Response['creater_image']=$SenderImage;
                   
                    $AdminModel=TblFeedbackAdmins::find()->where(['user_id'=>$user_id])->andWhere(['is_admin'=>1])->andWhere(['status'=>1])->one();
                    $isAd=count($AdminModel);
                    if($isAd==1){
                        $Response['is_admin']=1;
                    }else{
                        $Response['is_admin']=0;
                    }
                    
                    
                    $FeedbackMessageModel=TblFeedbackMessage::find()->where(['feedback_id'=>$feedback_id])->andWhere(['status'=>1])->orderBy(['id' => SORT_DESC])->all();
                    foreach($FeedbackMessageModel as $model){
                       $Report=array();
                       $Report['feedback_id']=$model->feedback_id;
                       $ssid=$model->sender_id;
                       $Report['sender_id']=$ssid;
                       $Report['sender_name']=$model->sender_name;
                       $Report['feedback_message']=str_replace("\\\\", "\\",$model->feedback_message);
                       $Report['date_time']=date('M d,Y H:i:s A',strtotime($model->date_time));
                       $Report['feedback_type']=$model->feedback_type;
                       $UserModelmes=User::find()->where(['id'=>$ssid])->one();
                       $Report['sender_image']=$UserModelmes->image_path.$UserModelmes->image_name;
                       array_push($Response['feedback_reply_list'], $Report);
                    }
                    echo json_encode($Response);die;
            }else{
                $Response['status']=3;
                $Response['message']='Unauthorise Access!';
            }
            echo json_encode($Response);die;
        }
    }
    public function actionGetFeedbackListing(){
        if (Yii::$app->request->post()) {
            $user_id=$_REQUEST['user_id'];
            $token=$_REQUEST['token'];
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            $Response=array();
            $Response['feedback_list']=array();
            if ($accessStatus['authentication'] === 1){
                $Response['status']=1;
                $AdminModel=TblFeedbackAdmins::find()->where(['user_id'=>$user_id])->andWhere(['status'=>1])->count();
                $Response['is_admin']=$AdminModel;
                $FeedbackMemberModel=TblFeedbackMember::find()->where(['user_id'=>$user_id])->andWhere(['status'=>1])->orderBy('id desc')->all();
                foreach($FeedbackMemberModel as $Key){
                    
                    $MemberId=$Key->id;
                    $FeedbackId=$Key->feedback_id;
                    $FeedbackModel=TblFeedback::find()->where(['id'=>$FeedbackId])->andWhere(['status'=>1])->one();
                    $FeedbackSenderId=$FeedbackModel->sender_id;
                    $CreatedDate=$FeedbackModel->date_time;
                    $CreaterName=$FeedbackModel->sender_name;
                    $FeedbackTitle=$FeedbackModel->feedback_str;
                    
                    
                    $FeedbackMessageModel=TblFeedbackMessage::find()->where(['feedback_id'=>$FeedbackId])->andWhere(['status'=>1])->orderBy(['id' => SORT_DESC])->one();
                    $LastMessage=str_replace("\\\\", "\\",$FeedbackMessageModel->feedback_message);
                    $UserModel=User::find()->where(['id'=>$FeedbackSenderId])->one();
                    $SenderImage=$UserModel->image_path.$UserModel->image_name;
                    
                    
                    
                    $Report=array();
                    if($user_id==$FeedbackSenderId){
                        $Report['created_by']='Created by you on '.date('M d,Y',strtotime($CreatedDate));
                    }else{
                        $Report['created_by']='Created by '.$CreaterName.' on '.date('M d,Y',strtotime($CreatedDate));
                    }
                    
                    $Report['feedback_title']=$FeedbackTitle;
                    $Report['last_message']=$LastMessage;
                    $Report['feedback_id']=$FeedbackId;
                    $Report['read_count'] = Yii::$app->samparq->getGroupCount('feedback', $FeedbackId, $user_id);
                    $Report['sender_image']=$SenderImage;
                    array_push($Response['feedback_list'], $Report);
                    
                }
                
                
                
            }else{
                $Response['status']=3;
                $Response['message']='Unauthorise access!!';
            }
            echo json_encode($Response);die;
            
        }
    }
    /****************Feedback new Group Created By sanjeev Poonia on Nov 10,2017***********************************/
    public function actionCreateNewFeedbackGroup(){
        if (Yii::$app->request->post()) {
            
            $user_id=$_REQUEST['user_id'];
            $user_name=$_REQUEST['user_name'];
            $feedback_title=$_REQUEST['feedback_title'];
            $msg=$_REQUEST['feedback_message'];
            $MsgStr = str_replace("\\", "\\\\", $msg);
            $feedback_message = str_replace("'", "\'", $MsgStr);
            
            $token=$_REQUEST['token'];
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            $Response=array();           
            if ($accessStatus['authentication'] === 1){
                $FeedbackModel=new TblFeedback();
                $FeedbackModel->sender_id=$user_id;
                $FeedbackModel->sender_name=$user_name;
                $FeedbackModel->feedback_str=$feedback_title;
                if($FeedbackModel->save(false)){
                    
                    $FeedbackId=$FeedbackModel->id;
                    $FeedbackMessageModel=new TblFeedbackMessage();
                    $FeedbackMessageModel->feedback_id=$FeedbackId;
                    $FeedbackMessageModel->sender_id=$user_id;
                    $FeedbackMessageModel->sender_name=$user_name;
                    $FeedbackMessageModel->feedback_message=$feedback_message;
                    $FeedbackMessageModel->save(false);
                    
                    $FeedbackMemberModel= new TblFeedbackMember();
                    $FeedbackMemberModel->feedback_id=$FeedbackId;
                    $FeedbackMemberModel->user_id=$user_id;
                    $FeedbackMemberModel->user_name=$user_name;
                    $FeedbackMemberModel->save(false);
                    
                    
                    $UserModel=User::find()->where(['key'=>1])->andWhere(['super_admin'=>1])->andWhere(['flag'=>'ACTIVE'])->all();
                    
                    foreach($UserModel as $us){
                        $FeedNewMMOdel=new TblFeedbackMember();
                        $FeedNewMMOdel->feedback_id=$FeedbackId;
                        $FeedNewMMOdel->user_id=$us->id;
                        $FeedNewMMOdel->user_name=$us->name;
                        $FeedNewMMOdel->is_admin=1;
                        $FeedNewMMOdel->save(false);
                    }
                    
                    $Response['status']=1;
                    $Response['message']='Feedback submitted successfully.';

                    $typeArr = [
                        'feedback_id' => $FeedbackId,
                        'sender_id' => $user_id,
                        'sender_name' => $user_name,
                        'Title' => $user_name,
                        'Body' => 'New Feedback',
                        'feedback_message' => $msg,
                        'feedback_type' => $FeedbackMessageModel->feedback_type,
                        'sender_image' => '',
                        'date_time' => $FeedbackMessageModel->date_time,
                        'process_type' => 'groupfeedback_type',
                    ];

                    Yii::$app->samparq->sendFeedbackNotification($FeedbackId,$typeArr,$user_id);

                }else{
                    $Response['status']=0;
                    $Response['message']='Something went wrong! Please try again.';
                }
                
                
            }else{
                $Response['status']=3;
                $Response['message']='Unauthorise access!!';
            }
            echo json_encode($Response);die;
        }
    }
    /******************Created by Sanjeev Poonia on Nov 09,2017*****************/
    public function actionGetAllAttachmentGroupChat(){
        if (Yii::$app->request->post()) {
            $group_id=$_REQUEST['group_id'];
            $message_id=$_REQUEST['message_id'];
            $token=$_REQUEST['token'];
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            $Response=array(); 
            $Response['attach_list']= array();
            
            if ($accessStatus['authentication'] === 1){
                    $AttachModel=TblChatgroupAttach::find()->where(['group_id'=>$group_id])->andWhere(['message_id'=>$message_id])->andWhere(['status'=>1])->all();
                    foreach($AttachModel as $key){
                        $Resport=array();
                        $Resport['id']=$key->id;
                        $Resport['orignal_name']=$key->orignal_name;
                        $Resport['new_name']=Yii::$app->params['file_url'].$key->new_name;
                        $Resport['thumb_name']=Yii::$app->params['file_url'].$key->thumb_name;
                        $Resport['date_time']=$key->date_time;
                        $Resport['status']=$key->status;
                        $Resport['type']=$key->type;
                        array_push($Response['attach_list'], $Resport);
                    }
                    $Response['status']=1;
                
            }else{
                    $Response['status']=3;
                    $Response['message']='Unauthorise access!!';
            }
            echo json_encode($Response);die;
        }
    }
    
    /*********************Change group Icon************************************/
    public function actionChangeGroupImage(){
         if (Yii::$app->request->post()) {
               $User_id=$_REQUEST['user_id'];
               $token=$_REQUEST['token'];
               $group_id=$_REQUEST['group_id'];
               $GroImage=$_REQUEST["group_image"];
               $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
               $Response=array(); 
                if ($accessStatus['authentication'] === 1){
                     $timeStamp = time();
                     $imageName = $timeStamp . $User_id . ".jpeg";
                     $GroupImage=base64_decode($GroImage);
                     $imgFile = fopen("Upload_Files/" . $imageName, 'w');
                     fwrite($imgFile, $GroupImage);
                     fclose($imgFile);
                     $GroupChatModel=TblChatgroup::find()->where(['id'=>$group_id])->andWhere(['status'=>1])->one();
                     $GroupChatModel->icon_thumb=$imageName;
                     $GroupChatModel->updated_by=$User_id;
                     $GroupChatModel->updated_at=date('Y-m-d H:i:s');
                     if($GroupChatModel->save(false)){
                         $AdUserModel=User::find()->where(['id'=>$User_id])->one();
                        $Adusername=$AdUserModel->name;
                        $ChatMessage=new TblChatgroupMessage();
                        $ChatMessage->group_id=$group_id;
                        $ChatMessage->sender_id=$User_id;
                        $ChatMessage->sender_name=$Adusername;
                        $ChatMessage->chat_message=$Adusername.' changed group icon on '.date('Y-m-d H:i:s');
                        $ChatMessage->chat_type=2;
                        $ChatMessage->save(false);
                        $Response['status']=1;
                        $Response['icon_thumb']=Yii::$app->params['file_url'].$imageName;
                        $Response['message']='Group icon changed successfully!!';
                     }else{
                          $Response['status']=0;
                          $Response['message']='Something went wrong!! Please try again.';
                     }
                     
                    
                }else{
                    $Response['status']=3;
                    $Response['message']='Unauthorise access!!';
                }
                echo json_encode($Response);die;
               
         }
    }
    
    
    /**********************Created By Sanjeev Poonia on Nov 08,2017********************************/
    
    public function actionChangeGroupName(){
        if (Yii::$app->request->post()) {
            $group_id=$_REQUEST['group_id'];
            $user_id=$_REQUEST['user_id'];
            $user_name=$_REQUEST['username'];
            $token=$_REQUEST['token'];
            $msg=$_REQUEST['group_title'];
            $MsgStr = str_replace("\\", "\\\\", $msg);
            $GroupTitle = str_replace("'", "\'", $MsgStr);
            $Response=array();
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            if ($accessStatus['authentication'] === 1){
                
                $GroupModel=TblChatgroup::find()->where(['id'=>$group_id])->andWhere(['status'=>1])->one();
                $GroupModel->name=$GroupTitle;
                $GroupModel->updated_at=date('Y-m-d H:i:s');
                $GroupModel->updated_by=$user_id;
                if($GroupModel->save(false)){
                    
                    $GroupMessageModel=new TblChatgroupMessage();
                    $GroupMessageModel->group_id=$group_id;
                    $GroupMessageModel->sender_id=$user_id;
                    $GroupMessageModel->sender_name=$user_name;
                    $GroupMessageModel->chat_message=$user_name.' changed the name to '.$GroupTitle.' on '.date('M d,Y H:i:s A',strtotime(date('Y-m-d H:i:s')));
                    $GroupMessageModel->status=1;
                    $GroupMessageModel->att_status=0;
                    $GroupMessageModel->chat_type=2;
                    $GroupMessageModel->save(false);
                    
                    $Response['status']=1;
                    $Response['message']='Name updated successfully!';
                    
                }else{
                    $Response['status']=0;
                    $Response['message']='Something went wrong';
                }
                
                
            }else{
                $Response['status']=3;
                $Response['message']='Unauthorise access!!';
                
            }
            echo json_encode($Response);die;
        }
    }
    
    /*******************Created By Sanjeev Poonia on Nov 06,2017*******************/
    
    public function actionAddNewParticipantChatGroup(){
        if (Yii::$app->request->post()) {
            $group_id=$_REQUEST['group_id'];
            $user_id=$_REQUEST['user_id'];
            $token=$_REQUEST['token'];
            $newMemId=$_REQUEST['newmember_id'];
            $NewMem_Username=$_REQUEST['newmember_name'];
            
            $Response=array();
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            if ($accessStatus['authentication'] === 1){
                $GroupMemberModel=TblChatgroupMembers::find()->where(['group_id'=>$group_id])->andWhere(['user_id'=>$newMemId])->one();
                $total=count($GroupMemberModel);
                if($total==1){
                    $GroupMemberModel->status=1;
                    $GroupMemberModel->is_admin=0;
                    $GroupMemberModel->updated_by=$user_id;
                      if($GroupMemberModel->save(false)){
                          $UserModel=User::find()->where(['id'=>$user_id])->one();
                             $RemoverName=$UserModel->name;
                             $MessageModel=new TblChatgroupMessage();
                             $MessageModel->group_id=$group_id;
                             $MessageModel->sender_id=$user_id;
                             $MessageModel->sender_name=$RemoverName;
                             $MessageModel->chat_message=$RemoverName.' added '.$NewMem_Username.' on '.date('M d,Y',strtotime(date('Y-m-d H:i:s')));
                             $MessageModel->att_status=0;
                             $MessageModel->chat_type=2;
                             $MessageModel->save(false);                                                                              
                             $NewUserModel=User::find()->where(['id'=>$newMemId])->one();                             
                             $Response['status']=1;
                             $Response['mem_id']=$GroupMemberModel->id;
                             $Response['user_image']=Yii::$app->params['file_url'].$NewUserModel->image_name;
                             $Response['user_email']=$NewUserModel->email;
                             $Response['message']=$NewMem_Username.' added successfully!';
                      } else {
                          $Response['status']=0;
                          $Response['message']='Something went wrong! Please try again.';
                      }
                    
                }else{
                     $GroupMemberModel=new TblChatgroupMembers();
                $GroupMemberModel->group_id=$group_id;
                $GroupMemberModel->user_id=$newMemId;
                $GroupMemberModel->user_name=$NewMem_Username;
                $GroupMemberModel->status=1;
                $GroupMemberModel->is_admin=0;
                $GroupMemberModel->created_by=$user_id;
                if($GroupMemberModel->save(false)){
                             $UserModel=User::find()->where(['id'=>$user_id])->one();
                             $RemoverName=$UserModel->name;
                             $MessageModel=new TblChatgroupMessage();
                             $MessageModel->group_id=$group_id;
                             $MessageModel->sender_id=$user_id;
                             $MessageModel->sender_name=$RemoverName;
                             $MessageModel->chat_message=$RemoverName.' added '.$NewMem_Username.' on '.date('M d,Y',strtotime(date('Y-m-d H:i:s')));
                             $MessageModel->att_status=0;
                             $MessageModel->chat_type=2;
                             $MessageModel->save(false);                                                                              
                             $NewUserModel=User::find()->where(['id'=>$newMemId])->one();                             
                             $Response['status']=1;
                             $Response['mem_id']=$GroupMemberModel->id;
                             $Response['user_image']=Yii::$app->params['file_url'].$NewUserModel->image_name;
                             $Response['user_email']=$NewUserModel->email;
                             $Response['message']=$NewMem_Username.' added successfully!';
                             
                }else{
                    $Response['status']=0;
                    $Response['message']='Something went wrong! Please try again.';
                }
                
                }
            }else{
                $Response['status']=3;
                $Response['message']='Unauthorise access!!';
            }
            echo json_encode($Response);die;
        }
    }
    
    
    
    /**************Created by Sanjeev Poonia On Nov 04,2017*************/
    
    public function actionExitChatGroup(){
        if (Yii::$app->request->post()) {
            $group_id=$_REQUEST['group_id'];
            $user_id=$_REQUEST['user_id'];
            $token=$_REQUEST['token'];
            $Response=array();
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            if ($accessStatus['authentication'] === 1){
                $GroupMemberModel=TblChatgroupMembers::find()->where(['group_id'=>$group_id])->andWhere(['status'=>1])->count();
                  if($GroupMemberModel>1){
                      $GroupMember=TblChatgroupMembers::find()->where(['group_id'=>$group_id])->andWhere(['user_id'=>$user_id])->one();
                      $isAdmin=$GroupMember->is_admin;
                      if($isAdmin==1){
                          $GroupMember->status=2;
                          $GroupMember->is_admin=0;
                          $GroupMember->updated_by=$user_id;
                          if($GroupMember->save(false)){
                            $NewAdminUser=TblChatgroupMembers::find()->where(['!=','user_id',$user_id])->andWhere(['group_id'=>$group_id])->andWhere(['status'=>1])->orderBy(['id' => SORT_ASC])->one();
                            $NewAdminUser->is_admin=1;
                            $NewAdminUser->updated_by=$user_id;
                            $NewAdminUser->save(false);
                            
                             $UserModel=User::find()->where(['id'=>$user_id])->one();
                             $RemoverName=$UserModel->name;
                             $MessageModel=new TblChatgroupMessage();
                             $MessageModel->group_id=$group_id;
                             $MessageModel->sender_id=$user_id;
                             $MessageModel->sender_name=$RemoverName;
                             $MessageModel->chat_message=$RemoverName.' left on '.date('M d,Y',strtotime(date('Y-m-d H:i:s')));
                             $MessageModel->att_status=0;
                             $MessageModel->chat_type=2;
                             $MessageModel->save(false);
                             $Response['status']=1;
                             $Response['message']='Exit successfully!!.';
                         }else{
                            $Response['status']=0;
                            $Response['message']='Something went wrong! Please try again.';
                         }
                      }else{
                          
                         $GroupMember->status=2;
                         $GroupMember->updated_by=$user_id;
                         if($GroupMember->save(false)){
                             
                             $UserModel=User::find()->where(['id'=>$user_id])->one();
                             $RemoverName=$UserModel->name;
                             $MessageModel=new TblChatgroupMessage();
                             $MessageModel->group_id=$group_id;
                             $MessageModel->sender_id=$user_id;
                             $MessageModel->sender_name=$RemoverName;
                             $MessageModel->chat_message=$RemoverName.' left on '.date('M d,Y',strtotime(date('Y-m-d H:i:s')));
                             $MessageModel->att_status=0;
                             $MessageModel->chat_type=2;
                             $MessageModel->save(false);
                             
                             
                             
                             $Response['status']=1;
                             $Response['message']='Exit successfully!!.';
                         }else{
                            $Response['status']=0;
                            $Response['message']='Something went wrong! Please try again.';
                         }
                          
                      }
                      
                      
                  }else{
                        $ModelGroupMember=TblChatgroupMembers::find()->where(['group_id'=>$group_id])->andWhere(['user_id'=>$user_id])->andWhere(['status'=>1])->one();                     
                        $ModelGroupMember->status=2;
                        $ModelGroupMember->updated_by=$user_id;
                        if($ModelGroupMember->save(false)){
                            $ModelGroup=TblChatgroup::find()->where(['id'=>$group_id])->one();    
                            $ModelGroup->updated_at=date('Y-m-d H:i:s');
                            $ModelGroup->updated_by=$user_id;
                            $ModelGroup->deleted_on=date('Y-m-d H:i:s');
                            $ModelGroup->deleted_by=$user_id;
                            $ModelGroup->status=2;
                            $ModelGroup->save(false);
                            $Response['status']=1;
                            $Response['message']='Group deleted successfully!!.';
                        }else{
                             $Response['status']=0;
                             $Response['message']='Something went wrong! Please try again.';
                        }                     
                  }
            }else{
                $Response['status']=3;
                $Response['message']='Unauthorised access!!';
            }
            
            echo json_encode($Response);die;
        }
    }
    
    public function actionRemoveGroupMember(){
        if (Yii::$app->request->post()) {
            $group_id=$_REQUEST['group_id'];
            $member_id=$_REQUEST['member_id'];
            $Member_user_id=$_REQUEST['member_uid'];
            $user_id=$_REQUEST['user_id'];
            $token=$_REQUEST['token'];
            $Response=array();
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            if ($accessStatus['authentication'] === 1){
                
                $GroupMemberModel=TblChatgroupMembers::find()->where(['id'=>$member_id])->andWhere(['group_id'=>$group_id])->andWhere(['user_id'=>$Member_user_id])->andWhere(['status'=>1])->one();
                $GroupMemberModel->status=2;
                $GroupMemberModel->updated_by=$user_id;
                $RemoveUserName=$GroupMemberModel->user_name;
                if($GroupMemberModel->save(false)){
                    
                    $UserModel=User::find()->where(['id'=>$user_id])->one();
                    $RemoverName=$UserModel->name;
                    
                    $MessageModel=new TblChatgroupMessage();
                    $MessageModel->group_id=$group_id;
                    $MessageModel->sender_id=$user_id;
                    $MessageModel->sender_name=$RemoverName;
                    $MessageModel->chat_message=$RemoverName.' removed '.$RemoveUserName.' on '.date('M d,Y',strtotime(date('Y-m-d H:i:s')));
                    $MessageModel->att_status=0;
                    $MessageModel->chat_type=2;
                    $MessageModel->save(false);
                    
                    $Response['status']=1;
                    $Response['message']=$RemoveUserName.' removed successfully.';
                   
                    
                    
                }else{
                $Response['status']=0;
                $Response['message']='Something went wrong! Please try again.';
                }
                
            }else{
                $Response['status']=3;
                $Response['message']='Unauthorised access!!';
            }
            echo json_encode($Response);die;
        }
    }
    
    /*Created By Sanjeev Poonia On Nov 03,2017************************/
    
    
    
    public function actionGetGroupChatDetails(){
        if (Yii::$app->request->post()) {
            $group_id=$_REQUEST['group_id'];
            $user_id=$_REQUEST['user_id'];
            $token=$_REQUEST['token'];
            $Response=array();
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            if ($accessStatus['authentication'] === 1){
                $Response['status']=1;
                $GroupModel=TblChatgroup::find()->where(['id'=>$group_id])->andWhere(['status'=>1])->one();
                $Response['group_name']=str_replace("\\\\", "\\",$GroupModel->name);
                $Response['icon_orignal']=Yii::$app->params['file_url'].$GroupModel->icon_orignal;
                $Response['icon_thumb']=Yii::$app->params['file_url'].$GroupModel->icon_thumb;
                $Response['created_at']=date('M d,Y',strtotime($GroupModel->created_at));
                $CreatedBy=$GroupModel->created_by;
                $Response['created_by']=$CreatedBy;
                $UserModel=User::find()->where(['id'=>$CreatedBy])->one();  
                $Response['creater_name']=$UserModel->name;
                
                $Response['members_list']=array();
                
                $MemberModel=TblChatgroupMembers::find()->where(['group_id'=>$group_id])->andWhere(['status'=>1])->all();
                
                foreach($MemberModel as $mem){
                    
                    $Report=array();
                    $Report['id']=$mem->id;
                    $uID=$mem->user_id;
                    $Report['user_id']=$uID;
                    $Report['user_name']=$mem->user_name;
                    $Report['status']=$mem->status;
                    $Report['is_admin']=$mem->is_admin;
                    $Report['created_by']=$mem->created_by;
                    
                    $UModel=User::find()->where(['id'=>$uID])->one();  
                    
                    $Report['user_image']=Yii::$app->params['file_url'].$UModel->image_name;
                    $Report['user_email']=$UModel->email;
                    array_push($Response['members_list'], $Report);
                }
                
                $AdminModel=TblChatgroupMembers::find()->where(['group_id'=>$group_id])->andWhere(['status'=>1])->andWhere(['is_admin'=>1])->one();
                $Response['admin_id']=$AdminModel->user_id;
                
                
                
                
            }else{
                $Response['status']=3;
            }
            echo json_encode($Response);die;
        }
    }

    
    /********************************Update on Oct 27,2017***********************************/
    
    public function actionGenerateNewGroupChatNotification() {

        if (Yii::$app->request->post()) {
            $messageId = $_POST['message_id'];
            $groupId=$_POST['group_id'];
            $senderId=$_POST['sender_id'];
            $messageModel = TblChatgroupMessage::findOne(['id' => $messageId]);
            $typeArr = [
                'message_id' => $messageModel->id,
                'group_id' => $groupId,
                'sender_id' => $senderId,
                'sender_name' => $messageModel->sender_name,
                'chat_message' => str_replace("\\\\", "\\",$messageModel->chat_message),
                'attach_status' => $messageModel->att_status,
                'chat_type' => $messageModel->chat_type,
                'Body' => $messageModel->sender_name.' send new message',
                'Title' => str_replace("\\\\", "\\",Yii::$app->samparq->getGroupDetailsById('name',$groupId)),
                'date_time' => $messageModel->date_time,
                'group_name' => str_replace("\\\\", "\\",Yii::$app->samparq->getGroupDetailsById('name',$groupId)),
                'process_type' => 'groupchat_type',
            ];

            Yii::$app->samparq->sendChatGroupNotification($groupId,$typeArr,$senderId);
        }
    }
   
    public function actionChatGroupSingleImageMessage(){
         if (Yii::$app->request->post()) {
             $user_id=$_REQUEST['user_id'];
             $token=$_REQUEST['token'];
             $group_id=$_REQUEST['group_id'];
             $OrignalName = $_REQUEST['orignal_name'];
             $AttachmentType = $_REQUEST['attachment_type'];
             $MessageId=$_REQUEST['message_id'];
             $File_Count = (int) $_REQUEST['fileCount'];
             $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
             $Response=array();
             if ($accessStatus['authentication'] === 1){
                 if($AttachmentType=='1'){
                    
                        $Insert_UploadFiles = new TblChatgroupAttach();
                        $pic_name = $_FILES['Filedata']['name'];
                        $tmp_name = $_FILES['Filedata']['tmp_name'];
                        $LocalUrl = Yii::getAlias('@frontend/web/Upload_Files/');
                        $thumbPath = Yii::getAlias('@frontend/web/thumb/');
                        $thumbName = time() . $pic_name;
                        
                        Image::frame($tmp_name, 5, '666', 0)
                            ->rotate(0)
                            ->save($thumbPath . $thumbName, ['quality' => 50]);
                        move_uploaded_file($tmp_name, $LocalUrl . $pic_name);
                        
                        $Insert_UploadFiles->group_id=$group_id;
                        $Insert_UploadFiles->sender_id=$user_id;
                        $Insert_UploadFiles->message_id=$MessageId;
                        $Insert_UploadFiles->orignal_name=$OrignalName;
                        $Insert_UploadFiles->new_name=$pic_name;
                        $Insert_UploadFiles->thumb_name=$thumbName;
                        $Insert_UploadFiles->type=$AttachmentType;
                        
                        if ($Insert_UploadFiles->save(false)) {
                            $AttachCountQuery = TblChatgroupAttach::find()->where(['message_id' => $MessageId])->all();
                            $AttachCount = count($AttachCountQuery);
                            $Response['success'] = 1;
                            $Response['count'] = $AttachCount;
                        } else {
                            $AttachCountQuery = TblChatgroupAttach::find()->where(['message_id' => $MessageId])->all();
                            $AttachCount = count($AttachCountQuery);
                            $Response['success'] = 0;
                            $Response['count'] = $AttachCount;
                        }
                 }else{
                        $Insert_UploadFiles = new TblChatgroupAttach();
                        $pic_name = $_FILES['Filedata']['name'];
                        $tmp_name = $_FILES['Filedata']['tmp_name'];
                        $LocalUrl = Yii::getAlias('@frontend/web/Upload_Files/');
                        move_uploaded_file($tmp_name, $LocalUrl . $pic_name);
                        
                        $Insert_UploadFiles->group_id=$group_id;
                        $Insert_UploadFiles->sender_id=$user_id;
                        $Insert_UploadFiles->message_id=$MessageId;
                        $Insert_UploadFiles->orignal_name=$OrignalName;
                        $Insert_UploadFiles->new_name=$pic_name;
                        $Insert_UploadFiles->thumb_name='';
                        $Insert_UploadFiles->type=$AttachmentType;
                        
                        if ($Insert_UploadFiles->save(false)) {
                            $AttachCountQuery = TblChatgroupAttach::find()->where(['message_id' => $MessageId])->all();
                            $AttachCount = count($AttachCountQuery);
                            $Response['success'] = 1;
                            $Response['count'] = $AttachCount;
                        } else {
                            $AttachCountQuery = TblChatgroupAttach::find()->where(['message_id' => $MessageId])->all();
                            $AttachCount = count($AttachCountQuery);
                            $Response['success'] = 0;
                            $Response['count'] = $AttachCount;
                        }
                 }
             }else{
                 $Response['status']=3;
             }
             echo json_encode($Response);die;
         }
    }
    /********************************Updates on Oct 26,2017********************************/
    
    public function actionChatGroupSendNewTextMessage(){
         if (Yii::$app->request->post()) {
         
             $user_id=$_REQUEST['user_id'];
             $token=$_REQUEST['token'];
             $group_id=$_REQUEST['group_id'];
             $msg=$_REQUEST['message_str'];
             $attach_Status=$_REQUEST['attach_status'];
             $MsgStr = str_replace("\\", "\\\\", $msg);
             $MessageStr = str_replace("'", "\'", $MsgStr);
             $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
             $Response = array();
             if ($accessStatus['authentication'] === 1){
                   
                   $UserModel=User::find()->where(['id'=>$user_id])->one();  
                   $SenderName=$UserModel->name;
                   $MessageModel=new TblChatgroupMessage();
                   $MessageModel->group_id=$group_id;
                   $MessageModel->sender_id=$user_id;
                   $MessageModel->sender_name=$SenderName;
                   $MessageModel->chat_message=$MessageStr;
                   $MessageModel->att_status=$attach_Status;
                   
                   if($MessageModel->save(false)){
                        $Response['status']=1;
                        $Response['message_id']=$MessageModel->id;
                        $typeArr = [
                            'message_id' => $MessageModel->id,
                            'group_id' => $group_id,
                            'sender_id' => $user_id,
                            'sender_name' => $SenderName,
                            'chat_message' => $MessageStr,
                            'attach_status' => $attach_Status,
                            'chat_type' => '1',
                            'Body' => $SenderName.' send new message',
                            'Title' => str_replace("\\\\", "\\",Yii::$app->samparq->getGroupDetailsById('name',$group_id)),
                            'date_time' => date('M d,Y H:i:s A'),
                            'group_name' => str_replace("\\\\", "\\",Yii::$app->samparq->getGroupDetailsById('name',$group_id)),
                            'process_type' => 'groupchat_type',
                        ];

                        Yii::$app->samparq->sendChatGroupNotification($group_id,$typeArr,$user_id);
                   } else {

                        $Response['status']=0;
                   }
             } else {

                 $Response['status']=3;
             }
             echo json_encode($Response);die;
         }
    }
    
    /******************************Updates on Oct 25th,2017*********************************/
    
    public function actionGetGroupAllChat(){
        if (Yii::$app->request->post()) {
            $user_id=$_REQUEST['user_id'];
            $token=$_REQUEST['token'];
            $group_id=$_REQUEST['group_id'];
            $message_limit=$_REQUEST['limit'];
            $Response=array();
            $Response['group_message_list']=array();
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
                if ($accessStatus['authentication'] === 1){

                         $Response['status']=1;
                         $GroupLimitMessageModel=TblChatgroupMessage::find()->where(['group_id'=>$group_id])->andWhere(['status'=>1])->all();
                         $Response['total_message']=count($GroupLimitMessageModel);
                         $GroupMod=TblChatgroup::find()->where(['id'=>$group_id])->andWhere(['status'=>1])->one();
                         $Response['group_name']=str_replace("\\\\", "\\",$GroupMod->name);
                         $Response['group_icon']=Yii::$app->params['file_url'].$GroupMod->icon_thumb;
                         
                         $GroupMessageModel=TblChatgroupMessage::find()->where(['group_id'=>$group_id])->andWhere(['status'=>1])->limit($message_limit)->orderBy(['id' => SORT_DESC])->all();

                         foreach($GroupMessageModel as $gp_me){
                             $Report=array();
                             $AttachStatus=$gp_me->att_status;
                             $SenderId= $gp_me->sender_id;
                             $MeId=$gp_me->id;
                             $Report['id']=$MeId;
                             $Report['group_id']=$gp_me->group_id;
                             $Report['sender_id']=$SenderId;
                             $Report['sender_name']=$gp_me->sender_name;
                             $Report['chat_message']=str_replace("\\\\", "\\",$gp_me->chat_message);
                             $Report['att_status']=$AttachStatus;
                             $Report['chat_type']=$gp_me->chat_type;
                             $Report['date_time']=  date('M d,Y h:i A', strtotime($gp_me->date_time)); 
                             $Report['attach_list']=array();
                             /*****************Attachment List*************************************/
                             
                             if($AttachStatus==1){
                                 $AttachModel=TblChatgroupAttach::find()->where(['group_id'=>$group_id])->andWhere(['message_id'=>$MeId])->andWhere(['status'=>1])->all();
                                 foreach($AttachModel as $att){
                                     $re=array();
                                     $re['id']=$att->id;
                                     $re['group_id']=$att->group_id;
                                     $re['sender_id']=$att->sender_id;
                                     $re['message_id']=$att->message_id;
                                     $re['orignal_name']=$att->orignal_name;
                                     $re['new_name']=$att->new_name;
                                     $re['thumb_name']=$att->thumb_name;
                                     $re['date_time']=$att->date_time;
                                     $re['status']=$att->status;
                                     $re['type']=$att->type;
                                     $re['att_path']=Yii::$app->params['file_url'];
                                     $re['thumb_path']=Yii::$app->params['thumb_url'];
                                     array_push( $Report['attach_list'], $re);
                                 }
                             }
                             array_push($Response['group_message_list'], $Report);
                         }

                } else {
                    $Response['status']=3;
                }
            echo json_encode($Response);die;
        }
    }
    
    /***************************Updates on Oct 24,2017***************************************/
    
    public function actionCreateDynamicChatgroup(){
          if (Yii::$app->request->post()) {
               $User_id=$_REQUEST['user_id'];
               $token=$_REQUEST['token'];
               $msg=$_REQUEST['group_title'];
               $MsgStr = str_replace("\\", "\\\\", $msg);
               $GroupTitle = str_replace("'", "\'", $MsgStr);
               
               $GroImage=$_REQUEST["group_image"];
               $GroupParticipants=$_REQUEST['group_participant'];
               $imageName='';
               if($GroImage!=''){
                    $timeStamp = time();
                    $imageName = $timeStamp . $User_id . ".jpeg";
               }
               
               $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
                if ($accessStatus['authentication'] === 1){
                    
                    $GroupModel= new TblChatgroup();
                    $GroupModel->name=$GroupTitle;
                    $GroupModel->created_by=$User_id;
                    $GroupModel->icon_thumb = $imageName;
                    $GroupModel->icon_orignal = $imageName;
                    if ($GroupModel->save(false)){
                       
                        if($GroImage!=''){
                            $GroupImage=base64_decode($GroImage);
                            $imgFile = fopen("Upload_Files/" . $imageName, 'w');
                            fwrite($imgFile, $GroupImage);
                            fclose($imgFile);
                        }
                        $GroupId=$GroupModel->id;
                        
                        
                        $To_Array = array();
                        $To_Array = explode(',', $GroupParticipants);
                        for ($i = 0; $i < sizeof($To_Array); $i++) {
                            $to_jisd = $To_Array[$i];
                          
                            $UserModel=User::find()->where(['id'=>$to_jisd])->one();
                            $username=$UserModel->name;
                            
                            
                            $GroupMemberModel=new TblChatgroupMembers();
                            $GroupMemberModel->group_id=$GroupId;
                            $GroupMemberModel->user_id=$to_jisd;
                            $GroupMemberModel->user_name=$username;
                            $GroupMemberModel->created_by=$User_id;
                            $GroupMemberModel->save(false);
                            
                        }
                        
                        
                        $AdminModel=TblChatgroupMembers::find()->where(['group_id'=>$GroupId])->andWhere(['user_id'=>$User_id])->one();
                        $AdminModel->is_admin=1;
                        $AdminModel->save(false);
                        
                        $AdUserModel=User::find()->where(['id'=>$User_id])->one();
                        $Adusername=$AdUserModel->name;
                        $ChatMessage=new TblChatgroupMessage();
                        $ChatMessage->group_id=$GroupId;
                        $ChatMessage->sender_id=$User_id;
                        $ChatMessage->sender_name=$Adusername;
                        $ChatMessage->chat_message=$Adusername.' Create a new group';
                        $ChatMessage->chat_type=2;
                        $ChatMessage->save(false);
                        
                        $Report=array();
                        $Report['status']=1;
                        $Report['message']='Group created successfully.';
                        echo json_encode($Report);die;
                        
                        
                        
                    }else{
                        $Report=array();
                        $Report['status']=0;
                        $Report['message']='Error!! Please try again later.';
                        echo json_encode($Report);die;
                    }
                    
                }else{
                    $Report=array();
                    $Report['status']=3;
                    $Report['message']='Error!! Unauthorise user.';
                    echo json_encode($Report);die;
                }
               
          }
    }
    
    /***************************Updates on Sep 28,2017***************************************/
    
    public function actionGetTrainingCount(){
            if (Yii::$app->request->post()) {
                 $User_id=$_REQUEST['user_id'];
                 $token=$_REQUEST['token'];
                 $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
                  if ($accessStatus['authentication'] === 1){
                      $TrainingCount=Trainees::find()->where(['user_id'=>$User_id])->andWhere(['status'=>1])->orderBy(['id' => SORT_DESC])->all();
                      
                      $Report=array();
                      $Report['training_count']=count($TrainingCount);
                      $Report['status']=1;
                      echo json_encode($Report);die;
                  }else{
                      $Report=array();
                      $Report['status']=3;
                      echo json_encode($Report);die;
                  }
                  
                 
            }
    }

    public function actionUpdateNotificationStatus(){
        if (Yii::$app->request->post()) {
             $User_id=$_REQUEST['user_id'];
             $token=$_REQUEST['token'];
              $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
              if ($accessStatus['authentication'] === 1){
                   $NotificationModel=TrainingNotification::find()->where(['user_id'=>$User_id])->andWhere(['read_status'=>0])->andWhere(['status'=>1])->all();
                   foreach($NotificationModel as $now){
                       $id=$now->id;
                       $noti=TrainingNotification::find()->where(['id'=>$id])->one();
                       $noti->read_status=1;
                       if($noti->save(false)){
                       echo 'saved';
                   }
                   }
                   
                   
                   
              }
        }
    }
    public function actionGetTrainingDetails(){
        if (Yii::$app->request->post()) {
            $User_id=$_REQUEST['user_id'];
            $TrainingId=$_REQUEST['training_id'];
            $token=$_REQUEST['token'];
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            $Response=array();
            $linkArr = [];
             $Response['video_list']=array();
             $Response['pdf_list']=array();
            if ($accessStatus['authentication'] === 1){
                 $Response['status']=1;
                 $Training_Module=Training::find()->where(['id'=>$TrainingId])->one();


                 if(!empty($Training_Module)){
                     $Response['trainer_name']=$Training_Module->trainer_name;
                     $Response['training_title']=$Training_Module->training_title;
                     $Response['training_image']= empty($Training_Module->file_new_name) === true? Yii::$app->params['file_url']."training_header_image.jpeg":Yii::$app->params['file_url'].$Training_Module->file_new_name;
                     $Response['description']=$Training_Module->description;
                     $Response['start_date']=$Training_Module->start_date;
                     $Response['end_date']=$Training_Module->end_date;
                     $Response['status']=$Training_Module->status;
                     $Response['training_type']=$Training_Module->training_type;
                     $Response['show_result'] = empty($Training_Module->show_result) ? 0 : $Training_Module->show_result;
                     if(!empty($Training_Module->youtube_url)){
                         $linkArr = @explode(',',$Training_Module->youtube_url);
                         foreach ($linkArr as $key =>  $link){
                             $linkList[] = [
                                 'link' => "https://www.youtube.com/embed/".$link."?modestbranding=0&autoplay=1&vq=large&refl=2&wmode=opaque&amp;rel=0&amp;autohide=1&amp;showinfo=0&amp;wmode=transparent"
                             ];
                         }

                         $Response['youtube_links'] = $linkList;

                     } else {

                         $Response['youtube_links'] = [];

                     }

                     $TrainingVideoModule=TrainingMaterial::find()->where(['training_id'=>$TrainingId])->andWhere(['type'=>1])->all();
                     $TrainingPdfModule=TrainingMaterial::find()->where(['training_id'=>$TrainingId])->andWhere(['type'=>0])->all();
                     $StatusModule=Trainees::find()->where(['training_id'=>$TrainingId])->andWhere(['user_id'=>$User_id])->andWhere(['!=','status',0])->one();

                     if(!empty($TrainingVideoModule)){

                         foreach($TrainingVideoModule as $video){
                             $Report=array();
                             $Report['original_name']=$video->original_name;
                             $Report['new_name']=$video->new_name;
                             $Report['type']=$video->type;
                             $Report['created_at']=$video->created_at;
                             $Report['path']=Yii::$app->params['file_url'];
                             array_push($Response['video_list'], $Report);
                         }
                     }

                     if(!empty($TrainingPdfModule)){
                         foreach($TrainingPdfModule as $pdf){
                             $Report1=array();
                             $Report1['original_name']=$pdf->original_name;
                             $Report1['new_name']=$pdf->new_name;
                             $Report1['type']=$pdf->type;
                             $Report1['created_at']=$pdf->created_at;
                             $Report1['path']=Yii::$app->params['file_url'];
                             array_push($Response['pdf_list'], $Report1);
                         }
                     }

                     $Response['training_status']=$StatusModule->status;

                 }
                 
            }else{
                $Response['status']=3;
            }
            echo json_encode($Response);die;
        }
    }
    
    public function actionGetTrainingSet(){
        if (Yii::$app->request->post()) {
            $token = $_REQUEST['token'];
            $option_ids = [];
            $option_values = [];
            $tid = $_REQUEST['tid'];
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            $uid = $_REQUEST['uid'];
            if ($accessStatus['authentication'] === 1) {
                $trainingModel = Training::findOne(['id' => $tid]);
                if(Yii::$app->samparq->checkDisability($trainingModel->end_date, 2) === false){

                    if(Yii::$app->samparq->getTrainingStartDate($trainingModel->start_date,true) <= 3600 && Yii::$app->samparq->getTrainingStartDate($trainingModel->end_date,true) >= 0){
                        $models = TrainingQuestion::findAll(['training_id' => $tid]);

                        if(empty($models)>0){
                            $response = [
                                'status' => 1,
                                'message' => 'Questions not set for this training',
                                'data' => [],
                            ];
                        } else {

                            foreach ($models as $key => $model){
                                $optionModel = Options::findOne(['tquestion_id' => $model->id, 'is_answer' => 1]);



                                $getOptions = Options::findAll(['tquestion_id' => $model->id]);
                                $traineeModel = Trainees::findOne(['training_id' => $tid,'user_id' => $uid ]);


                                $traineeModel->training_sd = date("Y-m-d H:i:s");
                                $traineeModel->save(false);


                                if(!empty($getOptions)){
                                    foreach ($getOptions as $go){
                                        $options[] = [
                                            'key' => $go->id,
                                            'value' => $go->option_value
                                        ];
                                    }
                                }

                                $submissionModel = TrainingSubmission::findOne(['training_id' => $tid, 'question_id' => $model->id, 'training_submitted_by' => $uid]);

                                $data[] = [
                                    "quid" => $model->id,
                                    "question_type" => $model->type,
                                    "question_text" => $model->question,
                                    "is_required" => $model->is_required,
                                    "correct_answer" => empty($optionModel)? '' : $optionModel->id,
                                    "negative_mark" => $model->negative_mark,
                                    "positive_marks" => $model->marks,
                                    "answer_given" => empty($submissionModel) === false ? empty($submissionModel->option_id) ? $submissionModel->comment_box: $submissionModel->option_id :"",
                                    "options" => !empty($getOptions) ? $options : []
                                ];

                                $options = [];
                            }


                            $response = [
                                "assessment_type" => $trainingModel->assessment_type,
                                "status" => 1,
                                "data" => $data

                            ];


                        }
                    } else {

                        $response = [
                            'status' => 0,
                            'message' => 'Test will start on '. Yii::$app->samparq->getTrainingStartDate($trainingModel->start_date, false)
                        ];

                    }
                } else {
                    $response = [
                        'status' => 0,
                        'message' => "Training had been expired on ". date('m d Y, h:i:s A', strtotime($trainingModel->end_date))
                    ];
                }



            } else {
                $response = [
                    'status' => 3,
                    'message' => 'unauthorised access'
                ];

            }
        }

        echo json_encode($response);
        die;


    }

    public function actionSubmitAssessment(){
        if (Yii::$app->request->post()) {
            $postArr = [
                "token" => isset($_POST['token']) && !empty($_POST['token'])?$_POST['token']:null,
                "tid" => isset($_POST['tid']) && !empty($_POST['tid'])?$_POST['tid']:null,
                "qid" => isset($_POST['qid']) && !empty($_POST['qid'])?$_POST['qid']:null,
                "uid" => isset($_POST['uid']) && !empty($_POST['uid'])?$_POST['uid']:null,
                ];
            foreach ($postArr as $key =>  $postVal){
                if(empty($postVal)){
                    $error[] = $key;
                }
            }

            if(!empty($error)){
                $response = [
                    'status' => 0,
                    'message' => 'required fields are missing',
                    'required_fields' => $error
                ];
            }else {
                $token = $_POST['token'];
                $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
                if ($accessStatus['authentication'] === 1) {
                    $tid = $_POST['tid'];
                    $uid = $_POST['uid'];
                    $getTrainees = Trainees::findOne(['training_id' => $tid, 'user_id' => $uid]);
                    $getCurrentTime = date("Y-m-d H:i:s");
                    $getTimeDiff = strtotime($getCurrentTime) - strtotime($getTrainees->training_sd);
                    if($getTimeDiff>=2100){
                        $response = [
                            'status' => 4,
                            'message' => 'Time Up!',
                        ];
                    } else {
                        $qid = $_POST['qid'];
                        $oid = $_POST['oid'];
                        $comment = $_POST['comment'];
                        $checkIfRecordExist = TrainingSubmission::find()->where(['training_id' => $tid,'question_id' => $qid,'training_submitted_by'=>$uid])->count();
                        if($checkIfRecordExist>0){
                            $assessmentModel = TrainingSubmission::findOne(['training_id' => $tid,'question_id' => $qid,'training_submitted_by'=>$uid]);
                        } else {
                            $assessmentModel = new TrainingSubmission();
                        }
                        $assessmentModel->training_id = $tid;
                        $assessmentModel->question_id = $qid;
                        if(empty($comment)){
                            $assessmentModel->option_id = $oid;
                        } else {
                            $assessmentModel->comment_box = $comment;
                        }
                        $assessmentModel->training_submitted_by = $uid;


                        if($assessmentModel->save(false)){
                            $getTrainees->training_ed = date("Y-m-d H:i:s");
                            $getTrainees->save(false);

                            $response = [
                                'status' =>1,
                                'message' => 'success',
                            ];
                        } else {
                            $response = [
                                'status' =>0,
                                'message' => 'something went wrong please try again later!'
                            ];
                        }

                    }

                } else {
                    $response = [
                        'status' => 3,
                        'message' => 'unauthorised access'
                    ];

                }
            }

        }

        echo json_encode($response);
        die;


    }

    public function actionCompleteAssessment(){
        if (Yii::$app->request->post()) {
            $postArr = [
                "token" => isset($_POST['token']) && !empty($_POST['token'])?$_POST['token']:null,
                "tid" => isset($_POST['tid']) && !empty($_POST['tid'])?$_POST['tid']:null,
                "uid" => isset($_POST['uid']) && !empty($_POST['uid'])?$_POST['uid']:null,
            ];
            foreach ($postArr as $key =>  $postVal){
                if(empty($postVal)){
                    $error[] = $key;
                }
            }

            if(!empty($error)){
                $response = [
                    'status' => 0,
                    'message' => 'required fields are missing',
                    'required_fields' => $error
                ];
            }else {
                $token = $_POST['token'];
                $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
                if ($accessStatus['authentication'] === 1) {
                    $tid = $_POST['tid'];
                    $uid = $_POST['uid'];
                    $model = Trainees::findOne(['user_id' => $uid, 'training_id' => $tid]);
                    if(count($model)>0){
                        $model->status = 2;
                        $model->training_ed = date("Y-m-d H:i:s");
                        if($model->save(false)){

                            $response = [
                                'status' => 1,
                                'data' => Yii::$app->samparq->getTraineeResult($tid, $uid),
                                'message' => 'Training submitted successfully',
                                'show_result' => Training::findOne(['id' => $tid])->show_result
                            ];
                        } else {
                            $response = [
                                'status' => 0,
                                'data' => [],
                                'message' => 'something went wrong please try again later!'
                            ];
                        }
                    } else {
                        $response = [
                            'status' => 1,
                            'data' => [],
                            'message' => 'user record not found'
                        ];
                    }

                } else {
                    $response = [
                        'status' => 3,
                        'message' => 'unauthorised access'
                    ];

                }
            }

        }

        echo json_encode($response);
        die;


    }

    public function actionGetTraineeResult(){
        if (Yii::$app->request->post()) {
            $postArr = [
                "token" => isset($_POST['token']) && !empty($_POST['token'])?$_POST['token']:null,
                "tid" => isset($_POST['tid']) && !empty($_POST['tid'])?$_POST['tid']:null,
                "uid" => isset($_POST['uid']) && !empty($_POST['uid'])?$_POST['uid']:null,
            ];
            foreach ($postArr as $key =>  $postVal){
                if(empty($postVal)){
                    $error[] = $key;
                }
            }

            if(!empty($error)){
                $response = [
                    'status' => 0,
                    'message' => 'required fields are missing',
                    'required_fields' => $error
                ];
            }else {
                $token = $_POST['token'];
                $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
                if ($accessStatus['authentication'] === 1) {
                    $tid = $_POST['tid'];
                    $uid = $_POST['uid'];
                    $response = [
                        'status' => 1,
                        'data' => Yii::$app->samparq->getTraineeResult($tid, $uid)
                    ];

                } else {
                    $response = [
                        'status' => 3,
                        'message' => 'unauthorised access'
                    ];

                }
            }

        }

        echo json_encode($response);
        die;


    }
    /**************************API Update on Sep 22,2017************************************/
    public function actionGetUserTrainingList(){
        if (Yii::$app->request->post()){
            $User_id=$_REQUEST['user_id'];
            $token=$_REQUEST['token'];
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            $Response=array();
            $Response['training_list']=array();
             $Response['notification_list']=array();
            if ($accessStatus['authentication'] === 1){
                 $Response['status']=1;
                 $Trainees_Model=Trainees::find()->where(['user_id'=>$User_id])->andWhere(['!=','status',0])->orderBy(['id' => SORT_DESC])->all();
                 
                 foreach($Trainees_Model as $trne){
                     $Report=array();
                     $trnStatus=$trne->status;
                     $TrnID=$trne->training_id;
                     $Report['tr_status']=$trnStatus;
                     $Report['training_id']=$TrnID;
                     $Training_Module=Training::find()->where(['id'=>$TrnID])->andWhere(['!=','status',0])->one();
                     if(!empty($Training_Module)){
                        $Report['Trainer_Name']=$Training_Module->trainer_name;
                        $Report['training_title']=$Training_Module->training_title;
                        $Report['training_image']= empty($Training_Module->file_new_name) === true? Yii::$app->params['file_url']."training_header_image.jpeg":Yii::$app->params['file_url'].$Training_Module->file_new_name;
                        $Report['start_date']=$Training_Module->start_date;
                        $Report['end_date']=$Training_Module->end_date;
                        $Report['created_at']=$Training_Module->created_at;
                        array_push($Response['training_list'], $Report);   
                     }
                 } 
                 $NotificationModel=TrainingNotification::find()->where(['user_id'=>$User_id])->andWhere(['read_status'=>0])->andWhere(['status'=>1])->all();
                 foreach($NotificationModel as $NotiM){
                     $Report1=array();
                     $Report1['id']=$NotiM->id;
                     $Report1['title']=$NotiM->title;
                     $Report1['description']=$NotiM->description;
                     $Report1['read_status']=$NotiM->read_status;
                     $Report1['status']=$NotiM->status;
                     $Report1['created_at']=$NotiM->created_at;
                     $Report1['created_by']=$NotiM->created_by;
                     array_push($Response['notification_list'], $Report1);
                     
                 }
                 
                 
            }else{
                $Response['status']=3;
            }
            echo json_encode($Response);die;
        }
    }
    /***************************************************************************************/
    /***************************API Update On Sep 20,2017*********************************/
    
    public function actionGetAllLikeNameList(){
         if (Yii::$app->request->post()) {
             $PostId=$_REQUEST['post_id']; 
             $token = $_REQUEST['token'];
             $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
             $Response=array();
             $Response['Liked_List']=array();
             if ($accessStatus['authentication'] === 1){
                $Response['status']=1;
                
                $LikeModel=TblPostLike::find()->where(['post_id'=>$PostId])->andWhere(['like_status'=>1])->orderBy(['id' => SORT_DESC])->all();
                foreach ($LikeModel as $row){
                    $Report=array();               
                    $SenderId = $row->like_userid;
                    $UserModel = User::find()->where(['id' => $SenderId])->one();
                    $Report['like_sender_image'] = $UserModel->image_path . $UserModel->image_name;                  
                    $Report['id']=$row->id;
                    $Report['post_id']=$row->post_id;
                    $Report['like_userid']=$row->like_userid;
                    $Report['like_username']=$row->like_username;
                    $Report['like_status']=$row->like_status;
                    $Report['like_date']=$row->like_date;
                    array_push($Response['Liked_List'], $Report);
                }
                
             }else{
                $Response['status']=3; 
             }
             echo json_encode($Response);die;
         }
    }
    
    public function actionGetAllLikeNameListOnAttachment(){
         if (Yii::$app->request->post()) {
             $PostId=$_REQUEST['post_id']; 
             $AttachId=$_REQUEST['attach_id'];
             $token = $_REQUEST['token'];
             $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
             $Response=array();
             $Response['Liked_List']=array();
             if ($accessStatus['authentication'] === 1){
                $Response['status']=1;
                
                $LikeModel=TblAttachLike::find()->where(['post_id'=>$PostId])->andWhere(['attach_id'=>$AttachId])->andWhere(['like_status'=>1])->orderBy(['id' => SORT_DESC])->all();
                foreach ($LikeModel as $row){
                    $Report=array();               
                    $SenderId = $row->like_userid;
                    $UserModel = User::find()->where(['id' => $SenderId])->one();
                    $Report['like_sender_image'] = $UserModel->image_path . $UserModel->image_name;                  
                    $Report['id']=$row->id;
                    $Report['attach_id']=$row->attach_id;
                    $Report['post_id']=$row->post_id;
                    $Report['like_userid']=$row->like_userid;
                    $Report['like_username']=$row->like_username;
                    $Report['like_status']=$row->like_status;
                    $Report['like_date']=$row->like_date;
                    array_push($Response['Liked_List'], $Report);
                }
                
             } else {
                $Response['status']=3; 
             }
             echo json_encode($Response);die;
         }
    }
    /************************************************************/
    public function actionCheckUpdateInstallation(){
            if (Yii::$app->request->post()) {
                    $VersionCode=$_REQUEST['version_code'];
                    $UpdateModel=AppUpdates::find()->where(['status'=>1])->one();
                    $Oversion=$UpdateModel->version_code;
                    if($Oversion>$VersionCode){
                            $Response=array();
                            $Response['update']=1;
                            echo json_encode($Response);die;
                    }else{
                            $Response=array();
                            $Response['update']=0;
                            echo json_encode($Response);die;
                    }
            }
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
                            'notification' => array('title' => $Receiver_name, 'body' => 'Send new message','sound'=>'default'),
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
                        'notification' => array('title' => $Receiver_name, 'body' => 'Send new message','sound'=>'default'),
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
            $Response['groups_list']=array();
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

                /******************Changes on 24th Oct,2017*************************************/
                $gids = [];
                $ChatGroupModel=TblChatgroupMembers::find()->select("group_id")->where(['user_id'=>$Receiver_id])->andWhere(['status'=>1])->all();

                foreach ($ChatGroupModel as $tt){
                    $rr = [];
                    $rr[] = $tt->group_id;
                    $gids = array_merge($rr, $gids);
                }

                $GetGroupModel=TblChatgroup::find()->where(['id'=>$gids])->andWhere(['status'=>1])->orderBy("name ASC")->all();

                if(!empty($GetGroupModel)){
                    foreach ($GetGroupModel as $gp){

                        $GReport=array();
                        $Group_Id=$gp->id;
                        $GReport['id']=$Group_Id;
                        
                        $GReport['name']=str_replace("\\\\", "\\",$gp->name);
                        $GReport['icon_orignal']=Yii::$app->params['file_url'].$gp->icon_orignal;
                        $GReport['icon_thumb']=Yii::$app->params['file_url'].$gp->icon_thumb;
                        $GReport['created_at']=$gp->created_at;
                        $GReport['created_by']=$gp->created_by;
                        $GReport['read_count'] = Yii::$app->samparq->getGroupCount('chat', $Group_Id, $Receiver_id);
                        $GroupMsgModel=TblChatgroupMessage::find()->where(['group_id'=>$Group_Id])->andWhere(['status'=>1])->orderBy("id DESC")->one();
                        $AttachStatus=$GroupMsgModel->att_status;
                        if($AttachStatus==1){
                            $GReport['message_str']='Attachment';
                        }else{
                            $GReport['message_str']=str_replace("\\\\", "\\",$GroupMsgModel->chat_message);
                        }
                        
                        array_push($Response['groups_list'], $GReport);
                    }
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
                                'notification' => array('title' => $TitleStr, 'body' => 'Created new policy','sound'=>'default'),
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
                        $Report['comment_text'] = str_replace("\\\\", "\\", $LastComment->comment_text);
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
                    'notification' => array('title' => $SenderName, 'body' => 'Added new post','sound'=>'default'),
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
        $userModel = User::find()->orderBy('id DESC')->all();

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
                $Report['birthday'] = date("d M",  strtotime($UserModel->dob));
                $Report['emp_id'] = $UserModel->employee_id;

                $Report['gender'] = $UserModel->gender;
                $Report['joining_date'] = $UserModel->joining_date;
                $Report['branch'] = $UserModel->branch;
                $Report['department'] = $UserModel->department;
                $Report['designation'] = $UserModel->designation;
                $Report['reporting_manager'] = $UserModel->reporting_in_charge1;
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
            $msg = $_REQUEST['comment_text'];           
            /****************Changes On Sep 21,2017******************/
            $MsgStr = str_replace("\\", "\\\\", $msg);
            $CommentText = str_replace("'", "\'", $MsgStr);
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
                    $Report['comment_text'] = str_replace("\\\\", "\\",$row->comment_text);
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
            //$CommentText = $_REQUEST['comment_text'];
            
            $msg = $_REQUEST['comment_text'];           
            /****************Changes On Sep 21,2017******************/
            $MsgStr = str_replace("\\", "\\\\", $msg);
            $CommentText = str_replace("'", "\'", $MsgStr);
            
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
                    $Report['comment_text'] = str_replace("\\\\", "\\",$row->comment_text);
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
                        $Report['comment_text'] =str_replace("\\\\", "\\", $LastComment->comment_text);
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
                        $Report['comment_text'] = str_replace("\\\\", "\\",$LastComment->comment_text);
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
            $name = ucwords(strtolower($_REQUEST['name_str']));
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

                $response = ['status' => 1, 'message' => 'Successfully logged in'];
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
                                'access_token' => $accessToken,
                                'gender' => $user->gender,
                                'joining_date' => $user->joining_date,
                                'branch' => $user->branch,
                                'department' => $user->department,
                                'designation' => $user->designation,
                                'report_in_charge_first' => $user->reporting_in_charge1,
                                'report_in_charge_second' => $user->reporting_in_charge2,
                                'sync_status' => $user->sync_status,


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
                                    'access_token' => $accessToken,
                                    'gender' => $user->gender,
                                    'joining_date' => $user->joining_date,
                                    'branch' => $user->branch,
                                    'department' => $user->department,
                                    'designation' => $user->designation,
                                    'report_in_charge_first' => $user->reporting_in_charge1,
                                    'report_in_charge_second' => $user->reporting_in_charge2,
                                    'sync_status' => $user->sync_status,
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

    public function actionSendMail() {

        $mail = Yii::$app->mailer->compose()
                ->setTo('hemendra.singh@qdegrees.com')
                ->setFrom('samparq@qdegrees.com')
                ->setSubject('HR added new post')
                ->setHtmlBody('HR added new post. Please login to samparq.');
        $mail->send();
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

                    Yii::$app->samparq->createLoginLog($model->username,$model->id,false,false,date('Y-m-d H:i:s'));

                    if ($timeDiff >= (86400 * 45)) {
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


    public function actionRenderSurveyForm($tid,$current,$uid){

        $encryptedTid = Yii::$app->utility->encryptUserData($tid);
        $decryptedTid = Yii::$app->utility->decryptUserData($tid);
        $decryptedCurrent = Yii::$app->utility->decryptUserData($current);
        $userId = Yii::$app->utility->decryptUserData($uid);
        $modelQuestion = TrainingQuestion::findAll(['training_id' => $decryptedTid]);

        $i = 0;

        foreach ($modelQuestion as $key => $question){
            $i++;
            $questionSet[] = [
                'count' => $i,
                'tid' => $decryptedTid,
                'id' => $question->id,
                'Question' => $question->question,
                'options' => Yii::$app->samparq->getOptions($question->id)
            ];
        }


        if(empty(Yii::$app->samparq->createQuestionPagination($decryptedCurrent, $questionSet))){
            return $this->render('completed');
        }


        $submissionModel = new TrainingSubmission();

        if($submissionModel->load(Yii::$app->request->post())){
            $submissionModel->training_submitted_by = Yii::$app->utility->decryptUserData($uid);
            if($submissionModel->save(false)){

                return $this->redirect(['render-survey-form', 'tid' => strlen($tid) > 5 ? $tid:$encryptedTid, 'current' => Yii::$app->utility->encryptUserData(Yii::$app->utility->decryptUserData($_GET['current']) + 1)]);
            }
        }
        return $this->render('survey-form', [
            'model' => $submissionModel,
            'modelQuestion' => $modelQuestion,
            'trainingTitle' => Yii::$app->samparq->getTrainingTitle($decryptedTid),
            'questions' => Yii::$app->samparq->createQuestionPagination($decryptedCurrent, $questionSet),
        ]);
    }

    public function actionWebcast(){
        if (Yii::$app->request->post()) {
            $token = $_REQUEST['token'];

            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            if ($accessStatus['authentication'] === 1) {
                $model = Webcast::findOne(['id' => '1']);
                if(empty($model)){
                    $response = [
                        'status' => 0,
                        'message' => 'record not found'
                    ];
                } else {
                    $response = [
                        'status' => 1,
                        'message' => 'success',
                        'webcast_status' => $model->status,
                        'live_status' => $model->live_status,
                        'webcast_link' => 'https://www.youtube.com/embed/'.$model->url.'?modestbranding=0&autoplay=1&vq=large&refl=2&wmode=opaque&amp;rel=0&amp;autohide=1&amp;showinfo=0&amp;wmode=transparent',
                    ];
                }
            } else {
                $response = [
                    'status' => 3,
                    'message' => 'Access denied'
                ];

            }
        }

        echo json_encode($response);
        die;
    }


    public function actionWebcastQuestion(){
        if (Yii::$app->request->post()) {
            $token = $_REQUEST['token'];
            $currentDt = date("Y-m-d H:i:s");
            $name = $_POST['name'];
            $regid = $_POST['regid'];
            $query = $_POST['query'];
            $location = $_POST['location'];
            $phone = $_POST['phone'];
            $email_id = $_POST['email_id'];
            $query_type = $_POST['query_type'];
            $empid = $_POST['empid'];
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            if ($accessStatus['authentication'] === 1) {

                $model = new WebcastQueries();
                $modelQuery = new WebcastQuery();
                $model->created_time = $currentDt;
                $modelQuery->created_time = $currentDt;
                $model->webcast_id = 1;
                $model->name = $name;
                $modelQuery->name = $name;
                $model->regid = $regid;
                $modelQuery->regid = $regid;
                $model->query = $query;
                $modelQuery->query = $query;
                $model->location = $location;
                $modelQuery->location = $location;
                $model->phone = $phone;
                $modelQuery->phone = $phone;
                $model->email_id = $email_id;
                $modelQuery->email_id = $email_id;
                $modelQuery->query_from = "Samparq";
                $model->query_type = $query_type;
                $modelQuery->query_type = $query_type;
                $model->empid = $empid;
                $modelQuery->empid = $empid;
                if($model->save(false) && $modelQuery->save(false)){
                    $response = [
                        'status' => 1,
                        'message' => 'Query submitted successfully'
                    ];
                } else {
                    $response = [
                        'status' => 0,
                        'message' => 'Something went wrong please try again later!'
                    ];
                }
            } else {
                $response = [
                    'status' => 3,
                    'message' => 'unauthorised access'
                ];

            }
        }

        echo json_encode($response);
        die;
    }

    public function actionWebcastViewers(){
        if (Yii::$app->request->post()) {
            $token = $_REQUEST['token'];
            $currentDt = date("Y-m-d H:i:s");
            $userid = $_POST['uid'];
            $status = $_POST['view_status'];
            $accessStatus = Yii::$app->utility->authenticateAccessToken($token);
            if ($accessStatus['authentication'] === 1) {
                $checkIfRecordExist = WebcastViewers::find()->where(['!=','view_status', 3])->andWhere(['user_id' => $userid])->one();
                $webcast = Webcast::find()->one();
                if(empty($checkIfRecordExist)){
                    $model = new WebcastViewers();
                    $model->start_date = $currentDt;
                } else {
                    $model = $checkIfRecordExist;
                    if($status == "0"){
                        $model->end_date = $currentDt;
                        $totalTime = (strtotime($model->end_date) - strtotime($model->start_date))/60;
                        $model->total = round($totalTime,2);
                    }

                }
                $model->user_id = $userid;
                $model->webcast_id = 1;
                $model->view_status = $status;

                if($webcast->live_status != 0){

                    if($model->save(false)){
                        $response = [
                            'status' => 1,
                            'message' => 'success'
                        ];
                    } else {
                        $response = [
                            'status' => 0,
                            'message' => 'Something went wrong please try again later!'
                        ];
                    }
                } else {

                    $response = [
                        'status' => 0,
                        'message' => 'Webcast has been completed!'
                    ];
                }

            } else {
                $response = [
                    'status' => 3,
                    'message' => 'unauthorised access'
                ];

            }
        }

        echo json_encode($response);
        die;
    }

    public function actionUpdateReadStatus(){
        if(Yii::$app->request->post()){
            $error = [];
            $model = Yii::$app->request->post('model');
            $mid = Yii::$app->request->post('mid');
            $uid = Yii::$app->request->post('uid');

            if($model === "chat"){
                $readModel = TblChatgroupReadstatus::findAll(['user_id' => $uid, 'group_id' => $mid]);
            } else  {
                $readModel = TblFeedbackReadstatus::findAll(['user_id' => $uid, 'feedback_id' => $mid]);
            }

            if(empty($readModel)){
                $response = [
                    'status' => false,
                    'message' => 'Invalid request'
                ];
            } else {
                foreach ($readModel as $model){
                    $model->status = 1;
                    if(!$model->save(false)){
                        $error = $model->errors;
                    }
                }


                if(empty($error)){
                    $response = [
                        'status' => true,
                        'message' => 'Success'
                    ];

                } else {
                    $response = [
                        'status' => false,
                        'message' => 'something went wrong, please try again later',
                        'error' => $error
                    ];
                }
            }


        } else {
            $response = [
                'status' => false,
                'message' => 'Request denied'
            ];
        }

        echo Json::encode($response);
        die;

    }


    public function actionSaveChatGroupImage(){
        $pic_name = $_FILES['file']['name'];
        $tmp_name = $_FILES['file']['tmp_name'];
        $LocalUrl = Yii::getAlias('@frontend/web/Upload_Files/');

        if($_POST['attachmentType'] == 1){
            $thumbPath = Yii::getAlias('@frontend/web/thumb/');
            $thumbName = time() . $pic_name;
            Image::frame($tmp_name, 5, '666', 0)->rotate(0)->save($thumbPath . $thumbName, ['quality' => 50]);
            move_uploaded_file($tmp_name, $LocalUrl . $pic_name);
            $response = [
                'thumb_name' => $thumbName
            ];
        } else {
            move_uploaded_file($tmp_name, $LocalUrl . $pic_name);
            $response = [
                'thumb_name' => ''
            ];
        }

        echo Json::encode($response);


    }


    public function actionSaveChatImage(){
        $pic_name = $_FILES['Filedata']['name'];
        $tmp_name = $_FILES['Filedata']['tmp_name'];
        $fname = Yii::getAlias('@frontend/web/Chat_Files/') . $pic_name;
        if(move_uploaded_file($tmp_name, $fname)){
            $response = [
                'status' => 1,
                'message' => 'success',
                'fileName' => $pic_name
            ];
        } else {
            $response = [
                'status' => 0,
                'message' => 'err'
            ];
        }

        echo Json::encode($response);


    }


    public function actionSaveEncodedImage(){
        $base64String = $_POST['base64String'];
        $imageName = $_POST['imageName'];
        $GroupImage=base64_decode($base64String);
        $imgFile = fopen("Upload_Files/" . $imageName, 'w');
        fwrite($imgFile, $GroupImage);
        fclose($imgFile);
    }

    Public function actionSaveAppPassword(){

        if(isset($_POST['password']) && !empty($_POST['password'])){

            $passwordHash = Yii::$app->security->generatePasswordHash($_POST['password']);

            $response = [
                'status' => true,
                'message' => 'success',
                'password' => $passwordHash

            ];

        } else {

            $response = [
                'status' => false,
                'message' => 'error'
            ];

        }

        echo Json::encode($response);
        die;
    }

    public function actionSaveProfileImage(){
        $base64String = $_POST['base64String'];
        $empname = $_POST['empname'];
        $password = $_POST['password'];
        $imageName = time().'_'.$empname;
        $GroupImage=base64_decode($base64String);
        $imgFile = fopen("Upload_Files/" . $imageName, 'w');
        fwrite($imgFile, $GroupImage);
        if(fclose($imgFile)){
            $response = [
                'status' => true,
                'message' => 'success',
                'fileName' => $imageName.".jpg",
                'password' => Yii::$app->security->generatePasswordHash($password)
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'err'
            ];
        }

        echo Json::encode($response);
    }

    public function actionLabTesting(){


        Yii::$app->mailer->compose('thanksTemplate', ['username' => 'sam'])
            ->setFrom('samparq@qdegrees.com')
            ->setTo($_POST['email'])
            ->setSubject('Welcome to Samparq - Connecting Qdegrees')
            ->send();
    }

    public function actionSaveCollage(){
        $pic_name = $_FILES['filename']['name'];
        $tmp_name = $_FILES['filename']['tmp_name'];
        $fname = Yii::getAlias('@frontend/web/Upload_Files/') . $pic_name;
        Image::frame($fname, 5, '666', 0)->rotate(0)->save(Yii::getAlias('@frontend/web/thumb/') . $pic_name, ['quality' => 50]);
        if(move_uploaded_file($tmp_name, $fname)){
            $response = [
                'status' => 1,
                'message' => 'success'
            ];
        } else {
            $response = [
                'status' => 0,
                'message' => 'err'
            ];
        }

        echo Json::encode($response);
    }

    public function actionSavePolicyDoc(){



        $pic_name = $_FILES['file']['name'];
        $tmp_name = $_FILES['file']['tmp_name'];
        $fname = Yii::getAlias('@frontend/web/Upload_Files/') . $pic_name;
       if(move_uploaded_file($tmp_name, $fname)){
            $response = [
                'status' => 1,
                'fileName' => $pic_name,
                'message' => 'success'
            ];
        } else {

           $response = [
               'status' => 0,
               'message' => 'err'
           ];
        }

        echo Json::encode($response);
       die;
    }


    public function actionNodeFileData(){

        $pic_name = $_FILES['file']['name'];
        $tmp_name = $_FILES['file']['tmp_name'];
        $fname = Yii::getAlias('@frontend/web/Upload_Files/') . $pic_name;
        if(move_uploaded_file($tmp_name, $fname)){
            $response = [
                'status' => 1,
                'fileName' => $pic_name,
                'message' => 'success'
            ];
        } else {

            $response = [
                'status' => 0,
                'message' => 'err'
            ];
        }

        echo Json::encode($response);
        die;
    }

    public function actionSyncCosecData(){


        function getBranchDetails(){

            $headers = array
            (
                'Authorization: Basic QXBpOkluZGlhQDIwMTg=',
                'Content-Type: application/json'
            );

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, 'http://182.71.112.194/COSEC/api.svc/v2/branch?action=get;format=json;');
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $branchResponse = curl_exec($curl);
            curl_close($curl);
            $branchDataArr  = json_decode($branchResponse, true);
            return $branchDataArr;
        }


        function getDepartmentDetails(){

            $headers = array
            (
                'Authorization: Basic QXBpOkluZGlhQDIwMTg=',
                'Content-Type: application/json'
            );


            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, 'http://182.71.112.194/COSEC/api.svc/v2/department?action=get;format=json;');
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $departResponse = curl_exec($curl);
            curl_close($curl);
            $departmentDataArr  = json_decode($departResponse, true);
            return $departmentDataArr;
        }


        function getDesignationDetails(){
            $headers = array
            (
                'Authorization: Basic QXBpOkluZGlhQDIwMTg=',
                'Content-Type: application/json'
            );
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, 'http://182.71.112.194/COSEC/api.svc/v2/designation?action=get;format=json;');
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $desigResponse = curl_exec($curl);
            curl_close($curl);
            $designationDataArr  = json_decode($desigResponse, true);
            return $designationDataArr;
        }


        $headers = array
        (
            'Authorization: Basic QXBpOkluZGlhQDIwMTg=',
            'Content-Type: application/json'
        );

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'http://182.71.112.194/COSEC/api.svc/v2/user?action=get;format=json;');
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $userResponse = curl_exec($curl);
        curl_close($curl);
        $userDataArr  = json_decode($userResponse, true);
        $user = [];






        function dateFormatter($date){
           return \DateTime::createFromFormat('dmY',$date)->format('Y-m-d');
        }

        $mergeData = array_merge($userDataArr, getBranchDetails(), getDepartmentDetails(), getDesignationDetails());

        foreach ($mergeData['user'] as $userData){

            $model = new CdUser();
            $model->employee_id = $userData['id'];
            $model->name = $userData['name'];
            $model->gender = $userData['gender'];
            $model->email = $userData['official-email'];
            $model->phone = $userData['official-cell'];
            $model->aadhar_no = $userData['aadhar-no'];
            $model->pan = $userData['pan'];
            $model->active = $userData['active'];
            $model->category = $userData['category_code'];
            $model->dob = empty($userData['date-of-birth']) ? '' : dateFormatter($userData['date-of-birth']);
            $model->joining_date = empty($userData['joining-date']) ? '' : dateFormatter($userData['joining-date']);
            $model->confirmation_date = empty($userData['confirmation-date']) ? '' : dateFormatter($userData['confirmation-date']);
            $model->marital_status = $userData['marital-status'];
            $model->branch = empty($userData['branch']) ? "" : $mergeData['branch'][($userData['branch']-1)]['name'];
            $model->department = empty($userData['department']) ? "" : $mergeData['department'][($userData['department']-1)]['name'];
            $model->designation = empty($userData['designation']) ? "" : $mergeData['designation'][($userData['designation']-1)]['name'];
            $model->reporting_in_charge1 = $userData['rg_incharge_1'];
            $model->reporting_in_charge2 = $userData['rg_incharge_2'];
            $model->save();
        }

    }




}
