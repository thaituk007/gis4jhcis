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
$str = $_GET[str];
$sql = "select
*,
if(toothmilk is not null,'1','0') as chk
from
(SELECT
village.pcucode, 
person.pid, 
person.idcard,
person.fname, 
concat(ctitle.titlename, person.fname , '  ' , person.lname) AS pname, 
person.birth,
village.villcode,
village.villname,
house.hno,
house.hcode,
house.xgis,
house.ygis,
ROUND(DATEDIFF('$str',person.birth)/30) AS age,
max(visitdentalcheck.toothmilk) as toothmilk,
max(visitdentalcheck.toothmilkcorrupt) as toothmilkcorrupt,
max(visitdentalcheck.toothpermanent) as toothpermanent,
max(visitdentalcheck.toothpermanentcorrupt) as toothpermanentcorrupt,
if(max(visitdentalcheck.tartar) = '0','ไม่มี','มี') as tartar,
if(max(visitdentalcheck.gumstatus) = '0','ไม่มี','มี')as gumstatus
from
village 
INNER JOIN house ON village.villcode = house.villcode AND village.pcucode = house.pcucode 
INNER JOIN person ON house.hcode = person.hcode AND house.pcucode = person.pcucodeperson
 INNER JOIN visit ON person.pid = visit.pid AND person.pcucodeperson = visit.pcucodeperson
INNER JOIN visitepi ON visit.pid = visitepi.pid AND visit.visitno = visitepi.visitno AND visit.pcucode = visitepi.pcucode
INNER JOIN ctitle ON person.prename = ctitle.titlecode
left JOIN visitdentalcheck ON visit.pcucode = visitdentalcheck.pcucode AND visit.visitno = visitdentalcheck.visitno
where  (person.dischargetype Is Null Or person.dischargetype='9') and right(house.villcode,2) <> '00' and ROUND(DATEDIFF('$str',person.birth)/30)  Between 9 And 24 and (visit.flagservice <'04' OR visit.flagservice is null OR length(trim(visit.flagservice))=0 ) $wvill
GROUP BY person.pid) as tmp_epi
order by villcode, fname";
$result = mysql_query($sql);
$xml = '<markers>';
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	if($row[chk] == "1"){$epi_chk = 'ได้รับการตรวจฟัน';}else{$epi_chk = 'ไม่ได้รับการตรวจฟัน';}
	if($row[chk] == "0"){$toothmilk = '-';}else{$toothmilk = $row[toothmilk];}
	if($row[chk] == "0"){$toothmilkcorrupt = '-';}else{$toothmilkcorrupt = $row[toothmilkcorrupt];}
	if($row[chk] == "0"){$toothpermanent = '-';}else{$toothpermanent = $row[toothpermanent];}
	if($row[chk] == "0"){$toothpermanentcorrupt = '-';}else{$toothpermanentcorrupt = $row[toothpermanentcorrupt];}
	if($row[chk] == "0"){$tartar = '-';}else{$tartar = $row[tartar];}
	if($row[chk] == "0"){$gumstatus = '-';}else{$gumstatus = $row[gumstatus];}
	$birth = retDatets($row[birth]);
  $xml .= '<marker ';
  $xml .= 'hcode="'.$row[hcode].'" ';
  $xml .= 'hono="'.$row[hno].'" ';
  $xml .= 'moo="'.$moo.'" ';
  $xml .= 'vill="'.$vill.'" ';
  $xml .= 'pname="'.$row[pname].'" ';
  $xml .= 'birth="'.$birth.'" ';
  $xml .= 'age="'.$row[age].'" ';
  $xml .= 'epi_chk="'.$epi_chk.'" ';
  $xml .= 'chk="'.$row[chk].'" ';
  $xml .= 'toothmilk="'.$toothmilk.'" ';
  $xml .= 'toothmilkcorrupt="'.$toothmilkcorrupt.'" ';
  $xml .= 'toothpermanent="'.$toothpermanent.'" ';
  $xml .= 'toothpermanentcorrupt="'.$toothpermanentcorrupt.'" ';
  $xml .= 'tartar="'.$tartar.'" ';
  $xml .= 'gumstatus="'.$gumstatus.'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= '/>';
}
$xml .= '</markers>';
echo $xml;
?>