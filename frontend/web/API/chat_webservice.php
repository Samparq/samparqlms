<?php

require 'db_config.php';
date_default_timezone_set('Asia/Calcutta');
$current_date_time = date('D,Y-m-d H:i:s');
$cureent_date = date('Y-m-d');

$Url = 'http://hrd.qdegrees.com/API/Chat_Files/';
$Url_ProImage = 'http://hrd.qdegrees.com/API/Upload_Files/';
if (isset($_POST['inset_chat_message'])) {
    $Sender_Id = $_POST['sender_id'];
    $Receiver_Id = $_POST['receiver_id'];
    $msg = $_POST['message_str'];
    $MsgStr = str_replace("\\", "\\\\", $msg);
    $Message_Str = str_replace("'", "\'", $MsgStr);
    $Message_Insert_query = mysql_query("insert into chat set sender_id='" . $Sender_Id . "',receiver_id='" . $Receiver_Id . "',message='" . $Message_Str . "',process_date='" . $current_date_time . "'")or die(mysql_error());
    if (!empty($Message_Insert_query)) {
        $Report = array();
        $Report['success'] = 1;
        $Report['message'] = 'Message sent Successfully!!';
        $chat_id = mysql_insert_id();
        $Report['id'] = $chat_id;
        echo json_encode($Report);
        /*         * ********************************************************* */
        $GetRegId = mysql_query("select (select name from user where id='" . $Sender_Id . "') as name,app_regid from user where id='" . $Receiver_Id . "'")or die(mysql_error());
        while ($row = mysql_fetch_array($GetRegId, MYSQL_ASSOC)) {
            $reg_id = $row['app_regid'];
            $Receiver_name = $row['name'];
            $fcmApiKey = 'AIzaSyA3ACIrZoCQLyv7kGvfTgmy_0-H3UlpLlI'; //App API Key(This is google cloud messaging api key not web api key)
            // API access key from Google API's Console
            define('API_ACCESS_KEY', 'AIzaSyA3ACIrZoCQLyv7kGvfTgmy_0-H3UlpLlI');
            $registrationIds = array($reg_id);
            // prep the bundle
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
            die();
            //echo $result;
        }

        /*         * ********************************************************* */
    } else {
        $Report = array();
        $Report['success'] = 0;
        $Report['message'] = 'Message sent Failed!!';
        echo json_encode($Report);
        die();
    }
}
/* * ***************************************** Get Chat Details*********************************************************** */
if (isset($_POST['get_chat_list'])) {
    $Sender_Id = $_POST['sender_id'];
    $Receiver_Id = $_POST['receiver_id'];
    $Response = array();
    $Response['chat_list'] = array();
    $GetChatList_Q = mysql_query("select * from chat where (sender_id='" . $Sender_Id . "' and receiver_id='" . $Receiver_Id . "'  and sender_flag=1)or(sender_id='" . $Receiver_Id . "' and receiver_id='" . $Sender_Id . "'  and receiver_flag=1)") or die(mysql_error());
    while ($row = mysql_fetch_array($GetChatList_Q, MYSQL_ASSOC)) {
        $Report = array();
        $Report['id'] = $row['id'];
        $Report['sender_id'] = $row['sender_id'];
        $Report['receiver_id'] = $row['receiver_id'];
        $Report['message'] = str_replace("\\\\", "\\", $row['message']);
        $Report['file_status'] = $row['file_status'];
        $Report['file_name'] = $row['file_name'];
        $Report['file_extention'] = $row['file_extention'];
        $Report['file_orignal_name'] = $row['file_orignal_name'];
        $Report['file_path'] = $Url . $row['file_name'];
        $Report['process_date'] = $row['process_date'];
        $Report['read_flag'] = $row['read_flag'];
        array_push($Response['chat_list'], $Report);
    }
    echo json_encode($Response);
    die();
}
/* * ***************************************Upload Chat Message**************************************************************************** */
if (isset($_POST['upload_chat_image'])) {
    $Sender_Id = $_POST['sender_id'];
    $Receiver_Id = $_POST['receiver_id'];
    $msg = $_POST['message_str'];
    $MsgStr = str_replace("\\", "\\\\", $msg);
    $Message_Str = str_replace("'", "\'", $MsgStr);
    $Orignal_File1_Name = $_POST['Orignal_Name'];
    $File1_Extention = $_POST['ext'];
    if (is_uploaded_file($_FILES['Filedata']['tmp_name'])) {
        //$uploads_dir = '/Upload_Files';
        $uploads_dir = $_SERVER["DOCUMENT_ROOT"] . "/API/Chat_Files/";
        //$uploads_dir = $_SERVER["DOCUMENT_ROOT"]."/qds_samparq/API/Chat_Files/";
        $tmp_name = $_FILES['Filedata']['tmp_name'];
        $pic_name = $_FILES['Filedata']['name'];
        $fname = $uploads_dir . $pic_name;
        move_uploaded_file($tmp_name, $fname);
        $Message_Insert_query = mysql_query("insert into chat set sender_id='" . $Sender_Id . "',receiver_id='" . $Receiver_Id . "',message='" . $Message_Str . "',process_date='" . $current_date_time . "',file_status=1,file_name='" . $pic_name . "',file_extention='" . $File1_Extention . "',file_orignal_name='" . $Orignal_File1_Name . "',file_path='" . $fname . "'")or die(mysql_error());
        $Report = array();
        $Report['success'] = 1;
        $Report['message'] = 'Message sent Successfully!!';
        $chat_id = mysql_insert_id();
        $Report['id'] = $chat_id;
        $Report['image_path'] = $Url.$pic_name;
        echo json_encode($Report);
        /*         * ********************************************************* */
        $GetRegId = mysql_query("select name,app_regid,(select name from user where id='".$Sender_Id."') as sender_name from user where id='" . $Receiver_Id . "'")or die(mysql_error());
        while ($row = mysql_fetch_array($GetRegId, MYSQL_ASSOC)) {
            $reg_id = $row['app_regid'];
            $Receiver_name = $row['name'];
            $Sender_name=$row['sender_name'];
            $fcmApiKey = 'AIzaSyA3ACIrZoCQLyv7kGvfTgmy_0-H3UlpLlI'; //App API Key(This is google cloud messaging api key not web api key)
            // API access key from Google API's Console
            define('API_ACCESS_KEY', 'AIzaSyA3ACIrZoCQLyv7kGvfTgmy_0-H3UlpLlI');
            $registrationIds = array($reg_id);
            // prep the bundle
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
                'file_path' => $Url . $pic_name,
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
            //echo $result;
        }
    } else {
        $Report = array();
        $Report['success'] = 0;
        $Report['message'] = 'Message sent failed!!';
        echo json_encode($Report);
        die();
    }
}
/* * ************************************************Update Chat Status********************************* */
if (isset($_POST['update_chat_status'])) {
    $Sender_ID = $_POST['sender_id'];
    $Receiver_Id = $_POST['receiver_id'];
    $get_Unread_List = mysql_query("select id,(select app_regid from user where id = c.sender_id) as reg_id from chat c where sender_id='" . $Sender_ID . "' and receiver_id='" . $Receiver_Id . "' and read_flag=0")or die(mysql_error());
    while ($row = mysql_fetch_array($get_Unread_List, MYSQL_ASSOC)) {
        $chat_id = $row['id'];
        $reg_id = $row['reg_id'];
        $Update_Query = mysql_query("update chat set read_flag=1 where id='" . $chat_id . "' and read_flag=0") or die(mysql_error());
        $fcmApiKey = 'AIzaSyA3ACIrZoCQLyv7kGvfTgmy_0-H3UlpLlI'; //App API Key(This is google cloud messaging api key not web api key)
        // API access key from Google API's Console
        define('API_ACCESS_KEY', 'AIzaSyA3ACIrZoCQLyv7kGvfTgmy_0-H3UlpLlI');
        $registrationIds = array($reg_id);
        // prep the bundle
        $msg = array
            (
            'receiver_id' => $Receiver_Id,
            'id' => $chat_id,
            'sender_id' => $Sender_ID,
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
        //echo $result;
    }
}
/* * ***********************************************get Last Chat_List***************************** */
if (isset($_POST['get_group_chat_list'])) {
    $Receiver_id = $_POST['receiver_id'];
    $Response = array();
    $Response['chat_group_list'] = array();
    /* $My_New_Query=mysql_query("select id,(select name from user where id=c.receiver_id)as name,(select image_name from user where id=c.receiver_id)as image_name,receiver_id as sender,(select count(*) from chat where sender_id=c.receiver_id and read_flag=0)as Total,(case when ( (select max(id) from chat where receiver_id=c.receiver_id and sender_id='".$Receiver_id."') > (select max(id) from chat where receiver_id=c.sender_id and sender_id=c.receiver_id) ) THEN(select message from chat where id=(select max(id) from chat where receiver_id=c.receiver_id and sender_id='".$Receiver_id."')) ELSE ifnull((select message from chat where id=(select max(id) from chat where receiver_id=c.sender_id and sender_id=c.receiver_id)),(select message from chat where id=(select max(id) from chat where receiver_id=c.receiver_id and sender_id='".$Receiver_id."'))) END) as tmp from chat c where (receiver_id!='".$Receiver_id."' OR sender_id='".$Receiver_id."') group by receiver_id order by Total DESC")or die(mysql_error()); */
    $My_New_Query = mysql_query("SELECT id,
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
 group by receiver_id,sender_id order by id desc")or die(mysql_error());
    $my_chat_Q = mysql_query("select sender_id,(select count(*) from chat where sender_id=c.sender_id and read_flag=0)as Total,MAX(message)as message,(select name from user where id=c.sender_id)as name,(select image_name from user where id=c.sender_id)as image_name  from chat c where receiver_id='" . $Receiver_id . "'  group by sender_id")or die(mysql_error());
    while ($row = mysql_fetch_array($My_New_Query, MYSQL_ASSOC)) {
        $Report = array();
        $Report['id'] = $row['sender'];
        $Report['Total'] = $row['total'];
        $Report['message'] = $row['message'];
        $Report['name'] = $row['name'];
        $Report['image_path'] = $Url_ProImage . $row['image_name'];
        array_push($Response['chat_group_list'], $Report);
    }
    echo json_encode($Response);
    die();
}
/* * **************************************************************************************************** */
if (isset($_POST['update_Single_chat_status'])) {
    $chat_id = $_POST['chat_id'];
    $Update_Qery = mysql_query("update chat set read_flag='1' where id='" . $chat_id . "'") or die(mysql_error());
    if (!empty($Update_Qery)) {
        $select_Receiver_id = mysql_query("select sender_id,receiver_id,(select app_regid from user where id=c.sender_id)as reg_id from chat c where id='" . $chat_id . "'")or die(mysql_error());
        while ($row = mysql_fetch_array($select_Receiver_id, MYSQL_ASSOC)) {
            $Reg_id = $row['reg_id'];
            $Sender_Id = $row['sender_id'];
            $Receiver_Id = $row['receiver_id'];
            $fcmApiKey = 'AIzaSyA3ACIrZoCQLyv7kGvfTgmy_0-H3UlpLlI';  //App API Key(This is google cloud messaging api key not web api key)
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
            //echo $result;
        }
    }
}
/* * ******************************************************************************************************* */
if (isset($_POST['delete_multi_chat'])) {
    $User_Id = $_POST['user_id'];
    $Chat_id = $_POST['chat_id'];
    $Mysql_Receiver_Q = mysql_query("update chat set receiver_flag=0 where receiver_id='" . $User_Id . "' and id='" . $Chat_id . "'")or die(mysql_error());
    $Mysql_Sender_Q = mysql_query("update chat set sender_flag=0 where sender_id='" . $User_Id . "' and id='" . $Chat_id . "'")or die(mysql_error());
    $Report = array();
    $Report['success'] = 1;
    echo json_encode($Report);
    die();
}
?>