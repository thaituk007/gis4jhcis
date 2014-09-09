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
if($chk_old == "0"){
	$chksto = "";
}elseif($chk_old == "1"){
	$chksto = "and vsb.visitdate is not null";	
}else{
	$chksto = "and vsb.visitdate is null";
}
$live_type = $_GET[live_type];
if($live_type == '2'){$live_type_name = "ตามทะเบียนบ้าน(0,1,2)";}elseif($live_type == '1'){$live_type_name = "ที่อาศัยอยู่จริง (0,1,3)";}else{$live_type_name = "ทั้งหมดในเขตรับผิดชอบ(0,1,2,3)";}	
if($live_type == '2'){$live_type2 = "and person.typelive in ('0','1','2')";}elseif($live_type == '1'){$live_type2 = "and person.typelive in ('0','1','3')";}else{$live_type2 = "and person.typelive in ('0','1','2','3')";}
$str = retdaterangstr($_GET[str]);
$sto = retdaterangsto($_GET[str]);
$strx = retDatets($str);
$stox = retDatets($sto);
$sql = "SELECT
person.pcucodeperson,
person.pid,
person.fname, 
concat(ctitle.titlename, person.fname , '  ' , person.lname) AS pname,
person.birth,
getAgeYearNum(person.birth,'$str') AS age,
village.villcode,
village.villname,
house.hno,
house.hcode,
house.xgis,
house.ygis,
vsb.pid as vspid,
vsb.visitdate,
vsb.labresulttext
FROM village
Inner Join house ON village.pcucode = house.pcucode AND village.villcode = house.villcode
INNER JOIN person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
INNER JOIN coccupa ON person.occupa = coccupa.occupacode
left Join ctitle ON person.prename = ctitle.titlecode
left join (SELECT vs.pcucodeperson, vs.pid, vs.visitdate, vslab.labresulttext
FROM visit as vs 
INNER JOIN visitdiag as vsd ON vs.pcucode = vsd.pcucode AND vs.visitno = vsd.visitno
left join visitlabchcyhembmsse vslab on vs.pcucodeperson = vslab.pcucodeperson and vslab.pid = vs.pid and vslab.datecheck = vs.visitdate
where vs.visitdate between '$str' and '$sto' and vsd.diagcode = 'Z10.0' 
group by vs.pcucodeperson,vs.pid) as vsb
on person.pcucodeperson = vsb.pcucodeperson and person.pid = vsb.pid
where getAgeYearNum(person.birth,'$str') > 14 and ((person.dischargetype is null) or (person.dischargetype = '9'))  and SUBSTRING(house.villcode,7,2) <> '00' and coccupa.mapoccupa like '6%' $live_type2 $chksto $wvill
order by person.pcucodeperson,village.villcode,person.fname";
$result = mysql_query($sql);
$xml = '<markers>';
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	if($row[visitdate] == ""){$old_chk = 'ยังไม่ได้ตรวจ';}else{$old_chk = 'ได้รับการตรวจ';}
	$birth = retDatets($row[birth]);
	if($row[visitdate] == ""){$visitdate = '--/--/----';}else{$visitdate = retDatets($row[visitdate]);}
  $xml .= '<marker ';
  $xml .= 'hono="'.$row[hno].'" ';
  $xml .= 'moo="'.$moo.'" ';
  $xml .= 'vill="'.$vill.'" ';
  $xml .= 'pname="'.$row[pname].'" ';
  $xml .= 'birth="'.$birth.'" ';
  $xml .= 'age="'.$row[age].'" ';
  $xml .= 'vspid="'.$vspid.'" ';
  $xml .= 'old_chk="'.$old_chk.'" ';
  $xml .= 'visitdate="'.$visitdate.'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= '/>';
}
$xml .= '</markers>';
echo $xml;
?>