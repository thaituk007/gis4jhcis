<?php 
header("Content-type: text/xml");
include("../includes/conndb.php"); 
include("../includes/config.inc.php"); 
function redatepick($d){
	$y = substr($d,6,4)-543;
	$m = substr($d,3,2);
	$dn = substr($d,0,2);
	$rt = $y."/".$m."/".$dn;
	return $rt;
}
$str = retdaterangstr($_GET[str]);
$sto = retdaterangsto($_GET[str]);
$strx = retDatets($str);
$stox = retDatets($sto);
	$sql = "SELECT person.idcard
,CONVERT(concat(ifnull(c.titlename,ifnull(person.prename,'ไม่ระบุ') ),person.fname,' ' ,person.lname) using utf8) as pname 
,DATE_FORMAT(visitdate,'%Y-%m-%d') as visitdate
,office.offid
,v.pcucode
,if(v.pcucode = office.offid,'หน่วยบริการ' ,'ที่อื่น ' ) as pcu
,drugname
,visitfp.dateupdate
,CONVERT(concat(c.titlename,u.fname,' ' ,u.lname) using utf8) as uname
,curdate() as cdate,
house.hno,
house.villcode,
house.xgis,
house.ygis,
visitfp.unit,
cdrugunitsell.unitsellname
FROM person left join ctitle on person.prename = ctitle.titlecode
	left join visit  v on person.pid = v.pid  and person.pcucodeperson = v.pcucodeperson
	left join visitfp on v.visitno = visitfp.visitno and v.pcucode = visitfp.pcucode
	left join cdrug on visitfp.fpcode = cdrug.drugcode
	left join cdrugunitsell on cdrug.unitsell = cdrugunitsell.unitsellcode
	left join user u on v.username = u.username
	left join ctitle c on c.titlecode = u.prename
	left join office on v.pcucode = office.offid
	Inner Join house ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
WHERE    cdrug.drugcode like'CONDOM%'   
 	and visitdate between '$str' and '$sto' and (v.flagservice <'04' OR v.flagservice is null OR length(trim(v.flagservice))=0 )
GROUP BY v.visitno,v.pcucodeperson
ORDER BY house.villcode,v.visitdate";

$result = mysql_query($sql);
$xml = '<markers>';
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$sick = retDatets($row[visitdate]);
  $xml .= '<marker ';
  $xml .= 'hono="'.$row[hno].'" ';
  $xml .= 'moo="'.$moo.'" ';
  $xml .= 'vill="'.$vill.'" ';
  $xml .= 'pname="'.$row[pname].'" ';
  $xml .= 'drugname="'.$row[drugname].'" ';
  $xml .= 'unit="'.$row[unit].'" ';
  $xml .= 'unitsellname="'.$row[unitsellname].'" ';
  $xml .= 'sick="'.$sick.'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= '/>';
}
$xml .= '</markers>';
echo $xml;
?>

