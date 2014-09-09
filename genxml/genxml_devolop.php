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
vepi.visitdate,
vepi.growdevelop
FROM
(SELECT
person.pcucodeperson,
person.pid,
person.idcard,
concat(ifnull(titlename,'..') ,fname,' ',lname) as pname,
person.birth,
FLOOR(datediff('$str',person.birth)/30.44) as age,
house.hno,
house.villcode,
house.xgis,
house.ygis
FROM
house
INNER JOIN person on person.pcucodeperson = house.pcucode and person.hcode = house.hcode
LEFT JOIN ctitle on ctitle.titlecode = person.prename
where FLOOR(datediff('$str',person.birth)/30.44) < 72 and ((person.dischargetype is null) or (person.dischargetype = '9')) $wvill $live_type2) as pepi
left JOIN
(SELECT
visitnutrition.pcucode,
visitnutrition.visitno,
visit.pid,
visit.visitdate,
visitnutrition.growdevelop
FROM
visit
INNER JOIN visitnutrition ON visit.pcucode = visitnutrition.pcucode AND visit.visitno = visitnutrition.visitno
where visitnutrition.growdevelop is not null and visitnutrition.growdevelop <> '' and visit.visitdate BETWEEN '$str' and '$sto'
and (visit.flagservice <'04' OR visit.flagservice is null OR length(trim(visit.flagservice))=0 )) as vepi
on pepi.pcucodeperson = vepi.pcucode and pepi.pid = vepi.pid
$chksto
order by pepi.villcode,pepi.age";
$result = mysql_query($sql);
$xml = '<markers>';
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	if($row[visitdate] == ""){$chk = 0;}else{$chk = 1;}
	if($row[growdevelop] == 1){$growdevelopn = 'พัฒนาการสมวัย';}elseif($row[growdevelop] == 2){$growdevelopn = 'พัฒนาการสงสัยล่าช้า';}elseif($row[growdevelop] == 3){$growdevelopn = 'พัฒนาการล่าช้า';}else{$growdevelopn = 'ไม่ได้ประเมิน';}
	$birth = retDatets($row[birth]);
	if($row[visitdate] == ""){$visitdate = '';}else{$visitdate = retDatets($row[visitdate]);}
  $xml .= '<marker ';
  $xml .= 'hono="'.$row[hno].'" ';
  $xml .= 'moo="'.$moo.'" ';
  $xml .= 'vill="'.$vill.'" ';
  $xml .= 'pname="'.$row[pname].'" ';
  $xml .= 'birth="'.$birth.'" ';
  $xml .= 'age="'.$row[age].'" ';
  $xml .= 'growdevelop="'.$row[growdevelop].'" ';
  $xml .= 'growdevelopn="'.$growdevelopn.'" ';
  $xml .= 'visitdate="'.$visitdate.'" ';
  $xml .= 'chk="'.$chk.'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= '/>';
}
$xml .= '</markers>';
echo $xml;
?>