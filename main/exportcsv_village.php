<?php
session_start();
$pcucode = $_SESSION[pcucode];
$d = date("d");
$m = date("m");
$yx = date("Y");
$y = date("Y") + 543;
$timein = date("His");
$daysdatestr = $yx."".$m."".$d."".$timein."";
if($_GET[vill] == 'xx'){
$filename = "gis_village_".$pcucode."_".$daysdatestr.".txt";
header("Content-Type: application/text");
header('Content-Disposition: attachment; filename='.$filename);#ชื่อไฟล์
include("../includes/conndb.php"); 
include("../includes/config.inc.php"); 
$txt = "pcucode,villcode,latitude,longitude,dateupdate\r\n";
$sql = "SELECT village.pcucode, village.villcode, village.latitude, village.longitude,village.dateupdate AS d_update
FROM village WHERE village.latitude IS NOT NULL";
$result=mysql_query($sql,$link);
while($row=mysql_fetch_array($result)) {
	$txt .= $row[0].",".$row[1].",".$row[2].",".$row[3].",".$row[4].",".$row[5]."\r\n";		
}	  
echo $txt;
}
?>
