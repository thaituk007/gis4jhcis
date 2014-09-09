<?php 
header("Content-type: text/xml");
include("../includes/conndb.php"); 
include("../includes/config.inc.php"); 
$nyear = date("Y");
$strage = $_GET[strage];
$village = $_GET[village];
$str = retdaterangstr($_GET[str]);
$sto = retdaterangsto($_GET[str]);
$strx = retDatets($str);
$stox = retDatets($sto);
if($village == '00000000'){$ect2 = "";}else{$ect2 = " h.villcode = '$village' AND ";}
$sql = "SELECT p.pid,p.prename,CONCAT(p.fname,' ',p.lname) AS pname,p.birth,h.hno,h.villcode,h.xgis,h.ygis,Max(n.screen_date) AS date_exam,($nyear - SUBSTRING(p.birth,1,4)) AS age
FROM
house AS h
Inner Join person AS p ON h.pcucode = p.pcucodeperson AND h.hcode = p.hcode
Left Join ncd_person_ncd_screen AS n ON p.pcucodeperson = n.pcucode AND p.pid = n.pid
WHERE
p.typelive IN  ('0', '1', '3') AND
((p.dischargetype is null) or (p.dischargetype = '9')) AND
$ect2
($nyear - SUBSTRING(p.birth,1,4)) >= $strage
GROUP BY
p.pid";
$result = mysql_query($sql);
$xml = '<markers>';
while($row=mysql_fetch_array($result)) {

	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$bod = retDatets($row[birth]);
	$title = getTitle($row[prename]);
	$age = $row[age];
	if(strlen($row[date_exam]) == 10){$dexam = retDatets($row[date_exam]);}else{$dexam='';}
	if($row[date_exam] == ''){
		$type = '0';
	}else if($row[date_exam] >= $str && $row[date_exam] <= $sto){
		$type = '1';
	}else{
		$type = '2';
	}
  $xml .= '<marker ';
  $xml .= 'hono="'.$row[hno].'" ';
  $xml .= 'moo="'.$moo.'" ';
  $xml .= 'vill="'.$vill.'" ';
  $xml .= 'pname="'.$title.$row[pname].'" ';
  $xml .= 'bod="'.$bod.'" ';
  $xml .= 'age="'.$age.'" ';
  $xml .= 'dexam="'.$dexam.'" ';
  $xml .= 'type="'.$type.'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= '/>';
}
$xml .= '</markers>';
echo $xml;
?>

