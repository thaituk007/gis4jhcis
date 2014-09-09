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
	$sqlu = "UPDATE
_tmp_condom
left join (SELECT
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
case when drugcode = 'CONDOM1' then sum(unit) else 0 end as condomfp49,
case when drugcode = 'CONDOM2' then sum(unit) else 0 end as condomfp52,
case when drugcode = 'CONDOM3' then sum(unit) else 0 end as condomaids49,
case when drugcode = 'CONDOM4' then sum(unit) else 0 end as condomaids52,
sum(unit) as sunit
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
where visit.visitdate BETWEEN '2013-01-01' and '2013-09-30' and visitdrug.drugcode between 'CONDOM1' and 'CONDOM4'
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
where visit.visitdate BETWEEN '2013-01-01' and '2013-09-30' and visitfp.fpcode between 'CONDOM1' and 'CONDOM4') as condom
GROUP BY occugroup) as tmpcondom
on _tmp_condom.id = tmpcondom.occugroup
set _tmp_condom.condomfp49 = tmpcondom.condomfp49,
_tmp_condom.condomfp52 = tmpcondom.condomfp52,
_tmp_condom.condomaids49 = tmpcondom.condomaids49,
_tmp_condom.condomaids52 = tmpcondom.condomaids52,
_tmp_condom.sumcondom = tmpcondom.sunit";
$resultu = mysql_query($sqlu);
$sql = "SELECT
_tmp_condom.id,
_tmp_condom.occupa,
_tmp_condom.condomfp49,
_tmp_condom.condomfp52,
_tmp_condom.condomaids49,
_tmp_condom.condomaids52,
_tmp_condom.sumcondom
FROM
_tmp_condom";
$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>จำนวนผู้ได้รับถุงยางอนามัย<br>ระหว่างวันที่  ';
$txt .= "$_GET[str] ถึงวันที่ $_GET[str] $mu</b><br></p><br><b>$hosp</b><br><table width='99%' border='0' cellspacing='1' cellpadding='1' class='tbhl'>
  <tr>
    <th width='40%' scope='col'>รายการ</th>
    <th width='8%' scope='col'>ขนาด 49 มม.</th>
	<th width='8%' scope='col'>ขนาด 49 มม.</th>
    <th width='8%' scope='col'>ขนาด 49 มม.</th>
	<th width='8%' scope='col'>ขนาด 49 มม.</th>
	<th width='8%' scope='col'>รวมทั้งสิ้น</th>
  </tr>";
while($row=mysql_fetch_array($result)) {

++$x;
	if(($x%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td>$x. $row[occupa]</td>
    <td><div align='center'>$row[condomfp49]</div></td>
    <td><div align='center'>$row[condomfp52]</div></td>
    <td><div align='center'>$row[condomaids49]</div></td>
	<td><div align='center'>$row[condomaids52]</div></td>
	<td><div align='center'>$row[sumcondom]</div></td>
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
