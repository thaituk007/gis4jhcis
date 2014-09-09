<?php 
header("Content-type: text/xml");
include("../includes/conndb.php"); 
include("../includes/config.inc.php");
$dx = date("md");
$yx = date("Y");
$yy = date("Y")-1;
if($dx > "1001"){$daymidyear = $yx."-07-01";}else{$daymidyear = $yy."-07-01";}

$villcode = $_GET[village];
if($villcode == "00000000"){
	$wvill = "";
}else{
	$wvill = " AND h.villcode='$villcode' ";	
}
$chk_stool = $_GET[chk_stool];
if($chk_stool == "1"){
	$chksto = "";
}elseif($chk_stool == "2"){
	$chksto = "where symptoms is not null";
}elseif($chk_stool == "3"){
	$chksto = "where vitalcheck like 'พบ'";		
}elseif($chk_stool == "4"){
	$chksto = "where (para like '%B66.0%' or para like '%B66.1%' or para like '%B66.2%' or para like '%B66.3%')";		
}else{
	$chksto = "where symptoms is null";	
}
$live_type = $_GET[live_type];
if($live_type == '2'){$live_type2 = "p.typelive in ('0','1','2') and";}elseif($live_type == '1'){$live_type2 = "p.typelive in ('0','1','3') and";}else{$live_type2 = "p.typelive in ('0','1','2','3') and";}
$getage = $_GET[getage];
if($getage == "35"){
	$gage = "AND FLOOR((TO_DAYS('$daymidyear')-TO_DAYS(p.birth))/365.25) between 30 and 39";
}elseif($getage == "20"){
	$gage = "AND FLOOR((TO_DAYS('$daymidyear')-TO_DAYS(p.birth))/365.25) < 30";
}elseif($getage == "30"){
	$gage = "AND FLOOR((TO_DAYS('$daymidyear')-TO_DAYS(p.birth))/365.25) > 29";
}elseif($getage == "40"){
	$gage = "AND FLOOR((TO_DAYS('$daymidyear')-TO_DAYS(p.birth))/365.25) > 39";
}else{
	$gage = "";
}
$str = retdaterangstr($_GET[str]);
$sto = retdaterangsto($_GET[str]);
$strx = retDatets($str);
$stox = retDatets($sto);

$sql = "SELECT
ov55.*,
fu56.visitno1,
fu56.sym2,
fu56.vital2,
fu56.code2,
fu56.date2
from
(SELECT
person.pcucodeperson,
person.pid,
person.fname,
CONCAT(ctitle.titlename,person.fname,' ',person.lname) AS pname,
house.hno,
house.villcode,
house.xgis,
house.ygis,
person.birth,
person.typelive,
FLOOR((TO_DAYS('$daymidyear')-TO_DAYS(person.birth))/365.25) AS age,
visit.visitno,
visit.symptoms,
visit.vitalcheck,
visitdiag.diagcode,
visit.visitdate
FROM
village
INNER JOIN house ON village.pcucode = house.pcucode AND village.villcode = house.villcode
INNER JOIN person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
left join ctitle on ctitle.titlecode = person.prename
INNER JOIN visit ON person.pcucodeperson = visit.pcucodeperson AND person.pid = visit.pid
INNER JOIN visitdiag ON visit.pcucode = visitdiag.pcucode AND visit.visitno = visitdiag.visitno
where visit.visitdate between '2011-10-01' and '2012-09-30' and ((person.dischargetype is null) or (person.dischargetype = '9')) AND
SUBSTRING(house.villcode,7,2) <> '00' and visitdiag.diagcode like 'B66%' and (visit.flagservice <'04' OR visit.flagservice is null OR length(trim(visit.flagservice))=0 )
group by person.pcucodeperson,person.pid) as ov55
left join 
(SELECT
person.pcucodeperson,
person.pid,
visit.visitno as visitno1,
visit.symptoms as sym2,
visit.vitalcheck as vital2,
visitdiag.diagcode as code2,
visit.visitdate as date2
FROM
village
INNER JOIN house ON village.pcucode = house.pcucode AND village.villcode = house.villcode
INNER JOIN person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
INNER JOIN visit ON person.pcucodeperson = visit.pcucodeperson AND person.pid = visit.pid
INNER JOIN visitdiag ON visit.pcucode = visitdiag.pcucode AND visit.visitno = visitdiag.visitno
where visit.visitdate between '$str' and '$str' and ((person.dischargetype is null) or (person.dischargetype = '9')) AND
SUBSTRING(house.villcode,7,2) <> '00' and visitdiag.diagcode like 'Z11.6' and (visit.flagservice <'04' OR visit.flagservice is null OR length(trim(visit.flagservice))=0 )
group by person.pcucodeperson,person.pid) as fu56
on ov55.pcucodeperson = fu56.pcucodeperson and ov55.pid = fu56.pid";
$result = mysql_query($sql);
$xml = '<markers>';
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	if($row[visitdate] == ""){$sick = "";}else{$sick = retDatets($row[visitdate]);}
  $xml .= '<marker ';
  $xml .= 'hcode="'.$row[hcode].'" ';
  $xml .= 'hono="'.$row[hno].'" ';
  $xml .= 'moo="'.$moo.'" ';
  $xml .= 'vill="'.$vill.'" ';
  $xml .= 'pname="'.$row[pname].'" ';
  $xml .= 'age="'.$row[age].'" ';
  $xml .= 'symptoms="'.$row[symptoms].'" ';
  $xml .= 'vitalcheck="'.$row[vitalcheck].'" ';
  $xml .= 'para="'.$row[para].'" ';
  $xml .= 'diseasename="'.$row[diseasename].'" ';
  $xml .= 'diseasenamethai="'.$row[diseasenamethai].'" ';
  $xml .= 'sick="'.$sick.'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= '/>';
}
$xml .= '</markers>';
echo $xml;
?>

