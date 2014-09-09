<?php 
include("../includes/conndb.php"); 
include("../includes/config.inc.php"); 
$villcode = $_GET[villcode];
if($villcode == "00000000"){
	$wvill = "";
}else{
	$wvill = "and village.villcode ='$villcode'";	
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
where right(village.villcode,2) <> 00 and village.villname is not null $wvill ORDER BY village.villcode";
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

