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
$villcode = $_GET[village];
if($villcode == "00000000"){
	$wvill = "";
}else{
	$wvill = " AND house.villcode='$villcode' ";	
}
$chk_ultra = $_GET[chk_ultra];
if($chk_ultra == "2"){
	$chksto = "and tmp.vitalcheck is not null";
}elseif($chk_ultra == "3"){
	$chksto = "and tmp.vitalcheck not like 'ปกติ' and tmp.vitalcheck is not null";
}elseif($chk_ultra == "4"){
	$chksto = "and tmp.vitalcheck is null";		
}else{
	$chksto = "";	
}
$str = retDate($_GET[str]);
$sto = retDate($_GET[sto]);
$ovyear = substr($sto,0,4);	
$sql = "SELECT
person.pcucodeperson,
person.pid,
person.idcard,
CONVERT(concat(ifnull(ctitle.titlename,ifnull(person.prename,'ไม่ระบุ') ),person.fname,' ' ,person.lname) using utf8) as pname,
ctitle.titlename,
person.fname,
person.lname,
person.birth,
getageyearnum(person.birth,'$str') AS age,
house.hno,
house.villcode,
house.xgis,
house.ygis,
house.usernamedoc,
visitepi.vaccinecode,
visitepi.dateepi
FROM
village
INNER JOIN house ON village.pcucode = house.pcucode AND village.villcode = house.villcode
INNER JOIN person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
INNER JOIN visitepi ON person.pcucodeperson = visitepi.pcucodeperson AND person.pid = visitepi.pid
INNER JOIN ctitle ON person.prename = ctitle.titlecode
WHERE visitepi.vaccinecode in ('dT1','dTs1') and visitepi.dateepi between '$str' and '$sto' $wvill
ORDER BY house.villcode asc ,house.hno*1 asc,getageyearnum(person.birth,'$str') desc
";

$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>รายชื่อผู้รับบริการฉีดวัคซีน dT1 และ dTs1 ';
$txt .= "<br>ข้อมูลระหว่างวันที่ $_GET[str] ถึง $_GET[sto]  $mu </b></p><b>$hosp</b><table width='99%' border='0' cellspacing='1' cellpadding='1' class='tbhl'>
  <tr>
    <th width='4%' scope='col'>ลำดับ</th>
	<th width='5%' scope='col'>HN</th>
	<th width='10%' scope='col'>เลขบัตรประชาชน</th>
    <th width='10%' scope='col'>ชื่อ - สกุล</th>
	<th width='5%' scope='col'>อายุ</th>
    <th width='6%' scope='col'>บ้านเลขที่</th>
    <th width='4%' scope='col'>หมู่ที่</th>
    <th width='8%' scope='col'>วันที่ฉีดวัคซีน</th>
	<th width='13%' scope='col'>ชนิดวัคซีน</th>
	<th width='12%' scope='col'>นสค.</th>
  </tr>";
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$nsk = getusername($row[usernamedoc]);
	if($row[dateepi] == ""){$sick = "";}else{$sick = retDatets($row[dateepi]);}
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
    <td><div align='center'>$sick</div></td>
	<td><div align='center'>$row[vaccinecode]</div></td>
	<td>$nsk</td>
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
