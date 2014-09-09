<?php 
header("Content-type: text/xml");
include("../includes/conndb.php"); 
include("../includes/config.inc.php"); 
$villcode = $_GET[village];
if($villcode == "00000000"){
	$wvill = "";
}else{
	$wvill = " AND house.villcode='$villcode' ";	
}
$chk_old = $_GET[chk_old];
if($chk_old == "8"){
	$chksto = "where vepi.pid is not null";
}elseif($chk_old == "1"){
	$chksto = "where vepi.pid is null";
}else{
	$chksto = "";	
}
$str = retdaterangstr($_GET[str]);
$sto = retdaterangsto($_GET[str]);
$strx = retDatets($str);
$stox = retDatets($sto);
$live_type = $_GET[live_type];
if($live_type == '2'){$live_type_name = "ตามทะเบียนบ้าน(0,1,2)";}elseif($live_type == '1'){$live_type_name = "ที่อาศัยอยู่จริง (0,1,3)";}else{$live_type_name = "ทั้งหมดในเขตรับผิดชอบ(0,1,2,3)";}	
if($live_type == '2'){$live_type2 = "and person.typelive in ('0','1','2')";}elseif($live_type == '1'){$live_type2 = "and person.typelive in ('0','1','3')";}else{$live_type2 = "and person.typelive in ('0','1','2','3')";}
$sql = "
SELECT
pepi.*,
vepi.dateepi,
vepi.vaccinecode
FROM
(SELECT
person.pcucodeperson,
person.pid,
person.idcard,
concat(ifnull(titlename,'..') ,fname,' ',lname) as pname,
person.birth,
getagemonth(person.birth,now()) as age,
house.hno,
house.villcode,
house.xgis,
house.ygis
FROM
house
INNER JOIN person on person.pcucodeperson = house.pcucode and person.hcode = house.hcode
LEFT JOIN ctitle on ctitle.titlecode = person.prename
where person.birth BETWEEN '$str' and '$sto' and ((person.dischargetype is null) or (person.dischargetype = '9')) $wvill $live_type2) as pepi
left JOIN
(SELECT
visitepi.pcucodeperson,
visitepi.pid,
visitepi.dateepi,
visitepi.vaccinecode
FROM
visitepi
INNER JOIN cdrug on cdrug.drugcode = visitepi.vaccinecode
where cdrug.files18epi = '061') as vepi
on pepi.pcucodeperson = vepi.pcucodeperson and pepi.pid = vepi.pid
$chksto
order by vepi.vaccinecode,pepi.villcode,pepi.age";
$result = mysql_query($sql);
$xml = '<markers>';
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	if($row[dateepi] == ""){$chk = 0;}else{$chk = 1;}
	$birth = retDatets($row[birth]);
	if($row[dateepi] == ""){$visitdate = '';}else{$visitdate = retDatets($row[dateepi]);}
  $xml .= '<marker ';
  $xml .= 'hono="'.$row[hno].'" ';
  $xml .= 'moo="'.$moo.'" ';
  $xml .= 'vill="'.$vill.'" ';
  $xml .= 'pname="'.$row[pname].'" ';
  $xml .= 'birth="'.$birth.'" ';
  $xml .= 'age="'.$row[age].'" ';
  $xml .= 'mmr="'.$row[vaccinecode].'" ';
  $xml .= 'visitdate="'.$visitdate.'" ';
  $xml .= 'chk="'.$chk.'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= '/>';
}
$xml .= '</markers>';
echo $xml;
?>