<?php
session_start();
set_time_limit(0);
if($_SESSION[username]){
include("../includes/conndb.php"); 
include("../includes/config.inc.php");
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="../ico/favicon.ico">

    <title><?php echo $titleweb; ?></title>

    <!-- Bootstrap core CSS -->
    <link href="../css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="../css/style.css" rel="stylesheet">

    <!-- Just for debugging purposes. Don't actually copy this line! -->
    <!--[if lt IE 9]><script src="../../assets/js/ie8-responsive-file-warning.js"></script><![endif]-->

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

    <script src="../js/jquery.1.11.0.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
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
$str = retdaterangstr($_GET[str]);
$sto = retdaterangsto($_GET[str]);
$strx = retDatets($str);
$stox = retDatets($sto);
$village = $_GET[village];
if($village == "00000000"){
	$mu = "ทุกหมู่บ้าน";
}else{
	$mu = getvillagename($village);	
}
$getchronic = $_GET[getchronic];
$live_type = $_GET[live_type];
$getage = $_GET[getage];
if($village == '00000000'){$ect2 = "";}else{$ect2 = " house.villcode = '$village' AND ";}
if($getchronic == '9'){$gchronic = "";}else{$gchronic = "where pid not in (SELECT p.pid FROM house AS h Inner Join person AS p ON h.pcucode = p.pcucodeperson AND h.hcode = p.hcode Inner Join personchronic AS pc ON p.pcucodeperson = pc.pcucodeperson AND p.pid = pc.pid Inner Join cdisease AS d ON pc.chroniccode = d.diseasecode Inner Join cdiseasechronic AS dc ON d.codechronic = dc.groupcode WHERE ((p.dischargetype is null) or (p.dischargetype = '9')) AND SUBSTRING(h.villcode,7,2) <> '00' AND pc.typedischart NOT IN  ('01', '02','07','10') and dc.groupcode in ('01','10') GROUP BY p.pid)";}
if($live_type == '2'){$live_type2 = "and person.typelive in ('0','1','2')";}elseif($live_type == '1'){$live_type2 = "and person.typelive in ('0','1','3')";}else{$live_type2 = "";}
if($getage == '15'){$gage = "AND FLOOR((TO_DAYS(NOW())-TO_DAYS(person.birth))/365.25) > 14";}elseif($getage == '35'){$gage = "AND FLOOR((TO_DAYS(NOW())-TO_DAYS(person.birth))/365.25) > 34";}else{$gage = "AND FLOOR((TO_DAYS(NOW())-TO_DAYS(person.birth))/365.25) between '15 and '34'";}
if($getage == "15"){
	$gagename = "อายุ 15 ปี ขึ้นไป";
}elseif($getage == "35"){
	$gagename = "อายุ 35 ปี ขึ้นไป";
}else{
	$gagename = "อายุ 15 - 34 ปี";
}
if($live_type == '2'){$live_type_name = "ตามทะเบียนบ้าน(0,1,2)";}elseif($live_type == '1'){$live_type_name = "ที่อาศัยอยู่จริง (0,1,3)";}else{$live_type_name = "ทั้งหมดในเขตรับผิดชอบ(0,1,2,3)";}	
if($getchronic == '9'){$gchronic_name = "ทุกคนทั้งป่วยและไม่ป่วย";}else{$gchronic_name = "เฉพาะผู้ที่ยังไม่ป่วย";}	
$sql = "select
per.pcucodeperson,
per.villcode,
per.villname,
count(distinct pid) as per,
count(distinct pid1) as count_screen,
sum(case when resultht like 'ปกติ' then 1 else 0 end) as ht1,
sum(case when resultht like 'เสี่ยง' then 1 else 0 end) as ht2,
sum(case when resultht like 'สูง' then 1 else 0 end) as ht3,
sum(case when resultdm like 'ปกติ' then 1 else 0 end) as dm1,
sum(case when resultdm like 'เสี่ยง' then 1 else 0 end) as dm2,
sum(case when resultdm like 'สูง' then 1 else 0 end) as dm3,
sum(case when resultwaist like 'รอบเอวปกติ' then 1 else 0 end) as waist1,
sum(case when resultwaist like 'รอบเอวเกิน' then 1 else 0 end) as waist2
from
(SELECT
person.pcucodeperson,
person.pid,
person.fname,
CONCAT(ctitle.titlename,person.fname,' ',person.lname) AS pname,
house.hno,
house.villcode,
village.villname,
house.xgis,
house.ygis,
person.birth,
FLOOR((TO_DAYS(NOW())-TO_DAYS(person.birth))/365.25) AS age
FROM
village
INNER JOIN house ON village.pcucode = house.pcucode AND village.villcode = house.villcode
Inner Join person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
Inner Join cstatus ON person.marystatus = cstatus.statuscode
Inner Join ctitle ON person.prename = ctitle.titlecode
WHERE $ect2 ((person.dischargetype is null) or (person.dischargetype = '9')) AND
				SUBSTRING(house.villcode,7,2) <> '00' $gage $live_type2 ORDER BY house.villcode,house.hno*1
) as per
left join 
(SELECT 
person.pcucodeperson as pcucodeperson1,
person.pid as pid1,
house.villcode, 
house.hno, house.xgis, house.ygis, person.idcard, CONCAT(ctitle.titlename,person.fname,' ',person.lname) AS pname, FLOOR((TO_DAYS(NOW())-TO_DAYS(person.birth))/365.25) as age, ncd_person_ncd_screen.screen_date, ncd_person_ncd_screen.bmi, ncd_person_ncd_screen.weight, ncd_person_ncd_screen.height, ncd_person_ncd_screen.waist, ncd_person_ncd_screen.hbp_s1,
ncd_person_ncd_screen.hbp_d1, ncd_person_ncd_screen.result_new_dm, ncd_person_ncd_screen.result_new_hbp, ncd_person_ncd_screen.result_new_waist, ncd_person_ncd_screen.result_new_obesity, if(ncd_person_ncd_screen.hbp_s2 is null ,if(ncd_person_ncd_screen.hbp_s1 between 120 and 139 or ncd_person_ncd_screen.hbp_d1 between 80 and  89, 'เสี่ยง',if(ncd_person_ncd_screen.hbp_s1 > 139 or ncd_person_ncd_screen.hbp_d1 > 89,'สูง','ปกติ')),if(ncd_person_ncd_screen.hbp_s2  between 120 and 139 or  ncd_person_ncd_screen.hbp_d2 between 80 and  89, 'เสี่ยง',if(ncd_person_ncd_screen.hbp_s2 > 139 or ncd_person_ncd_screen.hbp_d2 > 89,'สูง','ปกติ'))) as resultht,
if(ncd_person_ncd_screen.bstest = '3' or ncd_person_ncd_screen.bstest = '1',if(ncd_person_ncd_screen.bsl between 100 and 125,'เสี่ยง',if(ncd_person_ncd_screen.bsl > 125,'สูง','ปกติ')),if(ncd_person_ncd_screen.bsl between 140 and 199,'เสี่ยง',if(ncd_person_ncd_screen.bsl > 199,'สูง','ปกติ'))) as resultdm,
ncd_person_ncd_screen.bsl,
ncd_person_ncd_screen.hbp_s2, ncd_person_ncd_screen.hbp_d2, ncd_person_ncd_screen.bstest,
if(ncd_person_ncd_screen.waist is null,null,if( (person.sex='1' and ncd_person_ncd_screen.waist >89 ) or (person.sex='2' and ncd_person_ncd_screen.waist >79),'รอบเอวเกิน','รอบเอวปกติ')) as resultwaist
from
house
Inner Join person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
Inner Join village ON village.pcucode = house.pcucode AND village.villcode = house.villcode
inner join ncd_person_ncd_screen on person.pid = ncd_person_ncd_screen.pid AND person.pcucodeperson = ncd_person_ncd_screen.pcucode
Inner Join ctitle ON person.prename = ctitle.titlecode
where $ect2 ((person.dischargetype is null) or (person.dischargetype = '9'))  and SUBSTRING(house.villcode,7,2) <> '00' and ncd_person_ncd_screen.screen_date BETWEEN '$str' AND '$sto' $gage $live_type2
							ORDER BY
							house.villcode,house.hno*1
) as fp
on per.pcucodeperson = fp.pcucodeperson1 and per.pid = fp.pid1
$gchronic
group by pcucodeperson, villcode
order by pcucodeperson, villcode";

$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>จำนวนประชากร';
$txt .= " $gagename ที่ได้รับการคัดกรองโรคเบาหวานและความดันโลหิตสูง</b><br><b> $mu </b></p>ประชากร $live_type_name   $gchronic_name<br><b>$hosp</b><table width='98%' border='0' cellspacing='1' cellpadding='1' class='table table-striped table-hover table-bordered'>
  <tr>
    <th width='5%' scope='col'><div align='center'>ลำดับ</div></th>
    <th width='12%' scope='col'><div align='center'>หมู่บ้าน</div></th>
	<th width='8%' scope='col'><div align='center'>หมู่ที่</div></th>
    <th width='8%' scope='col'><div align='center'>ประชาชน$gagename</div></th>
    <th width='5%' scope='col'><div align='center'>ได้รับการคัดกรอง</div></th>
	<th width='5%' scope='col'><div align='center'>ร้อยละ</div></th>
	<th width='5%' scope='col'><div align='center'>เเบาหวานปกติ</div></th>
	<th width='5%' scope='col'><div align='center'>เบาหวานเสี่ยง</div></th>
	<th width='5%' scope='col'><div align='center'>เบาหวานสูง</div></th>
	<th width='5%' scope='col'><div align='center'>ความดันปกติ</div></th>
	<th width='5%' scope='col'><div align='center'>ความดันเสี่ยง</div></th>
	<th width='5%' scope='col'><div align='center'>ความดันสุง</div></th>
	<th width='5%' scope='col'><div align='center'>รอบเอวปกติ</div></th>
	<th width='5%' scope='col'><div align='center'>รอบเอวเกิน</div></th>
  </tr>";
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
if($row[per] == "0"){
	$percen = "0";
}else{
	$percen = ($row[count_screen])/($row[per])*100;	
}
	$percent1 = number_format($percen, 2, '.', '');
	$sum_per = $sum_per+$row[per];
	$sum_count_screen = $sum_count_screen+$row[count_screen];
	$sum_ht1 = $sum_ht1+$row[ht1];
	$sum_ht2 = $sum_ht2+$row[ht2];
	$sum_ht3 = $sum_ht3+$row[ht3];
	$sum_dm1 = $sum_dm1+$row[dm1];
	$sum_dm2 = $sum_dm2+$row[dm2];
	$sum_dm3 = $sum_dm3+$row[dm3];
	$sum_waist1 = $sum_waist1+$row[waist1];
	$sum_waist2 = $sum_waist2+$row[waist2];
if($sum_per == "0"){
	$sum_percen = "0";
}else{
	$sum_percen = $sum_count_screen/$sum_per*100;	
}
	$sum_percent1 = number_format($sum_percen, 2, '.', '');
++$i;
	if(($i%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$i</div></td>
    <td>$row[villname]</td>
    <td><div align='center'>$moo</div></td>
    <td><div align='center'>$row[per]</div></td>
	<td><div align='center'>$row[count_screen]</div></td>
	<td><div align='center'>$percent1</div></td>
	<td><div align='center'>$row[dm1]</div></td>
	<td><div align='center'>$row[dm2]</div></td>
	<td><div align='center'>$row[dm3]</div></td>
	<td><div align='center'>$row[ht1]</div></td>
	<td><div align='center'>$row[ht2]</div></td>
	<td><div align='center'>$row[ht3]</div></td>
	<td><div align='center'>$row[waist1]</div></td>
	<td><div align='center'>$row[waist2]</div></td>
  </tr>
  ";
}
$txt .= "<tr>
  <td>&nbsp;&nbsp;</td>
  <td><div align='center'>&nbsp;รวม</td>
  <td>&nbsp;&nbsp;</td>
  <td><div align='center'>$sum_per</div></td>
  <td><div align='center'>$sum_count_screen</div></td>
  <td><div align='center'>$sum_percent1</div></td>
  <td><div align='center'>$sum_dm1</div></td>
  <td><div align='center'>$sum_dm2</div></td>
  <td><div align='center'>$sum_dm3</div></td>
  <td><div align='center'>$sum_ht1</div></td>
  <td><div align='center'>$sum_ht2</div></td>
  <td><div align='center'>$sum_ht3</div></td>
  <td><div align='center'>$sum_waist1</div></td>
  <td><div align='center'>$sum_waist2</div></td>
  </tr></table><p div align='right' class='text-danger'>ข้อมูลระหว่างวันที่  $strx ถึง $stox </p></div><br>";  
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
