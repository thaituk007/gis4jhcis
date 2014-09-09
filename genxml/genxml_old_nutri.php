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
	$chksto = "where chk = 'ผอม'";
}elseif($chk_old == "2"){
	$chksto = "where chk = 'ปกติ'";	
}elseif($chk_old == "3"){
	$chksto = "where chk = 'อ้วน'";
}elseif($chk_old == "8"){
	$chksto = "where chk in ('ผอม','ปกติ','อ้วน')";
}elseif($chk_old == "9"){
	$chksto = "";	
}else{
	$chksto = "where chk is null";
}
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
ROUND(DATEDIFF('$str',person.birth)/365.25) AS age,
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
where  FLOOR((TO_DAYS('$str')-TO_DAYS(person.birth))/365.25) >59 and  ((person.dischargetype is null) or (person.dischargetype = '9'))  and SUBSTRING(house.villcode,7,2) <> '00' $wvill) as tmp_per
left join
(select
visit.pcucodeperson,
visit.pid,
max(visit.visitdate) as m_visit,
visit.visitno,
visit.weight,
visit.height,
visit.weight/pow(visit.height/100,2) as bmi,
case when visit.weight/pow(visit.height/100,2) < 18.9 then 'ผอม' when (visit.weight/pow(visit.height/100,2) ) between 18.9 and 22.9 then 'ปกติ' when (visit.weight/pow(visit.height/100,2) ) > 22.9 then 'อ้วน' else '' end as chk
FROM
village
Inner Join house ON village.pcucode = house.pcucode and village.villcode = house.villcode
Inner Join person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
Inner Join ctitle ON person.prename = ctitle.titlecode
Inner Join visit ON person.pcucodeperson = visit.pcucodeperson AND person.pid = visit.pid
Inner Join visitdiag ON visit.pcucode = visitdiag.pcucode AND visit.visitno = visitdiag.visitno
where FLOOR((TO_DAYS('$str')-TO_DAYS(person.birth))/365.25) >59 and  ((person.dischargetype is null) or (person.dischargetype = '9'))  and SUBSTRING(house.villcode,7,2) <> '00' and visit.weight is not null and visit.height is not null
 and visit.visitdate between '$str' and '$sto' and (visit.flagservice <'04' OR visit.flagservice is null OR length(trim(visit.flagservice))=0 )
group by person.pid) as tmp_ncd
ON tmp_per.pcucodeperson = tmp_ncd.pcucodeperson AND tmp_per.pid = tmp_ncd.pid
$chksto
order by tmp_per.pcucodeperson,villcode,fname";
$result = mysql_query($sql);
$xml = '<markers>';
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	if($row[chk] == ""){$old_chk = 'ไม่ได้ชัั่งน้ำหนัก';}else{$old_chk = $row[chk];}
	$birth = retDatets($row[birth]);
	if($row[m_visit] == ""){$visitdate = '--/--/----';}else{$visitdate = retDatets($row[m_visit]);}
	$bmi = number_format($row[bmi], 2, '.', '');
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
  $xml .= 'weight="'.$row[weight].'" ';
  $xml .= 'height="'.$row[height].'" ';
  $xml .= 'bmi="'.$bmi.'" ';
  $xml .= 'chk="'.$row[chk].'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= '/>';
}
$xml .= '</markers>';
echo $xml;
?>