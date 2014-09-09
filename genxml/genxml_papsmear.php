<?php 
header("Content-type: text/xml");
include("../includes/conndb.php"); 
include("../includes/config.inc.php"); 
$str = retdaterangstr($_GET[str]);
$sto = retdaterangsto($_GET[str]);
$strx = retDatets($str);
$stox = retDatets($sto);
$villcode = $_GET[village];
if($villcode == "00000000"){
	$wvill = "";
}else{
	$wvill = " AND house.villcode='$villcode' ";	
}
$chk_stool = $_GET[chk_stool];
if($chk_stool == "1"){
	$chksto = "";
}elseif($chk_stool == "2"){
	$chksto = "and cancer.result is not null";
}elseif($chk_stool == "3"){
	$chksto = "and cancer.result in ('1','2','5','6','9')";		
}else{
	$chksto = "and cancer.result is null";		
}
$live_type = $_GET[live_type];
if($live_type == '2'){$live_type2 = "and person.typelive in ('0','1','2')";}elseif($live_type == '1'){$live_type2 = "and person.typelive in ('0','1','3')";}else{$live_type2 = "and person.typelive in ('0','1','2','3')";}
$getage = $_GET[getage];
if($getage == "1"){
	$gage = "AND getAgeYearNum(person.birth,'$str') between 30 and 60";
}elseif($getage == "2"){
	$gage = "AND getAgeYearNum(person.birth,'$str') < 30";
}elseif($getage == "3"){
	$gage = "AND getAgeYearNum(person.birth,'$str') > 60";
}else{
	$gage = "";
}
$sql = "select person.pcucodeperson,
person.pid,
person.fname,
person.idcard,
CONCAT(ctitle.titlename,person.fname,' ',person.lname) AS pname,
house.hno,
house.hcode,
house.villcode,
house.xgis,
house.ygis,
person.birth,
person.typelive,
getAgeYearNum(person.birth,'$str') AS age,
cancer.datecheck,
cancer.typecancer,
cancer.result,
case when cancer.result = 'x' then 'รอผล'
when cancer.result = '0' then 'ปกติ'
when cancer.result = '1' then 'พบความผิดปกติ Cat II'
when cancer.result = '2' then 'พบความผิดปกติ Cat III,IV'
when cancer.result = '5' then 'Negative(-VIA)'
when cancer.result = '6' then 'Positive(+VIA)'
when cancer.result = '9' then 'พบวคามผิดปกตที่ไม่ใช่มะเร็ง' else null end as resultmean,
cancer.hoslab,
cancer.hosservice
from person 
inner join house on person.hcode = house.hcode and person.pcucodeperson = house.pcucode
inner join village on house.villcode = village.villcode and village.villno <>'0'
left join ctitle on person.prename = ctitle.titlecode
left join (select visit.visitno,visit.pid,visitlabcancer.datecheck as datecheck,visitlabcancer.typecancer, visitlabcancer.result,
visitlabcancer.hosservice,
visitlabcancer.hoslab
from visit inner join visitlabcancer on visit.visitno = visitlabcancer.visitno and visit.pcucode = visitlabcancer.pcucode
where visitlabcancer.typecancer in ('2','3') and visitlabcancer.datecheck between '$str' and '$sto')cancer on person.pid = cancer.pid 
where ((person.dischargetype is null) or (person.dischargetype = '9')) and right(house.villcode,2) <> '00' and person.sex = '2' $gage $wvill $chksto $live_type2
order by house.pcucode, house.villcode,person.fname";
$result = mysql_query($sql);
$xml = '<markers>';
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	if($row[datecheck] == ""){$sick = "";}else{$sick = retDatets($row[datecheck]);}
  $xml .= '<marker ';
  $xml .= 'hcode="'.$row[hcode].'" ';
  $xml .= 'hono="'.$row[hno].'" ';
  $xml .= 'moo="'.$moo.'" ';
  $xml .= 'vill="'.$vill.'" ';
  $xml .= 'pname="'.$row[pname].'" ';
  $xml .= 'age="'.$row[age].'" ';
  $xml .= 'resultmean="'.$row[resultmean].'" ';
  $xml .= 'result="'.$row[result].'" ';
  $xml .= 'sick="'.$sick.'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= '/>';
}
$xml .= '</markers>';
echo $xml;
?>

