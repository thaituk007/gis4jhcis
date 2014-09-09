<?php
session_start();
set_time_limit(0);
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
village.villcode,
(select count(distinct p.pid) from house h inner join person p on p.pcucodeperson = h.pcucode and p.hcode = h.hcode where h.villcode = house.villcode and ((p.dischargetype is null) or (p.dischargetype = '9'))) as per,
(select count(distinct p.pid) from house h inner join person p on p.pcucodeperson = h.pcucode and p.hcode = h.hcode where h.villcode = house.villcode and ((p.dischargetype is null) or (p.dischargetype = '9')) and (p.typelive = '1' or p.typelive = '0' or p.typelive = '3')) as peru,
(select count(distinct p.pid) from house h inner join person p on p.pcucodeperson = h.pcucode and p.hcode = h.hcode INNER JOIN visitepi ve ON p.pcucodeperson = ve.pcucodeperson AND p.pid = ve.pid where h.villcode = house.villcode and ((p.dischargetype is null) or (p.dischargetype = '9')) and (p.typelive = '1' or p.typelive = '0' or p.typelive = '3') and ve.vaccinecode in ('dT1','dTs1') and ve.dateepi between '$str' and '$sto') as perepi
FROM
village
INNER JOIN house ON village.pcucode = house.pcucode AND village.villcode = house.villcode
where village.villcode is not null $wvill
group by village.villcode";

$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>จำนวนผู้รับบริการฉีดวัคซีน dT1 และ dTs1';
$txt .= "<br>ระหว่างวันที่ $_GET[str] ถึง $_GET[sto]  $mu </b></p><br><b>$hosp</b><table width='99%' border='0' cellspacing='1' cellpadding='1' class='tbhl'>
  <tr>
    <th width='5%' scope='col'>ลำดับ</th>
    <th width='12%' scope='col'>หมู่บ้าน</th>
	<th width='8%' scope='col'>หมู่ที่</th>
    <th width='10%' scope='col'>ประชาชนทั้งหมด</th>
	<th width='10%' scope='col'>อยู่จริง</th>
    <th width='10%' scope='col'>ได้รับวัคซีน dT1 หรือ dTs1</th>
	<th width='9%' scope='col'>ร้อยละ</th>
  </tr>";
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$villn = getvillagename($row[villcode]);
if($row[peru] == "0"){
	$percen = "0";
}else{
	$percen = ($row[perepi])/($row[peru])*100;	
}
	$percent1 = number_format($percen, 2, '.', '');
	$sumper = $sumper+$row[per];
	$sumperu = $sumperu+$row[peru];
	$sumperepi = $sumperepi+$row[perepi];
if($sumperu == "0"){
	$percen2 = "0";
}else{
	$percen2 = ($sumperepi)/($sumperu)*100;	
}
	$sum_percent2 = number_format($percen2, 2, '.', '');
++$i;
	if(($i%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$i</div></td>
    <td>$villn</td>
    <td><div align='center'>$moo</div></td>
    <td><div align='center'>$row[per]</div></td>
	<td><div align='center'>$row[peru]</div></td>
	<td><div align='center'>$row[perepi]</div></td>
	<td><div align='center'>$percent1</div></td>
  </tr>
  ";
}
$txt .= "<tr>
  <td>&nbsp;&nbsp;</td>
  <td><div align='center'>&nbsp;รวม</td>
  <td>&nbsp;&nbsp;</td>
  <td><div align='center'>$sumper</div></td>
  <td><div align='center'>$sumperu</div></td>
  <td><div align='center'>$sumperepi</div></td>
  <td><div align='center'>$sum_percent2</div></td>
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
