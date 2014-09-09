<?php 
header("Content-type: text/xml");
include("../includes/conndb.php"); 
include("../includes/config.inc.php"); 
function redatepick($d){
	$y = substr($d,6,4)-543;
	$m = substr($d,3,2);
	$dn = substr($d,0,2);
	$rt = $y."/".$m."/".$dn;
	return $rt;
}
$str = retdaterangstr($_GET[str]);
$sto = retdaterangsto($_GET[str]);
$strx = retDatets($str);
$stox = retDatets($sto);
$sql = "SELECT h.villcode,h.hno,h.xgis,h.ygis,p.prename,CONCAT(p.fname,' ',p.lname) AS pname,p.birth,a.pid,a.weight
			FROM
			house AS h
			Inner Join person AS p ON h.pcucode = p.pcucodeperson AND h.hcode = p.hcode
			Inner Join visitancdeliverchild AS a ON p.pcucodeperson = a.pcucodechild AND p.pid = a.pidchild
			WHERE
			p.birth BETWEEN  '$str' AND '$sto' AND
			SUBSTRING(h.villcode,7,2) <> '00'
			ORDER BY p.birth";
$result = mysql_query($sql);
$xml = '<markers>';
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	if($row[birth] != ""){
		$birth = retDatets($row[birth]);
	}else{$birth = "";}
	$mother = getPersonName($row[pid]);
	$title = getTitle($row[prename]);
  $xml .= '<marker ';
  $xml .= 'hono="'.$row[hno].'" ';
  $xml .= 'moo="'.$moo.'" ';
  $xml .= 'vill="'.$vill.'" ';
  $xml .= 'pname="'.$title.$row[pname].'" ';
  $xml .= 'weight="'.$row[weight].'" ';
  $xml .= 'birth="'.$birth.'" ';
  $xml .= 'mother="'.$mother.'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= '/>';
}
$xml .= '</markers>';
echo $xml;
?>

