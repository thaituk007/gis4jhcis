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
if($villcode == "00000000"){
	$mu = "ทุกหมู่บ้าน";
}else{
	$mu = getvillagename($villcode);	
}
$chk_old = $_GET[chk_old];
if($chk_old == "8"){
	$chksto = "where vepi.pid is not null";
}elseif($chk_old == "1"){
	$chksto = "where vepi.pid is null";
}else{
	$chksto = "";	
}
$str = retDate($_GET[str]);
$sto = retDate($_GET[sto]);
$live_type = $_GET[live_type];
if($live_type == '2'){$live_type_name = "ตามทะเบียนบ้าน(0,1,2)";}elseif($live_type == '1'){$live_type_name = "ที่อาศัยอยู่จริง (0,1,3)";}else{$live_type_name = "ทั้งหมดในเขตรับผิดชอบ(0,1,2,3)";}	
if($live_type == '2'){$live_type2 = "and person.typelive in ('0','1','2')";}elseif($live_type == '1'){$live_type2 = "and person.typelive in ('0','1','3')";}else{$live_type2 = "and person.typelive in ('0','1','2','3')";}		
$sql = "SELECT
pepi.pcucodeperson,
pepi.villcode,
count(DISTINCT pepi.pid) as pchild,
COUNT(DISTINCT IF(vepi.files18epi = '061', pepi.pid, NULL)) AS pmmr
FROM
(SELECT
person.pcucodeperson,
person.pid,
person.idcard,
concat(ifnull(titlename,'..') ,fname,' ',lname) as pname,
person.birth,
getagemonth(person.birth,now()) as age,
house.hno,
house.villcode,
house.xgis,
house.ygis
FROM
house
INNER JOIN person on person.pcucodeperson = house.pcucode and person.hcode = house.hcode
LEFT JOIN ctitle on ctitle.titlecode = person.prename
where person.birth BETWEEN '$str' and '$sto' and ((person.dischargetype is null) or (person.dischargetype = '9')) $wvill $live_type2) as pepi
left JOIN
(SELECT
visitepi.pcucodeperson,
visitepi.pid,
visitepi.dateepi,
visitepi.vaccinecode,
visitepi.hosservice,
cdrug.files18epi
FROM
visitepi
INNER JOIN cdrug on cdrug.drugcode = visitepi.vaccinecode
where cdrug.files18epi = '061') as vepi
on pepi.pcucodeperson = vepi.pcucodeperson and pepi.pid = vepi.pid
GROUP BY pepi.pcucodeperson,pepi.villcode";

$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>รายงานเด็กอายุ 1 ปี ได้รับวัคซีน MMR<br>';
$txt .= "(เด็กเกิดระหว่างวันที่ $_GET[str] ถึง $_GET[sto]) <br>$mu</b></p><b>$live_type_name</b><br><b>$hosp</b><table width='95%' border='0' cellspacing='1' cellpadding='1' class='tbhl'>
  <tr>
    <th width='4%' scope='col'>ลำดับ</th>
    <th width='10%' scope='col'>หมู่บ้าน</th>
	<th width='4%' scope='col'>หมู่ที่</th>
	<th width='4%' scope='col'>เด็กอายุ 1 ปี</th>
	<th width='4%' scope='col'>ได้รับ MMR</th>
    <th width='4%' scope='col'>ร้อยละ</th>
  </tr>";
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$villname = getvillagename($row[villcode]);
if($row[pchild] == "0"){
	$percen = "0";
}else{
	$percen = ($row[pmmr])/($row[pchild])*100;	
}
	$percent1 = number_format($percen, 2, '.', '');
	$sumpchild = $sumpchild+$row[pchild];
	$sumpmmr = $sumpmmr+$row[pmmr];
if($sumpchild == "0"){
	$percenx = "0";
}else{
	$percenx = ($sumpmmr)/($sumpchild)*100;	
}
	$percent1x = number_format($percenx, 2, '.', '');
++$i;
	if(($i%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$i</div></td>
    <td>$villname</td>
    <td><div align='center'>&nbsp;$moo</div></td>
	<td><div align='center'>$row[pchild]</div></td>
	<td><div align='center'>$row[pmmr]</div></td>
    <td><div align='center'>$percent1</div></td>
  </tr>
  ";
}
$txt .= "<tr>
  	<td>&nbsp;&nbsp;</td>
  	<td><div align='center'>&nbsp;รวม</td>
  	<td>&nbsp;&nbsp;</td>
	<td><div align='center'>$sumpchild</div></td>
	<td><div align='center'>$sumpmmr</div></td>
  	<td><div align='center'>$percent1x</div></td>
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
