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
$chk_ultra = $_GET[chk_ultra];
if($chk_ultra == "2"){
	$chksto = "and tmp.vitalcheck is not null";
}elseif($chk_ultra == "3"){
	$chksto = "and tmp.vitalcheck not like 'ปกติ' and tmp.vitalcheck is not null";
}elseif($chk_ultra == "4"){
	$chksto = "and tmp.vitalcheck is null";		
}else{
	$chksto = "";	
}
$str = retdaterangstr($_GET[str]);
$sto = retdaterangsto($_GET[str]);
$strx = retDatets($str);
$stox = retDatets($sto);
$ovyear = substr($sto,0,4);
$sql = "SELECT
person.pcucodeperson,
person.pid,
CONVERT(concat(ifnull(ctitle.titlename,ifnull(person.prename,'ไม่ระบุ') ),person.fname,' ' ,person.lname) using utf8) as pname,
ctitle.titlename,
person.fname,
person.lname,
person.birth,
getageyearnum(person.birth,visit.visitdate) AS age,
house.hno,
house.villcode,
house.xgis,
house.ygis,
visit.pcucode,
visit.visitno,
visit.visitdate,
visit.symptoms,
visit.vitalcheck,
group_concat(visitdiag.diagcode) as xx,
tmp.visitno as ultravisitno,
tmp.visitdate as ultravisitdate,
tmp.symptoms as ultrasys,
tmp.vitalcheck as ultravital,
tmp.diagcode as ultradiag
FROM
village
INNER JOIN house ON village.pcucode = house.pcucode AND village.villcode = house.villcode
INNER JOIN person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
INNER JOIN visit ON person.pcucodeperson = visit.pcucodeperson AND person.pid = visit.pid
INNER JOIN visitdiag ON visit.pcucode = visitdiag.pcucode AND visit.visitno = visitdiag.visitno
INNER JOIN ctitle ON person.prename = ctitle.titlecode
left JOIN
(SELECT
v.pcucode,
v.visitno,
v.visitdate,
v.pcucodeperson,
v.pid,
v.symptoms,
v.vitalcheck,
vd.diagcode
FROM
visit v
INNER JOIN visitdiag vd ON v.pcucode = vd.pcucode AND v.visitno = vd.visitno
where v.visitdate between '$str' and '$sto' and v.symptoms like '%ตรวจอัลตร้าซาว%'  and vd.diagcode like 'Z12.8') as tmp
on tmp.pcucodeperson = visit.pcucodeperson and tmp.pid = visit.pid
where right(house.villcode,2) <> '00' and getageyearnum(person.birth,visit.visitdate) > 39 and visit.visitdate between '2010-10-01' and '$sto' $wvill $chksto
group by visit.pcucode, visit.visitno
having xx like '%Z11.6%' and xx like '%B66%'
";
$result = mysql_query($sql);
$xml = '<markers>';
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	if($row[ultravisitdate] == ""){$sick = "ยังไม่ได้รับการตรวจ";}else{$sick = retDatets($row[ultravisitdate]);}
  $xml .= '<marker ';
  $xml .= 'hono="'.$row[hno].'" ';
  $xml .= 'moo="'.$moo.'" ';
  $xml .= 'vill="'.$vill.'" ';
  $xml .= 'pname="'.$row[pname].'" ';
  $xml .= 'age="'.$row[age].'" ';
  $xml .= 'ultrasys="'.$row[ultrasys].'" ';
  $xml .= 'ultravital="'.$row[ultravital].'" ';
  $xml .= 'sick="'.$sick.'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= '/>';
}
$xml .= '</markers>';
echo $xml;
?>

