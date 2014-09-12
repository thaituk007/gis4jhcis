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
	$wt = " AND villagefoodshop.villcode='$villcode' ";	
}
$sql = "SELECT
villagefoodshop.pcucode,
villagefoodshop.villcode,
villagefoodshop.foodshopno,
concat(villagefoodshop.villcode,villagefoodshop.foodshopno) as hcode,
villagefoodshop.foodshopname,
villagefoodshop.foodshoptype,
villagefoodshop.xgis,
villagefoodshop.ygis
FROM
villagefoodshop
where SUBSTRING(villagefoodshop.villcode,7,2) <> '00' $wt order by villagefoodshop.villcode,villagefoodshop.foodshopno";
$result = mysql_query($sql);
//header("Content-type: text/xml");
$xml = '<markers>';
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
  //$ad = iconv( 'TIS-620', 'UTF-8',$ad);
  $xml .= '<marker ';
  $xml .= 'mno="'.$row[foodshopno].'" ';
  $xml .= 'moo="'.$moo.'" ';
  $xml .= 'vill="'.$vill.'" ';
  $xml .= 'hcode="'.$row[hcode].'" ';
  $xml .= 'hname="'.$row[foodshopname].'" ';
  $xml .= 'type="'.$row[foodshoptype].'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= '/>';
}

$xml .= '</markers>';
echo $xml;
?>

