<?php 
header("Content-type: text/xml");
include("../includes/conndb.php"); 
include("../includes/config.inc.php"); 
$villcode = $_GET[village];
if($villcode == "00000000"){
	$wvill = "";
}else{
	$wvill = " AND h.villcode='$villcode' ";	
}
$str = retdaterangstr($_GET[str]);
$sto = retdaterangsto($_GET[str]);
$strx = retDatets($str);
$stox = retDatets($sto);
$sql = "select
pname,
hno,
villcode,
xgis,
ygis,
birth,
age,
fptype,
if(fpname is null,'ไม่ระบุ',fpname) as fpname,
datesurvey,
statusname
from
(SELECT
p.pcucodeperson,
p.pid,
CONCAT(ctitle.titlename,p.fname,' ',p.lname) AS pname,
h.hno,
h.villcode,
h.xgis,
h.ygis,
p.birth,
FLOOR((TO_DAYS(NOW())-TO_DAYS(p.birth))/365.25) AS age,
cstatus.statusname
FROM
house AS h
Inner Join person AS p ON h.pcucode = p.pcucodeperson AND h.hcode = p.hcode
Inner Join cstatus ON p.marystatus = cstatus.statuscode
Inner Join ctitle ON p.prename = ctitle.titlecode
WHERE p.sex = '2' and ((p.dischargetype is null) or (p.dischargetype = '9')) AND
				SUBSTRING(h.villcode,7,2) <> '00' AND
				FLOOR((TO_DAYS(NOW())-TO_DAYS(p.birth))/365.25) < 20 and p.marystatus in ('f','2') $wvill ORDER BY h.villcode,h.hno*1) as per
left join 
(SELECT
women.pcucodeperson as pcucodeperson1,
women.pid as pid1,
women.fptype,
if(women.fptype is null,null,if(women.fptype = '1','ยาเม็ด',if(women.fptype = '2','ยาฉีด',if(women.fptype = '3','ห่วงอนามัย',if(women.fptype = '4','ยาฝัง',if(women.fptype = '5','ถุงยางอนามัย',if(women.fptype = '6','หมันชาย',if(women.fptype = '7','หมันหญิง','ไม่ได้คุม')))))))) as fpname,
women.reasonnofp,
women.childalive,
women.motherpomotion,
women.datesurvey,
women.flag18fileexpo,
women.dateupdate
FROM
women
where women.datesurvey between '$str' and '$sto') as fp
on per.pcucodeperson = fp.pcucodeperson1 and per.pid = fp.pid1
order by villcode, hno*1";
$result = mysql_query($sql);
$xml = '<markers>';
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$sick = retDatets($row[datesurvey]);
  $xml .= '<marker ';
  $xml .= 'hcode="'.$row[hcode].'" ';
  $xml .= 'hono="'.$row[hno].'" ';
  $xml .= 'moo="'.$moo.'" ';
  $xml .= 'vill="'.$vill.'" ';
  $xml .= 'pname="'.$row[pname].'" ';
  $xml .= 'age="'.$row[age].'" ';
  $xml .= 'fp="'.$row[fptype].'" ';
  $xml .= 'fpname="'.$row[fpname].'" ';
  $xml .= 'status="'.$row[statusname].'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= '/>';
}
$xml .= '</markers>';
echo $xml;
?>

