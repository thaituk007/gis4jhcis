<?php 
include("../includes/conndb.php"); 
include("../includes/config.inc.php");
function parseToXML($htmlStr) 
{ 
$xmlStr=str_replace('<','&lt;',$htmlStr); 
$xmlStr=str_replace('>','&gt;',$xmlStr); 
$xmlStr=str_replace('"','&quot;',$xmlStr); 
$xmlStr=str_replace("'",'&#39;',$xmlStr); 
$xmlStr=str_replace("&",'&amp;',$xmlStr); 
return $xmlStr; 
}  
$villcode = $_GET[villcode];
if($villcode == "00000000"){
	$wt = "";
}else{
	$wt = " AND villagewater.villcode='$villcode' ";	
}
$sql = "SELECT
villagewater.pcucode,
villagewater.villcode,
villagewater.waterno,
concat(villagewater.villcode,villagewater.waterno) AS hcode,
villagewater.watertype,
villagewater.`owner`,
villagewater.enableuse,
villagewater.dateupdate,
villagewater.xgis,
villagewater.ygis,
cwaterowner.waterownername,
cwatertype.watertypename
FROM
villagewater
INNER JOIN cwaterowner ON villagewater.`owner` = cwaterowner.waterownercode
INNER JOIN cwatertype ON villagewater.watertype = cwatertype.watertypecode
where SUBSTRING(villagewater.villcode,7,2) <> '00' $wt order by villagewater.villcode,villagewater.waterno";
$result = mysql_query($sql);
//header("Content-type: text/xml");
$xml = '<markers>';
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
  //$ad = iconv( 'TIS-620', 'UTF-8',$ad);
  $xml .= '<marker ';
  $xml .= 'mno="'.$row[waterno].'" ';
  $xml .= 'moo="'.$moo.'" ';
  $xml .= 'vill="'.$vill.'" ';
  $xml .= 'hcode="'.$row[hcode].'" ';
  $xml .= 'hname="'.$row[watertypename].'" ';
  $xml .= 'type="'.$row[waterownername].'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= '/>';
}

$xml .= '</markers>';
echo $xml;
?>

