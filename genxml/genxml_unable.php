<?php 
header("Content-type: text/xml");
include("../includes/conndb.php"); 
include("../includes/config.inc.php"); 
$villcode = $_GET[village];
if($villcode == "00000000"){
	$wvill = "";
}else{
	$wvill = "AND h.villcode='$villcode' ";	
}
	$utype = $_GET[utype];
	if($utype == '00'){$ect = "";}else{$ect = " pin.incompletecode = '$utype' AND ";}
	$sql = "SELECT p.prename,CONCAT(p.fname,' ',p.lname) AS pname,h.hno,h.villcode,h.xgis,h.ygis,pin.incompletename
				FROM house AS h
				Inner Join person AS p ON h.pcucode = p.pcucodeperson AND h.hcode = p.hcode
				Inner Join personunable AS pu ON p.pcucodeperson = pu.pcucodeperson AND p.pid = pu.pid
				Inner Join personunable1type AS pt ON pu.pcucodeperson = pt.pcucodeperson AND pu.pid = pt.pid
				Inner Join cpersonincomplete AS pin ON pt.typecode = pin.incompletecode
				WHERE $ect ((p.dischargetype is null) or (p.dischargetype = '9')) AND
				SUBSTRING(h.villcode,7,2) <> '00' $wvill
				ORDER BY h.villcode,h.hno";
$result = mysql_query($sql);
$xml = '<markers>';
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$title = getTitle($row[prename]);
  $xml .= '<marker ';
  $xml .= 'hono="'.$row[hno].'" ';
  $xml .= 'moo="'.$moo.'" ';
  $xml .= 'vill="'.$vill.'" ';
  $xml .= 'pname="'.$title.$row[pname].'" ';
  $xml .= 'dsc="'.$row[incompletename].'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= '/>';
}
$xml .= '</markers>';
echo $xml;
?>

