<?php
	$fcmApiKey = 'AIzaSyA3ACIrZoCQLyv7kGvfTgmy_0-H3UlpLlI';//App API Key(This is google cloud messaging api key not web api key)
// API access key from Google API's Console
define( 'API_ACCESS_KEY', 'AIzaSyA3ACIrZoCQLyv7kGvfTgmy_0-H3UlpLlI' );
$registrationIds = array('fwj55akGAJk:APA91bEDkiDanZPRMbFXFfUE2sR1Hy8DGo8sAI_mRrf17OJKpbBO97K0qXPMkDXnauwBeRnhYdBzQBeuRIendKYns7UGrGLRNC4JQau69H1A2Es-LNadsAsRGuFql4jUhfN_xaYgwwL6');
// prep the bundle
$msg = array
(
	'receiver_id'=>'2',
	'id'=>'7',
	'sender_id'=>'7',
	'message'=>'Hiiiiii \uD83D\uDE03\uD83D\uDE1C\uD83D\uDE22\uD83C\uDF81\uD83C\uDF84\uD83C\uDF85 ',
	'file_status'		=> '1',
	'file_name'		=> '',
	'file_extention'		=> '.jpg',
	'File_OrignalName'		=> '',
	'file_path'		=> 'http://192.168.4.58/qds_samparq/API/Chat_Files/7Chat2img20.jpg',
	'process_type'		=> 'chat_read_type',
	'inbox_id'		=> '81',
	'subject'		=> 'Hello Notification',
	'Sendername'		=> 'Sanjeev Poonia',
	'process_date'		=> '2017-05-03 11:14:24',
	'read_flag'		=> '0',
	'receiver_name'		=> 'SuperAdmin'
);
$fields = array
(
	'registration_ids' 	=> $registrationIds,
	'data'			=> $msg
);
$headers = array
(
	'Authorization: key=' . API_ACCESS_KEY,
	'Content-Type: application/json'
);
$ch = curl_init();
curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
curl_setopt( $ch,CURLOPT_POST, true );
curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
$result = curl_exec($ch );
curl_close( $ch );
echo $result;
?>
