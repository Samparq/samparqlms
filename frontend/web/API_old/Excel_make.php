<?php
require 'db_config.php';
$filename = 'data_basedata';
$sql = "Select leads_id,branch_id from leadsdata";
$result =mysql_query($sql) or die(mysql_error());
$file_ending = '.xls';   
header("Content-Type: application/xls");    
header("Content-Disposition: attachment; filename='".$filename."".$file_ending."'");  
header("Pragma: no-cache"); 
header("Expires: 0");
$sep = ",";
for ($i = 0; $i < mysql_num_fields($result); $i++) {
echo mysql_field_name($result,$i) . ",";
}
print("\n");
 while($row = mysql_fetch_row($result))
    {
        $schema_insert = "";
        for($j=0; $j<mysql_num_fields($result);$j++)
        {
            if(!isset($row[$j]))
                $schema_insert .= "NULL".$sep;
            elseif ($row[$j] != "")
                $schema_insert .= "$row[$j]".$sep;
            else
                $schema_insert .= "".$sep;
        }
        $schema_insert = str_replace($sep."$", "", $schema_insert);
        $schema_insert = preg_replace("/\r\n|\n\r|\n|\r/", " ", $schema_insert);
        $schema_insert .= "\t";
        print(trim($schema_insert));
        print "\n";
    }   
?>