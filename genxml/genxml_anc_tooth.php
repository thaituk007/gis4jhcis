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
$str = retdaterangstr($_GET[str]);
$sto = retdaterangsto($_GET[str]);
$strx = retDatets($str);
$stox = retDatets($sto);
$sql = "select
*,
toothcheck as chk
from
(select
village.pcucode, 
person.pid, 
person.idcard,
person.fname, 
concat(ctitle.titlename, person.fname , '  ' , person.lname) AS pname, 
MAX(pregno) as pregno,
village.villcode,
village.villname,
house.hno,
house.hcode,
house.xgis,
house.ygis,
person.birth,
ROUND(DATEDIFF(now(),person.birth)/365.25) AS age,
if(max(visitanc.caries) is null,0,visitanc.caries) as caries,
if(max(visitanc.gumfail) = '0','ไม่มี','มี') as gumfail,
if(max(visitanc.tartar) = '0','ไม่มี','มี') as tartar,
max(visitanc.toothcheck) as toothcheck
FROM 
visitanc 
	left join person on person.pid = visitanc.pid and person.pcucodeperson = visitanc.pcucodeperson
  	left join ctitle on person.prename = ctitle.titlecode
   	left join visitlabblood on visitanc.pid = visitlabblood.pid and visitanc.pcucodeperson = visitlabblood.pcucodeperson
	left join house on person.hcode = house.hcode and person.pcucodeperson = house.pcucode
	left join village on house.villcode = village.villcode and house.pcucode = village.pcucode
WHERE SUBSTRING(house.villcode,7,2) <> '00' and visitanc.datecheck between '$str' and '$sto'
and (birth IS NOT NULL OR birth NOT LIKE '0000%') $wvill
GROUP BY visitanc.pcucodeperson,visitanc.pid) as tmp_anc
order by villcode, fname";
$result = mysql_query($sql);
$xml = '<markers>';
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	if($row[chk] == "1"){$anc_chk = 'ได้รับการตรวจฟัน';}else{$anc_chk = 'ไม่ได้รับการตรวจฟัน';}
	if($row[chk] == "0"){$caries = '-';}else{$caries = $row[caries];}
	if($row[chk] == "0"){$gumfail = '-';}else{$gumfail = $row[gumfail];}
	if($row[chk] == "0"){$tartar = '-';}else{$tartar = $row[tartar];}
	$birth = retDatets($row[birth]);
  $xml .= '<marker ';
  $xml .= 'hcode="'.$row[hcode].'" ';
  $xml .= 'hono="'.$row[hno].'" ';
  $xml .= 'moo="'.$moo.'" ';
  $xml .= 'vill="'.$vill.'" ';
  $xml .= 'pname="'.$row[pname].'" ';
  $xml .= 'birth="'.$birth.'" ';
  $xml .= 'age="'.$row[age].'" ';
  $xml .= 'anc_chk="'.$anc_chk.'" ';
  $xml .= 'toothcheck="'.$row[toothcheck].'" ';
  $xml .= 'pregno="'.$row[pregno].'" ';
  $xml .= 'chk="'.$row[chk].'" ';
  $xml .= 'caries="'.$caries.'" ';
  $xml .= 'gumfail="'.$gumfail.'" ';
  $xml .= 'tartar="'.$tartar.'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= '/>';
}
$xml .= '</markers>';
echo $xml;
?>