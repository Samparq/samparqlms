<?php
require 'db_config.php';
$current_date_time=date('Y-m-d H:i:s');
$cureent_date=date('Y-m-d');
$user_id=15;
		$my_followup_q=mysql_query("select area,count(*) as total from leadsdata where reassign_lead='".$user_id."' and branch_id in (select branch_id from permission where user_id='".$user_id."') and lead_issue_status='Followup-Fos' group by area order by area")or die(mysql_error());
		$response=array();
		$response['data']=array();
		
			while ($row = mysql_fetch_array($my_followup_q, MYSQL_ASSOC)) {
				$Report=array();
				$Report['area']=$row["area"];
				$Report['total']=$row["total"];
				printf("ID: %s  Name: %s", $row["area"], $row["total"]);
				array_push($response['data'],$Report);
}
echo json_encode($response);
echo "<br/>";

		//$my_followup_num=mysql_fetch_row($my_followup_q);
		//echo count($my_followup_num);
		
		
		//if($my_followup_num>=0){
			//$orders=array();
			while($fRow=mysql_fetch_array($my_followup_q, MYSQL_ASSOC)){
				$orders[] = array(
                                          'area' => $fRow['area'],

                                                'total' => $fRow['total'],
                                  );
				//$followResponse['my_followup_list']=array("area"=>$fRow["area"]);
				//$followResponse["area"]=$fRow["area"];
				//$followResponse['my_followup_list']["total"]=$fRow["total"];
				
			}
		//}
	echo json_encode($orders);//die();
	
	?>
	
	