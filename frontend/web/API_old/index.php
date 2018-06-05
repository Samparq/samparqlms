<?php
require 'db_config.php';
date_default_timezone_set ( 'Asia/Calcutta' );
$current_date_time=date('Y-m-d H:i:s');
$cureent_date=date('Y-m-d');
$Url='http://192.168.4.201/yiiapi/frontend/web/API/Upload_Files/';
/**************************************** For LOg In ***************************************************/
if(isset($_POST['login']))
{
	$RegiD=$_POST['reg_id'];
	$Imei=$_POST['imei_id'];
	$User_id=$_POST['u_name'];
	$log=mysql_query("select * from user where (username='".$_POST['u_name']."' AND password_hash='".$_POST['password']."' AND flag='ACTIVE')") or die(mysql_error());
	$num=mysql_num_rows($log);
		if($num>0)
		{ 
			$Update=mysql_query("update user set imei_app='".$Imei."',app_regid='".$RegiD."',update_status=0 where username='".$User_id."'") or die(mysql_error());
			$report = array();
			$row=mysql_fetch_array($log);
			$report["success"] = 1;
			$report["id"] = $row['id'];
			$report["username"] = $row['username'];
			$report["email"] = $row['email'];
			$report["status"] = $row['status'];
			$report["created_at"] = $row['created_at'];
			$report["updated_at"] = $row['updated_at'];
			$report["user_type"] = $row['user_type'];
			$report["name"] = $row['name'];
			$report["last_name"] = $row['last_name'];
			$report["flag"] = $row['flag'];
			$report["employee_id"] = $row['employee_id'];
			$report["dob"] = $row['dob'];
			$report["image_path"] = $row['image_path'];
			$report["image_name"] = $row['image_name'];
			$report["mobile_no"] = $row['mobile_no'];
			$report["key"] = $row['key'];
			$report["message"] = "Login successfully";
						echo json_encode($report);
		}
		else
		{
				$report = array();
				$report["success"] = 0;
				$report["message"] = "Login Failed!!";
				echo json_encode($report); die();
		}

}
/******************************************Signup Qds SamparQ************************************/
if(isset($_POST['sign_up'])){
	
	$Mail_str=$_POST['mail_id'];
	$Birthday_date=$_POST['birthday'];
	$EmployeeId_Str=$_POST['employee_id'];
	$Password=$_POST['password'];
	$Image_Str=$_POST['image'];
	$Image_decoded=base64_decode($Image_Str);
	$timeStamp=time();
	$imagename=$timeStamp.$EmployeeId_Str.'.jpeg';
	
	$sign=mysql_query("select * from user where username='".$Mail_str."'") or die(mysql_error());
	$num=mysql_num_rows($sign);
	if($num>0){
		$Report=array();
		$Report['success']=0;
		$Report['message']='EMail id already existed!!';
		echo json_encode($Report);
	}else{
		$InsertQuerysignupDetails="INSERT INTO user(username,password_hash,email,created_at,employee_id,dob,image_name) VALUES ('".$Mail_str."','".$Password."','".$Mail_str."','".$current_date_time."','".$EmployeeId_Str."','".$Birthday_date."','".$imagename."')";
		$insertDetail=mysql_query($InsertQuerysignupDetails) or die(mysql_error());
		
		$fp = fopen($imagename, 'w');
	fwrite($fp, $Image_decoded);
	if(fclose($fp)){
		if(!empty($insertDetail)){
		$report = array();
		$report["success"] = 1;
		$report["message"] = "Signup Successfully";
		$report['id']=mysql_insert_id();
		echo json_encode($report); die();
	}else
	{   $report = array();
		$report["success"] = 0;
		$report["message"] = " not successfully";
		echo json_encode($report); die();
	}
	}else{
			 $report = array();
		$report["success"] = 0;
		$report["message"] = " not successfully";
		echo json_encode($report); die();
	}
		
	

	}
	
	
	
}

/****************************** get profileType**************************************/
if(isset($_POST['getprofile_type'])){
	$User_id=$_POST['user_id'];
	$Username=$_POST['username'];
	$My_ProType_Q=mysql_query("select * from profile where status='1'")or die(mysql_error());
	
	$response=array();
         $response['my_profile_list']=array();

             while ($row = mysql_fetch_array($My_ProType_Q, MYSQL_ASSOC)) {
                 $Report=array();
                 $Report['id']=$row["id"];
                 $Report['type']=$row["type"];
                 $Report["status"] = $row['status'];
                 array_push($response['my_profile_list'],$Report);
}
echo json_encode($response);die();	
}
/*************************Send Mail*************************************/
if(isset($_POST['fileUpload'])){
	$File_Count=$_POST['fileCount'];
	$User_id=$_POST['user_id'];
	$To_Str=$_POST['to'];
	$Subject_Str=str_replace("'","\'",$_POST['Subject']);
	$Message_Str=str_replace("'","\'",$_POST['Message']);
	$To_Details='';
	if($File_Count=='0'){

		$Get_To_Detail=mysql_query("select type from profile where id in(".$To_Str.")") or die(mysql_error());
		 while ($row = mysql_fetch_array($Get_To_Detail, MYSQL_ASSOC)) {
			 if($To_Details==''){
				  $To_Details= $row['type'];
			 }else{
				  $To_Details=$To_Details.','. $row['type'];
			 }
		 }
		 //$Insert_SentBox=mysql_query('insert into sent set mail_to="'.$To_Str.'" , to_detail="'.$To_Details.'" , mail_from="'.$User_id.'" , subject="'.$Subject_Str.'" , message="'.$Message_Str.'", file_status=0')or die(mysql_error());
		 
		 $Insert_SentBox=mysql_query("insert into sent set mail_to='".$To_Str."' , to_detail='".$To_Details."' , mail_from='".$User_id."' , subject='".$Subject_Str."' , message='".$Message_Str."', file_status=0")or die(mysql_error());
		 $sent_id=mysql_insert_id();
		if(!empty($Insert_SentBox))
			{			
				$To_Array=array();
				$To_Array=explode(',',$To_Str);
				for($i=0;$i<sizeof($To_Array);$i++){
					$to_jisd=$To_Array[$i];
					$Select_Username_query=mysql_query("select * from user where user_type='".$to_jisd."' and flag='ACTIVE'") or die(mysql_error());
					while ($row = mysql_fetch_array($Select_Username_query, MYSQL_ASSOC)) {
					$mail_to_userid=$row['id'];
					$Insert_Inbox=mysql_query("insert into inbox set sent_id='".$sent_id."', mail_to='".$to_jisd."', mail_from='".$User_id."', subject='".$Subject_Str."', message='".$Message_Str."', file_status=0,mail_to_userid='".$mail_to_userid."'") or die(mysql_error());
					 
					
					
					
					}
					
				}
				$Report=array();
				$Report['success']=1;
				$Report['message']='Message sent Successfully!!';
				echo json_encode($Report) ; die();
			}else{
				$Report=array();
				$Report['success']=0;
				$Report['message']='Message sent failed!!';
				echo json_encode($Report) ; die();
			}
		
	}else if($File_Count=='1'){
		
			$Orignal_File1_Name=$_POST['Orignal_Name'];
			$File1_Extention=$_POST['ext'];
			$Get_To_Detail=mysql_query("select type from profile where id in(".$To_Str.")") or die(mysql_error());
				while ($row = mysql_fetch_array($Get_To_Detail, MYSQL_ASSOC)) {
					if($To_Details==''){
						$To_Details= $row['type'];
					}else{
					$To_Details=$To_Details.','. $row['type'];
					}		
				}
				
				$Insert_SentBox=mysql_query("insert into sent set mail_to='".$To_Str."' , to_detail='".$To_Details."' , mail_from='".$User_id."' , subject='".$Subject_Str."' , message='".$Message_Str."', file_status=1")or die(mysql_error());
				$sent_id=mysql_insert_id();
				if(!empty($Insert_SentBox))
						{			
							if (is_uploaded_file($_FILES['Filedata']['tmp_name'])) {
									//$uploads_dir = '/Upload_Files';
									$uploads_dir = $_SERVER["DOCUMENT_ROOT"]."/qds_samparq/API/Upload_Files/";
									$tmp_name = $_FILES['Filedata']['tmp_name'];
									$pic_name = $_FILES['Filedata']['name'];
									$fname=$uploads_dir.$pic_name;
									move_uploaded_file($tmp_name, $fname);
												$To_Array=array();
												$To_Array=explode(',',$To_Str);
												for($i=0;$i<sizeof($To_Array);$i++){
													$to_jisd=$To_Array[$i];
													$Select_Username_query=mysql_query("select * from user where user_type='".$to_jisd."' and flag='ACTIVE'") or die(mysql_error());
													while ($row = mysql_fetch_array($Select_Username_query, MYSQL_ASSOC)) {
													$mail_to_userid=$row['id'];	
													$Insert_Inbox=mysql_query("insert into inbox set sent_id='".$sent_id."', mail_to='".$to_jisd."', mail_from='".$User_id."', subject='".$Subject_Str."', message='".$Message_Str."', file_status=1,mail_to_userid='".$mail_to_userid."'") or die(mysql_error());
													
													}
													$inbx_id=mysql_insert_id();
													
													$Insert_UploadFiles=mysql_query("insert into upload_files set  mail_to='".$to_jisd."', mail_from='".$User_id."', file_name='".$pic_name."', file_path='".$fname."', 
													orignal_filename='".$Orignal_File1_Name."',ext='".$File1_Extention."',inbox_id='".$inbx_id."', sent_id='".$sent_id."'") or die(mysql_error());
												}
									$Report=array();
									$Report['success']=1;
									$Report['message']='Message sent Successfully!!';
									echo json_encode($Report) ; die();
								}else
								{   
									$Report=array();
									$Report['success']=0;
									$Report['message']='Message sent failed!!';
									echo json_encode($Report) ; die();
								}
							
							
							
						}else{
							$Report=array();
							$Report['success']=0;
							$Report['message']='Message sent failed!!';
							echo json_encode($Report) ; die();
						}
			
				
	}else if($File_Count=='2'){
		
		$Orignal_File1_Name=$_POST['Orignal_Name1'];
		$Orignal_File2_Name=$_POST['Orignal_Name2'];
			$File1_Extention=$_POST['ext1'];
			$File2_Extention=$_POST['ext2'];
			$Get_To_Detail=mysql_query("select type from profile where id in(".$To_Str.")") or die(mysql_error());
				while ($row = mysql_fetch_array($Get_To_Detail, MYSQL_ASSOC)) {
					if($To_Details==''){
						$To_Details= $row['type'];
					}else{
					$To_Details=$To_Details.','. $row['type'];
					}		
				}
				$Insert_SentBox=mysql_query("insert into sent set mail_to='".$To_Str."' , to_detail='".$To_Details."' , mail_from='".$User_id."' , subject='".$Subject_Str."' , message='".$Message_Str."', file_status=1")or die(mysql_error());
				$sent_id=mysql_insert_id();
				if(!empty($Insert_SentBox))
						{			
					/*if (is_uploaded_file($_FILES['Filedata1']['tmp_name'])) {
						$uploads_dir = './';
								$tmp_name = $_FILES['Filedata1']['tmp_name'];
								$pic_name = $_FILES['Filedata1']['name'];
								move_uploaded_file($tmp_name, $uploads_dir.$pic_name);
								if (is_uploaded_file($_FILES['Filedata2']['tmp_name'])) {
										$uploads_dir2 = './';
										$tmp_name2 = $_FILES['Filedata2']['tmp_name'];
										$pic_name2 = $_FILES['Filedata2']['name'];
										move_uploaded_file($tmp_name2, $uploads_dir2.$pic_name2);
								}else{
									echo "File not uploaded successfully.";}
					}else{	   
							echo "File not uploaded successfully.";}*/
							
							/*****************/
							if (is_uploaded_file($_FILES['Filedata1']['tmp_name'])) {
									//$uploads_dir = '/Upload_Files';
									$uploads_dir = $_SERVER["DOCUMENT_ROOT"]."/qds_samparq/API/Upload_Files/";
									$tmp_name = $_FILES['Filedata1']['tmp_name'];
									$pic_name = $_FILES['Filedata1']['name'];
									$fname=$uploads_dir.$pic_name;
									move_uploaded_file($tmp_name, $fname);
											if (is_uploaded_file($_FILES['Filedata2']['tmp_name'])) {
												$tmp_name2 = $_FILES['Filedata2']['tmp_name'];
												$pic_name2 = $_FILES['Filedata2']['name'];
												$fname2=$uploads_dir.$pic_name2;
												move_uploaded_file($tmp_name2, $fname2);
												
												$To_Array=array();
												$To_Array=explode(',',$To_Str);
												for($i=0;$i<sizeof($To_Array);$i++){
														$to_jisd=$To_Array[$i];
														$Select_Username_query=mysql_query("select * from user where user_type='".$to_jisd."' and flag='ACTIVE'") or die(mysql_error());
													while ($row = mysql_fetch_array($Select_Username_query, MYSQL_ASSOC)) {
													$mail_to_userid=$row['id'];	
													$Insert_Inbox=mysql_query("insert into inbox set sent_id='".$sent_id."', mail_to='".$to_jisd."', mail_from='".$User_id."', subject='".$Subject_Str."', message='".$Message_Str."', file_status=1, mail_to_userid='".$mail_to_userid."'") or die(mysql_error());
													
													}
													$inbx_id=mysql_insert_id();
													
													$Insert_UploadFiles=mysql_query("insert into upload_files set  mail_to='".$to_jisd."', mail_from='".$User_id."', file_name='".$pic_name."', file_path='".$fname."', 
													orignal_filename='".$Orignal_File1_Name."',ext='".$File1_Extention."',inbox_id='".$inbx_id."', sent_id='".$sent_id."'") or die(mysql_error());
													
													$Insert_UploadFiles2=mysql_query("insert into upload_files set  mail_to='".$to_jisd."', mail_from='".$User_id."', file_name='".$pic_name2."', file_path='".$fname2."', 
													orignal_filename='".$Orignal_File2_Name."',ext='".$File2_Extention."',inbox_id='".$inbx_id."', sent_id='".$sent_id."'") or die(mysql_error());
												}
													$Report=array();
													$Report['success']=1;
													$Report['message']='Message sent Successfully!!';
													echo json_encode($Report) ; die();
											}else{
													$Report=array();
													$Report['success']=0;
													$Report['message']='Message sent failed!!';
													echo json_encode($Report) ; die();
											}
									
								}else
								{   
									$Report=array();
									$Report['success']=0;
									$Report['message']='Message sent failed!!';
									echo json_encode($Report) ; die();
								}
							
							
							
						}else{
							$Report=array();
							$Report['success']=0;
							$Report['message']='Message sent failed!!';
							echo json_encode($Report) ; die();
						}
				
				
				
	}else if($File_Count=='3'){
				/*if (is_uploaded_file($_FILES['Filedata1']['tmp_name'])) {
						$uploads_dir = './';
								$tmp_name = $_FILES['Filedata1']['tmp_name'];
								$pic_name = $_FILES['Filedata1']['name'];
								move_uploaded_file($tmp_name, $uploads_dir.$pic_name);
								if (is_uploaded_file($_FILES['Filedata2']['tmp_name'])) {
										$uploads_dir2 = './';
										$tmp_name2 = $_FILES['Filedata2']['tmp_name'];
										$pic_name2 = $_FILES['Filedata2']['name'];
										move_uploaded_file($tmp_name2, $uploads_dir2.$pic_name2);
										if (is_uploaded_file($_FILES['Filedata3']['tmp_name'])) {
											$uploads_dir3 = './';
											$tmp_name3 = $_FILES['Filedata3']['tmp_name'];
											$pic_name3 = $_FILES['Filedata3']['name'];
											move_uploaded_file($tmp_name3, $uploads_dir3.$pic_name3);
										}else{
										echo "File not uploaded successfully.";}
								}else{
									echo "File not uploaded successfully.";}
				}else{	   
					echo "File not uploaded successfully.";}*/
					
				
				$Orignal_File1_Name=$_POST['Orignal_Name1'];
				$Orignal_File2_Name=$_POST['Orignal_Name2'];
				$Orignal_File3_Name=$_POST['Orignal_Name3'];
				$File1_Extention=$_POST['ext1'];
				$File2_Extention=$_POST['ext2'];
				$File3_Extention=$_POST['ext3'];
				
				$Get_To_Detail=mysql_query("select type from profile where id in(".$To_Str.")") or die(mysql_error());
				while ($row = mysql_fetch_array($Get_To_Detail, MYSQL_ASSOC)) {
					if($To_Details==''){
						$To_Details= $row['type'];
					}else{
					$To_Details=$To_Details.','. $row['type'];
					}		
				}
				$Insert_SentBox=mysql_query("insert into sent set mail_to='".$To_Str."' , to_detail='".$To_Details."' , mail_from='".$User_id."' , subject='".$Subject_Str."' , message='".$Message_Str."', file_status=1")or die(mysql_error());
				$sent_id=mysql_insert_id();
				if(!empty($Insert_SentBox))
						{			
							if (is_uploaded_file($_FILES['Filedata1']['tmp_name'])) {
									//$uploads_dir = '/Upload_Files';
									$uploads_dir = $_SERVER["DOCUMENT_ROOT"]."/qds_samparq/API/Upload_Files/";
									$tmp_name = $_FILES['Filedata1']['tmp_name'];
									$pic_name = $_FILES['Filedata1']['name'];
									$fname=$uploads_dir.$pic_name;
									move_uploaded_file($tmp_name, $fname);
											if (is_uploaded_file($_FILES['Filedata2']['tmp_name'])) {
												$tmp_name2 = $_FILES['Filedata2']['tmp_name'];
												$pic_name2 = $_FILES['Filedata2']['name'];
												$fname2=$uploads_dir.$pic_name2;
												move_uploaded_file($tmp_name2, $fname2);
													if (is_uploaded_file($_FILES['Filedata3']['tmp_name'])) {
													$tmp_name3 = $_FILES['Filedata3']['tmp_name'];
													$pic_name3 = $_FILES['Filedata3']['name'];
													$fname3=$uploads_dir.$pic_name3;
													move_uploaded_file($tmp_name3, $fname3);
															$To_Array=array();
															$To_Array=explode(',',$To_Str);
															for($i=0;$i<sizeof($To_Array);$i++){
															$to_jisd=$To_Array[$i];
															$Select_Username_query=mysql_query("select * from user where user_type='".$to_jisd."' and flag='ACTIVE'") or die(mysql_error());
															while ($row = mysql_fetch_array($Select_Username_query, MYSQL_ASSOC)) {
															$mail_to_userid=$row['id'];	
															$Insert_Inbox=mysql_query("insert into inbox set sent_id='".$sent_id."', mail_to='".$to_jisd."', mail_from='".$User_id."', subject='".$Subject_Str."', message='".$Message_Str."', file_status=1,mail_to_userid='".$mail_to_userid."'") or die(mysql_error());
															
															}
															$inbx_id=mysql_insert_id();
													
															$Insert_UploadFiles=mysql_query("insert into upload_files set  mail_to='".$to_jisd."', mail_from='".$User_id."', file_name='".$pic_name."', file_path='".$fname."', 
															orignal_filename='".$Orignal_File1_Name."',ext='".$File1_Extention."',inbox_id='".$inbx_id."', sent_id='".$sent_id."'") or die(mysql_error());
													
															$Insert_UploadFiles2=mysql_query("insert into upload_files set  mail_to='".$to_jisd."', mail_from='".$User_id."', file_name='".$pic_name2."', file_path='".$fname2."', 
															orignal_filename='".$Orignal_File2_Name."',ext='".$File2_Extention."',inbox_id='".$inbx_id."', sent_id='".$sent_id."'") or die(mysql_error());
															
															$Insert_UploadFiles3=mysql_query("insert into upload_files set  mail_to='".$to_jisd."', mail_from='".$User_id."', file_name='".$pic_name3."', file_path='".$fname3."', 
															orignal_filename='".$Orignal_File3_Name."',ext='".$File3_Extention."',inbox_id='".$inbx_id."', sent_id='".$sent_id."'") or die(mysql_error());
															}
																$Report=array();
																$Report['success']=1;
																$Report['message']='Message sent Successfully!!';
															echo json_encode($Report) ; die();
													}else{
													$Report=array();
													$Report['success']=0;
													$Report['message']='Message sent failed!!';
													echo json_encode($Report) ; die();
											}
											}else{
													$Report=array();
													$Report['success']=0;
													$Report['message']='Message sent failed!!';
													echo json_encode($Report) ; die();
											}
									
								}else
								{   
									$Report=array();
									$Report['success']=0;
									$Report['message']='Message sent failed!!';
									echo json_encode($Report) ; die();
								}
							
							
							
						}else{
							$Report=array();
							$Report['success']=0;
							$Report['message']='Message sent failed!!';
							echo json_encode($Report) ; die();
						}
				
		
					
	}else if($File_Count=='4'){
				/*if (is_uploaded_file($_FILES['Filedata1']['tmp_name'])) {
						$uploads_dir = './';
							$tmp_name = $_FILES['Filedata1']['tmp_name'];
							$pic_name = $_FILES['Filedata1']['name'];
                            move_uploaded_file($tmp_name, $uploads_dir.$pic_name);
							if (is_uploaded_file($_FILES['Filedata2']['tmp_name'])) {
										$uploads_dir2 = './';
										$tmp_name2 = $_FILES['Filedata2']['tmp_name'];
										$pic_name2 = $_FILES['Filedata2']['name'];
										move_uploaded_file($tmp_name2, $uploads_dir2.$pic_name2);
										if (is_uploaded_file($_FILES['Filedata3']['tmp_name'])) {
										$uploads_dir3 = './';
										$tmp_name3 = $_FILES['Filedata3']['tmp_name'];
										$pic_name3 = $_FILES['Filedata3']['name'];
										move_uploaded_file($tmp_name3, $uploads_dir3.$pic_name3);
											if (is_uploaded_file($_FILES['Filedata4']['tmp_name'])) {
												$uploads_dir4 = './';
												$tmp_name4 = $_FILES['Filedata4']['tmp_name'];
												$pic_name4 = $_FILES['Filedata4']['name'];
												move_uploaded_file($tmp_name4, $uploads_dir4.$pic_name4);
												}else{
													echo "File not uploaded successfully.";}
										}else{
										echo "File not uploaded successfully.";}
								}else{
									echo "File not uploaded successfully.";}
				}else{	   
					echo "File not uploaded successfully.";}*/
					
				$Orignal_File1_Name=$_POST['Orignal_Name1'];
				$Orignal_File2_Name=$_POST['Orignal_Name2'];
				$Orignal_File3_Name=$_POST['Orignal_Name3'];
				$Orignal_File4_Name=$_POST['Orignal_Name4'];
				$File1_Extention=$_POST['ext1'];
				$File2_Extention=$_POST['ext2'];
				$File3_Extention=$_POST['ext3'];
				$File4_Extention=$_POST['ext4'];
				
				$Get_To_Detail=mysql_query("select type from profile where id in(".$To_Str.")") or die(mysql_error());
				while ($row = mysql_fetch_array($Get_To_Detail, MYSQL_ASSOC)) {
					if($To_Details==''){
						$To_Details= $row['type'];
					}else{
					$To_Details=$To_Details.','. $row['type'];
					}		
				}
				$Insert_SentBox=mysql_query("insert into sent set mail_to='".$To_Str."' , to_detail='".$To_Details."' , mail_from='".$User_id."' , subject='".$Subject_Str."' , message='".$Message_Str."', file_status=1")or die(mysql_error());
				$sent_id=mysql_insert_id();
				if(!empty($Insert_SentBox))
						{			
							if (is_uploaded_file($_FILES['Filedata1']['tmp_name'])) {
									//$uploads_dir = '/Upload_Files';
									$uploads_dir = $_SERVER["DOCUMENT_ROOT"]."/qds_samparq/API/Upload_Files/";
									$tmp_name = $_FILES['Filedata1']['tmp_name'];
									$pic_name = $_FILES['Filedata1']['name'];
									$fname=$uploads_dir.$pic_name;
									move_uploaded_file($tmp_name, $fname);
											if (is_uploaded_file($_FILES['Filedata2']['tmp_name'])) {
												$tmp_name2 = $_FILES['Filedata2']['tmp_name'];
												$pic_name2 = $_FILES['Filedata2']['name'];
												$fname2=$uploads_dir.$pic_name2;
												move_uploaded_file($tmp_name2, $fname2);
													if (is_uploaded_file($_FILES['Filedata3']['tmp_name'])) {
													$tmp_name3 = $_FILES['Filedata3']['tmp_name'];
													$pic_name3 = $_FILES['Filedata3']['name'];
													$fname3=$uploads_dir.$pic_name3;
													move_uploaded_file($tmp_name3, $fname3);
															if (is_uploaded_file($_FILES['Filedata4']['tmp_name'])) {
																$tmp_name4 = $_FILES['Filedata4']['tmp_name'];
																$pic_name4 = $_FILES['Filedata4']['name'];
																$fname4=$uploads_dir.$pic_name4;
																move_uploaded_file($tmp_name4, $fname4);
																		$To_Array=array();
																		$To_Array=explode(',',$To_Str);
																		for($i=0;$i<sizeof($To_Array);$i++){
																			$to_jisd=$To_Array[$i];
																		$Select_Username_query=mysql_query("select * from user where user_type='".$to_jisd."' and flag='ACTIVE'") or die(mysql_error());
																		while ($row = mysql_fetch_array($Select_Username_query, MYSQL_ASSOC)) {
																		$mail_to_userid=$row['id'];	
																		$Insert_Inbox=mysql_query("insert into inbox set sent_id='".$sent_id."', mail_to='".$to_jisd."', mail_from='".$User_id."', subject='".$Subject_Str."', message='".$Message_Str."', file_status=1,mail_to_userid='".$mail_to_userid."'") or die(mysql_error());
																		
																		}
																		$inbx_id=mysql_insert_id();
																		$Insert_UploadFiles=mysql_query("insert into upload_files set  mail_to='".$to_jisd."', mail_from='".$User_id."', file_name='".$pic_name."', file_path='".$fname."', 
																		orignal_filename='".$Orignal_File1_Name."',ext='".$File1_Extention."',inbox_id='".$inbx_id."', sent_id='".$sent_id."'") or die(mysql_error());
																
																		$Insert_UploadFiles2=mysql_query("insert into upload_files set  mail_to='".$to_jisd."', mail_from='".$User_id."', file_name='".$pic_name2."', file_path='".$fname2."', 
																		orignal_filename='".$Orignal_File2_Name."',ext='".$File2_Extention."',inbox_id='".$inbx_id."', sent_id='".$sent_id."'") or die(mysql_error());
																		
																		$Insert_UploadFiles3=mysql_query("insert into upload_files set  mail_to='".$to_jisd."', mail_from='".$User_id."', file_name='".$pic_name3."', file_path='".$fname3."', 
																		orignal_filename='".$Orignal_File3_Name."',ext='".$File3_Extention."',inbox_id='".$inbx_id."', sent_id='".$sent_id."'") or die(mysql_error());
																		
																		$Insert_UploadFiles4=mysql_query("insert into upload_files set  mail_to='".$to_jisd."', mail_from='".$User_id."', file_name='".$pic_name4."', file_path='".$fname4."',orignal_filename='".$Orignal_File4_Name."',ext='".$File4_Extention."',inbox_id='".$inbx_id."', sent_id='".$sent_id."'") or die(mysql_error());
																		
																		}
																			$Report=array();
																			$Report['success']=1;
																			$Report['message']='Message sent Successfully!!';
																		echo json_encode($Report) ; die();
															}else{
																	$Report=array();
																	$Report['success']=0;
																	$Report['message']='Message sent failed!!';
																	echo json_encode($Report) ; die();
											}
													}else{
													$Report=array();
													$Report['success']=0;
													$Report['message']='Message sent failed!!';
													echo json_encode($Report) ; die();
											}
											}else{
													$Report=array();
													$Report['success']=0;
													$Report['message']='Message sent failed!!';
													echo json_encode($Report) ; die();
											}
									
								}else
								{   
									$Report=array();
									$Report['success']=0;
									$Report['message']='Message sent failed!!';
									echo json_encode($Report) ; die();
								}
							
							
							
						}else{
							$Report=array();
							$Report['success']=0;
							$Report['message']='Message sent failed!!';
							echo json_encode($Report) ; die();
						}
				
					
	}else if($File_Count=='5'){
		
		/* if (is_uploaded_file($_FILES['Filedata1']['tmp_name'])) {
				$uploads_dir = './';
                            $tmp_name = $_FILES['Filedata1']['tmp_name'];
                            $pic_name = $_FILES['Filedata1']['name'];
                            move_uploaded_file($tmp_name, $uploads_dir.$pic_name);
							if (is_uploaded_file($_FILES['Filedata2']['tmp_name'])) {
									$uploads_dir2 = './';
									$tmp_name2 = $_FILES['Filedata2']['tmp_name'];
									$pic_name2 = $_FILES['Filedata2']['name'];
									move_uploaded_file($tmp_name2, $uploads_dir2.$pic_name2);
										if (is_uploaded_file($_FILES['Filedata3']['tmp_name'])) {
										$uploads_dir3 = './';
										$tmp_name3 = $_FILES['Filedata3']['tmp_name'];
										$pic_name3 = $_FILES['Filedata3']['name'];
										move_uploaded_file($tmp_name3, $uploads_dir3.$pic_name3);
											if (is_uploaded_file($_FILES['Filedata4']['tmp_name'])) {
												$uploads_dir4 = './';
												$tmp_name4 = $_FILES['Filedata4']['tmp_name'];
												$pic_name4 = $_FILES['Filedata4']['name'];
												move_uploaded_file($tmp_name4, $uploads_dir4.$pic_name4);
													if (is_uploaded_file($_FILES['Filedata5']['tmp_name'])) {
													$uploads_dir5 = './';
													$tmp_name5 = $_FILES['Filedata5']['tmp_name'];
													$pic_name5 = $_FILES['Filedata5']['name'];
													move_uploaded_file($tmp_name5, $uploads_dir5.$pic_name5);
													}else{
														echo "File not uploaded successfully.";}
												}else{
													echo "File not uploaded successfully.";}
													
										}else{
										echo "File not uploaded successfully.";}
								}else{
									echo "File not uploaded successfully.";}
				}else{	   
					echo "File not uploaded successfully.";}*/
		
			$Orignal_File1_Name=$_POST['Orignal_Name1'];
				$Orignal_File2_Name=$_POST['Orignal_Name2'];
				$Orignal_File3_Name=$_POST['Orignal_Name3'];
				$Orignal_File4_Name=$_POST['Orignal_Name4'];
				$Orignal_File5_Name=$_POST['Orignal_Name5'];
				$File1_Extention=$_POST['ext1'];
				$File2_Extention=$_POST['ext2'];
				$File3_Extention=$_POST['ext3'];
				$File4_Extention=$_POST['ext4'];
				$File5_Extention=$_POST['ext5'];
				
				$Get_To_Detail=mysql_query("select type from profile where id in(".$To_Str.")") or die(mysql_error());
				while ($row = mysql_fetch_array($Get_To_Detail, MYSQL_ASSOC)) {
					if($To_Details==''){
						$To_Details= $row['type'];
					}else{
					$To_Details=$To_Details.','. $row['type'];
					}		
				}
				$Insert_SentBox=mysql_query("insert into sent set mail_to='".$To_Str."' , to_detail='".$To_Details."' , mail_from='".$User_id."' , subject='".$Subject_Str."' , message='".$Message_Str."', file_status=1")or die(mysql_error());
				$sent_id=mysql_insert_id();
				if(!empty($Insert_SentBox))
						{			
							if (is_uploaded_file($_FILES['Filedata1']['tmp_name'])) {
									//$uploads_dir = '/Upload_Files';
									$uploads_dir = $_SERVER["DOCUMENT_ROOT"]."/qds_samparq/API/Upload_Files/";
									$tmp_name = $_FILES['Filedata1']['tmp_name'];
									$pic_name = $_FILES['Filedata1']['name'];
									$fname=$uploads_dir.$pic_name;
									move_uploaded_file($tmp_name, $fname);
											if (is_uploaded_file($_FILES['Filedata2']['tmp_name'])) {
												$tmp_name2 = $_FILES['Filedata2']['tmp_name'];
												$pic_name2 = $_FILES['Filedata2']['name'];
												$fname2=$uploads_dir.$pic_name2;
												move_uploaded_file($tmp_name2, $fname2);
													if (is_uploaded_file($_FILES['Filedata3']['tmp_name'])) {
													$tmp_name3 = $_FILES['Filedata3']['tmp_name'];
													$pic_name3 = $_FILES['Filedata3']['name'];
													$fname3=$uploads_dir.$pic_name3;
													move_uploaded_file($tmp_name3, $fname3);
															if (is_uploaded_file($_FILES['Filedata4']['tmp_name'])) {
																$tmp_name4 = $_FILES['Filedata4']['tmp_name'];
																$pic_name4 = $_FILES['Filedata4']['name'];
																$fname4=$uploads_dir.$pic_name4;
																move_uploaded_file($tmp_name4, $fname4);
																		if (is_uploaded_file($_FILES['Filedata5']['tmp_name'])) {
																			$tmp_name5 = $_FILES['Filedata5']['tmp_name'];
																			$pic_name5 = $_FILES['Filedata5']['name'];
																			$fname5=$uploads_dir.$pic_name5;
																			move_uploaded_file($tmp_name5, $fname5);
																				$To_Array=array();
																				$To_Array=explode(',',$To_Str);
																				for($i=0;$i<sizeof($To_Array);$i++){
																					$to_jisd=$To_Array[$i];
																				$Select_Username_query=mysql_query("select * from user where user_type='".$to_jisd."' and flag='ACTIVE'") or die(mysql_error());
																				while ($row = mysql_fetch_array($Select_Username_query, MYSQL_ASSOC)) {
																				$mail_to_userid=$row['id'];	
																				$Insert_Inbox=mysql_query("insert into inbox set sent_id='".$sent_id."', mail_to='".$to_jisd."', mail_from='".$User_id."', subject='".$Subject_Str."', message='".$Message_Str."', file_status=1,mail_to_userid='".$mail_to_userid."'") or die(mysql_error());
																				
																				}
																				$inbx_id=mysql_insert_id();
																				$Insert_UploadFiles=mysql_query("insert into upload_files set  mail_to='".$to_jisd."', mail_from='".$User_id."', file_name='".$pic_name."', file_path='".$fname."', 
																				orignal_filename='".$Orignal_File1_Name."',ext='".$File1_Extention."',inbox_id='".$inbx_id."', sent_id='".$sent_id."'") or die(mysql_error());
																		
																				$Insert_UploadFiles2=mysql_query("insert into upload_files set  mail_to='".$to_jisd."', mail_from='".$User_id."', file_name='".$pic_name2."', file_path='".$fname2."', 
																				orignal_filename='".$Orignal_File2_Name."',ext='".$File2_Extention."',inbox_id='".$inbx_id."', sent_id='".$sent_id."'") or die(mysql_error());
																				
																				$Insert_UploadFiles3=mysql_query("insert into upload_files set  mail_to='".$to_jisd."', mail_from='".$User_id."', file_name='".$pic_name3."', file_path='".$fname3."', 
																				orignal_filename='".$Orignal_File3_Name."',ext='".$File3_Extention."',inbox_id='".$inbx_id."', sent_id='".$sent_id."'") or die(mysql_error());
																				
																				$Insert_UploadFiles4=mysql_query("insert into upload_files set  mail_to='".$to_jisd."', mail_from='".$User_id."', file_name='".$pic_name4."', file_path='".$fname4."',orignal_filename='".$Orignal_File4_Name."',ext='".$File4_Extention."',inbox_id='".$inbx_id."', sent_id='".$sent_id."'") or die(mysql_error());
																				
																				$Insert_UploadFiles5=mysql_query("insert into upload_files set  mail_to='".$to_jisd."', mail_from='".$User_id."', file_name='".$pic_name5."', file_path='".$fname5."',orignal_filename='".$Orignal_File5_Name."',ext='".$File5_Extention."',inbox_id='".$inbx_id."', sent_id='".$sent_id."'") or die(mysql_error());
																				
																				}
																					$Report=array();
																					$Report['success']=1;
																					$Report['message']='Message sent Successfully!!';
																				echo json_encode($Report) ; die();
																		}else{
																		$Report=array();
																		$Report['success']=0;
																		$Report['message']='Message sent failed!!';
																		echo json_encode($Report) ; die();
																}
															}else{
																	$Report=array();
																	$Report['success']=0;
																	$Report['message']='Message sent failed!!';
																	echo json_encode($Report) ; die();
																}
													}else{
													$Report=array();
													$Report['success']=0;
													$Report['message']='Message sent failed!!';
													echo json_encode($Report) ; die();
											}
											}else{
													$Report=array();
													$Report['success']=0;
													$Report['message']='Message sent failed!!';
													echo json_encode($Report) ; die();
											}
									
								}else
								{   
									$Report=array();
									$Report['success']=0;
									$Report['message']='Message sent failed!!';
									echo json_encode($Report) ; die();
								}
							
							
							
						}else{
							$Report=array();
							$Report['success']=0;
							$Report['message']='Message sent failed!!';
							echo json_encode($Report) ; die();
						}
	}
}	



/*******************************************Sent Box Mail ********************************************************************/

if(isset($_POST['get_sent_box'])){
	$user_id=$_POST['user_id'];
	$my_sent_q=mysql_query("select * from sent where mail_from='".$user_id."' and flag=1 order by id DESC")or die(mysql_error());
	$Response=array();
	$Response['my_sent_list']=array();
	while($row=mysql_fetch_array($my_sent_q,MYSQL_ASSOC)){
		$Report=array();
		$Report['id']=$row['id'];
		$Report['mail_to']=$row['mail_to'];
		$Report['to_detail']=$row['to_detail'];
		$Report['subject']=$row['subject'];
		$Report['message']=$row['message'];
		$Report['file_status']=$row['file_status'];
		$Report['process_date']=$row['process_date'];
		array_push($Response['my_sent_list'],$Report);
	}
	echo json_encode($Response); die();
}
/***************************************Inbox Mail*****************************************************************/
if(isset($_POST['get_inbox_mail'])){
	$user_id=$_POST['user_id'];
	$user_type=$_POST['user_type'];
	$my_inbox_q=mysql_query("select id,sent_id,process_date,mail_from,(select username from user where id=i.mail_from)as Sendername,(select image_name from user where id=i.mail_from)as Senderimage,subject,message,file_status from inbox i where mail_to='".$user_type."' and mail_from!='".$user_id."' and flag=1 and mail_to_userid='".$user_id."' order by id DESC")or die(mysql_error());
	$Response=array();
	$Response['my_inbox_list']=array();
	while($row=mysql_fetch_array($my_inbox_q,MYSQL_ASSOC)){
		$Report=array();
		$Report['id']=$row['id'];
		$Report['sent_id']=$row['sent_id'];
		$Report['process_date']=$row['process_date'];
		$Report['mail_from']=$row['mail_from'];
		$Report['Sendername']=$row['Sendername'];
		$Report['subject']=$row['subject'];
		$Report['message']=$row['message'];
		$Report['Senderimage']=$Url.$row['Senderimage'];
		$Report['file_status']=$row['file_status'];
		array_push($Response['my_inbox_list'],$Report);
	}
	echo json_encode($Response);die();
	
}
/**********************************************Sent Mail Detaisl**************************************************/
if(isset($_POST['get_sent_mail_details'])){
	$user_id=$_POST['user_id'];
	$sent_id=$_POST['sent_id'];
	$mySent_Details=mysql_query("select * from sent where id='".$sent_id."' and mail_from='".$user_id."' and flag=1")or die (mysql_error());
	$Response=array();
	$Response['my_sent_detail']=array();
	$Response['my_sent_attach']=array();
	while($row=mysql_fetch_array($mySent_Details,MYSQL_ASSOC)){
		
		$Report['id']=$row['id'];
		$Report['mail_to']=$row['mail_to'];
		$Report['to_detail']=$row['to_detail'];
		$Report['subject']=$row['subject'];
		$Report['message']=$row['message'];
		$Report['file_status']=$row['file_status'];
		$Report['process_date']=$row['process_date'];
		array_push($Response['my_sent_detail'],$Report);
		if($row['file_status']=='1'){
				$MySent_Attach=mysql_query("select distinct file_name,file_path,ext,orignal_filename from upload_files where sent_id='".$sent_id."'")or die(mysql_error());
				while($row1=mysql_fetch_array($MySent_Attach,MYSQL_ASSOC)){
				$Report1['file_name']=$row1['file_name'];
				$Report1['file_path']=$row1['file_path'];
				$Report1['ext']=$row1['ext'];
				$Report1['orignal_filename']=$row1['orignal_filename'];
				$Report1['url']=$Url;
				array_push($Response['my_sent_attach'],$Report1);
				}
		}
	}
	echo json_encode($Response); die();
}
/*********************************Delete Sent Mail*****************************************/
if(isset($_POST['delete_sent_mail'])){
	$user_id=$_POST['user_id'];
	$sent_id=$_POST['sent_id'];
	$Update_Data_Query="UPDATE sent SET flag=0 WHERE id='".$sent_id."' and mail_from='".$user_id."'" ;
	$MySent_Delete_Q=mysql_query($Update_Data_Query) or die(mysql_error());
	if(!empty($MySent_Delete_Q)){
		$Report=array();
		$Report['success']=1;
		$Report['message']='Delete Successfully';
		echo json_encode($Report);die();
	}else{
		$Report=array();
		$Report['success']=0;
		$Report['message']='Error! Please Try again';
		echo json_encode($Report);die();
	}
}
/******************************************Delete Inbox Mail*************************************/
if(isset($_POST['delete_inbox_mail'])){
	$user_id=$_POST['user_id'];
	$inbox_id=$_POST['inbox_id'];
	$user_type=$_POST['user_type'];
	$Update_Delete_Query="UPDATE inbox SET flag=0 where id='".$inbox_id."' and mail_to='".$user_type."' and mail_to_userid='".$user_id."'";
	$MyInboxe_delete_Q=mysql_query($Update_Delete_Query) or die(mysql_error());
	if(!empty($MyInboxe_delete_Q)){
		$Report=array();
		$Report['success']=1;
		$Report['message']='Delete Successfully';
		echo json_encode($Report);die();
	}else{
		$Report=array();
		$Report['success']=0;
		$Report['message']='Error! Please Try again';
		echo json_encode($Report);die();
	}
}
/******************************************get Contact Details*****************************************/
if(isset($_POST['get_contact_list'])){
	$user_id=$_POST['user_id'];
	$MyContact_Query=mysql_query("select id,username,email,user_type,name,employee_id,image_path,image_name,mobile_no from user where flag='ACTIVE' and id!='".$user_id."' order by name")or die(mysql_error());
	$Response=array();
	$Response['my_contact_list']=array();
	while($row=mysql_fetch_array($MyContact_Query,MYSQL_ASSOC)){
		$Report= array();
		$Report['id']= $row['id'];
		$Report['username']=$row['username'];
		$Report['email']=$row['email'];
		$Report['user_type']=$row['user_type'];
		$Report['name']=$row['name'];
		$Report['employee_id']=$row['employee_id'];
		$Report['image_path']=$row['image_path'];
		$Report['image_name']=$row['image_name'];
		$Report['mobile_no']=$row['mobile_no'];
		array_push($Response['my_contact_list'],$Report);
	}
	echo json_encode($Response);die();
	
}
/**********************************************Sent Mail Detaisl**************************************************/
if(isset($_POST['get_inbox_mail_details'])){
	$user_id=$_POST['user_id'];
	$inbox_id=$_POST['inbox_id'];
	$mySent_Details=mysql_query("select id,sent_id,mail_to,subject,message,flag,file_status,process_date,mail_to_userid,(select username from user where id=i.mail_from)as mail_from from inbox i where id='".$inbox_id."' and mail_to_userid='".$user_id."' and flag=1")or die (mysql_error());
	$Response=array();
	$Response['my_inbox_detail']=array();
	$Response['my_inbox_attach']=array();
	while($row=mysql_fetch_array($mySent_Details,MYSQL_ASSOC)){
		$Report['id']=$row['id'];
		$Report['mail_to']=$row['mail_to'];
		$Report['mail_from']=$row['mail_from'];
		$Report['subject']=$row['subject'];
		$Report['message']=$row['message'];
		$Report['file_status']=$row['file_status'];
		$Report['process_date']=$row['process_date'];
		$sent_id=$row['sent_id'];
		array_push($Response['my_inbox_detail'],$Report);
		if($row['file_status']=='1'){
				$MySent_Attach=mysql_query("select distinct file_name,file_path,ext,orignal_filename from upload_files where sent_id='".$sent_id."'")or die(mysql_error());
				while($row1=mysql_fetch_array($MySent_Attach,MYSQL_ASSOC)){
				$Report1['file_name']=$row1['file_name'];
				$Report1['file_path']=$row1['file_path'];
				$Report1['ext']=$row1['ext'];
				$Report1['orignal_filename']=$row1['orignal_filename'];
				$Report1['url']=$Url;
				array_push($Response['my_inbox_attach'],$Report1);
				}
		}
	}
	echo json_encode($Response); die();
}
/************************************** Upload Policy************************************************/
if(isset($_POST['upload_policies'])){
	$user_id=$_POST['user_id'];	
	$DescriptionStr=$_POST['description'];
	$TitleStr=$_POST['title'];
	$FileSize=$_POST['file_size'];
	$Orignal_File1_Name=$_POST['Orignal_Name'];
	$File1_Extention=$_POST['ext'];
	if (is_uploaded_file($_FILES['Filedata']['tmp_name'])) {
		$uploads_dir = $_SERVER["DOCUMENT_ROOT"]."/qds_samparq/API/Upload_Files/";
		$tmp_name = $_FILES['Filedata']['tmp_name'];
		$pic_name = $_FILES['Filedata']['name'];
		$fname=$uploads_dir.$pic_name;
		move_uploaded_file($tmp_name, $fname);
					
		$Insert_UploadFiles=mysql_query("insert into policies set  file_name='".$pic_name."', file_path='".$fname."', 
		orignal_filename='".$Orignal_File1_Name."',ext='".$File1_Extention."',file_size='".$FileSize."',created_by='".$user_id."', description='".$DescriptionStr."',title='".$TitleStr."'") or die(mysql_error());
	
		$Report=array();
		$Report['success']=1;
		$Report['message']='Policy Added Successfully!!';
		echo json_encode($Report) ; die();
	}else
	{   
		$Report=array();
		$Report['success']=0;
		$Report['message']='Policy Add failed!!';
		echo json_encode($Report) ; die();
	}
}
if(isset($_POST['get_policy_list'])){

	$Response=array();
	$Response['policy_list']=array();
	$GetPolicyList= mysql_query("select * from policies where flag=1 order by id DESC")or die(mysql_error());
	while($row=mysql_fetch_array($GetPolicyList,MYSQL_ASSOC)){
		$Report= array();
		$Report['id']=$row['id'];
		$Report['file_name']=$row['file_name'];
		$Report['orignal_filename']=$row['orignal_filename'];
		$Report['file_path']=$row['file_path'];
		$Report['ext']=$row['ext'];
		$Report['file_size']=$row['file_size'];
		$Report['created_by']=$row['created_by'];
		$Report['process_date']=$row['process_date'];
		$Report['profile_id']=$row['profile_id'];
		$Report['description']=$row['description'];
		$Report['title']=$row['title'];
		$Report['url']=$Url;
		array_push($Response['policy_list'],$Report);
	}
	echo json_encode($Response);die();
}
/*************************************** Get Employee List********************************************/
if(isset($_POST['get_employee_list'])){
	$User_id=$_POST['user_id'];
	$Response=array();
	$Response['employee_list']=array();
	$Response['profile_list']=array();
	$GetEmpList_Q=mysql_query("select * from user where flag='ACTIVE' order by name")or die(mysql_error());
	$GetProfile_Q=mysql_query("select * from profile where status=1")or die(mysql_error());
	while($row=mysql_fetch_array($GetEmpList_Q,MYSQL_ASSOC)){
		$Report=array();
		$Report['id']=$row['id'];
		$Report['username']=$row['username'];
		$Report['email']=$row['email'];
		$Report['user_type']=$row['user_type'];
		$Report['name']=$row['name'];
		$Report['employee_id']=$row['employee_id'];
		array_push($Response['employee_list'],$Report);
	}
	while($row1=mysql_fetch_array($GetProfile_Q,MYSQL_ASSOC)){
		$Report1=array();
		$Report1['id']=$row1['id'];
		$Report1['type']=$row1['type'];
		$Report1['status']=$row1['status'];
		$Report1['created_on']=$row1['created_on'];
		array_push($Response['profile_list'],$Report1);
	}
	echo json_encode($Response);die();
}
/********************************************Update Role******************************************************/
if(isset($_POST['update_employee_role'])){
		$user_id=$_POST['user_id'];
		$employee_id=$_POST['employee_id'];
		$employee_type_id=$_POST['type_id'];
		$UpdateRole_Q=mysql_query("update user set user_type='".$employee_type_id."',updated_at='".$current_date_time."',update_status=1 where id='".$employee_id."'") or die(mysql_error());
		if(!empty($UpdateRole_Q)){
			$Report=array();
			$Report['success']=1;
			$Report['message']='Updated Successfully!!';
			echo json_encode($Report); die();
		}else{
			$Report=array();
			$Report['success']=0;
			$Report['message']='Error!! please try again.';
			echo json_encode($Report); die();
		}
}
/***************************************Get Contact List For Chat********************************************************/
if(isset($_POST['get_chat_contact_list'])){
	$user_id=$_POST['user_id'];
	$Response=array();
	$Response['chat_contact_list']=array();
	$GetContact_List=mysql_query("select id,name,image_path,image_name,(select type from profile where id=u.user_type)as department from user u where flag='ACTIVE' order by name")or die(mysql_error());
	while($row=mysql_fetch_array($GetContact_List,MYSQL_ASSOC)){
		$Report=array();
		$Report['id']=$row['id'];
		$Report['name']=$row['name'];
		$Report['image']=$row['image_path'].$row['image_name'];
		$Report['department']=$row['department'];
		array_push($Response['chat_contact_list'],$Report);
	}
	echo json_encode($Response);
}
/********************************Update Token***************************************************/
if(isset($_POST['update_token'])){
	$user_id=$_POST['user_id'];
	$red_id=$_POST['reg_id'];
	$Update=mysql_query("update user set app_regid='".$red_id."' where id='".$user_id."'") or die(mysql_error());
}
/*******************************************Check For User role Update*************************************************/
if(isset($_POST['check_update_user_role'])){
	$user_id=$_POST['user_id'];
	$Update_Check_Q=mysql_query("select count(*)as count from user where id='".$user_id."' and update_status=1")or die(mysql_error());
	while($row=mysql_fetch_array($Update_Check_Q,MYSQL_ASSOC)){
		$count=$row['count'];
		if($count=='1'){
			$Report=array();
			$Report['update_status']=1;
			echo json_encode($Report);
		}else{
			$Report=array();
			$Report['update_status']=0;
			echo json_encode($Report);
		}
	}
}
/*************************************************************Logout ********************************************************************/
if(isset($_POST['logout_user'])){
	$User_id=$_POST['user_id'];
	$logout_q=mysql_query("update user set app_regid='' where id='".$User_id."'")or die(mysql_error());
	if(!empty($logout_q)){
		$Report= array();
		$Report['success']=1;
		$Report['message']='Logout ! Successfully';
		echo json_encode($Report);die();
	}else{
		$Report= array();
		$Report['success']=0;
		$Report['message']='Logout ! Failed. Try again.';
		echo json_encode($Report);die();
	}
}
/**********************************************************Update User Details*********************************************************************************/
if(isset($_POST['update_userDetails'])){
	$user_id=$_POST['user_id'];
	$name=$_POST['name_str'];
	$mobile_str=$_POST['mobile_str'];
	$department_id=$_POST['department_id'];
	$update_query=mysql_query("update user set user_type='".$department_id."',name='".$name."',mobile_no='".$mobile_str."',form_submit=1 where id='".$user_id."'")or die(mysql_error());
	if(!empty($update_query)){
		$Report= array();
		$Report['success']=1;
		$Report['message']='Submitted!!';
		echo json_encode($Report);die();
	}else{
		$Report= array();
		$Report['success']=0;
		$Report['message']='Failed ! Tryagain';
		echo json_encode($Report);die();
	}
}
?>