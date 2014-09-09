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
	$mu = substr($_GET[village],6,2);	
}
$str = retDate($_GET[str]);
$sto = retDate($_GET[sto]);		
$sql = "select 
tmp_per.pcucodeperson,
villcode,
villname,
sum(case when tmp_per.pid is not null then 1 else 0 end) as per,
sum(case when chk is not null then 1 else 0 end) as per_chk,
sum(case when chk like 'ผอม' then 1 else 0 end) as lv1,
sum(case when chk like 'ปกติ' then 1 else 0 end) as lv2,
sum(case when chk like 'อ้วน' then 1 else 0 end) as lv3,
sum(case when chk is null then 1 else 0 end) as no_lv
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
group by tmp_per.pcucodeperson,villcode";

$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>รายงานการประเมินภาวะโภชนาการผู้สูงอายุ</b><br>';
$txt .= "<b>ข้อมูลระหว่างวันที่ $_GET[str] ถึง $_GET[sto] หมู่บ้าน $mu </b></p><br><b>$hosp</b><table width='95%' border='0' cellspacing='1' cellpadding='1' class='tbhl'>
  <tr>
    <th width='4%' scope='col'>ลำดับ</th>
    <th width='11%' scope='col'>หมู่บ้าน</th>
	<th width='6%' scope='col'>หมู่ที่</th>
    <th width='6%' scope='col'>ผู้สูงอายุทั้งหมด</th>
    <th width='6%' scope='col'>ได้รับการชั่ง</th>
	<th width='6%' scope='col'>ร้อยละ</th>
	<th width='6%' scope='col'>ผอม</th>
	<th width='6%' scope='col'>ร้อยละ</th>
	<th width='6%' scope='col'>ปกติ</th>
	<th width='6%' scope='col'>ร้อยละ</th>
	<th width='6%' scope='col'>อ้วน</th>
	<th width='6%' scope='col'>ร้อยละ</th>
	<th width='6%' scope='col'>ไม่ได้ชั่งน้ำหนัก</th>
  </tr>";
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
if($row[per] == "0"){
	$percen = "0";
}else{
	$percen = ($row[per_chk])/($row[per])*100;	
}
	$percent1 = number_format($percen, 2, '.', '');
if($row[per_chk] == "0"){
	$percen2 = "0";
}else{
	$percen2 = ($row[lv1])/($row[per_chk])*100;	
}
	$percent2 = number_format($percen2, 2, '.', '');
if($row[per_chk] == "0"){
	$percen3 = "0";
}else{
	$percen3 = ($row[lv2])/($row[per_chk])*100;	
}
	$percent3 = number_format($percen3, 2, '.', '');
if($row[per_chk] == "0"){
	$percen4 = "0";
}else{
	$percen4 = ($row[lv3])/($row[per_chk])*100;	
}
	$percent4 = number_format($percen4, 2, '.', '');
	$sum_per = $sum_per+$row[per];
	$sum_per_chk = $sum_per_chk+$row[per_chk];
	$sum_lv1 = $sum_lv1+$row[lv1];
	$sum_lv2 = $sum_lv2+$row[lv2];
	$sum_lv3 = $sum_lv3+$row[lv3];
	$sum_no_lv = $sum_no_lv+$row[no_lv];
if($sum_per == "0"){
	$sum_percen = "0";
}else{
	$sum_percen = $sum_per_chk/$sum_per*100;	
}
	$sum_percent1 = number_format($sum_percen, 2, '.', '');
if($sum_per_chk == "0"){
	$sum_percen2 = "0";
}else{
	$sum_percen2 = $sum_lv1/$sum_per_chk*100;	
}
	$sum_percent2 = number_format($sum_percen2, 2, '.', '');
if($sum_per_chk == "0"){
	$sum_percen3 = "0";
}else{
	$sum_percen3 = $sum_lv2/$sum_per_chk*100;	
}
	$sum_percent3 = number_format($sum_percen3, 2, '.', '');
if($sum_old_t == "0"){
	$sum_percen4 = "0";
}else{
	$sum_percen4 = $sum_lv3/$sum_per_chk*100;	
}
	$sum_percent4 = number_format($sum_percen4, 2, '.', '');
++$i;
	if(($i%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$i</div></td>
    <td>$row[villname]</td>
    <td><div align='center'>&nbsp;$moo</div></td>
    <td><div align='center'>$row[per]</div></td>
	<td><div align='center'>$row[per_chk]</div></td>
	<td><div align='center'>$percent1</div></td>
	<td><div align='center'>$row[lv1]</div></td>
	<td><div align='center'>$percent2</div></td>
	<td><div align='center'>$row[lv2]</div></td>
	<td><div align='center'>$percent3</div></td>
	<td><div align='center'>$row[lv3]</div></td>
	<td><div align='center'>$percent4</div></td>
	<td><div align='center'>$row[no_lv]</div></td>
  </tr>
  ";
}
$txt .= "<tr>
  	<td>&nbsp;&nbsp;</td>
  	<td><div align='center'>&nbsp;รวม</td>
  	<td>&nbsp;&nbsp;</td>
  	<td><div align='center'>$sum_per</div></td>
	<td><div align='center'>$sum_per_chk</div></td>
	<td><div align='center'>$sum_percent1</div></td>
	<td><div align='center'>$sum_lv1</div></td>
	<td><div align='center'>$sum_percent2</div></td>
	<td><div align='center'>$sum_lv2</div></td>
	<td><div align='center'>$sum_percent3</div></td>
	<td><div align='center'>$sum_lv3</div></td>
	<td><div align='center'>$sum_percent4</div></td>
	<td><div align='center'>$sum_no_lv</div></td>
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
