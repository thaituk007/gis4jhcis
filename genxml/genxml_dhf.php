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
$village = $_GET[village];
if($village == "00000000"){
	$wvill = "";
}elseif($village == "xxx"){
	$wvill = " AND right(h.villcode,2)='00'";	
}else{
	$wvill = " AND h.villcode='$village'";	
}
$str = retdaterangstr($_GET[str]);
$sto = retdaterangsto($_GET[str]);
$strx = retDatets($str);
$stox = retDatets($sto);
$ds = $_GET[ds];
	if($ds == '00'){$ect = "";}else{$ect = " dc.group506code = '$ds' AND ";}
	$sql = "SELECT CONCAT(t.titlename,p.fname,' ',p.lname) AS pname,h.hno,h.villcode,h.xgis,h.ygis,vd.sickdatestart,dc.group506name
							FROM
							house AS h
							Inner Join person AS p ON h.pcucode = p.pcucodeperson AND h.hcode = p.hcode
							Inner Join visit AS v ON p.pcucodeperson = v.pcucodeperson AND p.pid = v.pid
							Inner Join visitdiag506address AS vd ON v.pcucode = vd.pcucode AND v.visitno = vd.visitno
							Inner Join cdisease AS d ON vd.diagcode = d.diseasecode
							Inner Join cdisease506 AS dc ON d.code506 = dc.group506code
							Inner Join ctitle AS t ON p.prename = t.titlecode
							WHERE $ect 
							vd.sickdatestart BETWEEN  '$str' AND '$sto' and dc.group506code in ('26','27','66') $wvill
							ORDER BY vd.sickdatestart
							 ";

$result = mysql_query($sql);
$xml = '<markers>';
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$sick = retDatets($row[sickdatestart]);
  $xml .= '<marker ';
  $xml .= 'hono="'.$row[hno].'" ';
  $xml .= 'moo="'.$moo.'" ';
  $xml .= 'vill="'.$vill.'" ';
  $xml .= 'pname="'.$row[pname].'" ';
  $xml .= 'dc="'.$row[group506name].'" ';
  $xml .= 'sick="'.$sick.'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= '/>';
}
$xml .= '</markers>';
echo $xml;
?>

