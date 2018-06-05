<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace common\components;



use common\models\AppLogin;
use common\models\User;
use frontend\modules\notification\models\Notification;
use frontend\modules\notification\models\NotificationAttachment;
use frontend\modules\notify\models\TblNotifications;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use common\models\LoginForm;


class Utility extends Component {
    /*
     * By : Lakhan Singh on 31-Aug-2016
     * For encrpt and decrypt string 
     * source : http://stackoverflow.com/questions/1289061/best-way-to-use-php-to-encrypt-and-decrypt-passwords
     */

    public static function encrypt_decrpt($str, $action) {
        $key = 'scriptifi qdegrees services';
        $string = $str; // note the spaces
        $iv = mcrypt_create_iv(
                mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC), MCRYPT_DEV_URANDOM
        );

        $encrypted = base64_encode(
                $iv .
                mcrypt_encrypt(
                        MCRYPT_RIJNDAEL_128, hash('sha256', $key, true), $string, MCRYPT_MODE_CBC, $iv
                )
        );



        if ($action == 'e') {
            $strTreturn = $encrypted;
        } else {
            $data = base64_decode($str);
            $iv = substr($data, 0, mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC));

            $decrypted = rtrim(
                    mcrypt_decrypt(
                            MCRYPT_RIJNDAEL_128, hash('sha256', $key, true), substr($data, mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC)), MCRYPT_MODE_CBC, $iv
                    ), "\0"
            );
            $strTreturn = $decrypted;
        }
        return $strTreturn;
    }

    /* added-by: Saraswati Kalla On:10-Sept-2016
     *  Common function to change format of date
     */

    public static function dateformat($format, $param) {
        $date = \DateTime::createFromFormat($format, $param);
        return $date;
    }

    /* added-by: Saraswati Kalla On:10-Sept-2016
     *  Calculate time duration between two dates
     */

    public static function calDiffDates($date1, $date2) {

        $diff = abs(strtotime($date2) - strtotime($date1));

        $cal['year'] = floor($diff / (365 * 60 * 60 * 24));
        $cal['month'] = floor(($diff - $cal['year'] * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
        $cal['days'] = floor(($diff - $cal['year'] * 365 * 60 * 60 * 24 - $cal['month'] * 30 * 60 * 60 * 24) / (60 * 60 * 24));
        return $cal;
    }

    public static function SmsNotify($number, $msg) {

//Your authentication key
        $authKey = "126664AftMrtSpZlhK57eb91b3";

//Multiple mobiles numbers separated by comma
        $mobileNumber = $number;

//Sender ID,While using route4 sender id should be 6 characters long.
        $senderId = "script";

//Your message to send, Add URL encoding here.
        $message = urlencode($msg);

//Define route 
        $route = 4;
//Prepare you post parameters
        $postData = array(
            'authkey' => $authKey,
            'mobiles' => $mobileNumber,
            'message' => $message,
            'sender' => $senderId,
            'route' => $route
        );

//API URL
        $url = "http://api.msg91.com/api/sendhttp.php";

// init the resource
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData
                //,CURLOPT_FOLLOWLOCATION => true
        ));


//Ignore SSL certificate verification
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);


//get response
        $output = curl_exec($ch);
//var_dump($output);die;
//Print error if any
        if (curl_errno($ch)) {
            curl_close($ch);
            return false;
        } else {
            curl_close($ch);
            return true;
        }
    }

    public static function EmailNotify($to, $temp, $model, $subject = 'Scriptifi', $from = 'scriptifi@qdegrees.in', $cc = null, $bcc = null) {
        // try {
        $mail = Yii::$app->mailer->compose($temp, [
                    'model' => $model,
                ])
                ->setFrom($from)
                ->setTo($to)
                ->setSubject($subject);
        $mail->send();
        // } 
        // catch (\Swift_TransportException $e) {
        //Yii::warning("Division by zero.");
        //  }
        return true;
    }

    public static function getTblAuditName($id) {
        return \frontend\models\TblAuditName::find()->where(['id' => $id])->one();
    }

    public static function getParameterDetial($id) {
        return \frontend\modules\masters\models\TblParameterCategories::find()->where(['id' => $id])->one();
    }

    public static function getParameterSubDetial($id) {
        return \frontend\modules\masters\models\TblMainParameters::find()->where(['id' => $id])->one();
    }

    public static function getMangeParameterDetial($id) {
        return \frontend\modules\masters\models\TblParameters::find()->where(['id' => $id])->one();
    }

    public static function getMangeParameterDetial_Sub($id) {
        return \frontend\modules\masters\models\TblSubParameters::find()->where(['id' => $id])->one();
    }

    public static function getMangeQuestionAttr_Detail($id) {
        return \frontend\modules\masters\models\TblParameterCategories::find()->where(['id' => $id])->one();
    }

    public static function getMangeQuestionMain_Detail($id) {
        return \frontend\modules\masters\models\TblMainParameters::find()->where(['id' => $id])->one();
    }

    public static function get_totlal_option($id) {
        return \frontend\modules\masters\models\TblOptions::find()->where(['parameter_id' => $id])->count();
    }

    public static function getZoneName($id) {
        $result = \frontend\modules\masters\models\TblStores::find()->where(['id' => $id])->one();
        $result_zone = \frontend\modules\masters\models\TblZones::find()->where(['id' => $result->zone_id])->one();
        return $result_zone->name;
    }

    public static function getStoreName($id) {
        $result = \frontend\modules\masters\models\TblStores::find()->where(['id' => $id])->one();
        return $result->store_name;
    }

    public static function getAudit_templete_parameter_count($audit_name_id, $id) {
        //die();
        $model = \frontend\modules\masters\models\BeatPlanAssignTemplete::findOne(['beat_plan_id' => $id])->details;
        // print_r($model);
        // die();
        $sql = "select count(*) as total from tbl_parameter_categories tc , tbl_main_parameters tm , tbl_sub_parameters ts, tbl_parameters tp
where  tc.id=tm.parameter_cat_id and tc.id=ts.parameter_cat_id and ts.main_parameter_id=tm.id and tc.id=tp.parameter_cat_id
and tp.main_parameter_id=tm.id and tp.sub_parameter_id=ts.id  and tc.audit_name_id='" . $audit_name_id . "' and tp.id in (" . $model . ")";
        // echo $sql;
        // die();
        return \Yii::$app->db->createCommand($sql)->queryone();
    }

    public static function getTotalCatName_WithCount($audit_name_id) {

        $sql = "select tc.parameter,count(*) as total,tc.id,tc.required_main_parameter,tc.required_sub_parameter,tc.is_na  from tbl_parameter_categories tc , tbl_main_parameters tm , tbl_sub_parameters ts, tbl_parameters tp
where tc.id=tm.parameter_cat_id and tc.id=ts.parameter_cat_id and ts.main_parameter_id=tm.id and tc.id=tp.parameter_cat_id
and tp.main_parameter_id=tm.id and tp.sub_parameter_id=ts.id and tc.audit_name_id='" . $audit_name_id . "' group by tc.id";
        //$sql="select id,parameter,is_na,required_main_parameter,required_sub_parameter from tbl_parameter_categories where  audit_name_id='".$audit_name_id."' group by id";
        return \Yii::$app->db->createCommand($sql)->queryAll();
    }

    public static function getMainParameter($id) {
        $sql = "select tm.id,tm.parameter_cat_id,tm.main_parameter_name,tm.is_na  from tbl_main_parameters tm , tbl_sub_parameters ts, tbl_parameters tp
where  ts.main_parameter_id=tm.id and tp.parameter_cat_id='" . $id . "'
and tp.main_parameter_id=tm.id and tp.sub_parameter_id=ts.id and  tm.parameter_cat_id='" . $id . "'  and tm.status=1 group by tm.id";
        return \Yii::$app->db->createCommand($sql)->queryAll();
    }

    public static function getSubParameter($cat_id, $main_id) {
        $sql = "select ts.id,sub_parameter_name,ts.main_parameter_id,ts.is_na  from tbl_sub_parameters ts, tbl_parameters tp where  tp.parameter_cat_id='" . $cat_id . "'
and tp.main_parameter_id='" . $main_id . "' and tp.sub_parameter_id=ts.id and  ts.parameter_cat_id='" . $cat_id . "' and  ts.main_parameter_id='" . $main_id . "' and ts.status=1 group by ts.id";
        return \Yii::$app->db->createCommand($sql)->queryAll();
    }

    public static function gettblParameter_detail($cat_id, $main_id, $sub_parameter_id) {
        $sql = "select id,parameter_name,is_na,is_fatal,scorable_points  from  tbl_parameters where  parameter_cat_id='" . $cat_id . "'
and main_parameter_id='" . $main_id . "' and sub_parameter_id='" . $sub_parameter_id . "' and  status=1 group by id";
        return \Yii::$app->db->createCommand($sql)->queryAll();
    }

    public static function getTblPara($id, $beat_plan_id) {
        
        $model = \frontend\modules\masters\models\BeatPlanAssignTemplete::findOne(['beat_plan_id' => $beat_plan_id])->details;
        $tot_id = explode(",", $model);
        
        //$result=\frontend\modules\masters\models\TblParameters::find()->where(['audit_name_id' => $id])->andWhere(['in', 'id', $tot_id])->orderBy('parameter_cat_id asc')->all();
        $result=\frontend\modules\masters\models\TblParameters::find()->where(['audit_name_id' => $id])->andWhere(['in', 'id', $tot_id])->orderBy('parameter_cat_id,main_parameter_id,sub_parameter_id,id asc')->all();
       // print_r($result);die();
        return $result;
    }

    public static function getTblMain_Parameter($id) {
        return \frontend\modules\masters\models\TblMainParameters::find()->where(['id' => $id])->one();
    }

    public static function getTblSub_Parameter($id) {
        return \frontend\modules\masters\models\TblSubParameters::find()->where(['id' => $id])->one();
    }

    public static function getMainAttributeCount($id, $beat_plan_id) {
        $model = \frontend\modules\masters\models\BeatPlanAssignTemplete::findOne(['beat_plan_id' => $beat_plan_id])->details;
        $sql = "SELECT count(*) as total,parameter_cat_id FROM tbl_parameters where audit_name_id='" . $id . "' and id in (" . $model . ")  group by parameter_cat_id order by parameter_cat_id";
        return \Yii::$app->db->createCommand($sql)->queryAll();
    }

    public static function getTblCat($id) {
        // die();
        return \frontend\modules\masters\models\TblParameterCategories::find()->where(['id' => $id])->one();
    }

    public static function getRequiredfiled($id) {

        $mod = \frontend\modules\masters\models\TblOptions::find()->select('id')->where(['parameter_id' => $id])->andWhere(['status' => 1])->andwhere(['remark_required' => 1])->all();
        // echo count($mod);
        $result = "";
        foreach ($mod as $rs) {
            if ($result == "") {
                $result = $rs['id'];
            } else {
                $result = $result . "," . $rs['id'];
            }
        }
        return $result;
    }

    public static function getSaveTblOptionParameter($id) {
        return \frontend\modules\mobile\models\TblOptions::find()->where(['id' => $id])->one();
    }

    // audit details


    public static function getZone_Name($id) {
        return \frontend\models\TblZones::find()->where(['id' => $id])->one();
    }

    public static function getCircle_Name($id) {
        return \frontend\models\TblCirclesForm::find()->where(['id' => $id])->one();
    }

    public static function getAssigned_to($id) {
        return \frontend\models\User::find()->where(['id' => $id])->one();
    }

    public static function getAudit_templete_parameter_count_two_con($id, $beat_id) {
        $model = \frontend\modules\masters\models\BeatPlanAssignTemplete::findOne(['beat_plan_id' => $beat_id])->details;
        $sql = "select count(*) as total from tbl_parameter_categories tc , tbl_parameters tp where  tc.id=tp.parameter_cat_id   and tc.audit_name_id='" . $id . "' and tp.id in (" . $model . ")";
        return \Yii::$app->db->createCommand($sql)->queryone();
    }

    public static function getAudit_templete_parameter_count_one_con($id, $beat_id) {
        $model = \frontend\modules\masters\models\BeatPlanAssignTemplete::findOne(['beat_plan_id' => $beat_id])->details;
        $sql = "select count(*) as total from tbl_parameter_categories tc , tbl_main_parameters tm , tbl_parameters tp
where  tc.id=tm.parameter_cat_id  and tc.id=tp.parameter_cat_id
and tp.main_parameter_id=tm.id    and tc.audit_name_id='" . $id . "' and tp.id in (" . $model . ")";
        return \Yii::$app->db->createCommand($sql)->queryone();
    }

    public static function getUploadFile($id, $beat_plan_id) {
        return \frontend\modules\mobile\models\AuditorFormAttachment::find()->where(['parameter_id' => $id])->andwhere(['beat_plan_id' => $beat_plan_id])->andwhere(['status' => 1])->one();
    }

    public static function getUploadFile_store_close($id) {
        return \frontend\modules\mobile\models\AuditorFormAttachment::find()->andwhere(['beat_plan_id' => $id])->andwhere(['status' => 1])->one();
    }

//    public static function SaveNotification($user_id, $title, $notification, $isRead = 0) {
//        $notification_model = new \common\models\Notifications();
//        $notification_model->user_id = $user_id;
//        $notification_model->title = $title;
//        $notification_model->notification = $notification;
//        $notification_model->is_read = $isRead;
//        if ($notification_model->save()) {
//            return true;
//        } else {
//            return false;
//        }
//    }
//    public static function getEmployerCompanyByCompanyId($id) {
//        return \employer\models\Employers::find()->where(['id' => $id])->one();
//    }
//     public static function getEmployerCompanyByUserId($id) {
//         $sql="select GETNAME(".$id.", 'employers') as company_name;";
//        return \employer\models\Employers::findBySql($sql)->one();
//        
//    }
//    public static function getPriceFormatEcho($num) {
//
//       
//
//       
//
////function call
//       // $num = "789";
//        $ext = "lac "; //thousand,lac, crore
//        $number_of_digits = strlen($num);
//        if ($number_of_digits > 3) {
//            if ($number_of_digits % 2 != 0)
//                $divider = self::divider($number_of_digits - 1);
//            else
//                $divider = self::divider($number_of_digits);
//        } else
//            $divider = 1;
//
//        $fraction = $num / $divider;
//        $fraction = number_format($fraction, 2);
//        if ($number_of_digits == 4 || $number_of_digits == 5)
//            $ext = "k";
//        if ($number_of_digits == 6 || $number_of_digits == 7)
//            $ext = "Lac";
//        if ($number_of_digits == 8 || $number_of_digits == 9)
//            $ext = "Cr";
//        return $fraction . " " . $ext;
//    }
//
//    public static function getPriceFormat($num) {
//
//       
//
//       
//
////function call
//       // $num = "789";
//        $ext = "lac "; //thousand,lac, crore
//        $number_of_digits = strlen($num);
//        if ($number_of_digits > 3) {
//            if ($number_of_digits % 2 != 0)
//                $divider = self::divider($number_of_digits - 1);
//            else
//                $divider = self::divider($number_of_digits);
//        } else
//            $divider = 1;
//
//        $fraction = $num / $divider;
//        $fraction = number_format($fraction, 2);
//        if ($number_of_digits == 4 || $number_of_digits == 5)
//            $ext = "k";
//        if ($number_of_digits == 6 || $number_of_digits == 7)
//            $ext = "Lac";
//        if ($number_of_digits == 8 || $number_of_digits == 9)
//            $ext = "Cr";
//        echo $fraction . " " . $ext;
//    }
//    
//     public static function divider($number_of_digits) {
//            $tens = "1";
//            while (($number_of_digits - 1) > 0) {
//                $tens.="0";
//                $number_of_digits--;
//            }
//            return $tens;
//        }
//        
//        public static function getUserRoles($id)
//        {
//            $sql="SELECT u.id , group_concat(a.item_name) as role FROM scriptifi_new.user as u INNER JOIN auth_assignment as a ON u.id = a.user_id where u.id=".$id;
//        }
//        
//        /*
//         * By : Lakhan Singh
//         * On : 04-Oct-2016
//         * Functions for get count of all jobbcasts which created by employer
//         */
//        public static function getCountOfAllJobcastCreatedByEmployer($employer_id)
//        {
//            return \employer\models\Jobcasts::find()->where(['user_id'=>$employer_id])->count();
//        }
//        /*
//         * By : Lakhan Singh
//         * On : 04-Oct-2016
//         * Functions for get count of all jobbcasts which assigned by employer
//         */
//        public static function getCountOfAllJobcastAssignedByEmployer($employer_id)
//        {
//            return \recruiter\models\JobcastAssignment::find()->where(['employer_id'=>$employer_id])->count();
//        }
//        /*
//         * By : Lakhan Singh
//         * On : 04-Oct-2016
//         * Functions for get count of all live jobbcasts with status zero by employer
//         */
//        public static function getCountOfLiveJobcastForEmployer($employer_id)
//        {
//            return \recruiter\models\JobcastAssignment::find()->where(['employer_id'=>$employer_id])->andWhere(['status' => '1'])->count();
//        }
//        
//         /*
//         * By : Lakhan Singh
//         * On : 04-Oct-2016
//         * Functions for get count of closed jobbcasts which created by employer
//         */
//        public static function getCountOfClosedJobcastByEmployer($employer_id)
//        {
//            return \employer\models\Jobcasts::find()->where(['user_id'=>$employer_id])->andWhere(['status'=>'3'])->count();
//        }
//        
//         /*
//         * By : Lakhan Singh
//         * On : 04-Oct-2016
//         * Functions for get count of withdraw jobbcasts which created by employer
//         */
//        public static function getCountOfWithdrawJobcastByEmployer($employer_id)
//        {
//            return \employer\models\Jobcasts::find()->where(['user_id'=>$employer_id])->andWhere(['status'=>'2'])->count();
//        }
//        
//         /*
//         * By : Lakhan Singh
//         * On : 04-Oct-2016
//         * Functions for get schedule count with status
//         */
//        public static function getCountOfSchedulesByStatusForEmployer($employer_id , $status)
//        {
//            return \common\models\SubmitedCandidateByRecruiterToEmployer::find()->where(['employer_id'=>$employer_id])->andWhere(['status'=>$status])->count();
//        }
//        /*
//         * By : Lakhan Singh
//         * On : 04-Oct-2016
//         * For get today schedule count for employer
//         */
//        public static function getTodayScheduledCountForEmployer($emplyer_id)
//        {
//            $sql = "SELECT b.* FROM mst_submited_candidate_by_recruiter_to_employer as a INNER JOIN mst_scheduled_candidates_for_interview as b ON a.submision_id=b.submision_id WHERE a.employer_id=".$emplyer_id." AND b.status='1' AND DAY(b.interview_date)=" . date('d');
//            return \common\models\SubmitedCandidateByRecruiterToEmployer::findBySql($sql)->count();
//        }
//        /*
//         * By : Lakhan Singh
//         * On : 04-Oct-2016
//         * For get today posted jobcast by  employer
//         */
//        public static function getTodayCreatedJobcastsByEmployer($emplyer_id)
//        {
//            $sql = "SELECT * FROM jobcasts  WHERE user_id=".$emplyer_id."  AND DAY(created_on)=" . date('d')." AND MONTH(created_on)=" . date('m')." AND YEAR(created_on)=" . date('Y');
//            return \employer\models\Jobcasts::findBySql($sql)->count();
//        }
//        /*
//         * By : Lakhan Singh
//         * On : 4-oct-2016
//         * For get jobcast assignment count according to status and month
//         */
//        public static function getEmployerJobcastListAccrodingToMonth($employer_id, $month, $status = '') {
//        if ($status != '') {
//            $sql = "SELECT * FROM jobcast_assign_detail WHERE employer_id=" . $employer_id . " AND status='" . $status . "' AND YEAR(created_on)=" . date('Y') . " AND  MONTH(created_on) = " . $month;
//        } else {
//            $sql = "SELECT * FROM jobcast_assign_detail WHERE employer_id=" . $employer_id . " AND YEAR(created_on)=" . date('Y') . " AND  MONTH(created_on) = " . $month;
//        }
//        return \recruiter\models\JobcastAssignment::findBySql($sql)->count();
//        }
//    
//    /*
//     * Admin dashbord
//     * By : lakhan singh
//     * On : 14-oct-2016
//     */
//     public static function getUserCount($role)
//     {
//         $sql="select u.id from ".\common\models\User::tableName()." as u INNER JOIN ".\common\models\AuthAssignment::tableName()." as a On u.id=a.user_id where a.item_name='".$role."'";
//         $count=  \common\models\User::findBySql($sql)->count();
//         return $count;
//         
//     }
//     public static function getBlockedUserCount($role)
//     {
//         $sql="select u.id from ".\common\models\User::tableName()." as u INNER JOIN ".\common\models\AuthAssignment::tableName()." as a On u.id=a.user_id where a.item_name='".$role."' and u.account_freeze='1' and u.admin_verified='1'";
//         $count=  \common\models\User::findBySql($sql)->count();
//         return $count;
//         
//     }
//      public static function getTotalJobcastCount($status=null)
//     {
//          if($status!=null)
//          {
//              return \employer\models\Jobcasts::find()->where(['status'=>$status])->count(); 
//          }
//         return \employer\models\Jobcasts::find()->count();
//         
//     }
//     
//      public static function getTotalScheduleCount()
//     {
//         return \common\models\SubmitedCandidateByRecruiterToEmployer::find()->where(['status'=>'2'])->count();
//         
//     }
//     
//       public static function getTotalEarningAmount()
//     {
//         $sql="select ifnull(sum(due_payment),0) as dp from ".\common\models\Invoice::tableName()." where invoice_status='1'";
//         $count= \Yii::$app->db->createCommand($sql)->queryOne();
//         return $count['dp'];
//         
//     }
//      public static function getTotalDueAmount()
//     {
//         $sql="select ifnull(sum(due_payment),0) as dp from ".\common\models\Invoice::tableName()." where invoice_status='0'";
//         $count= \Yii::$app->db->createCommand($sql)->queryOne();
//         return $count['dp'];
//         
//     }
//     public static function getTopFiveHotJobs()
//     {
//         $sql='SELECT role,count(*) as total FROM '.\employer\models\Jobcasts::tableName().' group by role order by total DESC limit 5';
//         return $count=  \Yii::$app->db->createCommand($sql)->queryAll();
//                 
//     }
//     
//     public static function getSkillsBestCount()
//     {
//         $sql="    SELECT 
//        `skills`.`id` AS `id`,
//        `skills`.`title` AS `title`,
//        count(`skills`.`title`) as total,
//         `jobcasts`.`id` AS `jobcast_id`,
//       
//        `jobcasts`.`key_skill_must_have` AS `key_skill_must_have`,
//        (select count(id) from jobcasts) as job_count
//    FROM
//        (`skills` `skills`
//        JOIN `jobcasts` `jobcasts` ON (FIND_IN_SET(`skills`.`title`, `jobcasts`.`key_skill_must_have`))) group by `skills`.`title` order by total desc limit 5";
//          return $count=  \Yii::$app->db->createCommand($sql)->queryAll();
//     }
//     
//      public static function getTotalJobcastAccrodingToMonth($month, $status = '') {
//        if ($status == '') {
//            $sql = "SELECT user_id FROM jobcasts WHERE YEAR(created_on)=" . date('Y') . " AND  MONTH(created_on) = " . $month;
//        } else {
//           
//             $sql = "SELECT distinct(s.jobcast_id) FROM jobcasts as j INNER JOIN mst_submited_candidate_by_recruiter_to_employer as s ON j.id= s.jobcast_id WHERE YEAR(j.created_on)=" . date('Y') . " AND  MONTH(j.created_on) = " . $month ." AND YEAR(s.joined_on)=" . date('Y') . " AND  MONTH(s.joined_on) = " . $month;
//             }
//        return \employer\models\Jobcasts::findBySql($sql)->count();
//    }
//     public static function getTotalDuepaymentRecruiter($recruiter_id)
//     {
//         $sql="select ifnull(sum(due_payment),0) as dp from ".\common\models\Invoice::tableName()." where invoice_status='0' and recruiter_id=".$recruiter_id;
//         $count= \Yii::$app->db->createCommand($sql)->queryOne();
//         return $count['dp'];
//         
//     }
//      public static function getTodayTotalDuepaymentRecruiter($recruiter_id)
//     {
//         $sql="select ifnull(sum(due_payment),0) as dp from ".\common\models\Invoice::tableName()." where invoice_status='0' and recruiter_id=".$recruiter_id." AND  DAY(created_on)=" . date('d')." AND MONTH(created_on)=" . date('m')." AND YEAR(created_on)=" . date('Y');
//         $count= \Yii::$app->db->createCommand($sql)->queryOne();
//         return $count['dp'];
//         
//     }
//      public static function getTopEmployerRating()
//     {
//         $sql="select u.id,e.company_name,e.rattings,u.photo_time from employers e,user u where e.id=u.company_id  and e.rattings!=0 
//         and u.role='1'
//         and e.status='1' and u.id not in(select user_id from auth_assignment where item_name='admin') order by rattings desc limit 5";
//         return $count=  \Yii::$app->db->createCommand($sql)->queryAll();
//                 
//     }
//     
//      public static function getTopRecuiterRating()
//     {
//         $sql="select r.id,r.company_name,r.overall_ratting,u.photo_time from recruiter_master r,user u where r.id=u.company_id and r.overall_ratting!=0 and r.status='1'  and u.role='0' order by overall_ratting desc limit 5";
//         return $count=  \Yii::$app->db->createCommand($sql)->queryAll();
//                 
//     }
//     
//    public static function date_diff($date1 , $date2)
//    {
//        
//        $datetime1 = date_create($date1);
//        $datetime2 = date_create($date2);
//        $interval = date_diff($datetime1, $datetime2);
//            return $interval->format('%a days');
//    }
//// recruiter dashboard by lakhan on 08-dec-2016
//
//     
//     public static function getTopTenRecuiterRating()
//     {
//         $sql="select r.id,r.company_name,r.overall_ratting,u.photo_time from recruiter_master r,user u where r.id=u.company_id and r.overall_ratting!=0 and r.status='1'  and u.role='0' order by overall_ratting desc limit 10";
//         return $count=  \Yii::$app->db->createCommand($sql)->queryAll();
//                 
//     }

    public static function getTotalRecords() {
        $sql = "SELECT a.attribute_id,a.attribute_name,count(*) as total FROM attribute a, attribute_sub s where
a.attribute_id=s.attribute_id and a.attribute_status='1' and s.attribute_sub_status='1' group by attribute_name order by a.attribute_id";
        return $count = \Yii::$app->db->createCommand($sql)->queryAll();
    }

    public static function getTotalAttrubiute($id = null) {
//         $sql="SELECT s.attribute_id,s.attribute_sub_id,s.attribute_sub_name,m.rca_flag FROM attribute a, attribute_sub s,attribute_marks m where
//a.attribute_id=s.attribute_id and m.attribute_marks_sub_id=s.attribute_sub_id and s.attribute_id='".$id."' and a.attribute_status='1' and s.attribute_sub_status='1' order by s.attribute_id,s.attribute_sub_id";
//         return $count=  \Yii::$app->db->createCommand($sql)->queryAll();

        $sql = "SELECT s.attribute_id,s.attribute_sub_id,attribute_sub_name FROM attribute a, attribute_sub s where
a.attribute_id=s.attribute_id and s.attribute_id='" . $id . "' and a.attribute_status='1' and s.attribute_sub_status='1' order by s.attribute_id,s.attribute_sub_id";
        return $count = \Yii::$app->db->createCommand($sql)->queryAll();
    }

    public static function getRca_flag($sub) {
        return \frontend\models\AttributeMarks::find()->where(['attribute_marks_sub_id' => $sub])->andwhere(['rca_flag' => '1'])->count();
    }

    public static function getTotalRecords_view($id) {
        $sql = "select audit_obser_attribute_id as attribute_id,audit_obser_heading as attribute_name,count(*) as total 
from audit_obser where audit_trans_id='" . $id . "' group by audit_obser_attribute_id order by audit_obser_attribute_id";
        return $count = \Yii::$app->db->createCommand($sql)->queryAll();
    }

    public static function getTotalAttrubiute_view($model_id, $attribute_id) {
        $sql = "select audit_obser_attribute_id as attribute_id,audit_obser_attribute_subid as attribute_sub_id,audit_obser_question as attribute_sub_name
from audit_obser where audit_trans_id='" . $model_id . "' and audit_obser_attribute_id='" . $attribute_id . "'";
        return $count = \Yii::$app->db->createCommand($sql)->queryAll();
    }

    public static function getRca_flag_view($model_id, $sub) {
        return \frontend\models\AuditObser::find()->where(['audit_trans_id' => $model_id])->andwhere(['audit_obser_attribute_subid' => $sub])->andwhere(['audit_obser_rca_flag' => 'YES'])->count();
    }

    public static function getTotalAttrubiute_mark_view($id) {
        $sql = "SELECT attribute_marks_name FROM audit_obser o,attribute_marks m where audit_obser_id='" . $id . "' and o.audit_obser_actual_marking=m.attribute_marks_id";
        return $count = \Yii::$app->db->createCommand($sql)->queryAll();
    }

    public static function getTotalAttrubiute_view_score($id) {
        $sql = "SELECT audit_obser_heading,audit_obser_id,audit_obser_attribute_id,audit_obser_group_score FROM audit_obser  where audit_trans_id='" . $id . "' and audit_obser_group_score is not null";
        return $count = \Yii::$app->db->createCommand($sql)->queryAll();
    }

    public static function getRca_flag_export($model_id, $sub) {
        $sql = "select 
(select root_cause_analysis_desc from root_cause where root_cause_analysis_id=m.audit_obser_rca_level1) as audit_obser_rca_level1,
(select root_cause_sub_details from root_cause_sub where root_cause_sub_id=m.audit_obser_rca_level2) as audit_obser_rca_level2,
(select root_cause_third_details from root_cause_third where root_cause_third_id=m.audit_obser_rca_level3) as audit_obser_rca_level3,
 audit_obser_rca_remark, audit_obser_rca_people_opportunity_exists_level,
 audit_obser_rca_people_opportunity_exists_remark, audit_obser_rca_failure_point_people_opportunity_level,
 audit_obser_rca_failure_point_people_opportunity_remark
 from audit_obser_rca_details m where audit_obser_rca_audit_tran_id='" . $model_id . "' and audit_obser_rca_attribute_subid='" . $sub . "'";

        return $count = \Yii::$app->db->createCommand($sql)->queryAll();
    }

    public static function getRca_flag_export_main($model_id) {
        $sql = "select 
(select root_cause_analysis_desc from root_cause where root_cause_analysis_id=m.audit_obser_rca_level1) as audit_obser_rca_level1,
(select root_cause_sub_details from root_cause_sub where root_cause_sub_id=m.audit_obser_rca_level2) as audit_obser_rca_level2,
(select root_cause_third_details from root_cause_third where root_cause_third_id=m.audit_obser_rca_level3) as audit_obser_rca_level3,
 audit_obser_rca_remark, audit_obser_rca_people_opportunity_exists_level,
 (select root_cause_sub_details from root_cause_sub where root_cause_sub_id=m.audit_obser_rca_people_opportunity_exists_level2) as audit_obser_rca_people_opportunity_exists_level2,
(select root_cause_third_details from root_cause_third where root_cause_third_id=m.audit_obser_rca_people_opportunity_exists_level3) as audit_obser_rca_people_opportunity_exists_level3,
 audit_obser_rca_people_opportunity_exists_remark, audit_obser_rca_failure_point_people_opportunity_level,
 audit_obser_rca_failure_point_people_opportunity_remark
 from audit_obser_rca_details m where audit_obser_rca_audit_tran_id='" . $model_id . "'";

        return $count = \Yii::$app->db->createCommand($sql)->queryOne();
    }

    public static function getRca_flag_export_main_data($model_id) {
        $sql = "select 
(select root_cause_analysis_desc from root_cause where root_cause_analysis_id=m.audit_obser_rca_level1) as audit_obser_rca_level1,
(select root_cause_sub_details from root_cause_sub where root_cause_sub_id=m.audit_obser_rca_level2) as audit_obser_rca_level2,
(select root_cause_third_details from root_cause_third where root_cause_third_id=m.audit_obser_rca_level3) as audit_obser_rca_level3,
 audit_obser_rca_remark, audit_obser_rca_people_opportunity_exists_level,
 (select root_cause_sub_details from root_cause_sub where root_cause_sub_id=m.audit_obser_rca_people_opportunity_exists_level2) as audit_obser_rca_people_opportunity_exists_level2,
(select root_cause_third_details from root_cause_third where root_cause_third_id=m.audit_obser_rca_people_opportunity_exists_level3) as audit_obser_rca_people_opportunity_exists_level3,
 audit_obser_rca_people_opportunity_exists_remark, audit_obser_rca_failure_point_people_opportunity_level,
 audit_obser_rca_failure_point_people_opportunity_remark
 from audit_obser_rca_details m where audit_obser_rca_audit_tran_id='" . $model_id . "'";

        return $count = \Yii::$app->db->createCommand($sql)->queryAll();
    }

    public static function getRca_flag_export_level1($model_id) {
        $sql = "select 
(select root_cause_analysis_desc from root_cause where root_cause_analysis_id=m.audit_obser_rca_level1) as audit_obser_rca_level1,
(select root_cause_sub_details from root_cause_sub where root_cause_sub_id=m.audit_obser_rca_level2) as audit_obser_rca_level2,
(select root_cause_third_details from root_cause_third where root_cause_third_id=m.audit_obser_rca_level3) as audit_obser_rca_level3,
 audit_obser_rca_remark, audit_obser_rca_people_opportunity_exists_level,
 audit_obser_rca_people_opportunity_exists_remark, audit_obser_rca_failure_point_people_opportunity_level,
 audit_obser_rca_failure_point_people_opportunity_remark
 from audit_obser_rca_details m where audit_obser_rca_audit_tran_id='" . $model_id . "'";

        return $count = \Yii::$app->db->createCommand($sql)->queryOne();
    }

    public static function getRca_flag_export_level2($model_id) {
        $sql = "select 
(select root_cause_analysis_desc from root_cause where root_cause_analysis_id=m.audit_obser_rca_level1) as audit_obser_rca_level1,
(select root_cause_sub_details from root_cause_sub where root_cause_sub_id=m.audit_obser_rca_level2) as audit_obser_rca_level2,
(select root_cause_third_details from root_cause_third where root_cause_third_id=m.audit_obser_rca_level3) as audit_obser_rca_level3,
 audit_obser_rca_remark, audit_obser_rca_people_opportunity_exists_level,
 audit_obser_rca_people_opportunity_exists_remark, audit_obser_rca_failure_point_people_opportunity_level,
 audit_obser_rca_failure_point_people_opportunity_remark
 from audit_obser_rca_details m where audit_obser_rca_audit_tran_id='" . $model_id . "'";

        return $count = \Yii::$app->db->createCommand($sql)->queryOne();
    }

    public static function getRca_flag_export_level3($model_id) {
        $sql = "select 
(select root_cause_analysis_desc from root_cause where root_cause_analysis_id=m.audit_obser_rca_level1) as audit_obser_rca_level1,
(select root_cause_sub_details from root_cause_sub where root_cause_sub_id=m.audit_obser_rca_level2) as audit_obser_rca_level2,
(select root_cause_third_details from root_cause_third where root_cause_third_id=m.audit_obser_rca_level3) as audit_obser_rca_level3,
 audit_obser_rca_remark, audit_obser_rca_people_opportunity_exists_level,
 audit_obser_rca_people_opportunity_exists_remark, audit_obser_rca_failure_point_people_opportunity_level,
 audit_obser_rca_failure_point_people_opportunity_remark
 from audit_obser_rca_details m where audit_obser_rca_audit_tran_id='" . $model_id . "'";

        return $count = \Yii::$app->db->createCommand($sql)->queryOne();
    }

    public static function get_score_parameter($id) {
        //$sql = "select group_concat(option_text) as val from tbl_options where parameter_id='" . $id . "' and option_text not in ('NCF','NA') ";
        $sql = "select group_concat(option_text) as val from tbl_options where parameter_id='" . $id . "'";
        return $count = \Yii::$app->db->createCommand($sql)->queryOne();
    }

    public static function get_scoring_pattern($id) {
        $sql = "select group_concat(option_text,'=',option_value) as scoring_pattern from tbl_options where parameter_id='" . $id . "'";
        //$sql = "select group_concat(option_text,'=',option_value) as scoring_pattern from tbl_options where parameter_id='" . $id . "' and option_text not in ('NCF','NA')";
        return $count = \Yii::$app->db->createCommand($sql)->queryOne();
    }

    public static function get_scored($beat_plan_id, $parameter_id) {
        $count = \frontend\modules\mobile\models\TblAuditorForm::find()->where(['beat_plan_id' => $beat_plan_id])->andwhere(['parameter_id' => $parameter_id])->one();
        return $count;
    }

    public static function get_scored_parameter_value($opt_val) {
        $count = \frontend\modules\mobile\models\TblOptions::find()->where(['id' => $opt_val])->one()->option_text;
        return $count;
    }

    public static function get_scored_main_group_total($main_id, $audit_name_id, $beat_plan_id) {
        $total = \frontend\modules\masters\models\BeatPlanAssignTemplete::findOne(['beat_plan_id' => $beat_plan_id])->details;
        $sql = "select group_concat(id) as val from tbl_parameters where main_parameter_id='" . $main_id . "' and audit_name_id='" . $audit_name_id . "' and id in(" . $total . ")";

        $count = \Yii::$app->db->createCommand($sql)->queryOne();
        // print_r($count['val']);
        //$tot_id=$count['val'];
        $tot_id = explode(",", $count['val']);
        $model = \frontend\modules\mobile\models\TblAuditorForm::find()->where(['beat_plan_id' => $beat_plan_id])->andWhere(['in', 'parameter_id', $tot_id])->sum('parameter_value');
        return $model;
        //die();
    }

    public static function get_scored_main_group_total_scroable($main_id, $audit_name_id, $beat_plan_id) {
        $total = \frontend\modules\masters\models\BeatPlanAssignTemplete::findOne(['beat_plan_id' => $beat_plan_id])->details;

        $sql = "select group_concat(id) as val from tbl_parameters where main_parameter_id='" . $main_id . "' and audit_name_id='" . $audit_name_id . "' and id in(" . $total . ")";
        $count = \Yii::$app->db->createCommand($sql)->queryOne();
        $tot_id = explode(",", $count['val']);

        //$get_table_id=  \frontend\modules\mobile\models\TblAuditorForm::find()->where(['beat_plan_id'=>$beat_plan_id])->andwhere(['in','parameter_id',$tot_id])->andwhere(['=','option_id',0])->all();
        //print_r($get_table_id);
        //die();

        $sqloption_zero = "select group_concat(parameter_id) as val from tbl_auditor_form where parameter_id IN (" . $count['val'] . ") and beat_plan_id=" . $beat_plan_id . "  and option_id=0";
        $countoption_zero = \Yii::$app->db->createCommand($sqloption_zero)->queryOne();
        $countoption_total_id_zero = explode(",", $countoption_zero['val']);



        $sqloption = "select group_concat(id) as val from tbl_options where parameter_id IN (" . $count['val'] . ") and option_text='NA'";
        $countoption = \Yii::$app->db->createCommand($sqloption)->queryOne();
        $countoption_total_id = explode(",", $countoption['val']);

        if ($countoption['val'] != '') {

            $sqloption_get_id = "select group_concat(parameter_id) as val from tbl_auditor_form where beat_plan_id='" . $beat_plan_id . "' and parameter_id in (" . $count['val'] . ") and option_id not IN (" . $countoption['val'] . ")";
            $countoptions = \Yii::$app->db->createCommand($sqloption_get_id)->queryOne();
        } else {

            $sqloption_get_id = "select group_concat(parameter_id) as val from tbl_auditor_form where beat_plan_id='" . $beat_plan_id . "' and parameter_id in (" . $count['val'] . ")";
            $countoptions = \Yii::$app->db->createCommand($sqloption_get_id)->queryOne();
        }

        $tot_ids = explode(",", $countoptions['val']);

        if ($countoption_zero['val'] != '') {
            $model = \frontend\modules\mobile\models\TblParameters::find()->andWhere(['in', 'id', $tot_ids])->andwhere(['not in', 'id', $countoption_total_id_zero])->sum('scorable_points');
        } else {
            $model = \frontend\modules\mobile\models\TblParameters::find()->andWhere(['in', 'id', $tot_ids])->sum('scorable_points');
        }

        // print_r($model);

        if ($model == '') {
            $model = 0;
        }
        //die();
        //$model = \frontend\modules\mobile\models\TblParameters::find()->andWhere(['in', 'id', $tot_ids])->sum('scorable_points');
        // $model = \frontend\modules\mobile\models\TblAuditorForm::find()->where(['beat_plan_id' => $beat_plan_id])->andWhere(['in', 'parameter_id', $tot_id])->andwhere(['not in','option_id',$countoption_total_id])->sum('parameter_value');
        return $model;
    }

    public static function get_scored_attr_group_total($main_id, $audit_name_id, $beat_plan_id) {

        $total = \frontend\modules\masters\models\BeatPlanAssignTemplete::findOne(['beat_plan_id' => $beat_plan_id])->details;
        $sql = "select group_concat(id) as val from tbl_parameters where parameter_cat_id='" . $main_id . "' and audit_name_id='" . $audit_name_id . "' and  id in(" . $total . ")";

        $count = \Yii::$app->db->createCommand($sql)->queryOne();
        // print_r($count['val']);
        $tot_id = explode(",", $count['val']);
        //print_r($tot_id);
        //die();
        //print_r (explode(" ",$str));


        $model = \frontend\modules\mobile\models\TblAuditorForm::find()->where(['beat_plan_id' => $beat_plan_id])->andWhere(['IN', 'parameter_id', $tot_id])->sum('parameter_value');
        // print_r($model);
        //die();
        return $model;
        //die();
    }

    public static function getfinal_Attribute_total($main_id, $audit_name_id, $beat_plan_id) {
        $total = \frontend\modules\masters\models\BeatPlanAssignTemplete::findOne(['beat_plan_id' => $beat_plan_id])->details;
        $sql = "select group_concat(id) as val from tbl_parameters where parameter_cat_id='" . $main_id . "' and audit_name_id='" . $audit_name_id . "' and id in (" . $total . ")";

        $count = \Yii::$app->db->createCommand($sql)->queryOne();
        $tot_id = explode(",", $count['val']);



        $sql = "select group_concat(option_id) as id from tbl_auditor_form where parameter_id in (" . $count['val'] . ") and beat_plan_id='" . $beat_plan_id . "' and  status=1";

        $count_option_id = \Yii::$app->db->createCommand($sql)->queryOne();

        $tot_id_opt = explode(",", $count_option_id['id']);


        $get_all_option_count = \frontend\modules\mobile\models\TblOptions::find()->where(['IN', 'id', $tot_id_opt])->andWhere(['option_text' => 'NCF'])->count();
        //echo $get_all_option_count;

        if ($get_all_option_count == 0) {

            $model = \frontend\modules\mobile\models\TblAuditorForm::find()->where(['beat_plan_id' => $beat_plan_id])->andWhere(['IN', 'parameter_id', $tot_id])->sum('parameter_value');
        } else {

            $model = 0;
        }
        return $model;
        //print_r($get_all_option);
        // die();
        //$model = \frontend\modules\mobile\models\TblAuditorForm::find()->where(['beat_plan_id' => $beat_plan_id])->andWhere(['IN', 'parameter_id', $tot_id])->sum('parameter_value');
        // print_r($model);
        //die();
    }

    public static function get_scored_main_group_total_only_cat($main_id, $audit_name_id, $beat_plan_id) {
        $total = \frontend\modules\masters\models\BeatPlanAssignTemplete::findOne(['beat_plan_id' => $beat_plan_id])->details;

        $sql = "select group_concat(id) as val from tbl_parameters where parameter_cat_id='" . $main_id . "' and audit_name_id='" . $audit_name_id . "' and id in (" . $total . ")";

        $count = \Yii::$app->db->createCommand($sql)->queryOne();
        // print_r($count['val']);
        //$tot_id=$count['val'];
        $tot_id = explode(",", $count['val']);
        $model = \frontend\modules\mobile\models\TblAuditorForm::find()->where(['beat_plan_id' => $beat_plan_id])->andWhere(['in', 'parameter_id', $tot_id])->sum('parameter_value');
        return $model;
        //die();
    }

    public static function get_total_media_data($beatPlanId, $audit_name_id) {
        $sql = "select group_concat(t.parameter_id) as id from tbl_auditor_form t , tbl_parameters p where t.parameter_id=p.id and t.beat_plan_id=" . $beatPlanId;
        $count = \Yii::$app->db->createCommand($sql)->queryOne();
        $tot_id = explode(",", $count['id']);
        $result = \frontend\modules\mobile\models\TblParameters::find()->where(['audit_name_id' => $audit_name_id])->andWhere(['in', 'id', $tot_id])->orderBy('parameter_cat_id,main_parameter_id,sub_parameter_id,id')->all();
        return $result;
        //die();
    }

    public static function get_total_beat_assign_templete_id($audit_name_id) {
        $sql = "select group_concat(id) as id from tbl_parameters where audit_name_id=" . $audit_name_id . "  and status=1";
        $count = \Yii::$app->db->createCommand($sql)->queryOne();
        // $tot_id = explode(",", $count['id']);
        // $result = \frontend\modules\mobile\models\TblParameters::find()->where(['audit_name_id' => $audit_name_id])->andWhere(['in', 'id', $tot_id])->orderBy('parameter_cat_id')->all();
        return $count;
        //die();
    }

    public static function get_total_parameter_in_beatplan($beat_plan_id) {
        $model = \frontend\modules\masters\models\BeatPlanAssignTemplete::findOne(['beat_plan_id' => $beat_plan_id])->details;
        $tot_id = explode(",", $model);
        return $tot_id;
    }

    public static function get_scored_group_total_scroable($parameter_cat_id, $beatplan_id) {

        $total = \frontend\modules\masters\models\BeatPlanAssignTemplete::findOne(['beat_plan_id' => $beatplan_id])->details;
        $sql = "select group_concat(id) as val from tbl_parameters where parameter_cat_id='" . $parameter_cat_id . "' and  id in(" . $total . ")";
        $count = \Yii::$app->db->createCommand($sql)->queryOne();

        $sqloption_zero = "select group_concat(parameter_id) as val from tbl_auditor_form where parameter_id IN (" . $count['val'] . ") and beat_plan_id=" . $beatplan_id . "  and option_id=0";
        $countoption_zero = \Yii::$app->db->createCommand($sqloption_zero)->queryOne();
        $countoption_total_id_zero = explode(",", $countoption_zero['val']);

        $sqloption = "select group_concat(id) as val from tbl_options where parameter_id IN (" . $count['val'] . ") and option_text='NA'";
        $countoption = \Yii::$app->db->createCommand($sqloption)->queryOne();
        $countoption_total_id = explode(",", $countoption['val']);

        if ($countoption['val'] != '') {

            $sqloption_get_id = "select group_concat(parameter_id) as val from tbl_auditor_form where beat_plan_id='" . $beatplan_id . "' and parameter_id in (" . $count['val'] . ") and option_id not IN (" . $countoption['val'] . ")";
            $countoptions = \Yii::$app->db->createCommand($sqloption_get_id)->queryOne();
        } else {

            $sqloption_get_id = "select group_concat(parameter_id) as val from tbl_auditor_form where beat_plan_id='" . $beatplan_id . "' and parameter_id in (" . $count['val'] . ")";
            $countoptions = \Yii::$app->db->createCommand($sqloption_get_id)->queryOne();
        }


        $tot_id = explode(",", $count['val']);
        //$//sqloption_get_id = "select group_concat(parameter_id) as val from tbl_auditor_form where beat_plan_id='" . $beatplan_id . "' and parameter_id in (" . $count['val'] . ") and option_id not IN (" . $countoption['val'] . ")";
        // $countoptions = \Yii::$app->db->createCommand($sqloption_get_id)->queryOne();
        $tot_ids = explode(",", $countoptions['val']);

        if ($countoption_zero['val'] != '') {
            $model = \frontend\modules\mobile\models\TblParameters::find()->andWhere(['in', 'id', $tot_ids])->andwhere(['not in', 'id', $countoption_total_id_zero])->sum('scorable_points');
        } else {
            $model = \frontend\modules\mobile\models\TblParameters::find()->andWhere(['in', 'id', $tot_ids])->sum('scorable_points');
        }




        if ($model == '') {
            $model = 0;
        }
        return $model;
    }
    
    
    
    
    public function getEmUserName($id){
        $model = User::findOne(['id' => $id]);
        return empty($model)?"Unknown":$model->username;
    }

    public function getSenderList(){
        $id = Yii::$app->user->id;
        $getUserRole = User::findOne(['id' => $id]);
        $roleArray = [];
        if($getUserRole->user_type === 1){
            $roleArray = [6,5,3];
        } elseif ($getUserRole->user_type === 3){
            $roleArray = [6,5];
        } elseif ($getUserRole->user_type === 5){
            $roleArray = [3];
        } elseif ($getUserRole->user_type === 6){
            $roleArray = [3];
        }
        $model = User::find()->where(['user_type' => $roleArray])->all();
        return ArrayHelper::map($model,'id', 'username');
    }

    public function getReplySenderList($ids){
        if(is_array($ids)){
            $idArr = $ids;
        } else {
            $idArr = explode(',',$ids);
        }

        $id = Yii::$app->user->id;
        $getUserRole = User::findOne(['id' => $id]);
        $roleArray = [];
        if($getUserRole->user_type === 1){
            $roleArray = [6,5,3];
        } elseif ($getUserRole->user_type === 3){
            $roleArray = [6,5];
        } elseif ($getUserRole->user_type === 5){
            $roleArray = [3];
        } elseif ($getUserRole->user_type === 6){
            $roleArray = [3];
        }
        $model = User::find()->where(['user_type' => $roleArray])->all();
        $replyRecipients = User::find()->where(['id' => $idArr])->all();
        $mergeArray = ArrayHelper::merge($model,$replyRecipients);
        return ArrayHelper::map($mergeArray,'id', 'username');
    }

    public function getEmCount($status){

        if($status === 1){
            $count = Notification::find()->where(['receiver_id' => Yii::$app->user->id, 'notification_status' => $status])->andWhere(['=', 'read_status', 0])->count();
        } elseif ($status === 2) {
            $count = Notification::find()->where(['sender_id' => Yii::$app->user->id, 'notification_status' => $status])->andWhere(['=', 'read_status', 0])->count();
        } elseif ($status === 3){
            $count = Notification::find()->where(['notification_status' => 3])->andWhere(['created_by' => Yii::$app->user->id])->count();
        } elseif ($status === 4){
            $count = Notification::find()->where(['notification_status' => 4])->andWhere(['created_by' => Yii::$app->user->id])->count();
        }

        if(empty($count)){

            $count = 0;
        }

        return $count;
    }

    public function checkAttachment($script = false, $id = false){

        $checkforAtt = NotificationAttachment::find()->where(new Expression('FIND_IN_SET(:notification_id, notification_id)'))->addParams([':notification_id' => $id])->count();
        if(empty($checkforAtt)){
            $checkForAttachment = NotificationAttachment::find()->where(['file_script' => $script])->count();
        } else {

            $checkForAttachment = NotificationAttachment::find()->where(new Expression('FIND_IN_SET(:notification_id, notification_id)'))->addParams([':notification_id' => $id])->count();
        }

        if(empty($checkForAttachment)){
            $attachment = '';
        } elseif ($checkForAttachment == 0){
            $attachment = '';
        } else {
            $attachment = '<i class="fa fa-paperclip"></i>';
        }


        return $attachment;
    }

    // public function getEmDateFormat($time){
    //     $getCurrentTimestamp = strtotime(date('Y-m-d h:i:s'));
    //     $time = strtotime($time);
    //     $timeDiff = $getCurrentTimestamp - $time;
    //     if($timeDiff>86400){
    //         $showTime = Yii::$app->formatter->asDatetime($time);
    //     } else {
    //         $showTime = Yii::$app->formatter->asRelativeTime($time);
    //     }
    //     return $showTime;
    // }

    public function getUserNames($str){
        $arr = explode(',', $str);
        foreach ($arr as $ar){
            $names[$ar] = $this->getEmUserName($ar);
        }

        return implode(', ',$names);
    }

    public function getNotifyCount($type){
        if($type == 1){
            $count = TblNotifications::find()->where(['read_flag' => 1, 'user_id' => Yii::$app->user->id])->count();
        } elseif ($type == 2){
            $count = TblNotifications::find()->where(['read_flag' => 0, 'user_id' => Yii::$app->user->id])->count();

        } elseif ($type == 3){
            $count = TblNotifications::find()->where(['user_id' => Yii::$app->user->id])->count();
        }

        return $count;
    }
    
    public function encryptUserData( $data ) {
        $cryptKey  = 'abCDjdskf48756125SFxvB';
        $encodedData      = base64_encode( mcrypt_encrypt( MCRYPT_RIJNDAEL_256, md5( $cryptKey ), $data, MCRYPT_MODE_CBC, md5( md5( $cryptKey ) ) ) );
        return( $encodedData );
    }

    function decryptUserData( $data ) {
        $cryptKey  = 'abCDjdskf48756125SFxvB';
        $decodedData      = rtrim( mcrypt_decrypt( MCRYPT_RIJNDAEL_256, md5( $cryptKey ), base64_decode( $data ), MCRYPT_MODE_CBC, md5( md5( $cryptKey ) ) ), "\0");
        return( $decodedData );
    }

    public function authenticateAccessToken($token){
        $decryptToken = $this->decryptUserData($token);

        $getArray = explode('$#@!k', $decryptToken);
        $countAray = count($getArray);

        if($countAray !=2){
            $response = [
                'authentication' => 0,
                'userid' => 'Invalid user'
            ];

            return $response;
        } else {
            $username = $getArray[0];
            $password = $getArray[1];
            $model = new AppLogin();

            $model->username = $username;
            $model->password = $password;

            if($model->login()){
                $response = [
                    'authentication' => 1,
                    'userid' => Yii::$app->user->identity->id
                ];

                return $response;
            } else {
                $response = [
                    'authentication' => 0,
                    'userid' => 'Invalid user'
                ];

                return $response;
            }
        }

    }

public function getEmDateFormat($time){
        $getCurrentTimestamp = strtotime(date('Y-m-d H:i:s'));
        $time = strtotime($time);
        $timeDiff = $getCurrentTimestamp - $time;
        if($timeDiff>86400){
            $showTime = Yii::$app->formatter->asDatetime($time);
        } else {
            $showTime = Yii::$app->formatter->asRelativeTime($time);
        }

        return $showTime;
    }

}
