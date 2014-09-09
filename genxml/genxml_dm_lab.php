<?php 
header("Content-type: text/xml");
include("../includes/conndb.php"); 
include("../includes/config.inc.php"); 
$villcode = $_GET[village];
if($villcode == "00000000"){
	$wvill = "";
}else{
	$wvill = "AND house.villcode='$villcode' ";	
}
$str = retdaterangstr($_GET[str]);
$sto = retdaterangsto($_GET[str]);
$strx = retDatets($str);
$stox = retDatets($sto);
$sql = "select *,
case when CH99 is not null then 1 else 0 end as chk
 from
(SELECT
person.pcucodeperson,
person.pid,
person.fname,
concat(ctitle.titlename,person.fname ,'  ' ,person.lname) AS pname,
person.birth,
ROUND(DATEDIFF(now(),person.birth)/365.25) AS age,
village.villcode,
village.villname,
house.hno,
house.hcode,
house.xgis,
house.ygis,
group_concat(cdisease.codechronic) as codex,
group_concat(cdiseasechronic.groupname) as chronicx
FROM
personchronic
inner join person on person.pcucodeperson = personchronic.pcucodeperson AND person.pid = personchronic.pid
inner join cdisease on personchronic.chroniccode = cdisease.diseasecode
left join cdiseasechronic on cdiseasechronic.groupcode = cdisease.codechronic
inner join ctitle on person.prename = ctitle.titlecode
inner join house on person.hcode = house.hcode and person.pcucodeperson = house.pcucode
inner join village ON house.villcode = village.villcode and house.pcucode = village.pcucode
where ((person.dischargetype is null) or (person.dischargetype = '9')) AND SUBSTRING(house.villcode,7,2) <> '00' $wvill
group by person.pcucodeperson, person.pid
having codex like '%10%'
) as tmp_per
left join
(select
person.pid as pid1,
person.pcucodeperson as pcucodeperson1,
(select l1.datecheck from visitlabchcyhembmsse l1  where person.pid = l1.pid  and person.pcucodeperson=l1.pcucodeperson  and l1.labcode='CH99'  and (l1.datecheck  IS NOT NULL OR  left(l1.datecheck,4) != '0000') group by l1.pid,l1.pcucodeperson) as CH99,
(select l1.datecheck from visitlabchcyhembmsse l1  where person.pid = l1.pid  and person.pcucodeperson=l1.pcucodeperson  and l1.labcode='CH25'  and (l1.datecheck  IS NOT NULL OR  left(l1.datecheck,4) != '0000') group by l1.pid,l1.pcucodeperson) as CH25,
(select l1.datecheck from visitlabchcyhembmsse l1  where person.pid = l1.pid  and person.pcucodeperson=l1.pcucodeperson  and l1.labcode='CH07'  and (l1.datecheck  IS NOT NULL OR  left(l1.datecheck,4) != '0000') group by l1.pid,l1.pcucodeperson) as CH07,
(select l1.datecheck from visitlabchcyhembmsse l1  where person.pid = l1.pid  and person.pcucodeperson=l1.pcucodeperson  and l1.labcode='CH14'  and (l1.datecheck  IS NOT NULL OR  left(l1.datecheck,4) != '0000') group by l1.pid,l1.pcucodeperson) as CH14,
(select l1.datecheck from visitlabchcyhembmsse l1  where person.pid = l1.pid  and person.pcucodeperson=l1.pcucodeperson  and l1.labcode='CH17'  and (l1.datecheck  IS NOT NULL OR  left(l1.datecheck,4) != '0000') group by l1.pid,l1.pcucodeperson) as CH17,
(select l1.datecheck from visitlabchcyhembmsse l1  where person.pid = l1.pid  and person.pcucodeperson=l1.pcucodeperson  and l1.labcode='CH04'  and (l1.datecheck  IS NOT NULL OR  left(l1.datecheck,4) != '0000') group by l1.pid,l1.pcucodeperson) as CH04,
(select l1.datecheck from visitlabchcyhembmsse l1  where person.pid = l1.pid  and person.pcucodeperson=l1.pcucodeperson  and l1.labcode='CH09'  and (l1.datecheck  IS NOT NULL OR  left(l1.datecheck,4) != '0000') group by l1.pid,l1.pcucodeperson) as CH09,
(select l1.datecheck from visitlabchcyhembmsse l1  where person.pid = l1.pid  and person.pcucodeperson=l1.pcucodeperson  and l1.labcode='Cha1'  and (l1.datecheck  IS NOT NULL OR  left(l1.datecheck,4) != '0000') group by l1.pid,l1.pcucodeperson) as Cha1,
(select l1.datecheck from visitlabchcyhembmsse l1  where person.pid = l1.pid  and person.pcucodeperson=l1.pcucodeperson  and l1.labcode='Chc1'  and (l1.datecheck  IS NOT NULL OR  left(l1.datecheck,4) != '0000') group by l1.pid,l1.pcucodeperson) as Chc1
FROM
personchronic
inner join person on person.pcucodeperson = personchronic.pcucodeperson AND person.pid = personchronic.pid
inner join cdisease on personchronic.chroniccode = cdisease.diseasecode
inner join cdiseasechronic on cdiseasechronic.groupcode = cdisease.codechronic
inner Join visitlabchcyhembmsse ON person.pcucodeperson = visitlabchcyhembmsse.pcucodeperson AND visitlabchcyhembmsse.pid = person.pid
inner join clabchcyhembmsse ON visitlabchcyhembmsse.labcode = clabchcyhembmsse.labcode
inner join ctitle on person.prename = ctitle.titlecode
inner join house on person.hcode = house.hcode and person.pcucodeperson = house.pcucode
inner join village ON house.villcode = village.villcode and house.pcucode = village.pcucode
where  visitlabchcyhembmsse.datecheck between '$str' and '$sto'
group by person.pid,person.pcucodeperson) as tmp_lab
on tmp_per.pid = tmp_lab.pid1 and tmp_per.pcucodeperson = tmp_lab.pcucodeperson1
order by villcode, fname";
$result = mysql_query($sql);
$xml = '<markers>';
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	if($row[CH99] == ''){$ch99 = 'ยังไม่ตรวจ';}else{$ch99 = retDatets($row[CH99]);}
  $xml .= '<marker ';
  $xml .= 'hcode="'.$row[hcode].'" ';
  $xml .= 'hono="'.$row[hno].'" ';
  $xml .= 'moo="'.$moo.'" ';
  $xml .= 'vill="'.$vill.'" ';
  $xml .= 'pname="'.$row[pname].'" ';
  $xml .= 'age="'.$row[age].'" ';
  $xml .= 'codex="'.$row[codex].'" ';
  $xml .= 'chronicx="'.$row[chronicx].'" ';
  $xml .= 'ch99="'.$ch99.'" ';
  $xml .= 'chk="'.$row[chk].'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= '/>';
}
$xml .= '</markers>';
echo $xml;
?>

