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
getageyearnum(person.birth,'$str') AS age,
house.hno,
house.villcode,
house.xgis,
house.ygis,
visitepi.vaccinecode,
visitepi.dateepi
FROM
village
INNER JOIN house ON village.pcucode = house.pcucode AND village.villcode = house.villcode
INNER JOIN person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
INNER JOIN visitepi ON person.pcucodeperson = visitepi.pcucodeperson AND person.pid = visitepi.pid
INNER JOIN ctitle ON person.prename = ctitle.titlecode
WHERE visitepi.vaccinecode in ('dT1','dTs1') and visitepi.dateepi between '$str' and '$sto' $wvill
ORDER BY house.villcode asc ,house.hno*1 asc,getageyearnum(person.birth,'$str') desc
";
$result = mysql_query($sql);
$xml = '<markers>';
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	if($row[dateepi] == ""){$sick = "ยังไม่ได้รับการตรวจ";}else{$sick = retDatets($row[dateepi]);}
  $xml .= '<marker ';
  $xml .= 'hono="'.$row[hno].'" ';
  $xml .= 'moo="'.$moo.'" ';
  $xml .= 'vill="'.$vill.'" ';
  $xml .= 'pname="'.$row[pname].'" ';
  $xml .= 'age="'.$row[age].'" ';
  $xml .= 'vaccinecode="'.$row[vaccinecode].'" ';
  $xml .= 'sick="'.$sick.'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= '/>';
}
$xml .= '</markers>';
echo $xml;
?>

