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
$chk_old = $_GET[chk_old];
if($chk_old == "1"){
	$chksto = "where chk = 'ผอม'";
}elseif($chk_old == "2"){
	$chksto = "where chk = 'ปกติ'";	
}elseif($chk_old == "3"){
	$chksto = "where chk = 'อ้วน'";
}elseif($chk_old == "8"){
	$chksto = "where chk in ('ผอม','ปกติ','อ้วน')";
}elseif($chk_old == "9"){
	$chksto = "";	
}else{
	$chksto = "where chk is null";
}
$str = retDate($_GET[str]);
$sto = retDate($_GET[sto]);
$sql = "select *
from
(select
person.pcucodeperson,
person.pid,
person.fname, 
concat(ctitle.titlename, person.fname , '  ' , person.lname) AS pname,
person.birth,
ROUND(DATEDIFF('$str',person.birth)/365.25) AS age,
village.villcode,
village.villname,
house.hno,
house.hcode,
house.xgis,
house.ygis
FROM
village
Inner Join house ON village.pcucode = house.pcucode AND village.villcode = house.villcode
Inner Join person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
Inner Join ctitle ON person.prename = ctitle.titlecode
where  FLOOR((TO_DAYS('$str')-TO_DAYS(person.birth))/365.25) >59 and  ((person.dischargetype is null) or (person.dischargetype = '9'))  and SUBSTRING(house.villcode,7,2) <> '00' $wvill) as tmp_per
left join
(select
visit.pcucodeperson,
visit.pid,
max(visit.visitdate) as m_visit,
visit.visitno,
visit.weight,
visit.height,
visit.weight/pow(visit.height/100,2) as bmi,
case when visit.weight/pow(visit.height/100,2) < 18.9 then 'ผอม' when (visit.weight/pow(visit.height/100,2) ) between 18.9 and 22.9 then 'ปกติ' when (visit.weight/pow(visit.height/100,2) ) > 22.9 then 'อ้วน' else '' end as chk
FROM
village
Inner Join house ON village.pcucode = house.pcucode and village.villcode = house.villcode
Inner Join person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
Inner Join ctitle ON person.prename = ctitle.titlecode
Inner Join visit ON person.pcucodeperson = visit.pcucodeperson AND person.pid = visit.pid
Inner Join visitdiag ON visit.pcucode = visitdiag.pcucode AND visit.visitno = visitdiag.visitno
where FLOOR((TO_DAYS('$str')-TO_DAYS(person.birth))/365.25) >59 and  ((person.dischargetype is null) or (person.dischargetype = '9'))  and SUBSTRING(house.villcode,7,2) <> '00' and visit.weight is not null and visit.height is not null
 and visit.visitdate between '$str' and '$sto' and (visit.flagservice <'04' OR visit.flagservice is null OR length(trim(visit.flagservice))=0 )
group by person.pid) as tmp_ncd
ON tmp_per.pcucodeperson = tmp_ncd.pcucodeperson AND tmp_per.pid = tmp_ncd.pid
$chksto
order by tmp_per.pcucodeperson,villcode,fname";

$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>รายงานการประเมินภาวะโภชนาการผู้สูงอายุ </b><br>';
$txt .= "<b>ข้อมูลระหว่างวันที่ $_GET[str] ถึง $_GET[sto] หมู่ที่ $mu </b></p><br><b>$hosp</b><table width='98%' border='0' cellspacing='1' cellpadding='1' class='tbhl'>
  <tr>
    <th width='4%' scope='col'>ลำดับ</th>
    <th width='13%' scope='col'>ชื่อ - สกุล</th>
	<th width='8%' scope='col'>ว/ด/ป เกิด</th>
	<th width='5%' scope='col'>อายุ</th>
    <th width='7%' scope='col'>บ้านเลขที่</th>
    <th width='4%' scope='col'>หมู่ที่</th>
	<th width='4%' scope='col'>วันที่สำรวจ</th>
	<th width='15%' scope='col'>น้ำหนัก</th>
	<th width='15%' scope='col'>ส่วนสูง</th>
	<th width='15%' scope='col'>BMI</th>
	<th width='15%' scope='col'>สรุปผล</th>
  </tr>";
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	if($row[chk] == ""){$old_chk = 'ไม่ได้ชัั่งน้ำหนัก';}else{$old_chk = $row[chk];}
	$birth = retDatets($row[birth]);
	if($row[m_visit] == ""){$visitdate = '--/--/----';}else{$visitdate = retDatets($row[m_visit]);}
	$bmi = number_format($row[bmi], 2, '.', '');
++$i;
	if(($i%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$i</div></td>
    <td>$row[pname]</td>
	<td><div align='center'>&nbsp;$birth</div></td>
	<td><div align='center'>&nbsp;$row[age]</div></td>
    <td><div align='center'>&nbsp;$row[hno]</div></td>
    <td><div align='center'>&nbsp;$moo</div></td>
    <td><div align='center'>&nbsp;$visitdate</div></td>
	<td><div align='center'>&nbsp;$row[weight]</div></td>
	<td><div align='center'>&nbsp;$row[height]</div></td>
	<td><div align='center'>&nbsp;$bmi</div></td>
	<td><div align='center'>&nbsp;$old_chk</div></td>
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
