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
$chk_stool = $_GET[chk_stool];
if($chk_stool == "2"){
	$chksto = "having chk = '1'";
}elseif($chk_stool == "3"){
	$chksto = "having chk = '2'";
}elseif($chk_stool == "4"){
	$chksto = "having chk = '3'";
}elseif($chk_stool == "5"){
	$chksto = "having chk <> '0'";
}elseif($chk_stool == "6"){
	$chksto = "having chk = '0'";		
}else{
	$chksto = "";		
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
order by person.pcucodeperson,house.villcode,person.fname) as epi
$chksto";

$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>ประชาชน ';
$txt .= "$gagename ที่ได้รับการฉีดวัคซีน dT หรือ dTs </b><br><b>ระหว่างวันที่ $_GET[str] ถึง $_GET[sto]  $mu </b></p>ประชากร $live_type_name <br><b>$hosp</b><table width='99%' border='0' cellspacing='1' cellpadding='1' class='tbhl'>
  <tr>
    <th width='4%' scope='col'>ลำดับ</th>
	<th width='6%' scope='col'>HN</th>
	<th width='10%' scope='col'>เลขบัตรประชาชน</th>
    <th width='10%' scope='col'>ชื่อ - สกุล</th>
	<th width='5%' scope='col'>อายุ</th>
    <th width='6%' scope='col'>บ้านเลขที่</th>
    <th width='4%' scope='col'>หมู่ที่</th>
	<th width='9%' scope='col'>วันที่ฉีดdT1 หรือ dTs1</th>
    <th width='15%' scope='col'>วันที่ฉีด dT2 หรือ dTs2</th>
  </tr>";
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	if($row[dt1] == ""){$sick1 = "";}else{$sick1 = retDatets($row[dt1]);}
	if($row[dt2] == ""){$sick2 = "";}else{$sick2 = retDatets($row[dt2]);}
++$i;
	if(($i%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$i</div></td>
	<td><div align='center'>$row[pid]</div></td>
	<td><div align='center'>$row[idcard]</div></td>
    <td>$row[pname]</td>
	<td><div align='center'>$row[age]</div></td>
    <td><div align='center'>$row[hno]</div></td>
    <td><div align='center'>$moo</div></td>
	<td><div align='center'>$sick1</div></td>
    <td><div align='center'>$sick2</div></td>
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
