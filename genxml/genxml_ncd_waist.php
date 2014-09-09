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
$chk_ncd = $_GET[chk_ncd];
if($chk_ncd == "1"){
	$chksto = "";
}elseif($chk_ncd == "2"){
	$chksto = "where chk = 1 or chk = 0";	
}elseif($chk_ncd == "3"){
	$chksto = "where chk = 1";	
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
ROUND(DATEDIFF(now(),person.birth)/365.25) AS age,
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
where ROUND(DATEDIFF(now(),person.birth)/365.25) > 14 and SUBSTRING(house.villcode,7,2) <> '00' AND (person.dischargetype Is Null Or person.dischargetype='9') $wvill) as tmp_per
left join
(select 
ncd_person_ncd_screen.pcucode,
ncd_person_ncd_screen.pid,
ncd_person_ncd_screen.screen_date,
ncd_person_ncd_screen.weight,
ncd_person_ncd_screen.height,
ncd_person_ncd_screen.bmi,
ncd_person_ncd_screen.waist,
if(ncd_person_ncd_screen.waist is null,null,if( (person.sex='1' and ncd_person_ncd_screen.waist >89 ) or (person.sex='2' and ncd_person_ncd_screen.waist >79),1,0)) as chk
FROM  ncd_person_ncd_screen
inner join person on ncd_person_ncd_screen.pcucode = person.pcucodeperson and ncd_person_ncd_screen.pid = person.pid
where ncd_person_ncd_screen.screen_date between '$str' and '$sto') as tmp_ncd
ON tmp_per.pcucodeperson = tmp_ncd.pcucode AND tmp_per.pid = tmp_ncd.pid
$chksto
order by villcode, fname";
$result = mysql_query($sql);
$xml = '<markers>';
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	if($row[chk] == "0"){$waist_chk = 'รอบเอวปกติ';}elseif($row[chk] == "1"){$waist_chk = 'รอบเอวเกิน';}else{$waist_chk = 'ยังไม่ได้วัดรอบเอว';}
	$birth = retDatets($row[birth]);
	if($row[screen_date] == ""){$screen_date = '--/--/----';}else{$screen_date = retDatets($row[screen_date]);}
	if($row[waist] == ""){$waist = '--';}else{$waist = $row[waist];}
  $xml .= '<marker ';
  $xml .= 'hcode="'.$row[hcode].'" ';
  $xml .= 'hono="'.$row[hno].'" ';
  $xml .= 'moo="'.$moo.'" ';
  $xml .= 'vill="'.$vill.'" ';
  $xml .= 'pname="'.$row[pname].'" ';
  $xml .= 'birth="'.$birth.'" ';
  $xml .= 'age="'.$row[age].'" ';
  $xml .= 'waist_chk="'.$waist_chk.'" ';
  $xml .= 'screen_date="'.$screen_date.'" ';
  $xml .= 'weight="'.$row[weight].'" ';
  $xml .= 'height="'.$row[height].'" ';
  $xml .= 'waist="'.$waist.'" ';
  $xml .= 'chk="'.$row[chk].'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= '/>';
}
$xml .= '</markers>';
echo $xml;
?>