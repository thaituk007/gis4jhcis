<?php
session_start();
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
$str = retDate($_GET[str]);
$sto = retDate($_GET[sto]);
$villcode = $_GET[village];
if($villcode == "00000000"){
	$wvill = "";
}else{
	$wvill = " AND house.villcode='$villcode' ";	
}
if($villcode == "00000000"){
	$mu = "ทุกหมู่บ้าน";
}else{
	$mu = getvillagename($villcode);	
}
$getage = $_GET[getage];
if($getage == "7"){
	$gage = "getageyearnum(person.birth,'$str') > 6";
}elseif($getage == "13"){
	$gage = "getageyearnum(person.birth,'$str') > 12";
}elseif($getage == "12"){
	$gage = "getageyearnum(person.birth,'$str') between 7 and 12";
}else{
	$gage = "";
}
if($getage == "7"){
	$gagename = "อายุ 7 ปีขึ้นไป";
}elseif($getage == "13"){
	$gagename = "อายุ 13 ปีขึ้นไป";
}elseif($getage == "12"){
	$gagename = "อายุ 7 - 12 ปี";
}else{
	$gagename = "ทั้งหมด";
}
$live_type = $_GET[live_type];
if($live_type == '2'){$live_type2 = "and person.typelive in ('0','1','2')";}elseif($live_type == '1'){$live_type2 = "and person.typelive in ('0','1','3')";}else{$live_type2 = "and person.typelive in ('0','1','2','3')";}
if($live_type == '2'){$live_type_name = "ตามทะเบียนบ้าน(0,1,2)";}elseif($live_type == '1'){$live_type_name = "ที่อาศัยอยู่จริง (0,1,3)";}else{$live_type_name = "ทั้งหมดในเขตรับผิดชอบ(0,1,2,3)";}	
$sql = "SELECT
epi2.pcucodeperson,
epi2.villcode,
epi2.villname,
sum(case when chk = 0 then 1 else 0 end) as nodt,
sum(case when chk = 1 then 1 else 0 end) as dt1,
sum(case when chk = 2 then 1 else 0 end) as dt2,
sum(case when chk = 3 then 1 else 0 end) as dt1dt2,
sum(1) as per
from
(SELECT
epi.*,
case when epi.dt1 is not null and epi.dt2 is null then 1
     when epi.dt1 is null and epi.dt2 is not null then 2
     when epi.dt1 is not null and epi.dt2 is not null then 3 else 0 end as chk
from
(SELECT
person.pcucodeperson,
person.pid,
person.fname,
person.idcard,
CONCAT(ctitle.titlename,person.fname,' ',person.lname) AS pname,
house.hno,
house.hcode,
village.villname,
house.villcode,
house.xgis,
house.ygis,
person.birth,
person.typelive,
getAgeYearNum(person.birth,'$str') AS age
,(select DATE_FORMAT(v1.dateepi,'%Y-%m-%d')  from visitepi v1  where v1.dateepi between '$str' and '$sto' and visitepi.pid = v1.pid  and visitepi.pcucodeperson=v1.pcucodeperson  and v1.vaccinecode in ('DT1','DTS1')  and (v1.dateepi  IS NOT NULL OR  left(v1.dateepi,4) != '0000'  )   group by v1.pid    and v1.pcucodeperson) as dt1
,(select DATE_FORMAT(v1.dateepi,'%Y-%m-%d')  from visitepi v1  where v1.dateepi between '$str' and '$sto' and visitepi.pid = v1.pid  and visitepi.pcucodeperson=v1.pcucodeperson  and v1.vaccinecode in ('DT2','DTS2')  and (v1.dateepi  IS NOT NULL OR  left(v1.dateepi,4) != '0000'  )   group by v1.pid    and v1.pcucodeperson) as dt2
FROM
village
INNER JOIN house ON village.pcucode = house.pcucode AND village.villcode = house.villcode
INNER JOIN person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
left join ctitle on ctitle.titlecode = person.prename
left JOIN visitepi ON person.pcucodeperson = visitepi.pcucodeperson AND person.pid = visitepi.pid
left join cdrug on cdrug.drugcode = visitepi.vaccinecode
where  right(house.villcode,2) <> '00' and ((person.dischargetype is null) or (person.dischargetype = '9')) and $gage $wvill $live_type2
group by person.pcucodeperson,person.pid
order by person.pcucodeperson,house.villcode,person.fname) as epi) as epi2
group by epi2.villcode";

$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>จำนวนประชากร';
$txt .= "$gagename ที่ได้รับการฉีดวัคซีน dT หรือ dTs </b><br><b>ระหว่างวันที่ $_GET[str] ถึง $_GET[sto]  $mu </b></p>ประชากร $live_type_name <br><b>$hosp</b><table width='99%' border='0' cellspacing='1' cellpadding='1' class='tbhl'>
  <tr>
    <th width='5%' scope='col'>ลำดับ</th>
    <th width='12%' scope='col'>หมู่บ้าน</th>
	<th width='8%' scope='col'>หมู่ที่</th>
    <th width='10%' scope='col'>$gagename</th>
    <th width='10%' scope='col'>ได้รับการฉีด dT1 อย่างเียว</th>
	<th width='10%' scope='col'>ได้รับการฉีด dT2 อย่างเดียว</th>
	<th width='10%' scope='col'>ได้รับการฉีด dT1 และ dT2</th>
	<th width='10%' scope='col'>ร้อยละ</th>
    <th width='10%' scope='col'>ยังไม่ได้ฉีด</th>
  </tr>";
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$villname = getvillagename($row[villcode]);
	$sum_per = $sum_per+$row[per];
	$sum_dt1 = $sum_dt1+$row[dt1];
	$sum_dt2 = $sum_dt2+$row[dt2];
	$sum_dt1dt2 = $sum_dt1dt2+$row[dt1dt2];
	$sum_nodt = $sum_nodt+$row[nodt];
if($row[per] == "0"){
	$percen = "0";
}else{
	$percen = $row[dt1dt2]/$row[per]*100;	
}
	$percent = number_format($percen, 2, '.', '');
if($sum_per == "0"){
	$sum_percen = "0";
}else{
	$sum_percen = $sum_dt1dt2/$sum_per*100;	
}
	$sum_percent1 = number_format($sum_percen, 2, '.', '');
++$i;
	if(($i%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$i</div></td>
    <td>$row[villname]</td>
    <td><div align='center'>$moo</div></td>
    <td><div align='center'>$row[per]</div></td>
	<td><div align='center'>$row[dt1]</div></td>
	<td><div align='center'>$row[dt2]</div></td>
	<td><div align='center'>$row[dt1dt2]</div></td>
	<td><div align='center'>$percent</div></td>
	<td><div align='center'>$row[nodt]</div></td>
  </tr>
  ";
}
$txt .= "<tr>
  <td>&nbsp;&nbsp;</td>
  <td><div align='center'>&nbsp;รวม</td>
  <td>&nbsp;&nbsp;</td>
  <td><div align='center'>$sum_per</div></td>
  <td><div align='center'>$sum_dt1</div></td>
  <td><div align='center'>$sum_dt2</div></td>
  <td><div align='center'>$sum_dt1dt2</div></td>
  <td><div align='center'>$sum_percent1</div></td>
  <td><div align='center'>$sum_nodt</div></td>
  </tr></table>";  
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
