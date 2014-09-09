<?php
session_start();
$pcucode = $_SESSION[pcucode];
$d = date("d");
$m = date("m");
$yx = date("Y");
$y = date("Y") + 543;
$timein = date("His");
$daysdatestr = $yx."".$m."".$d."".$timein."";
 
$villcode = $_GET[vill];
$filename = "gis_".$pcucode."_".$daysdatestr.".txt";
header("Content-Type: application/text");
header('Content-Disposition: attachment; filename='.$filename);#ชื่อไฟล์
include("../includes/conndb.php"); 
include("../includes/config.inc.php"); 
if($villcode == '00000000'){
	$wt = " ";
}else{
	$wt = " villcode='$villcode' AND ";
}
$txt = "pcucode,hcode,villcode,house_no,lat,lng,dateupdate\r\n";
$sql = "SELECT pcucode,hcode,villcode,hno,ygis,xgis,IF( house.dateupdate IS NULL OR TRIM(house.dateupdate)='' OR house.dateupdate LIKE '0000-00-00%',DATE_FORMAT(house.housesurveydate,'%Y%m%d%H%i%s'),DATE_FORMAT(house.dateupdate,'%Y%m%d%H%i%s') ) AS d_update FROM house WHERE $wt ygis IS NOT NULL";
$result=mysql_query($sql,$link);
while($row=mysql_fetch_array($result)) {
	$txt .= $row[0].",".$row[1].",".$row[2].",".$row[3].",".$row[4].",".$row[5].",".$row[6]."\r\n";		
}	  
echo $txt;
?>
