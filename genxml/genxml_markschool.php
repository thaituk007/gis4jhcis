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
	$wt = " AND villcode='$villcode' ";	
}
$sql = "SELECT
villageschool.villcode,
villageschool.schoolno,
concat(villageschool.villcode,villageschool.schoolno) as hcode,
villageschool.schoolname,
villageschool.xgis,
villageschool.ygis
FROM
villageschool
where SUBSTRING(villcode,7,2) <> '00' $wt
";
$result = mysql_query($sql);
//header("Content-type: text/xml");
$xml = '<markers>';
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
  //$ad = iconv( 'TIS-620', 'UTF-8',$ad);
  $xml .= '<marker ';
  $xml .= 'schoolno="'.$row[schoolno].'" ';
  $xml .= 'moo="'.$moo.'" ';
  $xml .= 'vill="'.$vill.'" ';
  $xml .= 'hcode="'.$row[hcode].'" ';
  $xml .= 'hname="'.$row[schoolname].'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= '/>';
}

$xml .= '</markers>';
echo $xml;
?>

