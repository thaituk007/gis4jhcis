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
$risk = $_GET[risk];
$live_type = $_GET[live_type];
$getage = $_GET[getage];
if($village == '00000000'){$ect2 = "";}else{$ect2 = " house.villcode = '$village' AND ";}
if($getchronic == '9'){$gchronic = "";}else{$gchronic = "and pid not in (SELECT p.pid FROM house AS h Inner Join person AS p ON h.pcucode = p.pcucodeperson AND h.hcode = p.hcode Inner Join personchronic AS pc ON p.pcucodeperson = pc.pcucodeperson AND p.pid = pc.pid Inner Join cdisease AS d ON pc.chroniccode = d.diseasecode Inner Join cdiseasechronic AS dc ON d.codechronic = dc.groupcode WHERE ((p.dischargetype is null) or (p.dischargetype = '9')) AND SUBSTRING(h.villcode,7,2) <> '00' AND pc.typedischart NOT IN  ('01', '02','07','10') and dc.groupcode in ('01','10') GROUP BY p.pid)";}
if($risk == '3'){$risk2 = "and resultht in ('เสี่ยง','สูง') and resultdm in ('เสี่ยง','สูง')";}elseif($risk == '1'){$risk2 = "and resultdm in ('เสี่ยง','สูง')";}elseif($risk == '2'){$risk2 = "and resultht in ('เสี่ยง','สูง')";}elseif($risk == '4'){$risk2 = "and resultht in ('เสี่ยง','สูง') or resultdm in ('เสี่ยง','สูง')";}else{$risk2 = "";}
if($live_type == '2'){$live_type2 = "typelive in ('0','1','2')";}elseif($live_type == '1'){$live_type2 = "typelive in ('0','1','3')";}else{$live_type2 = "typelive in ('0','1','2','3')";}
if($getage == '15'){$gage = "AND age > 14";}elseif($getage == '35'){$gage = "AND age > 34";}else{$gage = "AND age between '15 and '34'";}
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
*,
case when resultht = '' or resultht is null then '0'
	when resultht in ('เสี่ยง','สูง') or resultdm in ('เสี่ยง','สูง') then '2' else '1' end as chk
from
(SELECT
person.pcucodeperson,
person.pid,
person.idcard,
person.fname,
CONCAT(ctitle.titlename,person.fname,' ',person.lname) AS pname,
house.hno,
house.villcode,
village.villname,
house.xgis,
house.ygis,
person.birth,
person.typelive,
FLOOR((TO_DAYS(NOW())-TO_DAYS(person.birth))/365.25) AS age
FROM
village
INNER JOIN house ON village.pcucode = house.pcucode AND village.villcode = house.villcode
Inner Join person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
Inner Join cstatus ON person.marystatus = cstatus.statuscode
Inner Join ctitle ON person.prename = ctitle.titlecode
WHERE $ect2 ((person.dischargetype is null) or (person.dischargetype = '9')) AND
				SUBSTRING(house.villcode,7,2) <> '00' ORDER BY house.villcode,house.hno*1
) as per
left join 
(SELECT 
person.pcucodeperson as pcucodeperson1,
person.pid as pid1,
ncd_person_ncd_screen.screen_date, ncd_person_ncd_screen.bmi, ncd_person_ncd_screen.weight, ncd_person_ncd_screen.height, ncd_person_ncd_screen.waist, ncd_person_ncd_screen.hbp_s1,
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
where $ect2 ((person.dischargetype is null) or (person.dischargetype = '9'))  and SUBSTRING(house.villcode,7,2) <> '00' and ncd_person_ncd_screen.screen_date BETWEEN '$str' AND '$sto' 
							ORDER BY
							house.villcode,house.hno*1
) as fp
on per.pcucodeperson = fp.pcucodeperson1 and per.pid = fp.pid1
where $live_type2 $gage $risk2 $gchronic
ORDER BY villcode, fname";

$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>รายชื่อประชาชน';
$txt .= "$gagename ที่ได้รับการคัดกรองโรคเบาหวานและความดันโลหิตสูง</b><br><b> $mu </b></p>ประชากร $live_type_name   $gchronic_name<br><b>$hosp</b><table width='98%' border='0' cellspacing='1' cellpadding='1' class='table table-striped table-hover table-bordered'>
  <tr>
    <th width='3%' scope='col'><div align='center'>ลำดับ</div></th>
	<th width='8%' scope='col'><div align='center'>เลขบัตรประชาชน</div></th>
    <th width='9%' scope='col'><div align='center'>ชื่อ - สกุล</div></th>
	<th width='3%' scope='col'><div align='center'>อาย</div>ุ</th>
	<th width='7%' scope='col'><div align='center'>ที่อยู่</div></th>
	<th width='6%' scope='col'><div align='center'>วันที่คัดกรอง</div></th>
    <th width='4%' scope='col'><div align='center'>BMI</div></th>
	<th width='7%' scope='col'><div align='center'>BP1/BP2</div></th>
    <th width='5%' scope='col'><div align='center'>ระดับน้ำตาล</div></th>
	<th width='5%' scope='col'><div align='center'>รอบเอว</div></th>
	<th width='8%' scope='col'><div align='center'>สรุปผลเบาหวาน</div></th>
	<th width='8%' scope='col'><div align='center'>สรุปผลความดันโลหิต</div></th>
	<th width='8%' scope='col'><div align='center'>สรุปผลรอบเอว</div></th>
  </tr>";
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$sick = retDatets($row[screen_date]);
	$bmi = number_format($row[bmi], 2, '.', '');
++$x;
	if(($x%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$x</div></td>
	<td><div align='center'>$row[idcard]</div></td>
    <td>$row[pname]</td>
	<td><div align='center'>$row[age]</div></td>
	<td><div align='right'>$row[hno] หมู่ที่ $moo</div></td>
    <td><div align='center'>$sick</div></td>
    <td><div align='center'>$bmi</div></td>
	<td>$row[hbp_s1]/$row[hbp_d1],$row[hbp_s2]/$row[hbp_d2]</td>
	<td><div align='center'>$row[bsl]</div></td>
    <td><div align='center'>$row[waist]</div></td>
	<td><div align='center'>$row[resultdm]</div></td>
	<td><div align='center'>$row[resultht]</div></td>
	<td><div align='center'>$row[resultwaist]</div></td>
  </tr>";
}
$txt .= "</table><p div align='right' class='text-danger'>ข้อมูลระหว่างวันที่  $strx ถึง $stox </p></div><br>";  
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
