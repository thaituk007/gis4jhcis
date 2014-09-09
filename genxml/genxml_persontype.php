<?php 
header("Content-type: text/xml");
include("../includes/conndb.php"); 
include("../includes/config.inc.php"); 
$ptype = $_GET[ptype];
$village = $_GET[village];
if($village == '00000000'){$ect2 = "";}else{$ect2 = " h.villcode = '$village' AND ";}
	if($ptype == '00'){$ect = "";}else{$ect = " pt.persontypecode = '$ptype' AND ";}
	$sql = "SELECT p.prename,CONCAT(p.fname,' ',p.lname) AS pname, p.telephoneperson, h.hno,h.villcode,h.xgis,h.ygis,pt.persontypename
				FROM house AS h
				Inner Join person AS p ON h.pcucode = p.pcucodeperson AND h.hcode = p.hcode
				Inner Join persontype AS ps ON p.pcucodeperson = ps.pcucodeperson AND p.pid = ps.pid
				Inner Join cpersontype AS pt ON ps.typecode = pt.persontypecode
				WHERE $ect2 $ect ((p.dischargetype is null) or (p.dischargetype = '9')) AND
				ps.dateretire IS NULL AND
				SUBSTRING(h.villcode,7,2) <> '00'
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
  $xml .= 'dsc="'.$row[persontypename].'" ';
  $xml .= 'telephoneperson="'.$row[telephoneperson].'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= '/>';
}
$xml .= '</markers>';
echo $xml;
?>

