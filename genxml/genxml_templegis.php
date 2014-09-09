<?php 
include("../includes/conndb.php"); 
include("../includes/config.inc.php"); 
$villcode = $_GET[villcode];
if($villcode == '00000000'){
	$wt = " ";
}else{
	$wt = " AND villcode='$villcode' ";
}
$sql = "
SELECT
villagetemple.villcode,
villagetemple.templeno,
villagetemple.templename,
villagetemple.xgis,
villagetemple.ygis
FROM
villagetemple
			WHERE  SUBSTRING(villcode,7,2) <> '00' $wt 
			ORDER BY villagetemple.villcode,villagetemple.templeno";
$result = mysql_query($sql);
//header("Content-type: text/xml");
$xml = '<markers>';
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
  //$ad = iconv( 'TIS-620', 'UTF-8',$ad);
  $xml .= '<marker ';
  $xml .= 'templeno="'.$row[templeno].'" ';
  $xml .= 'moo="'.$moo.'" ';
  $xml .= 'vill="'.$vill.'" ';
  $xml .= 'hhouse="'.$row[templename].'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= '/>';
}

$xml .= '</markers>';
echo $xml;
?>

