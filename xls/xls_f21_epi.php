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
}elseif($villcode == "xx"){
	$wvill = " AND right(house.villcode,2)='00'";
}else{
	$wvill = " AND h.villcode='$villcode' ";	
}
if($villcode == "00000000"){
	$mu = "ทุกหมู่บ้าน";
}elseif($villcode == "xx"){
	$mu = "นอกเขต";
}else{
	$mu = substr($_GET[village],6,2);	
}
$chk_stool = $_GET[chk_stool];
if($chk_stool == "1"){
	$chksto = "";
}elseif($chk_stool == "2"){
	$chksto = "where count_visit is not null";	
}else{
	$chksto = "where count_visit is null";	
}
$str = retDate($_GET[str]);
$sto = retDate($_GET[sto]);
$sql = "SELECT DISTINCT  trim(visitepi.pcucodeperson) AS pcucode, visitepi.pid, concat(ctitle.titlename,person.fname,'  ',person.lname) as pname, getAgeMonth(person.birth,CURDATE()) AS agemonth, visitepi.visitno AS seq, cdrug.files18epi AS vcctype,  cdrug.drugname, IF(visitepi.dateepi IS NULL OR TRIM(visitepi.dateepi)='' OR visitepi.dateepi LIKE '0000-00-00%','',DATE_FORMAT(visitepi.dateepi,'%Y%m%d')) AS date_serv,  visitepi.dateepi, IF(visitepi.hosservice IS NULL OR visitepi.hosservice='',trim(visitepi.pcucode),trim(visitepi.hosservice)) AS vccplace,  IF(visitepi.dateupdate IS NULL OR TRIM(visitepi.dateupdate)='' OR     visitepi.dateupdate LIKE '0000-00-00%',DATE_FORMAT(visitepi.dateepi,'%Y%m%d%H%i%s'),    DATE_FORMAT(visitepi.dateupdate,'%Y%m%d%H%i%s') ) AS d_update, visitepi.dateupdate, idcard as cid , house.hno, house.villcode, house.xgis, house.ygis
FROM 
visitepi  
join person on visitepi.pcucodeperson = person.pcucodeperson and visitepi.pid = person.pid
join house on person.pcucodeperson = house.pcucode and person.hcode = house.hcode
left join ctitle on person.prename = ctitle.titlecode  
LEFT JOIN cdrug ON (visitepi.vaccinecode=cdrug.drugcode AND cdrug.drugtype='05') 
WHERE visitepi.dateepi IS NOT NULL AND TRIM(visitepi.dateepi)<>''  AND TRIM(visitepi.pcucodeperson)<>''
AND (visitepi.dateepi >= '$str') AND (visitepi.dateepi BETWEEN '$str' AND '$sto') $wvill
ORDER BY visitepi.pcucodeperson ASC, visitepi.dateepi DESC, visitepi.visitno DESC";

$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>รายงาน 21 แฟ้ม แฟ้ม EPI ปี 2556</b><br>';
$txt .= "<b>ข้อมูลระหว่างวันที่ $_GET[str] ถึง $_GET[sto] หมู่ที่ $mu </b></p><br><b>$hosp</b><table width='99%' border='0' cellspacing='1' cellpadding='1' class='tbhl'>
  <tr>
    <th width='4%' scope='col'>ลำดับ</th>
    <th width='4%' scope='col'>pcucode</th>
	<th width='4%' scope='col'>pid</th>
    <th width='10%' scope='col'>ชื่อ - สกุล</th>
    <th width='3%' scope='col'>อายุ</th>
	<th width='4%' scope='col'>บ้านเลขที่</th>
	<th width='4%' scope='col'>หมู่ที่</th>
	<th width='4%' scope='col'>SEQ(ลำดับที่)</th>
    <th width='6%' scope='col'>วันที่</th>
	<th width='5%' scope='col'>รหัสวัคซีน</th>
	<th width='12%' scope='col'>ชื่อวัคซีน</th>
	<th width='7%' scope='col'>สถานที่รับวัคซีน</th>
	<th width='7%' scope='col'>วันที่ปรับปรุงข้อมูล</th>
	<th width='7%' scope='col'>เลขที่บัตรประชาชน</th>
  </tr>";
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$dateepi = retDatets($row[dateepi]);
++$i;
	if(($i%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$i</div></td>
    <td><div align='center'>$row[pcucode]</div></td>
	<td><div align='center'>$row[pid]</div></td>
	<td>$row[pname]</td>
	<td><div align='center'>$row[agemonth]</div></td>
	<td><div align='center'>$row[hno]</div></td>
	<td><div align='center'>$moo</div></td>
	<td><div align='center'>$row[seq]</div></td>
	<td><div align='center'>$row[dateepi]</div></td>
	<td><div align='center'>$row[vcctype]</div></td>
	<td>$row[drugname]</td>
	<td><div align='center'>$row[vccplace]</div></td>
	<td><div align='center'>$row[d_update]</div></td>
	<td><div align='center'>$row[cid]</div></td>
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
