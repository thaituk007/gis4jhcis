<?php
set_time_limit(0); 
header("Content-type: text/xml");
include("../includes/conndb.php"); 
include("../includes/config.inc.php"); 
$villcode = $_GET[village];
if($villcode == "00000000"){
	$wvill = "";
}else{
	$wvill = " h.villcode='$villcode' and ";	
}
if($villcode == "00000000"){
	$mu = "ทุกหมู่บ้าน";
}else{
	$mu = getvillagename($villcode);	
}
$chk_old = $_GET[chk_old];
if($chk_old == "0"){
	$chksto = "";
}elseif($chk_old == "1"){
	$chksto = "having bwok <> 0";	
}elseif($chk_old == "2"){
	$chksto = "having bwok = 3";
}elseif($chk_old == "3"){
	$chksto = "having bwok = 1";
}else{
	$chksto = "having bwok = 0";
}
$str = retdaterangstr($_GET[str]);
$sto = retdaterangsto($_GET[str]);
$strx = retDatets($str);
$stox = retDatets($sto);
$live_type = $_GET[live_type];
if($live_type == '2'){$live_type_name = "ตามทะเบียนบ้าน(0,1,2)";}elseif($live_type == '1'){$live_type_name = "ที่อาศัยอยู่จริง (0,1,3)";}else{$live_type_name = "ทั้งหมดในเขตรับผิดชอบ(0,1,2,3)";}	
if($live_type == '2'){$live_type2 = "and p.typelive in ('0','1','2')";}elseif($live_type == '1'){$live_type2 = "and p.typelive in ('0','1','3')";}else{$live_type2 = "and p.typelive in ('0','1','2','3')";}

$sql = "SELECT 
t1.pcucode,
t1.pcucodeperson,
t1.pid,
t1.pname,
t1.birth,
t1.agemonth,
t1.hno, 
t1.mumoi,
t1.villname,
t1.villcode,
t1.xgis,
t1.ygis,
t2.visitdate ,
t2.tall,
t2.weight,
t2.bw_level,
t2.bmi_level,
t2.heigth_level,
CASE when t2.heigth_level in ('3','4','5') or t2.bmi_level=3 THEN 3 when t2.bmi_level is null then 0 else 1 end as bwok
FROM
(SELECT 
h.pcucode,
h.xgis,
h.ygis,
p.pcucodeperson,
p.pid,
concat(ctitle.titlename,p.fname,'  ',p.lname) as pname,
p.birth,
FLOOR((TO_DAYS('$sto')-TO_DAYS(p.birth))/30.44) as agemonth,
h.hno, 
RIGHT(h.villcode,2) AS mumoi,
villname,h.villcode
FROM person p
LEFT JOIN ctitle on ctitle.titlecode = p.prename
LEFT JOIN house h ON h.hcode=p.hcode and h.pcucodeperson=p.pcucodeperson
LEFT JOIN village v ON v.villcode=h.villcode and v.pcucode=h.pcucode
WHERE $wvill p.BIRTH<'$sto' AND RIGHT(h.villcode,2)<>'00' $live_type2 AND CONCAT(p.pid,p.pcucodeperson) NOT IN (SELECT CONCAT(persondeath.pid,persondeath.pcucodeperson) FROM persondeath)
GROUP BY p.pcucodeperson,p.pid
HAVING agemonth <72
ORDER BY p.mumoi,hnomoi) t1

LEFT JOIN

(SELECT nu.pcucode,nu.pid,nu.sex,nu.visitdate ,nu.agemonth,nu.tall,nu.weight
,max(CASE when nu.tall BETWEEN hc.hmi and hc.hmx THEN hc.nul else null end) as 'heigth_level'
,MAX(case when nu.weight BETWEEN bmi_c.bwmi and bmi_c.bwmx THEN bmi_c.bwnul else null end) as 'bmi_level'
,MAX(case when nu.weight BETWEEN bc.bmin and bc.bmax THEN bc.bnul else null end) as 'bw_level'
from
(SELECT n.pcucode,v.pid,p.sex,p.birth,FLOOR((TO_DAYS(v.visitdate)-TO_DAYS(p.birth))/30.44) as agemonth,n.tall,n.weight,v.visitdate,CONCAT(getAgeMonth(p.birth,v.visitdate)
,case when p.sex=1 then 'm' Else 'f'end)as 'ms'
,CONCAT(CEILING(n.tall)
,case when p.sex=1 then 'm' Else 'f'end)as 'ts'

FROM visitnutrition as n
INNER JOIN visit as v on n.visitno=v.visitno
INNER JOIN person as p on v.pcucodeperson=p.pcucodeperson and v.pid=p.pid
WHERE v.visitdate BETWEEN '$str' and '$sto')as nu
INNER JOIN(SELECT cchart_bh.height_min as hmi ,cchart_bh.height_max as hmx,cchart_bh.nutrition_level as 'nul'
,concat(cchart_bh.age_month,case when cchart_bh.sex=1 then 'm' Else 'f'end)as 'ms' from cchart_bh) as hc on nu.ms=hc.ms
INNER JOIN (SELECT cchart_bmi.bw_min as bwmi ,cchart_bmi.bw_max as bwmx,cchart_bmi.nutrition_level as 'bwnul'
,concat(cchart_bmi.height,case when cchart_bmi.sex=1 then 'm' Else 'f'end)as 'bws' from cchart_bmi) as bmi_c on nu.ts=bmi_c.bws
INNER JOIN(SELECT cchart_bw.bw_min as bmin ,cchart_bw.bw_max as bmax,cchart_bw.nutrition_level as 'bnul'
,concat(cchart_bw.age_month,case when cchart_bw.sex=1 then 'm' Else 'f'end)as 'bs' from cchart_bw) as bc on nu.ms=bc.bs
where nu.agemonth < 72
GROUP BY nu.pcucode,nu.pid
ORDER BY nu.pid) t2
ON t2.pid=t1.pid and t2.pcucode=t1.pcucode
$chksto
order by t1.villcode,t1.agemonth";
$result = mysql_query($sql);
$xml = '<markers>';
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$birth = retDatets($row[birth]);
	if($row[visitdate] == ""){$visitdate = "";}else{$visitdate = "วันที่ ".retDatets($row[visitdate]);}
if($row[bwok] == "3"){$bwokm = "สูงระดับดี/สมส่วน";}elseif($row[bwok] == "1"){$bwokm = "ต้องเฝ้าระวัง";}else{$bwokm = "ยังไม่ได้ชั่งน้ำหนัก";}
  $xml .= '<marker ';
  $xml .= 'hono="'.$row[hno].'" ';
  $xml .= 'moo="'.$moo.'" ';
  $xml .= 'vill="'.$vill.'" ';
  $xml .= 'pname="'.$row[pname].'" ';
  $xml .= 'birth="'.$birth.'" ';
  $xml .= 'age="'.$row[agemonth].'" ';
  $xml .= 'visitdate="'.$visitdate.'" ';
  $xml .= 'bwokm="'.$bwokm.'" ';
  $xml .= 'bwok="'.$row[bwok].'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= '/>';
}
$xml .= '</markers>';
echo $xml;
?>