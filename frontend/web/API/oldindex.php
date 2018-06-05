<?php
require 'db_config.php';
$current_date_time=date('Y-m-d H:i:s');
$cureent_date=date('Y-m-d');
/**************************************** For LOg In ***************************************************/
if(isset($_POST['login']))
{
	$log=mysql_query("select * from user where (username='".$_POST['u_name']."' AND username='".$_POST['password']."' AND flag='ACTIVE' AND user_type='2')") or die(mysql_error());
	$num=mysql_num_rows($log);
		if($num>0)
		{ 
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
			$report["flag"] = $row['flag'];
			$report["mobile_permission"] = $row['mobile_permission'];
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
/*******************************************Total Count Of Data and Follow Ups********************************************************************/
if(isset($_POST['total_data_count'])){
	$user_id=$_POST['user_id'];
	$my_data_q=mysql_query("select count(*) as total from leadsdata where reassign_lead='".$user_id."' AND lead_issue_status='Open-Fos' and branch_id in (select branch_id from permission where user_id='".$user_id."')")or die(mysql_error());
	$my_follow_up_q=mysql_query("select count(*) as total from leadsdata where reassign_lead='".$user_id."' AND lead_issue_status='Followup-Fos' and branch_id in (select branch_id from permission where user_id='".$user_id."')")or die(mysql_error());
	$Datarow=mysql_fetch_row($my_data_q);
	$FollowUpRow=mysql_fetch_row($my_follow_up_q);
	$report = array();
	$report["total_mydata"] = $Datarow[0];
	$report["total_myfollowup"] = $FollowUpRow[0];
	echo json_encode($report);die();
}
/*************************************************MyData Group By Area***********************************************/
if(isset($_POST['total_mydata'])){
		$User_id=$_POST['user_id'];
		$my_Data_q=mysql_query("select area,count(*) as total from leadsdata where reassign_lead='".$User_id."' and branch_id in (select branch_id from permission where user_id='".$User_id."') and lead_issue_status='Open-Fos' group by area order by area")or die(mysql_error());
		$response=array();
		$response['my_data_list']=array();
		while($row=mysql_fetch_array($my_Data_q,MYSQL_ASSOC)){
			$report = array();
			$report["area"] = $row['area'];
			$report["total"] = $row['total'];
			array_push($response['my_data_list'],$report);
		}
	echo json_encode($response);die();
}
/*************************************************MyFollowup Group By Area***********************************************/
if(isset($_POST['total_myfollowup'])){
		$user_id=$_POST['user_id'];
		$my_followup_q=mysql_query("select area,count(*) as total from leadsdata where reassign_lead='".$user_id."' and branch_id in (select branch_id from permission where user_id='".$user_id."') and lead_issue_status='Followup-Fos' group by area order by area")or die(mysql_error());
		$response=array();
		$response['my_followup_list']=array();
		
			while ($row = mysql_fetch_array($my_followup_q, MYSQL_ASSOC)) {
				$Report=array();
				$Report['area']=$row["area"];
				$Report['total']=$row["total"];
				array_push($response['my_followup_list'],$Report);
}
echo json_encode($response);die();
}
/*****************************************MyData Groupwise Data**************************************/
if(isset($_POST['mydata_groupwise'])){
	$user_id=$_POST['user_id'];
	$area_name=$_POST['area'];
	$my_groupdata_q=mysql_query("select * from leadsdata where reassign_lead='".$user_id."' and branch_id in (select branch_id from permission where user_id='".$user_id."') and area='".$area_name."' and lead_issue_status='Open-Fos' ORDER BY leads_id DESC")or die(mysql_error());
	$Allocation_type_q=mysql_query("select * from allocation where status='active'")or die(mysql_error());
	$response=array();
	$response['my_data_list_groupwise']=array();
	$response['allocation_type_list']=array();
	while($row = mysql_fetch_array($my_groupdata_q, MYSQL_ASSOC)){
		$Report=array();
				$Report['id']=$row["leads_id"];
				$Report['address']=$row["address"];
				$Report['area']=$row["area"];
				$Report['churn_request_reason']=$row["churn_request_reason"];
				$Report['customer_name']=$row["customer_name"];
				$Report['msisdn']=$row["msisdn"];
				$Report['alternate_number']=$row["alternate_number"];
				$Report['churn_request_date']=$row["churn_request_date"];
				$Report['churn_type']=$row["churn_type"];
				$Report['allocation_type']=$row["allocation_type"];
				array_push($response['my_data_list_groupwise'],$Report);
	}
	while($row = mysql_fetch_array($Allocation_type_q, MYSQL_ASSOC)){
				$Report=array();
				$Report['id']=$row["id"];
				$Report['type']=$row["type"];
				array_push($response['allocation_type_list'],$Report);
	}
	echo json_encode($response);die();
}
/***********************************************MyFollowUp*****************************************************************************/
if(isset($_POST['myfollowup_groupwise'])){
	$user_id=$_POST['user_id'];
	$area_name=$_POST['area'];
	$my_groupdata_q=mysql_query("select * from leadsdata where reassign_lead='".$user_id."' and branch_id in (select branch_id from permission where user_id='".$user_id."') and area='".$area_name."' and lead_issue_status='Followup-Fos' ORDER BY follow_up_date ASC")or die(mysql_error());
	$Allocation_type_q=mysql_query("select * from allocation where status='active'")or die(mysql_error());
	$response=array();
	$response['my_followup_list_groupwise']=array();
	$response['allocation_type_list']=array();
	while($row = mysql_fetch_array($my_groupdata_q, MYSQL_ASSOC)){
		$Report=array();
				$Report['id']=$row["leads_id"];
				$Report['address']=$row["address"];
				$Report['area']=$row["area"];
				$Report['customer_name']=$row["customer_name"];
				$Report['msisdn']=$row["msisdn"];
				$Report['alternate_number']=$row["alternate_number"];
				$Report['churn_request_date']=$row["churn_request_date"];
				$Report['churn_type']=$row["churn_type"];
				$Report['allocation_type']=$row["allocation_type"];
				$Report['follow_up']=$row["follow_up"];
				$Report['follow_up_date']=$row["follow_up_date"];
				array_push($response['my_followup_list_groupwise'],$Report);
	}
	while($row = mysql_fetch_array($Allocation_type_q, MYSQL_ASSOC)){
				$Report=array();
				$Report['id']=$row["id"];
				$Report['type']=$row["type"];
				array_push($response['allocation_type_list'],$Report);
	}
	echo json_encode($response);die();
}
/***********************************************Update Data*************************************************/
if(isset($_POST['update_data'])){
	
	$leads_id=$_POST['leads_id'];
	$DataType=$_POST['data_type'];
	$churnRequestDate=$_POST['churn_request_date'];
	$Form_Latitude=$_POST['form_latitude'];
	$Form_Longitude=$_POST['form_longitude'];
	$OtherContact=$_POST['other_contact'];
	$Fos_Remark=$_POST['fos_remark'];
	$FosIssueWithId=$_POST['issuewith_id'];
	$sandardRemarkId=$_POST['standardremark_id'];
	$CollectionStatus=$_POST['collection_status'];
	$CollectionAmount=($_POST['collection_amount']!='')? $_POST['collection_amount'] : '0.00';
	$WaveOffStatus=$_POST['waveoff_status'];
	$WaveOffAmount=($_POST['waveoff_amount']!='')? $_POST['waveoff_amount'] : '0.00';
	$Status=$_POST['status'];
	$statusFollowup=$_POST['status_followup'];
	$statusFollowup_Date=$_POST['status_followup_date'];
	$lead_issue_status=$_POST['lead_issue_status'];
	$Update_Data_Query="UPDATE leadsdata SET data_type='".$DataType."',churn_request_date='".$churnRequestDate."',latitude='".$Form_Latitude."',longitude='".$Form_Longitude."',other_contact_no='".$OtherContact."',fos_remark='".$Fos_Remark."',extra1='".$FosIssueWithId."',extra2='".$sandardRemarkId."',collection_status='".$CollectionStatus."',waveoff_status='".$WaveOffStatus."',collection=".$CollectionAmount.",wave_off_required=".$WaveOffAmount.",status='".$Status."',lead_issue_status='".$lead_issue_status."',follow_up='".$statusFollowup."',follow_up_date='".$statusFollowup_Date."' WHERE leads_id='".$leads_id."'" ;
	//echo $Update_Data_Query;die();
	$UpdateData=mysql_query($Update_Data_Query) or die(mysql_error());
	if(!empty($UpdateData))
	{
		$report = array();
		$report["success"] = 1;
		$report["message"] = "Updated Successfully";
		echo json_encode($report); die();
	}else{
		$report = array();
		$report["success"] = 0;
		$report["message"] = " Update not successfully";
		echo json_encode($report); die();
	}
 
}
/**********************************My Data Single Data For Update*****************************************/
if(isset($_POST['mydata_viewsingle_forupdate'])){
	$leads_id=$_POST['leads_id'];
	$myData_query=mysql_query("select * from leadsdata where leads_id='".$leads_id."' and lead_issue_status='Open-Fos'") or die(mysql_error());
	$num=mysql_num_rows($myData_query);
	
	$response=array();
	$response['issue_mast_list']=array();
	$response['myddata_list']=array();
	$response['standard_remark_list']=array();
	
	
	if($num>0)
	{ 
			$report = array();
			$row=mysql_fetch_array($myData_query);
			$report["success"] = 1;
			$report["leads_id"] = $row['leads_id'];
			$report["customer_code"] = $row['customer_code'];
			$report["area"] = $row['area'];
			$report["msisdn"] = $row['msisdn'];
			$report["alternate_number"] = $row['alternate_number'];
			
			$zoneid=$row['zone_id'];
			$ZoneNameQuery=mysql_query("select * from zone where id=".$zoneid)or die(mysql_error());
			while($row1 = mysql_fetch_array($ZoneNameQuery, MYSQL_ASSOC)){
				$report["zone_id"] = $row1['name'];
			}
			$BranchId=$row['branch_id'];
			
			$BranchNameQuery=mysql_query("select * from branch where id=".$BranchId)or die(mysql_error());
				while($row2 = mysql_fetch_array($BranchNameQuery, MYSQL_ASSOC)){
				$report["branch_id"] = $row2['name'];
			}
			
			$report["churn_request_reason"] = $row['churn_request_reason'];
			$report["customer_name"] = $row['customer_name'];
			$report["unbilled_amount"] = $row['unbilled_amount'];
			$report["dealer_name"] = $row['dealer_name'];
			$report["current_plan"] = $row['current_plan'];
			$report["email_id"] = $row['email_id'];
			$report["outstanding_amount"] = $row['outstanding_amount'];
			$report["churn_type"] = $row['churn_type'];
		
			$AllocationTypeId=$row['allocation_type'];
			$AllocationTypeQuery=mysql_query("select * from allocation where id=".$AllocationTypeId)or die(mysql_error());
				while($row3 = mysql_fetch_array($AllocationTypeQuery, MYSQL_ASSOC)){
				$report["allocation_type"] = $row3['type'];
			}
			$issueWithId=$row['issue_with'];
			if($issueWithId!=null){
			$IssueWithQuery=mysql_query("select * from issuemast where id=".$issueWithId)or die(mysql_error());
			while($row4=mysql_fetch_array($IssueWithQuery, MYSQL_ASSOC)){
			$report["issue_with"] = $row4['details'];
			}
			}else{$report["issue_with"] = $issueWithId;}
			$SandardRemarkId=$row['standard_remark'];
			if($SandardRemarkId!=null){
			$StandardRemarkQuery=mysql_query("select * from standardremark where id=".$issueWithId)or die(mysql_error());
			while($row5=mysql_fetch_array($StandardRemarkQuery, MYSQL_ASSOC)){
			$report["standard_remark"] = $row5['details'];
			}
			}else{$report["standard_remark"] = $SandardRemarkId;}
			$assignLed=$row['assigned_lead'];
			if($assignLed!=null){
			$AssignedLeadName_Q=mysql_query("select * from user where id=".$assignLed)or die(mysql_error());
			while($row7 = mysql_fetch_array($AssignedLeadName_Q, MYSQL_ASSOC)){
				$report["assigned_lead"] = $row7['username'];
			}
			}else{
				$report["assigned_lead"] = $assignLed;
			}
			$report["previous_issue"] = $row['previous_issue'];
			$report["aon"] = $row['aon'];
			$report["rpu"] = $row['rpu'];
			$report["calling_attempt"] = $row['calling_attempt'];
			$report["caller_remark"] = $row['customer_comment'];
			
			$report["latitude"] = $row['latitude'];
			$report["longitude"] = $row['longitude'];
			$report["cat"] = $row['cat'];
			$report["cat2"] = $row['cat2'];
			$report["customer_type"] = $row['customer_type'];
			$report["data_type"] = (isset($row['data_type']))? $row['data_type'] : '';
			$report["disposition_time_attempt1"] = $row['disposition_time_attempt1'];
			$report["disposition_attempt1_remark"] = $row['disposition_attempt1_remark'];
			$report["disposition_time_attempt2"] = $row['disposition_time_attempt2'];
			$report["disposition_attempt2_remark"] = $row['disposition_attempt2_remark'];
			$report["churn_request_date"] = (isset($row['churn_request_date']))? $row['churn_request_date'] : '';
			$report["other_contact_no"] = (isset($row['other_contact_no']))? $row['other_contact_no'] : '';
			$report["fos_remark"] = (isset($row['fos_remark']))? $row['fos_remark'] : '';
			$report["collection_status"] = (isset($row['collection_status']))? $row['collection_status'] : '';
			$report["waveoff_status"] = (isset($row['waveoff_status']))? $row['waveoff_status'] : '';
			$report["collection"] = (isset($row['collection']))? $row['collection'] : '';
			$report["wave_off_required"] = (isset($row['wave_off_required']))? $row['wave_off_required'] : '';
			$report["status"] = $row['status'];
			$report["extra1"] = (isset($row['extra1']))? $row['extra1'] : '0';
			$report["extra2"] = (isset($row['extra2']))? $row['extra2'] : '0';
			$report["follow_up"] = (isset($row['follow_up']))? $row['follow_up'] : '';
			$report["follow_up_date"] = (isset($row['follow_up_date']))? $row['follow_up_date'] : '';
			
			$issueMastQuery=mysql_query("select * from issuemast where status='Active'")or die(mysql_error());
				while($row8 = mysql_fetch_array($issueMastQuery, MYSQL_ASSOC)){
				$issueArray=array();
				$issueArray["id"] = $row8['id'];
				$issueArray["details"] = $row8['details'];
				array_push($response['issue_mast_list'],$issueArray);
			}
			$StandardRemarkQuery=mysql_query("select * from standardremark where status='Active'")or die(mysql_error());
				while($row9 = mysql_fetch_array($StandardRemarkQuery, MYSQL_ASSOC)){
				$remarkArray=array();
				$remarkArray["id"] = $row9['id'];
				$remarkArray["issue_id"] = $row9['issue_id'];
				$remarkArray["details"] = $row9['details'];
				array_push($response['standard_remark_list'],$remarkArray);
			}
			
			array_push($response['myddata_list'],$report);
			echo json_encode($response); die();
	}else
	{
		$report = array();
		$report["success"] = 0;
		$report["message"] = "This Data Removed From Open Fos!!";	
		array_push($response['myddata_list'],$report);		
		echo json_encode($response); die();
	}
}
/**********************************My Followup Single Data For Update*****************************************/
if(isset($_POST['myfollowup_viewsingle_forupdate'])){
	$leads_id=$_POST['leads_id'];
	$myData_query=mysql_query("select * from leadsdata where leads_id='".$leads_id."' and lead_issue_status='Followup-Fos'") or die(mysql_error());
	$num=mysql_num_rows($myData_query);
	
	$response=array();
	$response['issue_mast_list']=array();
	$response['myddata_list']=array();
	$response['standard_remark_list']=array();
	
	
	if($num>0)
	{ 
			$report = array();
			$row=mysql_fetch_array($myData_query);
			$report["success"] = 1;
			$report["leads_id"] = $row['leads_id'];
			$report["customer_code"] = $row['customer_code'];
			$report["area"] = $row['area'];
			$report["msisdn"] = $row['msisdn'];
			$report["alternate_number"] = $row['alternate_number'];
			
			$zoneid=$row['zone_id'];
			$ZoneNameQuery=mysql_query("select * from zone where id=".$zoneid)or die(mysql_error());
			while($row1 = mysql_fetch_array($ZoneNameQuery, MYSQL_ASSOC)){
				$report["zone_id"] = $row1['name'];
			}
			$BranchId=$row['branch_id'];
			
			$BranchNameQuery=mysql_query("select * from branch where id=".$BranchId)or die(mysql_error());
				while($row2 = mysql_fetch_array($BranchNameQuery, MYSQL_ASSOC)){
				$report["branch_id"] = $row2['name'];
			}
			
			$report["churn_request_reason"] = $row['churn_request_reason'];
			$report["customer_name"] = $row['customer_name'];
			$report["unbilled_amount"] = $row['unbilled_amount'];
			$report["dealer_name"] = $row['dealer_name'];
			$report["current_plan"] = $row['current_plan'];
			$report["email_id"] = $row['email_id'];
			$report["outstanding_amount"] = $row['outstanding_amount'];
			$report["churn_type"] = $row['churn_type'];
		
			$AllocationTypeId=$row['allocation_type'];
			$AllocationTypeQuery=mysql_query("select * from allocation where id=".$AllocationTypeId)or die(mysql_error());
				while($row3 = mysql_fetch_array($AllocationTypeQuery, MYSQL_ASSOC)){
				$report["allocation_type"] = $row3['type'];
			}
			$issueWithId=$row['issue_with'];
			if($issueWithId!=null){
			$IssueWithQuery=mysql_query("select * from issuemast where id=".$issueWithId)or die(mysql_error());
			while($row4=mysql_fetch_array($IssueWithQuery, MYSQL_ASSOC)){
			$report["issue_with"] = $row4['details'];
			}
			}else{$report["issue_with"] = $issueWithId;}
			$SandardRemarkId=$row['standard_remark'];
			if($SandardRemarkId!=null){
			$StandardRemarkQuery=mysql_query("select * from standardremark where id=".$issueWithId)or die(mysql_error());
			while($row5=mysql_fetch_array($StandardRemarkQuery, MYSQL_ASSOC)){
			$report["standard_remark"] = $row5['details'];
			}
			}else{$report["standard_remark"] = $SandardRemarkId;}
			$assignLed=$row['assigned_lead'];
			if($assignLed!=null){
			$AssignedLeadName_Q=mysql_query("select * from user where id=".$assignLed)or die(mysql_error());
			while($row7 = mysql_fetch_array($AssignedLeadName_Q, MYSQL_ASSOC)){
				$report["assigned_lead"] = $row7['username'];
			}
			}else{
				$report["assigned_lead"] = $assignLed;
			}
			$report["previous_issue"] = $row['previous_issue'];
			$report["aon"] = $row['aon'];
			$report["rpu"] = $row['rpu'];
			$report["calling_attempt"] = $row['calling_attempt'];
			$report["caller_remark"] = $row['customer_comment'];
			
			$report["latitude"] = $row['latitude'];
			$report["longitude"] = $row['longitude'];
			$report["cat"] = $row['cat'];
			$report["cat2"] = $row['cat2'];
			$report["customer_type"] = $row['customer_type'];
			$report["data_type"] = (isset($row['data_type']))? $row['data_type'] : '';
			$report["disposition_time_attempt1"] = $row['disposition_time_attempt1'];
			$report["disposition_attempt1_remark"] = $row['disposition_attempt1_remark'];
			$report["disposition_time_attempt2"] = $row['disposition_time_attempt2'];
			$report["disposition_attempt2_remark"] = $row['disposition_attempt2_remark'];
			$report["churn_request_date"] = (isset($row['churn_request_date']))? $row['churn_request_date'] : '';
			$report["other_contact_no"] = (isset($row['other_contact_no']))? $row['other_contact_no'] : '';
			$report["fos_remark"] = (isset($row['fos_remark']))? $row['fos_remark'] : '';
			$report["collection_status"] = (isset($row['collection_status']))? $row['collection_status'] : '';
			$report["waveoff_status"] = (isset($row['waveoff_status']))? $row['waveoff_status'] : '';
			$report["collection"] = (isset($row['collection']))? $row['collection'] : '';
			$report["wave_off_required"] = (isset($row['wave_off_required']))? $row['wave_off_required'] : '';
			$report["status"] = $row['status'];
			$report["extra1"] = (isset($row['extra1']))? $row['extra1'] : '0';
			$report["extra2"] = (isset($row['extra2']))? $row['extra2'] : '0';
			$report["follow_up"] = (isset($row['follow_up']))? $row['follow_up'] : '';
			$report["follow_up_date"] = (isset($row['follow_up_date']))? $row['follow_up_date'] : '';
			
			$issueMastQuery=mysql_query("select * from issuemast where status='Active'")or die(mysql_error());
				while($row8 = mysql_fetch_array($issueMastQuery, MYSQL_ASSOC)){
				$issueArray=array();
				$issueArray["id"] = $row8['id'];
				$issueArray["details"] = $row8['details'];
				array_push($response['issue_mast_list'],$issueArray);
			}
			$StandardRemarkQuery=mysql_query("select * from standardremark where status='Active'")or die(mysql_error());
				while($row9 = mysql_fetch_array($StandardRemarkQuery, MYSQL_ASSOC)){
				$remarkArray=array();
				$remarkArray["id"] = $row9['id'];
				$remarkArray["issue_id"] = $row9['issue_id'];
				$remarkArray["details"] = $row9['details'];
				array_push($response['standard_remark_list'],$remarkArray);
			}
			
			array_push($response['myddata_list'],$report);
			echo json_encode($response); die();
	}else
	{
		$report = array();
		$report["success"] = 0;
		$report["message"] = "This Data Removed From Followup-Fos!!";	
		array_push($response['myddata_list'],$report);		
		echo json_encode($response); die();
	}
}
/**********************************MyData Single Data Show*************************************************/
if(isset($_POST['mydata_viewdata_single'])){
	$Leads_ID=$_POST['leads_id'];
$log=mysql_query("select * from leadsdata where leads_id='".$Leads_ID."' and lead_issue_status='Open-Fos'") or die(mysql_error());
	$num=mysql_num_rows($log);
		if($num>0)
		{ 
			$report = array();
			$row=mysql_fetch_array($log);
			$report["success"] = 1;
			$report["leads_id"] = $row['leads_id'];
			$report["leads_date"] = $row['leads_date'];
			$report["churn_request_reason"] = $row['churn_request_reason'];
			$report["customer_name"] = $row['customer_name'];
			$report["area"] = $row['area'];
			$report["address"] = $row['address'];
			$report["msisdn"] = $row['msisdn'];
			$report["alternate_number"] = $row['alternate_number'];
			$report["data_type"] = $row['data_type'];
			$report["churn_type"] = $row['churn_type'];
			$report["churn_request_date"] = $row['churn_request_date'];
			$report["aon"] = $row['aon'];
			$report["rpu"] = $row['rpu'];
			$report["calling_attempt"] = $row['calling_attempt'];
			$issueWithId=$row['issue_with'];
			if($issueWithId!=null){
			$IssueWithQuery=mysql_query("select * from issuemast where id=".$issueWithId)or die(mysql_error());
			while($row4=mysql_fetch_array($IssueWithQuery, MYSQL_ASSOC)){
			$report["issue_with"] = $row4['details'];
			}
			}else{$report["issue_with"] = $issueWithId;}
			
			
			$report["previous_issue"] = $row['previous_issue'];
			$report["customer_comment"] = $row['customer_comment'];
			$report["status"] = $row['status'];
			
			$SandardRemarkId=$row['standard_remark'];
			if($SandardRemarkId!=null){
			$StandardRemarkQuery=mysql_query("select * from standardremark where id=".$issueWithId)or die(mysql_error());
			while($row5=mysql_fetch_array($StandardRemarkQuery, MYSQL_ASSOC)){
			$report["standard_remark"] = $row5['details'];
			}
			}else{$report["standard_remark"] = $SandardRemarkId;}
			
			$report["allocation_to_field"] = $row['allocation_to_field'];
			$report["collection"] = $row['collection'];
			$report["wave_off_required"] = $row['wave_off_required'];
			$assignLed=$row['assigned_lead'];
			if($assignLed!=null){
			$AssignedLeadName_Q=mysql_query("select * from user where id=".$assignLed)or die(mysql_error());
			while($row1 = mysql_fetch_array($AssignedLeadName_Q, MYSQL_ASSOC)){
				$report["assigned_lead"] = $row1['username'];
			}
			}else{
				$report["assigned_lead"] = $assignLed;
			}
			$report["user_id"] = $row['user_id'];
			$report["creation_datetime"] = $row['creation_datetime'];
			$report["follow_up"] = $row['follow_up'];
			$report["follow_up_date"] = $row['follow_up_date'];
			$report["disposition_time_attempt1"] = $row['disposition_time_attempt1'];
			$report["disposition_attempt1_remark"] = $row['disposition_attempt1_remark'];
			$report["disposition_attempt2_remark"] = $row['disposition_attempt2_remark'];
			echo json_encode($report);
		}
		else
		{
				$report = array();
				$report["success"] = 0;
				$report["message"] = "This Data Removed From Open Fos!!";
				echo json_encode($report); die();
		}
}
/**********************************MyFollowUp Single Data Show*************************************************/
if(isset($_POST['myfollowup_viewdata_single'])){
	$Leads_ID=$_POST['leads_id'];
$log=mysql_query("select * from leadsdata where leads_id='".$Leads_ID."' and lead_issue_status='Followup-Fos'") or die(mysql_error());
	$num=mysql_num_rows($log);
		if($num>0)
		{ 
			$report = array();
			$row=mysql_fetch_array($log);
			$report["success"] = 1;
			$report["leads_id"] = $row['leads_id'];
			$report["leads_date"] = $row['leads_date'];
			$report["churn_request_reason"] = $row['churn_request_reason'];
			$report["customer_name"] = $row['customer_name'];
			$report["area"] = $row['area'];
			$report["address"] = $row['address'];
			$report["msisdn"] = $row['msisdn'];
			$report["alternate_number"] = $row['alternate_number'];
			$report["data_type"] = $row['data_type'];
			$report["churn_type"] = $row['churn_type'];
			$report["churn_request_date"] = $row['churn_request_date'];
			$report["aon"] = $row['aon'];
			$report["rpu"] = $row['rpu'];
			$report["calling_attempt"] = $row['calling_attempt'];
			
			$issueWithId=$row['issue_with'];
			if($issueWithId!=null){
			$IssueWithQuery=mysql_query("select * from issuemast where id=".$issueWithId)or die(mysql_error());
			while($row4=mysql_fetch_array($IssueWithQuery, MYSQL_ASSOC)){
			$report["issue_with"] = $row4['details'];
			}
			}else{$report["issue_with"] = $issueWithId;}
			
			$report["previous_issue"] = $row['previous_issue'];
			$report["customer_comment"] = $row['customer_comment'];
			$report["status"] = $row['status'];
			$SandardRemarkId=$row['standard_remark'];
			
			if($SandardRemarkId!=null){
			$StandardRemarkQuery=mysql_query("select * from standardremark where id=".$issueWithId)or die(mysql_error());
			while($row5=mysql_fetch_array($StandardRemarkQuery, MYSQL_ASSOC)){
			$report["standard_remark"] = $row5['details'];
			}
			}else{$report["standard_remark"] = $SandardRemarkId;}
			
			$report["allocation_to_field"] = $row['allocation_to_field'];
			$report["collection"] = $row['collection'];
			$report["wave_off_required"] = $row['wave_off_required'];
			$assignLed=$row['assigned_lead'];
			if($assignLed!=null){
			$AssignedLeadName_Q=mysql_query("select * from user where id=".$assignLed)or die(mysql_error());
			while($row1 = mysql_fetch_array($AssignedLeadName_Q, MYSQL_ASSOC)){
				$report["assigned_lead"] = $row1['username'];
			}
			}else{
				$report["assigned_lead"] = $assignLed;
			}
			$report["user_id"] = $row['user_id'];
			$report["creation_datetime"] = $row['creation_datetime'];
			$report["follow_up"] = $row['follow_up'];
			$report["follow_up_date"] = $row['follow_up_date'];
			$report["disposition_time_attempt1"] = $row['disposition_time_attempt1'];
			$report["disposition_attempt1_remark"] = $row['disposition_attempt1_remark'];
			$report["disposition_attempt2_remark"] = $row['disposition_attempt2_remark'];
			echo json_encode($report);
		}
		else
		{
				$report = array();
				$report["success"] = 0;
				$report["message"] = "This Data Removed From Open Fos!!";
				echo json_encode($report); die();
		}
}

/************************************* For Location *************************************************************/
if(isset($_POST['locationBackend']))
{
   $UserID=$_POST['user_id'];
	$LatitudeM=$_POST['latitudem'];
	$LogitudeM=$_POST['longitudem'];
	$DeviceTime=$_POST['device_time'];
	$IMEINO=$_POST['imei_no'];
	$SIMNO=$_POST['sim_no'];
	$Address=$_POST['address'];
	$City=$_POST['city'];
	$State=$_POST['state'];
	$Country=$_POST['country'];
	$Postal_code=$_POST['postal_code'];
	$KnownName=$_POST['known_name'];
	
    $genlink=mysql_query("insert into user_location set device_date='".$DeviceTime."' , device_latitude='".$LatitudeM."' , device_longitude='".$LogitudeM."' ,  user_id='".$UserID."'
	,  user_imei_no='".$IMEINO."'
	,  user_sim_no='".$SIMNO."'
	,  user_address='".$Address."'
	,  user_city='".$City."'
	,  user_state='".$State."'
	,  user_country='".$Country."'
	,  user_postal_code='".$Postal_code."'
	,  location_known_name='".$KnownName."'") or die(mysql_error());
	if(!empty($genlink))
	{ 
				$report = array();
			$report["success"] = 1;
			$report["message"] = "Inserted Successfully";
						echo json_encode($report); die();
	
	}
	else
	{ $report = array();
			$report["success"] = 0;
			$report["message"] = " not successfully";
						echo json_encode($report); die();
	}
}

?>