<?php
session_start();
if($_SESSION[username]){
include("includes/conndb.php"); 
include("includes/config.inc.php");
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
function redatepick($d){
	$y = substr($d,6,4)-543;
	$m = substr($d,3,2);
	$dn = substr($d,0,2);
	$rt = $y."/".$m."/".$dn;
	return $rt;
}
$str = redatepick($_GET[str]);
$sto = redatepick($_GET[sto]);
$sql = "CREATE TEMPORARY TABLE tmpdata
		SELECT
case 
when mapoccupa like 'xxxx' then '4'
when mapoccupa like 'xxxx' then '5'
when mapoccupa like 'xxxx' then '6'
when mapoccupa like 'xxxx' then '7'
when mapoccupa like 'xxxx' then '8' 
when mapoccupa like '0%10' then '9'
when mapoccupa like '5412' then '10'
when mapoccupa like '9000' and age <= 18 then '12'
when mapoccupa like '9000' and age between 19 and 23 then '13'
else '11' end as occugroup,
sum(case when drugcode = 'CONDOM1' then unit else 0 end) as condomfp49,
sum(case when drugcode = 'CONDOM2' then unit else 0 end) as condomfp52,
sum(case when drugcode = 'CONDOM3' then unit else 0 end) as condomaids49,
sum(case when drugcode = 'CONDOM4' then unit else 0 end) as condomaids52,
sum(unit) as sumcondom
from
(SELECT
person.pcucodeperson,
person.pid,
getAgeYearNum(person.birth,now()) as age,
visit.visitno,
visit.visitdate,
visitdrug.drugcode,
coccupa.occupaname,
coccupa.mapoccupa,
visitdrug.unit
FROM
person
INNER JOIN visit ON person.pcucodeperson = visit.pcucodeperson AND person.pid = visit.pid
INNER JOIN visitdrug ON visit.pcucode = visitdrug.pcucode AND visit.visitno = visitdrug.visitno
INNER JOIN coccupa ON person.occupa = coccupa.occupacode
where visit.visitdate BETWEEN '$str' and '$sto' and visitdrug.drugcode between 'CONDOM1' and 'CONDOM4'
UNION
SELECT
person.pcucodeperson,
person.pid,
getAgeYearNum(person.birth,now()) as age,
visit.visitno,
visit.visitdate,
visitfp.fpcode,
coccupa.occupaname,
coccupa.mapoccupa,
visitfp.unit
FROM
person
INNER JOIN visit ON person.pcucodeperson = visit.pcucodeperson AND person.pid = visit.pid
INNER JOIN visitfp ON visit.pcucode = visitfp.pcucode AND visit.visitno = visitfp.visitno
INNER JOIN coccupa ON person.occupa = coccupa.occupacode
where visit.visitdate BETWEEN '$str' and '$sto' and visitfp.fpcode between 'CONDOM1' and 'CONDOM4') as condom
GROUP BY occugroup";
mysql_query($sql);
$txt = '<p align=\'center\'><b>รายงานการรับจ่ายและการใช้ถุงยางอนามัยรายเดือน<br>ระหว่างวันที่  ';
$txt .= "$_GET[str] ถึงวันที่ $_GET[str] $mu</b><br></p><br><b>$hosp</b><br><table width='99%' border='0' cellspacing='1' cellpadding='1' class='tbhl'>
  <tr>
    <th width='40%' rowspan='3' scope='col'>รายการ</th>
	<th width='40%'  colspan='5'>จำนวนถึงยางอนามัย(ชิ้น)</th>
  </tr>
  <tr>
    <th colspan='2'>วางแผนครอบครัว</th>
    <th colspan='2'>งานเอดส์</th>
    <th rowspan='2'>รวมทั้งสิ้น</th>
  </tr>
  <tr>
    <th width='8%' scope='col'>ขนาด 49 มม.</th>
	<th width='8%' scope='col'>ขนาด 52 มม.</th>
    <th width='8%' scope='col'>ขนาด 49 มม.</th>
	<th width='8%' scope='col'>ขนาด 52 มม.</th>
  </tr>";
$txt .="  <tr class='altrow'>
    <td>1.  จำนวนถุงยางอนามัยที่มีอยู่</td>
    <td><div align='center'>$row[condomfp49]</div></td>
    <td><div align='center'>$row[condomfp52]</div></td>
    <td><div align='center'>$row[condomaids49]</div></td>
	<td><div align='center'>$row[condomaids52]</div></td>
	<td><div align='center'>$row[sumcondom]</div></td>
  </tr>";
$txt .="  <tr class='altrow'>
    <td>2.  จำนวนถุงยางอนามัยที่รับใหม่</td>
    <td><div align='center'>$row[condomfp49]</div></td>
    <td><div align='center'>$row[condomfp52]</div></td>
    <td><div align='center'>$row[condomaids49]</div></td>
	<td><div align='center'>$row[condomaids52]</div></td>
	<td><div align='center'>$row[sumcondom]</div></td>
  </tr>";
$txt .="  <tr class='altrow'>
    <td>3.  รวมจำนวนถุงยางอนามัยทั้งหมด</td>
    <td><div align='center'>$row[condomfp49]</div></td>
    <td><div align='center'>$row[condomfp52]</div></td>
    <td><div align='center'>$row[condomaids49]</div></td>
	<td><div align='center'>$row[condomaids52]</div></td>
	<td><div align='center'>$row[sumcondom]</div></td>
  </tr>";
$sql4 = "select * from tmpdata where tmpdata.occugroup = '7'";
$result4 = mysql_query($sql4);
$row4=mysql_fetch_array($result4);
$txt .="  <tr>
    <td>4.  จ่ายให้สถานบริการทางเพศ </td>
    <td><div align='center'>$row4[condomfp49]</div></td>
    <td><div align='center'>$row4[condomfp52]</div></td>
    <td><div align='center'>$row4[condomaids49]</div></td>
	<td><div align='center'>$row4[condomaids52]</div></td>
	<td><div align='center'>$row4[sumcondom]</div></td>
  </tr>";
$sql5 = "select * from tmpdata where tmpdata.occugroup = '5'";
$result5 = mysql_query($sql5);
$row5=mysql_fetch_array($result5);
$txt .="  <tr>
    <td>5.  จ่ายให้ผู้ให้บริการทางเพศ </td>
    <td><div align='center'>$row5[condomfp49]</div></td>
    <td><div align='center'>$row5[condomfp52]</div></td>
    <td><div align='center'>$row5[condomaids49]</div></td>
	<td><div align='center'>$row5[condomaids52]</div></td>
	<td><div align='center'>$row5[sumcondom]</div></td>
  </tr>";
$sql6 = "select * from tmpdata where tmpdata.occugroup = '6'";
$result6 = mysql_query($sql6);
$row6=mysql_fetch_array($result6);
$txt .="  <tr>
    <td>6.  จ่ายให้ผู้มาตรวจรักษาโรคติดเชื้อทางเพศสัมพันธุ์</td>
    <td><div align='center'>$row6[condomfp49]</div></td>
    <td><div align='center'>$row6[condomfp52]</div></td>
    <td><div align='center'>$row6[condomaids49]</div></td>
	<td><div align='center'>$row6[condomaids52]</div></td>
	<td><div align='center'>$row6[sumcondom]</div></td>
  </tr>";
$sql7 = "select * from tmpdata where tmpdata.occugroup = '7'";
$result7 = mysql_query($sql7);
$row7=mysql_fetch_array($result7);
$txt .="  <tr>
    <td>7.  จ่ายให้ผู้ติดเชื้อไวรัสเอดส์</td>
    <td><div align='center'>$row7[condomfp49]</div></td>
    <td><div align='center'>$row7[condomfp52]</div></td>
    <td><div align='center'>$row7[condomaids49]</div></td>
	<td><div align='center'>$row7[condomaids52]</div></td>
	<td><div align='center'>$row7[sumcondom]</div></td>
  </tr>";
$sql8 = "select * from tmpdata where tmpdata.occugroup = '8'";
$result8 = mysql_query($sql8);
$row8=mysql_fetch_array($result8);
$txt .="  <tr>
    <td>8.  จ่ายให้ผู้ติดยาเสพติด</td>
    <td><div align='center'>$row8[condomfp49]</div></td>
    <td><div align='center'>$row8[condomfp52]</div></td>
    <td><div align='center'>$row8[condomaids49]</div></td>
	<td><div align='center'>$row8[condomaids52]</div></td>
	<td><div align='center'>$row8[sumcondom]</div></td>
  </tr>";
$sql9 = "select * from tmpdata where tmpdata.occugroup = '9'";
$result9 = mysql_query($sql9);
$row9=mysql_fetch_array($result9);
$txt .="  <tr>
    <td>9.  จ่ายให้ทหาร</td>
    <td><div align='center'>$row9[condomfp49]</div></td>
    <td><div align='center'>$row9[condomfp52]</div></td>
    <td><div align='center'>$row9[condomaids49]</div></td>
	<td><div align='center'>$row9[condomaids52]</div></td>
	<td><div align='center'>$row9[sumcondom]</div></td>
  </tr>";
$sql10 = "select * from tmpdata where tmpdata.occugroup = '10'";
$result10 = mysql_query($sql10);
$row10=mysql_fetch_array($result10);
$txt .="  <tr>
    <td>10. จ่ายให้ตำรวจ</td>
    <td><div align='center'>$row10[condomfp49]</div></td>
    <td><div align='center'>$row10[condomfp52]</div></td>
    <td><div align='center'>$row10[condomaids49]</div></td>
	<td><div align='center'>$row10[condomaids52]</div></td>
	<td><div align='center'>$row10[sumcondom]</div></td>
  </tr>";
$sql11 = "select * from tmpdata where tmpdata.occugroup = '11'";
$result11 = mysql_query($sql11);
$row11=mysql_fetch_array($result11);
$txt .="  <tr>
    <td>11. จ่ายให้ประชาชนทั่วไป</td>
    <td><div align='center'>$row11[condomfp49]</div></td>
    <td><div align='center'>$row11[condomfp52]</div></td>
    <td><div align='center'>$row11[condomaids49]</div></td>
	<td><div align='center'>$row11[condomaids52]</div></td>
	<td><div align='center'>$row11[sumcondom]</div></td>
  </tr>";
$sql12 = "select * from tmpdata where tmpdata.occugroup = '12'";
$result12 = mysql_query($sql12);
$row12=mysql_fetch_array($result12);
$txt .="  <tr>
    <td>12.  จ่ายให้นักเรียน</td>
    <td><div align='center'>$row12[condomfp49]</div></td>
    <td><div align='center'>$row12[condomfp52]</div></td>
    <td><div align='center'>$row12[condomaids49]</div></td>
	<td><div align='center'>$row12[condomaids52]</div></td>
	<td><div align='center'>$row12[sumcondom]</div></td>
  </tr>";
$sql13 = "select * from tmpdata where tmpdata.occugroup = '13'";
$result13 = mysql_query($sql13);
$row13=mysql_fetch_array($result13);
$txt .="  <tr>
    <td>13.  จ่ายให้นักศึกษา</td>
    <td><div align='center'>$row13[condomfp49]</div></td>
    <td><div align='center'>$row13[condomfp52]</div></td>
    <td><div align='center'>$row13[condomaids49]</div></td>
	<td><div align='center'>$row13[condomaids52]</div></td>
	<td><div align='center'>$row13[sumcondom]</div></td>
  </tr>";
$sql = "SELECT * FROM tmpdata";
$result = mysql_query($sql);
while($row=mysql_fetch_array($result)) {
	$condomfp49 = $condomfp49+$row[condomfp49];
	$condomfp52 = $condomfp52+$row[condomfp52];
	$condomaids49 = $condomaids49+$row[condomaids49];
	$condomaids52 = $condomaids52+$row[condomaids52];
	$sumcondom = $sumcondom+$row[sumcondom];
}
$txt .="  <tr class='altrow'>
    <td>14.  จำนวนจ่ายทั้งสิ้น</td>
    <td><div align='center'>$condomfp49</div></td>
    <td><div align='center'>$condomfp52</div></td>
    <td><div align='center'>$condomaids49</div></td>
	<td><div align='center'>$condomaids52</div></td>
	<td><div align='center'>$sumcondom</div></td>
  </tr>";
 $txt .="  <tr class='altrow'>
    <td>15.  จำนวนถุงยางอนามัยที่คงเหลือ</td>
    <td><div align='center'>$row[condomfp49]</div></td>
    <td><div align='center'>$row[condomfp52]</div></td>
    <td><div align='center'>$row[condomaids49]</div></td>
	<td><div align='center'>$row[condomaids52]</div></td>
	<td><div align='center'>$row[sumcondom]</div></td>
  </tr>";
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
