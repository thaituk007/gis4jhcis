<?php 
header("Content-type: text/xml");
include("../includes/conndb.php"); 
include("../includes/config.inc.php"); 
$villcode = $_GET[village];
if($villcode == "00000000"){
	$wvill = "";
}elseif($villcode == "xx"){
	$wvill = " AND right(house.villcode,2)='00'";
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
$sql = "SELECT DISTINCT  trim(visitepi.pcucodeperson) AS pcucode, visitepi.pid, concat(ctitle.titlename,person.fname,'  ',person.lname) as pname, getAgeMonth(person.birth,CURDATE()) AS agemonth, visitepi.visitno AS seq, cdrug.files18epi AS vcctype,  cdrug.drugname, IF(visitepi.dateepi IS NULL OR TRIM(visitepi.dateepi)='' OR visitepi.dateepi LIKE '0000-00-00%','',DATE_FORMAT(visitepi.dateepi,'%Y%m%d')) AS date_serv,  visitepi.dateepi, IF(visitepi.hosservice IS NULL OR visitepi.hosservice='',trim(visitepi.pcucode),trim(visitepi.hosservice)) AS vccplace,  IF(visitepi.dateupdate IS NULL OR TRIM(visitepi.dateupdate)='' OR     visitepi.dateupdate LIKE '0000-00-00%',DATE_FORMAT(visitepi.dateepi,'%Y%m%d%H%i%s'),    DATE_FORMAT(visitepi.dateupdate,'%Y%m%d%H%i%s') ) AS d_update  ,idcard as cid , house.hno, house.villcode, house.xgis, house.ygis
FROM 
visitepi  
join person on visitepi.pcucodeperson = person.pcucodeperson and visitepi.pid = person.pid
join house on person.pcucodeperson = house.pcucode and person.hcode = house.hcode
left join ctitle on person.prename = ctitle.titlecode  
LEFT JOIN cdrug ON (visitepi.vaccinecode=cdrug.drugcode AND cdrug.drugtype='05') 
WHERE visitepi.dateepi IS NOT NULL AND TRIM(visitepi.dateepi)<>''  AND TRIM(visitepi.pcucodeperson)<>''
AND (visitepi.dateepi >= '$str') AND (visitepi.dateepi BETWEEN '$str' AND '$sto') $wvill
ORDER BY visitepi.pcucodeperson ASC, visitepi.dateepi DESC, visitepi.visitno DESC";
$result = mysql_query($sql);
$xml = '<markers>';
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$dateepi = retDatets($row[dateepi]);
  $xml .= '<marker ';
  $xml .= 'pcucode="'.$row[pcucode].'" ';
  $xml .= 'pid="'.$row[pid].'" ';
  $xml .= 'pname="'.$row[pname].'" ';
  $xml .= 'agemonth="'.$row[agemonth].'" ';
  $xml .= 'seq="'.$row[seq].'" ';
  $xml .= 'vcctype="'.$row[vcctype].'" ';
  $xml .= 'drugname="'.$row[drugname].'" ';
  $xml .= 'dateepi="'.$dateepi.'" ';
  $xml .= 'vccplace="'.$row[vccplace].'" ';
  $xml .= 'd_update="'.$row[d_update].'" ';
  $xml .= 'hono="'.$row[hno].'" ';
  $xml .= 'moo="'.$moo.'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= '/>';
}
$xml .= '</markers>';
echo $xml;
?>

