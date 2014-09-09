<?php 
header("Content-type: text/xml");
include("../includes/conndb.php"); 
include("../includes/config.inc.php"); 
$chronic = $_GET[chronic];
	$village = $_GET[village];
	if($village == "00000000"){
		$wvill = "";
	}else{
		$wvill = "AND house.villcode='$village'";	
	}
	if($village == "00000000"){
	$mu = "ทุกหมู่บ้าน";
}else{
	$mu = getvillagename($village);	
}
	if($chronic == '00'){
		$ect = "having chronicc like '%01%' or chronicc like '%10%'";
	}elseif($chronic == '01'){
		$ect = "having chronicc like '%01%' and chronicc like '%10%'";
	}elseif($chronic == '02'){
		$ect = "having chronicc not like '%01%' and chronicc like '%10%'";
	}elseif($chronic == '03'){
		$ect = "having chronicc like '%01%' and chronicc not like '%10%'";
	}elseif($chronic == '04'){
		$ect = "having chronicc like '%10%'";
	}elseif($chronic == '05'){
		$ect = "having chronicc like '%01%'";
	}else{}
$sql = "select villname,concat(ifnull(titlename,'..') ,fname,' ',lname) as pname, FLOOR((TO_DAYS(NOW())-TO_DAYS(person.birth))/365.25) as age, house.hno,house.villcode,house.xgis,house.ygis,person.idcard,person.pcucodeperson,person.pid,pc.datefirstdiag,pc.datedxfirst,pc.datedischart,
CASE
when pc.typedischart='01' then 'หาย'
when pc.typedischart='02' then 'ตาย'
when pc.typedischart='03' then 'ยังรักษาอยู่ฯ'
when pc.typedischart='04' then 'ไม่ทราบ(ไม่มีข้อมูล)'
when pc.typedischart='05' then 'รอการจำหน่าย/เฝ้าระวัง'
when pc.typedischart='06' then 'ยังรักษาอยู่ฯ'
when pc.typedischart='07' then 'ครบการรักษาฯ'
when pc.typedischart='08' then 'โรคอยู่ในภาวะสงบฯ'
when pc.typedischart='09' then 'ปฏิเสธการรักษาฯ'
when pc.typedischart='10' then 'ออกจากพื้นที่'
else null end AS typedischart,pc.cup
,group_concat(dc.groupcode) as chronicc
,group_concat(dc.groupname) as chronicx
FROM personchronic pc
left join person ON pc.pid = person.pid and pc.pcucodeperson = person.pcucodeperson
left join house ON person.hcode = house.hcode and person.pcucodeperson = house.pcucode
left join village ON house.villcode = village.villcode
left join ctitle ON person.prename = ctitle.titlecode
left Join cdisease d ON pc.chroniccode = d.diseasecode
left Join cdiseasechronic dc ON d.codechronic = dc.groupcode
where SUBSTRING(house.villcode,7,2) <> '00' AND pc.typedischart NOT IN  ('01', '02','07','10') and person.pid NOT IN (SELECT persondeath.pid FROM persondeath WHERE persondeath.pcucodeperson= person.pcucodeperson and (persondeath.deaddate IS NULL OR persondeath.deaddate<=now())) $wvill
group by pc.pcucodeperson, pc.pid
$ect
ORDER BY village.villcode,person.fname";
$result = mysql_query($sql);
$xml = '<markers>';
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$dsc = $row[chronicx];
  $xml .= '<marker ';
  $xml .= 'hono="'.$row[hno].'" ';
  $xml .= 'moo="'.$moo.'" ';
  $xml .= 'vill="'.$vill.'" ';
  $xml .= 'pname="'.$row[pname].'" ';
  $xml .= 'age="'.$row[age].'" ';
  $xml .= 'dsc="'.$dsc.'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= '/>';
}
$xml .= '</markers>';
echo $xml;
?>