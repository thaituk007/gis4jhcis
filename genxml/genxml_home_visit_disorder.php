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
$chk_stool = $_GET[chk_stool];
if($chk_stool == "1"){
	$chksto = "";
}elseif($chk_stool == "2"){
	$chksto = "where count_visit is not null";	
}else{
	$chksto = "where count_visit is null";	
}
$str = retdaterangstr($_GET[str]);
$sto = retdaterangsto($_GET[str]);
$strx = retDatets($str);
$stox = retDatets($sto);
$sql = "
select * ,case when pid1 is not null then 1 else 0 end as chk
from
(select
person.pcucodeperson,
person.pid,
person.fname,
CONCAT(ctitle.titlename,person.fname,' ',person.lname) AS pname,
person.birth,
getageyearnum(person.birth,now()) as age,
house.hcode,
house.hno,
right(house.villcode,2) as moo,
house.villcode,
village.villname,
house.xgis,
house.ygis,
group_concat(cpersonincomplete.incompletename) as incom
FROM
village
Inner Join house ON village.pcucode = house.pcucode AND village.villcode = house.villcode
Inner Join person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
left Join ctitle ON person.prename = ctitle.titlecode
Inner Join personunable ON person.pcucodeperson = personunable.pcucodeperson AND person.pid = personunable.pid
Inner Join personunable1type ON personunable.pcucodeperson = personunable1type.pcucodeperson AND personunable.pid = personunable1type.pid
Inner Join cpersonincomplete ON personunable1type.typecode = cpersonincomplete.incompletecode
where ((person.dischargetype is null) or (person.dischargetype = '9')) AND SUBSTRING(house.villcode,7,2) <> '00' $wvill
group by person.pcucodeperson,person.pid
order by house.villcode,person.fname) as per_chronic 
left join
(SELECT
visit.pcucodeperson as pcucodeperson1,
visit.pid as pid1,
visit.visitno,
count(distinct visit.visitno) as count_visit,
max(visit.visitdate) as visitdate,
max(chomehealthtype.homehealthmeaning) as homehealthmeaning,
max(visithomehealthindividual.patientsign) as patientsign,
max(visithomehealthindividual.homehealthdetail) as homehealthdetail,
max(visithomehealthindividual.homehealthresult) as homehealthresult,
max(visithomehealthindividual.homehealthplan) as homehealthplan,
max(visithomehealthindividual.dateappoint) as dateappoint,
concat(ctitle.titlename,`user`.fname,`user`.lname) as userh,
max(visithomehealthindividual.`user`) as `user`
FROM
visit
Inner Join visithomehealthindividual ON visit.pcucode = visithomehealthindividual.pcucode AND visit.visitno = visithomehealthindividual.visitno
Inner Join chomehealthtype ON visithomehealthindividual.homehealthtype = chomehealthtype.homehealthcode
INNER JOIN `user` ON visit.pcucodeperson = `user`.pcucode AND visithomehealthindividual.`user` = `user`.username
left JOIN ctitle ON `user`.prename = ctitle.titlecode
where visit.visitdate between '$str' and '$sto' and (visit.flagservice <'04' OR visit.flagservice is null OR length(trim(visit.flagservice))=0 )
group by visit.pcucodeperson,visit.pid) as per_homevisit
on per_chronic.pcucodeperson = per_homevisit.pcucodeperson1 and per_chronic.pid = per_homevisit.pid1
$chksto
order by villcode, fname";
$result = mysql_query($sql);
$xml = '<markers>';
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$birth = retDatets($row[birth]);
	if($row[visitdate] == ""){$sick = "--/--/----";}else{$sick = retDatets($row[visitdate]);}
	if($row[count_visit] == ""){$count_visit = "--";}else{$count_visit = $row[count_visit];}
	if($row[count_visit] != ""){$chk = "ได้รับการเยี่ยม";}else{$chk = "ไม่ได้เยี่ยม";}
  $xml .= '<marker ';
  $xml .= 'hcode="'.$row[hcode].'" ';
  $xml .= 'hono="'.$row[hno].'" ';
  $xml .= 'moo="'.$moo.'" ';
  $xml .= 'vill="'.$vill.'" ';
  $xml .= 'pname="'.$row[pname].'" ';
  $xml .= 'birth="'.$birth.'" ';
  $xml .= 'age="'.$row[age].'" ';
  $xml .= 'count_visit="'.$count_visit.'" ';
  $xml .= 'sick="'.$sick.'" ';
  $xml .= 'chk="'.$chk.'" ';
  $xml .= 'incom="'.$row[incom].'" ';
  $xml .= 'homehealthdetail="'.$row[homehealthdetail].'" ';
  $xml .= 'userh="'.$row[userh].'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= '/>';
}
$xml .= '</markers>';
echo $xml;
?>

