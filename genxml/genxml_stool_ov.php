<?php
set_time_limit(0); 
header("Content-type: text/xml");
include("../includes/conndb.php"); 
include("../includes/config.inc.php");
$dx = date("md");
$yx = date("Y");
$yy = date("Y")-1;
if($dx > "1001"){$daymidyear = $yx."-10-01";}else{$daymidyear = $yy."-10-01";}

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
	$chksto = "where fp.visitdate is not null";
}elseif($chk_stool == "3"){
	$chksto = "where para.para is not null";		
}elseif($chk_stool == "4"){
	$chksto = "where para.para like '%B66%'";		
}else{
	$chksto = "where fp.visitdate is null";	
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
$sql = "select *,
case when fp.visitdate is not null and para.para is not null then concat('ผลการตรวจ พบ',para.para,' ',para.diseasenamethai)
when fp.visitdate is not null and para.para is null then 'ผลการตรวจ ไม่พบ'
when fp.visitdate is null then 'ยังไม่ได้ตรวจ' ELSE '' end as chk
from
(SELECT
p.pcucodeperson,
p.pid,
p.fname,
CONCAT(ctitle.titlename,p.fname,' ',p.lname) AS pname,
h.hno,
h.villcode,
h.xgis,
h.ygis,
p.birth,
p.typelive,
FLOOR((TO_DAYS('$daymidyear')-TO_DAYS(p.birth))/365.25) AS age
FROM
house AS h
Inner Join person AS p ON h.pcucode = p.pcucodeperson AND h.hcode = p.hcode
Inner Join cstatus ON p.marystatus = cstatus.statuscode
Inner Join ctitle ON p.prename = ctitle.titlecode
WHERE $live_type2 ((p.dischargetype is null) or (p.dischargetype = '9')) AND
				SUBSTRING(h.villcode,7,2) <> '00' $gage $wvill ORDER BY h.villcode,h.hno*1
) as per
left join 
(SELECT
visit.pcucodeperson as pcucodeperson1,
visit.pid as pid1,
visit.visitno as visitno1,
visitdiag.diagcode,
visit.visitdate
FROM
visit
INNER JOIN visitdiag ON visit.pcucode = visitdiag.pcucode AND visit.visitno = visitdiag.visitno
where visit.visitdate between '$str' and '$sto' and visitdiag.diagcode = 'Z11.6' and (visit.flagservice <'04' OR visit.flagservice is null OR length(trim(visit.flagservice))=0 )) as fp
on per.pcucodeperson = fp.pcucodeperson1 and per.pid = fp.pid1
left join
(SELECT
visit.pcucodeperson as pcucodeperson2,
visit.pid as pid2,
visit.visitno as visitno2,
GROUP_CONCAT(visitdiag.diagcode) as para,
GROUP_CONCAT(cdisease.diseasenamethai) as diseasenamethai
FROM
visit
INNER JOIN visitdiag ON visit.pcucode = visitdiag.pcucode AND visit.visitno = visitdiag.visitno
inner join cdisease on visitdiag.diagcode = cdisease.diseasecode
where visit.visitdate between '$str' and '$sto' and visitdiag.diagcode like 'B%' and (visit.flagservice <'04' OR visit.flagservice is null OR length(trim(visit.flagservice))=0 ) 
group by visit.pcucode,visit.visitno) as para
on para.pcucodeperson2 = fp.pcucodeperson1 and para.pid2 = fp.pid1 and para.visitno2 = fp.visitno1
$chksto
order by villcode, fname";
$result = mysql_query($sql);
$xml = '<markers>';
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	if($row[visitdate] == ""){$sick = "";}else{$sick = "วันที่ตรวจ ".retDatets($row[visitdate]);}

  $xml .= '<marker ';
  $xml .= 'hcode="'.$row[hcode].'" ';
  $xml .= 'hono="'.$row[hno].'" ';
  $xml .= 'moo="'.$moo.'" ';
  $xml .= 'vill="'.$vill.'" ';
  $xml .= 'pname="'.$row[pname].'" ';
  $xml .= 'age="'.$row[age].'" ';
  $xml .= 'chk="'.$row[chk].'" ';
  $xml .= 'para="'.$row[para].'" ';
  $xml .= 'diseasenamethai="'.$row[diseasenamethai].'" ';
  $xml .= 'sick="'.$sick.'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= '/>';
}
$xml .= '</markers>';
echo $xml;
?>

