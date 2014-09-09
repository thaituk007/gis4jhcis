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
$village = $_GET[village];
$getchronic = $_GET[getchronic];
$risk = $_GET[risk];
$live_type = $_GET[live_type];
$getage = $_GET[getage];
if($village == '00000000'){$ect2 = "";}else{$ect2 = " house.villcode = '$village' AND ";}
if($getchronic == '9'){$gchronic = "";}else{$gchronic = "and pid not in (SELECT p.pid FROM house AS h Inner Join person AS p ON h.pcucode = p.pcucodeperson AND h.hcode = p.hcode Inner Join personchronic AS pc ON p.pcucodeperson = pc.pcucodeperson AND p.pid = pc.pid Inner Join cdisease AS d ON pc.chroniccode = d.diseasecode Inner Join cdiseasechronic AS dc ON d.codechronic = dc.groupcode WHERE ((p.dischargetype is null) or (p.dischargetype = '9')) AND SUBSTRING(h.villcode,7,2) <> '00' AND pc.typedischart NOT IN  ('01', '02','07','10') and dc.groupcode in ('01','10') GROUP BY p.pid)";}
if($risk == '3'){$risk2 = "and resultht in ('เสี่ยง','สูง') and resultdm in ('เสี่ยง','สูง')";}elseif($risk == '1'){$risk2 = "and resultdm in ('เสี่ยง','สูง')";}elseif($risk == '2'){$risk2 = "and resultht in ('เสี่ยง','สูง')";}elseif($risk == '4'){$risk2 = "and resultht in ('เสี่ยง','สูง') or resultdm in ('เสี่ยง','สูง')";}else{$risk2 = "";}
if($live_type == '2'){$live_type2 = "typelive in ('0','1','2')";}elseif($live_type == '1'){$live_type2 = "typelive in ('0','1','3')";}else{$live_type2 = "typelive in ('0','1','2','3')";}
if($getage == '15'){$gage = "AND age > 14";}elseif($getage == '35'){$gage = "AND age > 34";}else{$gage = "AND age between '15 and '34'";}
	$sql = "select
*,
case when resultht = '' or resultht is null then '0'
	when resultht in ('เสี่ยง','สูง') or resultdm in ('เสี่ยง','สูง') then '2' else '1' end as chk
from
(SELECT
person.pcucodeperson,
person.pid,
person.fname,
CONCAT(ctitle.titlename,person.fname,' ',person.lname) AS pname,
house.hno,
house.villcode,
village.villname,
house.xgis,
house.ygis,
person.birth,
person.typelive,
FLOOR((TO_DAYS(NOW())-TO_DAYS(person.birth))/365.25) AS age
FROM
village
INNER JOIN house ON village.pcucode = house.pcucode AND village.villcode = house.villcode
Inner Join person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
Inner Join cstatus ON person.marystatus = cstatus.statuscode
Inner Join ctitle ON person.prename = ctitle.titlecode
WHERE $ect2 ((person.dischargetype is null) or (person.dischargetype = '9')) AND
				SUBSTRING(house.villcode,7,2) <> '00' ORDER BY house.villcode,house.hno*1
) as per
left join 
(SELECT 
person.pcucodeperson as pcucodeperson1,
person.pid as pid1,
ncd_person_ncd_screen.screen_date, ncd_person_ncd_screen.bmi, ncd_person_ncd_screen.weight, ncd_person_ncd_screen.height, ncd_person_ncd_screen.waist, ncd_person_ncd_screen.hbp_s1,
ncd_person_ncd_screen.hbp_d1, ncd_person_ncd_screen.result_new_dm, ncd_person_ncd_screen.result_new_hbp, ncd_person_ncd_screen.result_new_waist, ncd_person_ncd_screen.result_new_obesity, if(ncd_person_ncd_screen.hbp_s2 is null ,if(ncd_person_ncd_screen.hbp_s1 between 120 and 139 or ncd_person_ncd_screen.hbp_d1 between 80 and  89, 'เสี่ยง',if(ncd_person_ncd_screen.hbp_s1 > 139 or ncd_person_ncd_screen.hbp_d1 > 89,'สูง','ปกติ')),if(ncd_person_ncd_screen.hbp_s2  between 120 and 139 or  ncd_person_ncd_screen.hbp_d2 between 80 and  89, 'เสี่ยง',if(ncd_person_ncd_screen.hbp_s2 > 139 or ncd_person_ncd_screen.hbp_d2 > 89,'สูง','ปกติ'))) as resultht,
if(ncd_person_ncd_screen.bstest = '3' or ncd_person_ncd_screen.bstest = '1',if(ncd_person_ncd_screen.bsl between 100 and 125,'เสี่ยง',if(ncd_person_ncd_screen.bsl > 125,'สูง','ปกติ')),if(ncd_person_ncd_screen.bsl between 140 and 199,'เสี่ยง',if(ncd_person_ncd_screen.bsl > 199,'สูง','ปกติ'))) as resultdm,
ncd_person_ncd_screen.bsl,
ncd_person_ncd_screen.hbp_s2, ncd_person_ncd_screen.hbp_d2, ncd_person_ncd_screen.bstest,
if(ncd_person_ncd_screen.waist is null,null,if( (person.sex='1' and ncd_person_ncd_screen.waist >89 ) or (person.sex='2' and ncd_person_ncd_screen.waist >79),'รอบเอวเกิน','รอบเอวปกติ')) as resultwaist
from
house
Inner Join person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
Inner Join village ON village.pcucode = house.pcucode AND village.villcode = house.villcode
inner join ncd_person_ncd_screen on person.pid = ncd_person_ncd_screen.pid AND person.pcucodeperson = ncd_person_ncd_screen.pcucode
Inner Join ctitle ON person.prename = ctitle.titlecode
where $ect2 ((person.dischargetype is null) or (person.dischargetype = '9'))  and SUBSTRING(house.villcode,7,2) <> '00' and ncd_person_ncd_screen.screen_date BETWEEN '$str' AND '$sto' 
							ORDER BY
							house.villcode,house.hno*1
) as fp
on per.pcucodeperson = fp.pcucodeperson1 and per.pid = fp.pid1
where $live_type2 $gage $risk2 $gchronic
ORDER BY villcode, fname";
$result = mysql_query($sql);
$xml = '<markers>';
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$sick = retDatets($row[screen_date]);
	$bmi = number_format($row[bmi], 2, '.', '');
  $xml .= '<marker ';
  $xml .= 'hono="'.$row[hno].'" ';
  $xml .= 'moo="'.$moo.'" ';
  $xml .= 'vill="'.$vill.'" ';
  $xml .= 'pname="'.$row[pname].'" ';
  $xml .= 'dm="'.$row[resultdm].'" ';
  $xml .= 'ht="'.$row[resultht].'" ';
  $xml .= 'waist="'.$row[resultwaist].'" ';
  $xml .= 'sick="'.$sick.'" ';
  $xml .= 'chk="'.$row[chk].'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= '/>';
}
$xml .= '</markers>';
echo $xml;
?>