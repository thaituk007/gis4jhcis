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
$villcode = $_GET[village];
if($villcode == "00000000"){
	$wvill = "";
}else{
	$wvill = " AND house.villcode='$villcode' ";	
}
$chk_ncd = $_GET[chk_ncd];
if($chk_ncd == "1"){
	$chksto = "";
}elseif($chk_ncd == "2"){
	$chksto = "where chk = 1 or chk = 0";	
}elseif($chk_ncd == "3"){
	$chksto = "where chk = 1";	
}else{
	$chksto = "where chk is null";
}
if($villcode == "00000000"){
	$mu = "ทุกหมู่บ้าน";
}else{
	$mu = getvillagename($villcode);	
}
$str = retdaterangstr($_GET[str]);
$sto = retdaterangsto($_GET[str]);
$strx = retDatets($str);
$stox = retDatets($sto);
$sql = "select *
from
(select
person.pcucodeperson,
person.pid,
person.fname, 
concat(ctitle.titlename, person.fname , '  ' , person.lname) AS pname,
person.birth,
ROUND(DATEDIFF(now(),person.birth)/365.25) AS age,
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
where ROUND(DATEDIFF(now(),person.birth)/365.25) > 14 and SUBSTRING(house.villcode,7,2) <> '00' AND (person.dischargetype Is Null Or person.dischargetype='9') $wvill) as tmp_per
left join
(select 
ncd_person_ncd_screen.pcucode,
ncd_person_ncd_screen.pid,
ncd_person_ncd_screen.screen_date,
ncd_person_ncd_screen.weight,
ncd_person_ncd_screen.height,
ncd_person_ncd_screen.bmi,
ncd_person_ncd_screen.waist,
if(ncd_person_ncd_screen.waist is null,null,if( (person.sex='1' and ncd_person_ncd_screen.waist >89 ) or (person.sex='2' and ncd_person_ncd_screen.waist >79),1,0)) as chk
FROM  ncd_person_ncd_screen
inner join person on ncd_person_ncd_screen.pcucode = person.pcucodeperson and ncd_person_ncd_screen.pid = person.pid
where ncd_person_ncd_screen.screen_date between '$str' and '$sto') as tmp_ncd
ON tmp_per.pcucodeperson = tmp_ncd.pcucode AND tmp_per.pid = tmp_ncd.pid
$chksto
order by villcode, fname";

$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>รายงานประชาชนอายุ 15 ปีขึ้นไป ที่ได้รับการวัดรอบเอว</b><br>';
$txt .= "<b>$mu </b></p><br><b>$hosp</b><table width='99%' border='0' cellspacing='1' cellpadding='1' class='table table-striped table-hover table-bordered'>
  <tr>
    <th width='4%' scope='col'><div align='center'>ลำดับ</div></th>
    <th width='13%' scope='col'><div align='center'>ชื่อ - สกุล</div></th>
	<th width='8%' scope='col'><div align='center'>ว/ด/ป เกิด</div></th>
	<th width='3%' scope='col'><div align='center'>อายุ</div></th>
    <th width='4%' scope='col'><div align='center'>บ้านเลขที่</div></th>
    <th width='3%' scope='col'><div align='center'>หมู่ที่</div></th>
	<th width='4%' scope='col'><div align='center'>วันที่สำรวจ</div></th>
	<th width='10%' scope='col'><div align='center'>น้ำหนัก</div></th>
	<th width='7%' scope='col'><div align='center'>ส่วนสูง</div></th>
	<th width='7%' scope='col'><div align='center'>BMI</div></th>
	<th width='7%' scope='col'><div align='center'>วัดกรอบเอว</div></th>
	<th width='7%' scope='col'><div align='center'>ผลการวัดรอบเอว</div></th>
  </tr>";
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	if($row[chk] == "0"){$waist_chk = 'รอบเอวปกติ';}elseif($row[chk] == "1"){$waist_chk = 'รอบเอวเกิน';}else{$waist_chk = 'ยังไม่ได้วัดรอบเอว';}
	$birth = retDatets($row[birth]);
	if($row[screen_date] == ""){$screen_date = "";}else{$screen_date = retDatets($row[screen_date]);}
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
    <td><div align='center'>&nbsp;$screen_date</div></td>
	<td><div align='center'>&nbsp;$row[weight]</div></td>
	<td><div align='center'>&nbsp;$row[height]</div></td>
	<td><div align='center'>&nbsp;$bmi</div></td>
	<td><div align='center'>&nbsp;$row[waist]</div></td>
	<td><div align='center'>&nbsp;$waist_chk</div></td>
  </tr>";
}
$txt .= "</table><p div align='right' class='text-danger'>ข้อมูลระหว่างวันที่  $strx ถึง $stox </p></div><br>";  
echo $txt;
?>
<?php
}
else{
		header("Location: ../main/login.php");
		}
		?>
        
</body>
</html>
