<?php 
header("Content-type: text/xml");
include("../includes/conndb.php"); 
include("../includes/config.inc.php"); 
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
if($chk_stool == "2"){
	$chksto = "having chk = '1'";
}elseif($chk_stool == "3"){
	$chksto = "having chk = '2'";
}elseif($chk_stool == "4"){
	$chksto = "having chk = '3'";
}elseif($chk_stool == "5"){
	$chksto = "having chk <> '0'";
}elseif($chk_stool == "6"){
	$chksto = "having chk = '0'";		
}else{
	$chksto = "";		
}
$getage = $_GET[getage];
if($getage == "7"){
	$gage = "getageyearnum(person.birth,'$str') > 6";
}elseif($getage == "13"){
	$gage = "getageyearnum(person.birth,'$str') > 12";
}elseif($getage == "12"){
	$gage = "getageyearnum(person.birth,'$str') between 7 and 12";
}else{
	$gage = "";
}
$live_type = $_GET[live_type];
if($live_type == '2'){$live_type2 = "and person.typelive in ('0','1','2')";}elseif($live_type == '1'){$live_type2 = "and person.typelive in ('0','1','3')";}else{$live_type2 = "and person.typelive in ('0','1','2','3')";}
$sql = "SELECT
epi.*,
case when epi.dt1 is not null and epi.dt2 is null then 1
     when epi.dt1 is null and epi.dt2 is not null then 2
     when epi.dt1 is not null and epi.dt2 is not null then 3 else 0 end as chk
from
(SELECT
person.pcucodeperson,
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
getAgeYearNum(person.birth,'$str') AS age
,(select DATE_FORMAT(v1.dateepi,'%Y-%m-%d')  from visitepi v1  where v1.dateepi between '$str' and '$sto' and visitepi.pid = v1.pid  and visitepi.pcucodeperson=v1.pcucodeperson  and v1.vaccinecode in ('DT1','DTS1')  and (v1.dateepi  IS NOT NULL OR  left(v1.dateepi,4) != '0000'  )   group by v1.pid    and v1.pcucodeperson) as dt1
,(select DATE_FORMAT(v1.dateepi,'%Y-%m-%d')  from visitepi v1  where v1.dateepi between '$str' and '$sto' and visitepi.pid = v1.pid  and visitepi.pcucodeperson=v1.pcucodeperson  and v1.vaccinecode in ('DT2','DTS2')  and (v1.dateepi  IS NOT NULL OR  left(v1.dateepi,4) != '0000'  )   group by v1.pid    and v1.pcucodeperson) as dt2
FROM
village
INNER JOIN house ON village.pcucode = house.pcucode AND village.villcode = house.villcode
INNER JOIN person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
left join ctitle on ctitle.titlecode = person.prename
left JOIN visitepi ON person.pcucodeperson = visitepi.pcucodeperson AND person.pid = visitepi.pid
left join cdrug on cdrug.drugcode = visitepi.vaccinecode
where right(house.villcode,2) <> '00' and ((person.dischargetype is null) or (person.dischargetype = '9')) and $gage $wvill $live_type2
group by person.pcucodeperson,person.pid
order by person.pcucodeperson,house.villcode,person.fname) as epi
$chksto";
$result = mysql_query($sql);
$xml = '<markers>';
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	if($row[dt1] == ""){$sick1 = "";}else{$sick1 = retDatets($row[dt1]);}
	if($row[dt2] == ""){$sick2 = "";}else{$sick2 = retDatets($row[dt2]);}
  $xml .= '<marker ';
  $xml .= 'hcode="'.$row[hcode].'" ';
  $xml .= 'hono="'.$row[hno].'" ';
  $xml .= 'moo="'.$moo.'" ';
  $xml .= 'vill="'.$vill.'" ';
  $xml .= 'pname="'.$row[pname].'" ';
  $xml .= 'age="'.$row[age].'" ';
  $xml .= 'sick1="'.$sick1.'" ';
  $xml .= 'sick2="'.$sick2.'" ';
  $xml .= 'chk="'.$row[chk].'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= '/>';
}
$xml .= '</markers>';
echo $xml;
?>

