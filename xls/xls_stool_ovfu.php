<?php
session_start();
$dx = date("md");
$yx = date("Y");
$yy = date("Y")-1;
if($dx > "1001"){$daymidyear = $yx."-10-01";}else{$daymidyear = $yy."-10-01";}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $titleweb; ?></title>
<link href="css/style.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="js/jquery-1.4.2.min.js"></script>
</head>

<body>
<?php 
if($_SESSION[username]){
include("includes/conndb.php"); 
include("includes/config.inc.php");
$sql = "SELECT
     concat('สถานบริการ(สถานีอนามัย/PCU): ',chospital.`hosname`,' หมู่ที่:',ifnull(chospital.`mu`,'...'),' ต.',
	ifnull(csubdistrict.`subdistname`,' ...'),' อ.',ifnull(cdistrict.`distname`,' ...'),' จ.',
	ifnull(cprovince.`provname`,'...')) AS chospital_hosname
FROM
     `chospital` chospital 
     INNER JOIN `office` office ON chospital.`hoscode` = office.`offid`
     left outer join `csubdistrict` csubdistrict ON chospital.`provcode` = csubdistrict.`provcode`
                                                        AND chospital.`distcode` = csubdistrict.`distcode`
                                                        AND chospital.`subdistcode` = csubdistrict.`subdistcode`
     left outer JOIN `cdistrict` cdistrict ON chospital.`provcode` = cdistrict.`provcode`
                                                  AND chospital.`distcode` = cdistrict.`distcode`
     INNER JOIN `cprovince` cprovince ON chospital.`provcode` = cprovince.`provcode`";

$result = mysql_query($sql);
$row=mysql_fetch_array($result);
$hosp=$row[chospital_hosname];
$village = $_GET[village];
if($village == "00000000"){
	$wvill = "";
}else{
	$wvill = " AND h.villcode='$village' ";	
}
if($village == "00000000"){
	$mu = "ทุกหมู่บ้าน";
}else{
	$mu = getvillagename($village);	
}
$chk_stool = $_GET[chk_stool];
if($chk_stool == "1"){
	$chksto = "";
}elseif($chk_stool == "2"){
	$chksto = "where symptoms is not null";	
}elseif($chk_stool == "3"){
	$chksto = "where vitalcheck like 'พบ'";
}elseif($chk_stool == "4"){
	$chksto = "where (para like '%B66.0%' or para like '%B66.1%' or para like '%B66.2%' or para like '%B66.3%')";		
}else{
	$chksto = "where symptoms is null";	
}
$live_type = $_GET[live_type];
if($live_type == '2'){$live_type2 = "p.typelive in ('0','1','2') and";}elseif($live_type == '1'){$live_type2 = "p.typelive in ('0','1','3') and";}else{$live_type2 = "p.typelive in ('0','1','2','3') and";}
if($live_type == '2'){$live_type_name = "ตามทะเบียนบ้าน(0,1,2)";}elseif($live_type == '1'){$live_type_name = "ที่อาศัยอยู่จริง (0,1,3)";}else{$live_type_name = "ทั้งหมดในเขตรับผิดชอบ(0,1,2,3)";}	
$getage = $_GET[getage];
if($getage == "35"){
	$gage = "AND FLOOR((TO_DAYS('$daymidyear')-TO_DAYS(p.birth))/365.25) between 30 and 39";
}elseif($getage == "20"){
	$gage = "AND FLOOR((TO_DAYS('$daymidyear')-TO_DAYS(p.birth))/365.25) < 30";
}elseif($getage == "30"){
	$gage = "AND FLOOR((TO_DAYS('$daymidyear')-TO_DAYS(p.birth))/365.25) > 29";
}elseif($getage == "40"){
	$gage = "AND FLOOR((TO_DAYS('$daymidyear')-TO_DAYS(p.birth))/365.25) > 39";
}else{
	$gage = "";
}
if($getage == "35"){
	$gagename = "อายุ 30 - 39 ปี";
}elseif($getage == "20"){
	$gagename = "อายุต่ำกว่า 30 ปี";
}elseif($getage == "30"){
	$gagename = "อายุ 30 ปี ขึ้นไป";
}elseif($getage == "40"){
	$gagename = "อายุ 40 ปี ขึ้นไป";
}else{
	$gagename = "ทั้งหมด";
}
$str = retDate($_GET[str]);
$sto = retDate($_GET[sto]);	
$sql = "select *
from
(SELECT
p.pcucodeperson,
p.pid,
p.fname,
p.idcard,
CONCAT(ctitle.titlename,p.fname,' ',p.lname) AS pname,
h.hno,
h.villcode,
h.xgis,
h.ygis,
p.birth,
p.typelive,
FLOOR((TO_DAYS('$daymidyear')-TO_DAYS(p.birth))/365.25) AS age
FROM
house AS h
Inner Join person AS p ON h.pcucode = p.pcucodeperson AND h.hcode = p.hcode
Inner Join cstatus ON p.marystatus = cstatus.statuscode
Inner Join ctitle ON p.prename = ctitle.titlecode
WHERE $live_type2 ((p.dischargetype is null) or (p.dischargetype = '9')) AND
				SUBSTRING(h.villcode,7,2) <> '00' $gage $wvill ORDER BY h.villcode,h.hno*1
) as per
left join 
(SELECT
person.pcucodeperson as pcucodeperson1,
person.pid as pid1,
visit.visitno as visitno1,
visit.symptoms,
visit.vitalcheck,
visitdiag.diagcode,
visit.visitdate
FROM
village
INNER JOIN house ON village.pcucode = house.pcucode AND village.villcode = house.villcode
INNER JOIN person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
INNER JOIN visit ON person.pcucodeperson = visit.pcucodeperson AND person.pid = visit.pid
INNER JOIN visitdiag ON visit.pcucode = visitdiag.pcucode AND visit.visitno = visitdiag.visitno
where visit.visitdate between '$str' and '$sto' and ((person.dischargetype is null) or (person.dischargetype = '9')) AND
				SUBSTRING(house.villcode,7,2) <> '00' and visitdiag.diagcode = 'Z11.6' and (visit.flagservice <'04' OR visit.flagservice is null OR length(trim(visit.flagservice))=0 )) as fp
on per.pcucodeperson = fp.pcucodeperson1 and per.pid = fp.pid1
left join
(SELECT
person.pcucodeperson as pcucodeperson2,
person.pid as pid2,
visit.visitno as visitno2,
if(max(visitdiag.diagcode) = min(visitdiag.diagcode),max(visitdiag.diagcode),concat(max(visitdiag.diagcode),' , ',min(visitdiag.diagcode))) as para,
if(max(cdisease.diseasenamethai) = min(cdisease.diseasenamethai),max(cdisease.diseasenamethai),concat(max(cdisease.diseasenamethai),' , ',min(cdisease.diseasenamethai))) as diseasenamethai,
if(max(cdisease.diseasename) = min(cdisease.diseasename),max(cdisease.diseasename),concat(max(cdisease.diseasename),' , ',min(cdisease.diseasename))) as diseasename
FROM
village
INNER JOIN house ON village.pcucode = house.pcucode AND village.villcode = house.villcode
INNER JOIN person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
INNER JOIN visit ON person.pcucodeperson = visit.pcucodeperson AND person.pid = visit.pid
INNER JOIN visitdiag ON visit.pcucode = visitdiag.pcucode AND visit.visitno = visitdiag.visitno
inner join cdisease on visitdiag.diagcode = cdisease.diseasecode
where visit.visitdate between '$str' and '$sto' and ((person.dischargetype is null) or (person.dischargetype = '9')) AND
				SUBSTRING(house.villcode,7,2) <> '00' and visitdiag.diagcode != 'Z11.6' and (visit.flagservice <'04' OR visit.flagservice is null OR length(trim(visit.flagservice))=0 ) group by visit.visitno) as para
on para.pcucodeperson2 = fp.pcucodeperson1 and para.pid2 = fp.pid1 and para.visitno2 = fp.visitno1
$chksto
order by villcode, fname";

$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>ประชาชน ';
$txt .= "$gagename ที่ได้รับการตรวจพยาธิใบไม้ตับ</b><br><b>ข้อมูลระหว่างวันที่ $_GET[str] ถึง $_GET[sto]  $mu </b></p>ประชากร $live_type_name <br><b>$hosp</b><table width='99%' border='0' cellspacing='1' cellpadding='1' class='tbhl'>
  <tr>
    <th width='4%' scope='col'>ลำดับ</th>
	<th width='10%' scope='col'>เลขบัตรประชาชน</th>
    <th width='10%' scope='col'>ชื่อ - สกุล</th>
	<th width='5%' scope='col'>อายุ</th>
    <th width='6%' scope='col'>บ้านเลขที่</th>
    <th width='4%' scope='col'>หมู่ที่</th>
    <th width='15%' scope='col'>กิจกรรม</th>
	<th width='9%' scope='col'>ผลการตรวจ</th>
	<th width='20%' scope='col'>พยาธิที่พบ</th>
	<th width='9%' scope='col'>วันที่ตรวจ</th>
  </tr>";
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$title = getTitle($row[prename]);
	if($row[visitdate] == ""){$sick = "";}else{$sick = retDatets($row[visitdate]);}
++$i;
	if(($i%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$i</div></td>
	<td><div align='center'>$row[idcard]</div></td>
    <td>$row[pname]</td>
	<td><div align='center'>$row[age]</div></td>
    <td><div align='center'>$row[hno]</div></td>
    <td><div align='center'>$moo</div></td>
    <td>&nbsp;$row[symptoms]</td>
	<td><div align='center'>$row[vitalcheck]</div></td>
	<td>&nbsp;$row[para]&nbsp;&nbsp;$row[diseasenamethai]</td>
	<td>&nbsp;$sick</td>
  </tr>";
}
$txt .= "</table><br>";  
echo $txt;
?>
<?php
}
else{
		header("Location: login.php");
		}
		?>
        
</body>
</html>
