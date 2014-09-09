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
	$wvill = " and h.villcode='$villcode' ";	
}
if($villcode == "00000000"){
	$mu = "ทุกหมู่บ้าน";
}else{
	$mu = substr($_GET[village],6,2);	
}
$str = retDate($_GET[str]);
$sto = retDate($_GET[sto]);		
$sql = "select
tmp.pcucode,
tmp.villcode,
village.villname,
count(distinct pid) as pidva,
count(pid) as pidvac
FROM
(SELECT DISTINCT  trim(visitepi.pcucodeperson) AS pcucode, visitepi.pid, concat(ctitle.titlename,person.fname,'  ',person.lname) as pname, getAgeMonth(person.birth,CURDATE()) AS agemonth, visitepi.visitno AS seq, cdrug.files18epi AS vcctype,  cdrug.drugname, IF(visitepi.dateepi IS NULL OR TRIM(visitepi.dateepi)='' OR visitepi.dateepi LIKE '0000-00-00%','',DATE_FORMAT(visitepi.dateepi,'%Y%m%d')) AS date_serv,  visitepi.dateepi, IF(visitepi.hosservice IS NULL OR visitepi.hosservice='',trim(visitepi.pcucode),trim(visitepi.hosservice)) AS vccplace,  IF(visitepi.dateupdate IS NULL OR TRIM(visitepi.dateupdate)='' OR     visitepi.dateupdate LIKE '0000-00-00%',DATE_FORMAT(visitepi.dateepi,'%Y%m%d%H%i%s'),    DATE_FORMAT(visitepi.dateupdate,'%Y%m%d%H%i%s') ) AS d_update, visitepi.dateupdate, idcard as cid , house.hno, house.villcode, house.xgis, house.ygis
FROM 
visitepi  
join person on visitepi.pcucodeperson = person.pcucodeperson and visitepi.pid = person.pid
join house on person.pcucodeperson = house.pcucode and person.hcode = house.hcode
left join ctitle on person.prename = ctitle.titlecode  
LEFT JOIN cdrug ON (visitepi.vaccinecode=cdrug.drugcode AND cdrug.drugtype='05') 
WHERE visitepi.dateepi IS NOT NULL AND TRIM(visitepi.dateepi)<>''  AND TRIM(visitepi.pcucodeperson)<>''
AND (visitepi.dateepi >= '$str') AND (visitepi.dateepi BETWEEN '$str' AND '$sto')
ORDER BY visitepi.pcucodeperson ASC, visitepi.dateepi DESC, visitepi.visitno DESC) as tmp
inner join village on village.pcucode = tmp.pcucode and village.villcode = tmp.villcode
group by tmp.villcode";

$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>รายงาน 21 เฟ้ม แฟ้ม EPI ปี 2556</b><br>';
$txt .= "<b>ข้อมูลระหว่างวันที่ $_GET[str] ถึง $_GET[sto] หมู่บ้าน $mu </b></p><br><b>$hosp</b><table width='95%' border='0' cellspacing='1' cellpadding='1' class='tbhl'>
  <tr>
    <th width='5%' scope='col'>ลำดับ</th>
    <th width='12%' scope='col'>หมู่บ้าน</th>
	<th width='8%' scope='col'>หมู่ที่</th>
    <th width='8%' scope='col'>รับวัคซีน(คน)</th>
	<th width='8%' scope='col'>รับวัคซีน(รายการ)</th>
  </tr>";
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$sum_pidva = $sum_pidva+$row[pidva];
	$sum_pidvac = $sum_pidvac+$row[pidvac];

++$i;
	if(($i%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$i</div></td>
    <td>$row[villname]</td>
    <td><div align='center'>&nbsp;$moo</div></td>
    <td><div align='center'>$row[pidva]</div></td>
	<td><div align='center'>$row[pidvac]</div></td>
  </tr>
  ";
}
$txt .= "<tr>
  <td>&nbsp;&nbsp;</td>
  <td><div align='center'>&nbsp;รวม</td>
  <td>&nbsp;&nbsp;</td>
  <td><div align='center'>$sum_pidva</div></td>
  <td><div align='center'>$sum_pidvac</div></td>
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
