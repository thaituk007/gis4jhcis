<?php 
header("Content-type: text/xml");
include("../includes/conndb.php"); 
include("../includes/config.inc.php");
$op = $_GET['app_type'];
if($op === 'chronic'){
	chronic();
}else if($op === 'epi'){
    epi();
}else if($op === 'anc'){
    anc();
}else if($op === 'fp'){
    fp();
}


function chronic(){ //function นัด เบาหวานความดัน
$village = $_GET[village];
if($village == "00000000"){
	$wvill = "";
}else{
	$wvill = " AND perapp.villcode='$village' ";	
}
if($village == "00000000"){
	$mu = "ทุกหมู่บ้าน";
}else{
	$mu = getvillagename($village);	
}
$chk_ncd = $_GET[chk_ncd];
if($chk_ncd == "2"){
	$chkncd = " AND vper.visitdate is not null";	
}else if($chk_ncd == "3"){
	$chkncd = " AND vper.visitdate is null";	
}else{
	$chkncd = "";	
}

$str = $_GET[str];
$strx = retDatets($str);
$sql = "SELECT
perapp.*,
vper.visitdate as datechk
FROM
(SELECT
person.pcucodeperson,
person.pid,
person.idcard,
person.fname,
concat(ctitle.titlename, person.fname , '  ' , person.lname) AS pname,
person.birth,
ROUND(DATEDIFF(now(),person.birth)/365.25) AS age,
house.villcode,
house.hno,
house.hcode,
house.xgis,
house.ygis,
visit.visitdate,
visitdiagappoint.appodate,
visitdiagappoint.appotype
FROM
house
INNER JOIN person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
left JOIN ctitle on person.prename = ctitle.titlecode
INNER JOIN visit ON person.pcucodeperson = visit.pcucodeperson AND person.pid = visit.pid
INNER JOIN visitdiagappoint ON visit.pcucode = visitdiagappoint.pcucode AND visit.visitno = visitdiagappoint.visitno
INNER JOIN cdisease ON visitdiagappoint.diagcode = cdisease.diseasecode
INNER JOIN cdiseasechronic ON cdisease.codechronic = cdiseasechronic.groupcode
where cdiseasechronic.groupcode in ('01','10' ) and visitdiagappoint.appodate = '$str'
GROUP BY visit.pcucodeperson,visit.pid
ORDER BY visitdiagappoint.appodate,house.villcode) as perapp
left JOIN (SELECT visit.* FROM visit WHERE visit.visitdate = '$str') as vper 
on perapp.pcucodeperson = vper.pcucodeperson and perapp.pid = vper.pid
where perapp.pcucodeperson is not null $chkncd $wvill
order by perapp.villcode,perapp.fname";
$result = mysql_query($sql);
$xml = '<markers>';
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$title = getTitle($row[prename]);
	if($row[appotype] == "1"){$apptypename = "รับยา";}elseif($row[appotype] == "2"){$apptypename = "ฟังผล";}elseif($row[appotype] == "3"){$apptypename = "ทำแผล";}elseif($row[appotype] == "4"){$apptypename = "เจาะเลือด";}elseif($row[appotype] == "5"){$apptypename = "ตรวจน้ำตาล(DTX)";}elseif($row[appotype] == "6"){$apptypename = "วัดความดันฯ";}else{$apptypename = retDatets($row[appodate]);}
	if($row[appodate] == ""){$appsick = "";}else{$appsick = retDatets($row[appodate]);}
	if($row[datechk] == ""){$sick = "";}else{$sick = retDatets($row[datechk]);}
	if($row[datechk] == ""){$sicksign = "ขาดนัด";}else{$sicksign = "มาตามนัด";}
  $xml .= '<marker ';
  $xml .= 'pid="'.$row[pid].'" ';
  $xml .= 'pname="'.$row[pname].'" ';
  $xml .= 'age="'.$row[age].'" ';
  $xml .= 'hno="'.$row[hno].'" ';
  $xml .= 'moo="'.$moo.'" ';
  $xml .= 'appsick="'.$appsick.'" ';
  $xml .= 'apptypename="'.$apptypename.'" ';
  $xml .= 'sicksign="'.$sicksign.'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= '/>';
}
$xml .= '</markers>';
echo $xml;
}

function epi(){ //function นัด epi
$village = $_GET[village];
if($village == "00000000"){
	$wvill = "";
}else{
	$wvill = " AND perapp.villcode='$village' ";	
}
if($village == "00000000"){
	$mu = "ทุกหมู่บ้าน";
}else{
	$mu = getvillagename($village);	
}
$chk_ncd = $_GET[chk_ncd];
if($chk_ncd == "2"){
	$chkncd = " AND vper.visitdate is not null";	
}else if($chk_ncd == "3"){
	$chkncd = " AND vper.visitdate is null";	
}else{
	$chkncd = "";	
}


$str = $_GET[str];
$strx = retDatets($str);
$sql = "SELECT
perapp.*,
vper.visitdate as datechk
FROM
(SELECT
person.pcucodeperson,
person.pid,
person.idcard,
person.fname,
concat(ctitle.titlename, person.fname , '  ' , person.lname) AS pname,
person.birth,
ROUND(DATEDIFF(now(),person.birth)/30.44) AS age,
house.villcode,
house.hno,
house.hcode,
house.xgis,
house.ygis,
visitepiappoint.dateappoint,
GROUP_CONCAT(visitepiappoint.vaccinecode) as vaccinex
FROM
house
INNER JOIN person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
left JOIN ctitle on person.prename = ctitle.titlecode
INNER JOIN visitepiappoint on person.pcucodeperson = visitepiappoint.pcucode and person.pid = visitepiappoint.pid
where visitepiappoint.dateappoint = '$str'
GROUP BY visitepiappoint.pcucodeperson,visitepiappoint.pid
ORDER BY visitepiappoint.dateappoint,house.villcode) as perapp
left JOIN (SELECT visit.* FROM visit WHERE visit.visitdate = '$str') as vper 
on perapp.pcucodeperson = vper.pcucodeperson and perapp.pid = vper.pid
where perapp.pcucodeperson is not null $chkncd $wvill
order by perapp.villcode,perapp.fname";
$result = mysql_query($sql);
$xml = '<markers>';
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$title = getTitle($row[prename]);
	if($row[dateappoint] == ""){$appsick = "";}else{$appsick = retDatets($row[dateappoint]);}
	if($row[datechk] == ""){$sick = "";}else{$sick = retDatets($row[datechk]);}
	if($row[datechk] == ""){$sicksign = "ขาดนัด";}else{$sicksign = "มาตามนัด";}
  $xml .= '<marker ';
  $xml .= 'pid="'.$row[pid].'" ';
  $xml .= 'pname="'.$row[pname].'" ';
  $xml .= 'age="'.$row[age].'" ';
  $xml .= 'hno="'.$row[hno].'" ';
  $xml .= 'moo="'.$moo.'" ';
  $xml .= 'appsick="'.$appsick.'" ';
  $xml .= 'apptypename="'.$row[vaccinex].'" ';
  $xml .= 'sicksign="'.$sicksign.'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= '/>';
}
$xml .= '</markers>';
echo $xml;
}
function anc(){ //function นัด anc
$village = $_GET[village];
if($village == "00000000"){
	$wvill = "";
}else{
	$wvill = " AND perapp.villcode='$village' ";	
}
if($village == "00000000"){
	$mu = "ทุกหมู่บ้าน";
}else{
	$mu = getvillagename($village);	
}
$chk_ncd = $_GET[chk_ncd];
if($chk_ncd == "2"){
	$chkncd = " AND vper.visitdate is not null";	
}else if($chk_ncd == "3"){
	$chkncd = " AND vper.visitdate is null";	
}else{
	$chkncd = "";	
}

$str = $_GET[str];
$strx = retDatets($str);
$sql = "SELECT
perapp.*,
vper.visitdate as datechk
FROM
(
SELECT
person.pcucodeperson,
person.pid,
person.idcard,
person.fname,
concat(ctitle.titlename, person.fname , '  ' , person.lname) AS pname,
person.birth,
ROUND(DATEDIFF(now(),person.birth)/365.25) AS age,
house.villcode,
house.hno,
house.hcode,
house.xgis,
house.ygis,
visitanc.pregage,
visitanc.datecheck,
visitanc.dateappointcheck
FROM
house
INNER JOIN person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
INNER JOIN visitanc ON person.pcucodeperson = visitanc.pcucodeperson AND person.pid = visitanc.pid
LEFT JOIN ctitle ON person.prename = ctitle.titlecode
where visitanc.dateappointcheck = '$str') as perapp
left JOIN (SELECT visit.* FROM visit WHERE visit.visitdate = '$str') as vper 
on perapp.pcucodeperson = vper.pcucodeperson and perapp.pid = vper.pid
where perapp.pcucodeperson is not null $chkncd $wvill
order by perapp.villcode,perapp.fname";
$result = mysql_query($sql);
$xml = '<markers>';
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$title = getTitle($row[prename]);
	if($row[dateappointcheck] == ""){$appsick = "";}else{$appsick = retDatets($row[dateappointcheck]);}
	if($row[datechk] == ""){$sick = "";}else{$sick = retDatets($row[datechk]);}
	if($row[datechk] == ""){$sicksign = "ขาดนัด";}else{$sicksign = "มาตามนัด";}
	$apptypename = "anc";
  $xml .= '<marker ';
  $xml .= 'pid="'.$row[pid].'" ';
  $xml .= 'pname="'.$row[pname].'" ';
  $xml .= 'age="'.$row[age].'" ';
  $xml .= 'hno="'.$row[hno].'" ';
  $xml .= 'moo="'.$moo.'" ';
  $xml .= 'appsick="'.$appsick.'" ';
  $xml .= 'apptypename="'.$apptypename.'" ';
  $xml .= 'sicksign="'.$sicksign.'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= '/>';
}
$xml .= '</markers>';
echo $xml;
}
 function fp(){ //function นัด fp
$village = $_GET[village];
if($village == "00000000"){
	$wvill = "";
}else{
	$wvill = " AND perapp.villcode='$village' ";	
}
if($village == "00000000"){
	$mu = "ทุกหมู่บ้าน";
}else{
	$mu = getvillagename($village);	
}
$chk_ncd = $_GET[chk_ncd];
if($chk_ncd == "2"){
	$chkncd = " AND vper.visitdate is not null";	
}else if($chk_ncd == "3"){
	$chkncd = " AND vper.visitdate is null";	
}else{
	$chkncd = "";	
}

$str = $_GET[str];
$strx = retDatets($str);
$sql = "SELECT
perapp.*,
vper.visitdate as datechk
FROM
(
SELECT
person.pcucodeperson,
person.pid,
person.idcard,
person.fname,
concat(ctitle.titlename, person.fname , '  ' , person.lname) AS pname,
person.birth,
ROUND(DATEDIFF(now(),person.birth)/365.25) AS age,
house.villcode,
house.hno,
house.hcode,
house.xgis,
house.ygis,
visitfp.datedue,
cdrug.drugname,
cdrug.drugtypesub,
visitfp.datefp
FROM
house
INNER JOIN person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
INNER JOIN visitfp ON person.pcucodeperson = visitfp.pcucodeperson AND person.pid = visitfp.pid
inner JOIN cdrug on visitfp.fpcode = cdrug.drugcode
LEFT JOIN ctitle ON person.prename = ctitle.titlecode
where visitfp.datedue = '$str') as perapp
left JOIN (SELECT visit.* FROM visit WHERE visit.visitdate = '$str') as vper 
on perapp.pcucodeperson = vper.pcucodeperson and perapp.pid = vper.pid
where perapp.pcucodeperson is not null $chkncd $wvill
order by perapp.villcode,perapp.fname";
$result = mysql_query($sql);
$xml = '<markers>';
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$title = getTitle($row[prename]);
	
	if($row[drugtypesub] == "0"){$apptypename = "วชย.ทดสอบตั้งครรภ์";}elseif($row[drugtypesub] == "1"){$apptypename = "ยาเม็ด";}elseif($row[drugtypesub] == "2"){$apptypename = "ยาฉีด";}elseif($row[drugtypesub] == "3"){$apptypename = "ยาฝัง";}elseif($row[drugtypesub] == "4"){$apptypename = "ห่วง";}elseif($row[drugtypesub] == "5"){$apptypename = "ถุงยางอนามัย";}elseif($row[drugtypesub] == "6"){$apptypename = "หมันชาย";}else{$apptypename = "หมันหญิง";}
	if($row[datedue] == ""){$appsick = "";}else{$appsick = retDatets($row[datedue]);}
	if($row[datechk] == ""){$sick = "";}else{$sick = retDatets($row[datechk]);}
	if($row[datechk] == ""){$sicksign = "ขาดนัด";}else{$sicksign = "มาตามนัด";}
  $xml .= '<marker ';
  $xml .= 'pid="'.$row[pid].'" ';
  $xml .= 'pname="'.$row[pname].'" ';
  $xml .= 'age="'.$row[age].'" ';
  $xml .= 'hno="'.$row[hno].'" ';
  $xml .= 'moo="'.$moo.'" ';
  $xml .= 'appsick="'.$appsick.'" ';
  $xml .= 'apptypename="'.$apptypename.'" ';
  $xml .= 'sicksign="'.$sicksign.'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= '/>';
}
$xml .= '</markers>';
echo $xml;
}
?>

