<?php 
header("Content-type: text/xml");
include("../includes/conndb.php"); 
include("../includes/config.inc.php"); 
$age = $_GET[age];
$ect0 = "''";
if(strpos($age,",",0) > 0){
	$listage = explode(',',$age);
	foreach ($listage as $a){
		if(strpos($a,"-",0) > 0){
			list($str,$end) = split("-",$a,2);
			for($i = $str; $i <= $end; $i++){
				$ect0 .= ",'".$i."'";
			}
		}else{
			$ect0 .= ",'".$a."'";
		}
	}
}else{
		if(strpos($age,"-",0) > 0){
			list($str,$end) = split("-",$age,2);
			for($i = $str; $i <= $end; $i++){
				$ect0 .= ",'".$i."'";
			}
		}else{
			$ect0 .= ",'".$age."'";
		}
}
$vaccine = $_GET[vaccine];
if($vaccine == ''){$ect1 = "";}else{$ect1 = " visitepi.vaccinecode = '$vaccine' ";}
$village = $_GET[village];
if($village == '00000000'){$ect2 = "";}else{$ect2 = " villcode = '$village' AND ";}
	$sql = "select
pcu,
pid,
pname,
birth,
age,
hno,
villcode,
villname,
vaccinecode,
drugname,
dateepi,
xgis,
ygis
FROM
(SELECT
person.pcucodeperson as pcu,
person.pid as pid,
concat(ctitle.titlename,person.fname,' ',person.lname) AS pname,
person.fname,
person.birth as birth,
round(DATEDIFF(now(),person.birth) /30) AS age,
house.hno as hno,
house.villcode as villcode,
CONVERT(village.villname using utf8) AS villname,
house.xgis,
house.ygis
FROM
house
Inner Join person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
Inner Join village ON village.pcucode = house.pcucode AND village.villcode = house.villcode
Inner Join ctitle ON person.prename = ctitle.titlecode
where right(house.villcode,2) <> '00' and ((person.dischargetype is null) or (person.dischargetype = '9')) and  round(DATEDIFF(now(),person.birth) /30)  IN($ect0)
order by age) as per_epi
left Join (SELECT
person.pcucodeperson as pcu1,
person.pid as pid1,
visitepi.vaccinecode,
cdrug.drugname,
visitepi.dateepi
FROM
house
Inner Join person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
Inner Join village ON village.pcucode = house.pcucode AND village.villcode = house.villcode
Inner Join visitepi ON person.pcucodeperson = visitepi.pcucodeperson AND person.pid = visitepi.pid
Inner Join ctitle ON person.prename = ctitle.titlecode
Inner Join cdrug ON visitepi.vaccinecode = cdrug.drugcode
where right(house.villcode,2) <> '00' and ((person.dischargetype is null) or (person.dischargetype = '9')) and  round(DATEDIFF(now(),person.birth) /30)  IN($ect0) and $ect1
) as visit_epi ON per_epi.pcu = visit_epi.pcu1 AND per_epi.pid = visit_epi.pid1
where $ect2 pid1 is null
order by right(villcode,2),fname";
$result = mysql_query($sql);
$xml = '<markers>';
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$bod = retDatets($row[birth]);
	$title = getTitle($row[prename]);
	$dateepix = retDatets($row[dateepi]);
  $xml .= '<marker ';
  $xml .= 'hono="'.$row[hno].'" ';
  $xml .= 'moo="'.$moo.'" ';
  $xml .= 'vill="'.$vill.'" ';
  $xml .= 'pname="'.$title.$row[pname].'" ';
  $xml .= 'bod="'.$bod.'" ';
  $xml .= 'ag="'.$row[age].'" ';
  $xml .= 'drugname="'.$row[drugname].'" ';
  $xml .= 'vaccinecode="'.$row[vaccinecode].'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= '/>';
}
$xml .= '</markers>';
echo $xml;
?>

