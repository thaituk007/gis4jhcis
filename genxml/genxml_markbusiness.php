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
	$wt = " AND villagebusiness.villcode='$villcode' ";	
}
$sql = "SELECT
villagebusiness.pcucode,
villagebusiness.villcode,
villagebusiness.businessno,
concat(villagebusiness.villcode,villagebusiness.businessno) as hcode,
villagebusiness.businessname,
villagebusiness.businesstype,
villagebusiness.xgis,
villagebusiness.ygis
FROM
villagebusiness
where SUBSTRING(villagebusiness.villcode,7,2) <> '00' $wt order by villagebusiness.villcode,villagebusiness.businessno";
$result = mysql_query($sql);
//header("Content-type: text/xml");
$xml = '<markers>';
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
  //$ad = iconv( 'TIS-620', 'UTF-8',$ad);
  $xml .= '<marker ';
  $xml .= 'mno="'.$row[businessno].'" ';
  $xml .= 'moo="'.$moo.'" ';
  $xml .= 'vill="'.$vill.'" ';
  $xml .= 'hcode="'.$row[hcode].'" ';
  $xml .= 'hname="'.$row[businessname].'" ';
  $xml .= 'type="'.$row[businesstype].'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= '/>';
}

$xml .= '</markers>';
echo $xml;
?>

