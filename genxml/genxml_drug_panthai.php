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
,CONVERT(concat(ifnull(titlename,ifnull(prename,'ไม่ระบุ') ),fname,' ',lname) USING utf8) as pname 
,v.pid
       ,CONVERT(case when person.subdistcodemoi is null  then 'นอกเขต' 
              when person.hnomoi is null then concat(' หมู่ที่ ',  person.`mumoi` ,' ต.',  csd.`subdistname` )
              when person.mumoi is null then concat(person.`hnomoi`  ,' ต.',  csd.`subdistname` )
              else concat(person.`hnomoi` ,' หมู่ที่ ',  person.`mumoi` ,' ต.',  csd.`subdistname` ) end   USING utf8)  AS address
       ,v.rightcode,rightname,v.visitno,v.pcucode,v.visitdate,chospital.hosname,
	   GROUP_CONCAT(cdrug.drugname) as drugname,
house.hno,
house.villcode,
house.xgis,
house.ygis
from visit v left join person on v.pid = person.pid and v.pcucodeperson = person.pcucodeperson
	left join ctitle on person.prename = ctitle.titlecode
        left join cright on v.rightcode = cright.rightcode
        left join house on person.hcode = house.hcode and person.pcucodeperson = house.pcucode
        left join village on house.villcode = village.villcode and house.pcucode = village.pcucode
        left join csubdistrict csd on csd.provcode = left(village.villcode,2) and csd.distcode = substring(village.villcode,3,2) and csd.subdistcode = substring(village.villcode,5,2)
	left join chospital on v.pcucode = chospital.hoscode
        left join visitdrug on v.visitno = visitdrug.visitno and v.pcucode = visitdrug.pcucode
        left join cdrug on visitdrug.drugcode = cdrug.drugcode
WHERE    cdrug.drugtype='10'    
 	and visitdate between '$str' and '$sto' and (v.flagservice <'04' OR v.flagservice is null OR length(trim(v.flagservice))=0 )
group by v.visitno,v.pcucode
order by visitdate,village.villcode";

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
  $xml .= 'sick="'.$sick.'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= '/>';
}
$xml .= '</markers>';
echo $xml;
?>

