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
	$wvill = "";
}else{
	$wvill = "where village.villcode ='$villcode'";	
}
$sql = "SELECT
village.pcucode,
village.villcode,
village.villno,
village.villname,
village.villmetro,
village.cup,
village.postcode,
village.latitude,
village.longitude
FROM
village
$wvill ORDER BY village.villcode";
$result = mysql_query($sql);
//header("Content-type: text/xml");
$xml = '<markers>';
while($row=mysql_fetch_array($result)) {
  //$ad = iconv( 'TIS-620', 'UTF-8',$ad);
  $xml .= '<marker ';
  $xml .= 'pcucode="'.$row[pcucode].'" ';
  $xml .= 'villcode="'.$row[villcode].'" ';
  $xml .= 'villno="'.$row[villno].'" ';
  $xml .= 'villname="'.$row[villname].'" ';
  $xml .= 'lat="'.$row[latitude].'" ';
  $xml .= 'lng="'.$row[longitude].'" ';
  $xml .= '/>';
}

$xml .= '</markers>';
echo $xml;
?>

