<?php 
header("Content-type: text/xml");
include("../includes/conndb.php"); 
include("../includes/config.inc.php"); 
$villcode = $_GET[village];
if($villcode == "00000000"){
	$wvill = "";
}else{
	$wvill = " AND h.villcode='$villcode' ";	
}
$str = retdaterangstr($_GET[str]);
$sto = retdaterangsto($_GET[str]);
$strx = retDatets($str);
$stox = retDatets($sto);
$sql = "SELECT h.villcode,h.hno,h.xgis,h.ygis,p.prename,CONCAT(p.fname,' ',p.lname) AS pname,p.birth,a.pregno,a.lmp,a.edc
			FROM
			house AS h
			Inner Join person AS p ON h.pcucode = p.pcucodeperson AND h.hcode = p.hcode
			Inner Join visitancpregnancy AS a ON p.pid = a.pid
			WHERE
			a.lmp BETWEEN  '$str' AND '$sto' AND
			SUBSTRING(h.villcode,7,2) <> '00' $wvill
			ORDER BY a.lmp";
$result = mysql_query($sql);
$xml = '<markers>';
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	if($row[birth] != ""){
		$birth = retDatets($row[birth]);
	}else{$birth = "";}
	if($row[lmp] != ""){
		$lmp = retDatets($row[lmp]);
	}else{$lmp="";}
	if($row[edc] != ""){
		$edc = retDatets($row[edc]);
	}else{$edc = "";}
	$title = getTitle($row[prename]);
  $xml .= '<marker ';
  $xml .= 'hono="'.$row[hno].'" ';
  $xml .= 'moo="'.$moo.'" ';
  $xml .= 'vill="'.$vill.'" ';
  $xml .= 'pname="'.$title.$row[pname].'" ';
  $xml .= 'pregno="'.$row[pregno].'" ';
  $xml .= 'birth="'.$birth.'" ';
  $xml .= 'lmp="'.$lmp.'" ';
  $xml .= 'edc="'.$edc.'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= '/>';
}
$xml .= '</markers>';
echo $xml;
?>

