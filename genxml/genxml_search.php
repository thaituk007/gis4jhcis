<?php 
include("../includes/conndb.php"); 
include("../includes/config.inc.php"); 
if($_GET[cid]){
	$cid = $_GET[cid];
	$sql = "SELECT CONCAT(p.prename,p.fname,' ',p.lname) AS pname,h.villcode,p.idcard,h.villcode,h.hno,h.ygis,h.xgis
				FROM
				house AS h
				Inner Join person AS p ON h.pcucode = p.pcucodeperson AND h.hcode = p.hcode
				WHERE
				p.idcard =  '$cid' AND
				SUBSTRING(h.villcode,7,2) <> '00'";
}else{
	$fname = $_GET[fname];
	$lname = $_GET[lname];
	$sql = "SELECT p.prename,CONCAT(p.fname,' ',p.lname) AS pname,h.villcode,p.idcard,h.villcode,h.hno,h.ygis,h.xgis
				FROM
				house AS h
				Inner Join person AS p ON h.pcucode = p.pcucodeperson AND h.hcode = p.hcode
				WHERE
				p.fname LIKE  '$fname%' AND
				p.lname LIKE  '$lname%' AND
				SUBSTRING(h.villcode,7,2) <> '00'";
}
$result = mysql_query($sql);
//header("Content-type: text/xml");
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
  $xml .= 'cid="'.$row[idcard].'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= '/>';
}
$xml .= '</markers>';
echo $xml;
?>

