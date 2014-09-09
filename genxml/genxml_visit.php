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
$villcode = $_GET[village];
if($villcode == "00000000"){
	$wvill = "";
}else{
	$wvill = " AND house.villcode='$villcode' ";	
}
$chk_v = $_GET[chk_v];
if($chk_v == "0"){
	$chksto = "";
}else{
	$chksto = "and visitdiag.diagcode not like 'Z%'";
}
$str = retdaterangstr($_GET[str]);
$sto = retdaterangsto($_GET[str]);
$strx = retDatets($str);
$stox = retDatets($sto);
	$sql = "SELECT 
person.pid,
person.idcard,
CONVERT(concat(ifnull(c.titlename,ifnull(person.prename,'ไม่ระบุ') ),person.fname,' ' ,person.lname) using utf8) as pname,
house.hno,
house.villcode,
house.xgis,
house.ygis,
v.visitno,
v.visitdate,
v.symptoms,
v.vitalcheck,
GROUP_CONCAT(visitdiag.diagcode) as gdiagcode,
GROUP_CONCAT(cdisease.diseasename) as gdiagname,
GROUP_CONCAT(cdisease.diseasenamethai) as gdiagnamethai
FROM
village
INNER JOIN house ON village.pcucode = house.pcucode AND village.villcode = house.villcode
INNER JOIN person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
left join ctitle c on c.titlecode = person.prename
INNER JOIN visit v ON person.pcucodeperson = v.pcucodeperson AND person.pid = v.pid
INNER JOIN visitdiag ON v.pcucode = visitdiag.pcucode AND v.visitno = visitdiag.visitno
inner join cdisease on visitdiag.diagcode = cdisease.diseasecode
WHERE v.visitdate between '$str' and '$sto' and (v.flagservice <'04' OR v.flagservice is null OR length(trim(v.flagservice))=0 ) $wvill $chksto
group by v.pcucode,v.visitno
order by v.visitdate desc, person.fname";

$result = mysql_query($sql);
$xml = '<markers>';
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$sick = retDatets($row[visitdate]);
  $xml .= '<marker ';
  $xml .= 'hono="'.$row[hno].'" ';
  $xml .= 'moo="'.$moo.'" ';
  $xml .= 'vill="'.$vill.'" ';
  $xml .= 'pname="'.$row[pname].'" ';
  $xml .= 'gdiagcode="'.$row[gdiagcode].'  '.$row[gdiagnamethai].'" ';
  $xml .= 'sick="'.$sick.'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= '/>';
}
$xml .= '</markers>';
echo $xml;
?>

