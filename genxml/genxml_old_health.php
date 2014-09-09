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
if($chk_old == "1"){
	$chksto = "where chk = 1";
}elseif($chk_old == "2"){
	$chksto = "where chk = 2";
}elseif($chk_old == "8"){
	$chksto = "where chk in (1,2)";
}elseif($chk_old == "9"){
	$chksto = "";	
}else{
	$chksto = "where chk is null";
}
$live_type = $_GET[live_type];
if($live_type == '2'){$live_type_name = "ตามทะเบียนบ้าน(0,1,2)";}elseif($live_type == '1'){$live_type_name = "ที่อาศัยอยู่จริง (0,1,3)";}else{$live_type_name = "ทั้งหมดในเขตรับผิดชอบ(0,1,2,3)";}	
if($live_type == '2'){$live_type2 = "and person.typelive in ('0','1','2')";}elseif($live_type == '1'){$live_type2 = "and person.typelive in ('0','1','3')";}else{$live_type2 = "and person.typelive in ('0','1','2','3')";}
$str = retdaterangstr($_GET[str]);
$sto = retdaterangsto($_GET[str]);
$strx = retDatets($str);
$stox = retDatets($sto);
$sql = "select *
from
(select
person.pcucodeperson,
person.pid,
person.fname, 
concat(ctitle.titlename, person.fname , '  ' , person.lname) AS pname,
person.birth,
ROUND(DATEDIFF(now(),person.birth)/365.25) AS age,
village.villcode,
village.villname,
house.hno,
house.hcode,
house.xgis,
house.ygis
FROM
village
Inner Join house ON village.pcucode = house.pcucode AND village.villcode = house.villcode
Inner Join person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
Inner Join ctitle ON person.prename = ctitle.titlecode
where  FLOOR((TO_DAYS(NOW())-TO_DAYS(person.birth))/365.25) >59 and  ((person.dischargetype is null) or (person.dischargetype = '9'))  and SUBSTRING(house.villcode,7,2) <> '00' $wvill $live_type2) as tmp_per
left join
(select
visit.pcucodeperson,
visit.pid,
visit.visitdate, 
max(visit.visitdate) as m_visit,
visit.visitno,
visit.symptoms,
visit.vitalcheck,
visitdiag.diagcode,
case when vitalcheck is null then 0 when vitalcheck not like '%ไม่ผ่าน%' then 1 when vitalcheck like '%ไม่ผ่าน%' then 2 else '9' end as chk
FROM
village
Inner Join house ON village.pcucode = house.pcucode and village.villcode = house.villcode
Inner Join person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
Inner Join ctitle ON person.prename = ctitle.titlecode
Inner Join visit ON person.pcucodeperson = visit.pcucodeperson AND person.pid = visit.pid
Inner Join visitdiag ON visit.pcucode = visitdiag.pcucode AND visit.visitno = visitdiag.visitno
where FLOOR((TO_DAYS(NOW())-TO_DAYS(person.birth))/365.25) >59 and  ((person.dischargetype is null) or (person.dischargetype = '9'))  and SUBSTRING(house.villcode,7,2) <> '00' $live_type2 and visit.symptoms like '%ตรวจสุขภาพผู้สูงอายุ%'
 and visit.visitdate between '$str' and '$sto' and visitdiag.diagcode in ('Z00.0') and (visit.flagservice <'04' OR visit.flagservice is null OR length(trim(visit.flagservice))=0 )
group by person.pid) as tmp_ncd
ON tmp_per.pcucodeperson = tmp_ncd.pcucodeperson AND tmp_per.pid = tmp_ncd.pid
$chksto
order by tmp_per.pcucodeperson,villcode,fname";
$result = mysql_query($sql);
$xml = '<markers>';
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	if($row[chk] == 0){$old_chk = 'ยังไม่ได้ตรวจ';}elseif($row[chk] == 1){$old_chk = 'ผ่าน';}elseif($row[chk] == 2){$old_chk = 'ไม่ผ่าน';}else{$old_chk = 'ไม่ทราบ';}
	$birth = retDatets($row[birth]);
	if($row[visitdate] == ""){$visitdate = '--/--/----';}else{$visitdate = retDatets($row[visitdate]);}
  $xml .= '<marker ';
  $xml .= 'hcode="'.$row[hcode].'" ';
  $xml .= 'hono="'.$row[hno].'" ';
  $xml .= 'moo="'.$moo.'" ';
  $xml .= 'vill="'.$vill.'" ';
  $xml .= 'pname="'.$row[pname].'" ';
  $xml .= 'birth="'.$birth.'" ';
  $xml .= 'age="'.$row[age].'" ';
  $xml .= 'old_chk="'.$old_chk.'" ';
  $xml .= 'visitdate="'.$visitdate.'" ';
  $xml .= 'chk="'.$row[chk].'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= '/>';
}
$xml .= '</markers>';
echo $xml;
?>