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
	$chksto = "and person.candobedhomesocial = 1";
}elseif($chk_old == "2"){
	$chksto = "and person.candobedhomesocial = 2";	
}elseif($chk_old == "3"){
	$chksto = "and person.candobedhomesocial = 3";
}elseif($chk_old == "8"){
	$chksto = "and person.candobedhomesocial in (1,2,3)";
}elseif($chk_old == "9"){
	$chksto = "";	
}else{
	$chksto = "and person.candobedhomesocial is null";
}
$str = date("Y-d-m");
$strd = substr($str,8,2);
$strm = substr($str,5,2);
$stryT = substr($str,0,4);
$stryF = substr($str,0,4)-1;
$dx = $strm."".$strd;
if($dx > "1001"){$daymidyear = $stryT."-10-01";}else{$daymidyear = $stryF."-10-01";}
$sql = "SELECT
person.pcucodeperson,
person.pid,
person.fname, 
concat(ctitle.titlename, person.fname , '  ' , person.lname) AS pname,
person.birth,
ROUND(DATEDIFF('$daymidyear',person.birth)/365.25) AS age,
village.villcode,
village.villname,
house.hno,
house.hcode,
house.xgis,
house.ygis,
person.candobedhomesocial as chk,
person.dateupdate
FROM
village
INNER JOIN house ON village.pcucode = house.pcucode AND village.villcode = house.villcode
INNER JOIN person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
left join ctitle on ctitle.titlecode = person.prename
where getAgeYearNum(person.birth,'$daymidyear') > 59 and ((person.dischargetype is null) or (person.dischargetype = '9'))  and SUBSTRING(house.villcode,7,2) <> '00' $chksto $wvill
order by person.pcucodeperson,village.villcode,person.fname";
$result = mysql_query($sql);
$xml = '<markers>';
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	if($row[chk] == ""){$old_chk = 'ยังไม่ได้ประเมิน';}elseif($row[chk] == 3){$old_chk = 'ติดเตียง';}elseif($row[chk] == 2){$old_chk = 'ติดบ้าน';}elseif($row[chk] == 1){$old_chk = 'ติดสังคม';}else{$old_chk = 'ไม่ทราบ';}
	$birth = retDatets($row[birth]);
	if($row[dateupdate] == ""){$visitdate = '';}else{$visitdate = retDatets($row[dateupdate]);}
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
  $xml .= 'chk="'.$row[chk].'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= '/>';
}
$xml .= '</markers>';
echo $xml;
?>