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
include("../includes/conndb.php"); 
include("../includes/config.inc.php");
$op = $_GET['app_type'];
if($op === 'chronic'){
	chronic();
}else if($op === 'epi'){
    epi();
}else if($op === 'anc'){
    anc();
}else if($op === 'fp'){
    fp();
}
function chronic(){ //function นัด เบาหวานความดัน
$village = $_GET[village];
if($village == "00000000"){
	$wvill = "";
}else{
	$wvill = " AND perapp.villcode='$village' ";	
}
if($village == "00000000"){
	$mu = "ทุกหมู่บ้าน";
}else{
	$mu = getvillagename($village);	
}
$chk_ncd = $_GET[chk_ncd];
if($chk_ncd == "2"){
	$chkncd = " AND vper.visitdate is not null";	
}else if($chk_ncd == "3"){
	$chkncd = " AND vper.visitdate is null";	
}else{
	$chkncd = "";	
}

$str = $_GET[str];
$strx = retDatets($str);
	
$sql = "SELECT
perapp.*,
vper.visitdate as datechk
FROM
(SELECT
person.pcucodeperson,
person.pid,
person.idcard,
person.fname,
concat(ctitle.titlename, person.fname , '  ' , person.lname) AS pname,
person.birth,
ROUND(DATEDIFF(now(),person.birth)/365.25) AS age,
house.villcode,
house.hno,
house.hcode,
house.xgis,
house.ygis,
visit.visitdate,
visitdiagappoint.appodate,
visitdiagappoint.appotype
FROM
house
INNER JOIN person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
left JOIN ctitle on person.prename = ctitle.titlecode
INNER JOIN visit ON person.pcucodeperson = visit.pcucodeperson AND person.pid = visit.pid
INNER JOIN visitdiagappoint ON visit.pcucode = visitdiagappoint.pcucode AND visit.visitno = visitdiagappoint.visitno
INNER JOIN cdisease ON visitdiagappoint.diagcode = cdisease.diseasecode
INNER JOIN cdiseasechronic ON cdisease.codechronic = cdiseasechronic.groupcode
where cdiseasechronic.groupcode in ('01','10' ) and visitdiagappoint.appodate = '$str'
GROUP BY visit.pcucodeperson,visit.pid
ORDER BY visitdiagappoint.appodate,house.villcode) as perapp
left JOIN (SELECT visit.* FROM visit WHERE visit.visitdate = '$str') as vper 
on perapp.pcucodeperson = vper.pcucodeperson and perapp.pid = vper.pid
where perapp.pcucodeperson is not null $chkncd $wvill
order by perapp.villcode,perapp.fname";

$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>รายงานการนัดวางแผนครอบครัว';
$txt .= "ข้อมูลการนัดวันที่ $_GET[str] $mu</b></p><br><b>$hosp</b><table width='99%' border='0' cellspacing='1' cellpadding='1' class='table table-striped table-hover table-bordered'>
  <tr>
    <th width='4%' scope='col'><div align='center'>ลำดับ</div></th>
	<th width='10%' scope='col'><div align='center'>เลขบัตรประชาชน</div></th>
    <th width='10%' scope='col'><div align='center'>ชื่อ - สกุล</div></th>
	<th width='5%' scope='col'><div align='center'>อายุ</div></th>
    <th width='6%' scope='col'><div align='center'>บ้านเลขที่</div></th>
	<th width='4%' scope='col'><div align='center'>หมู่ที่</div></th>
    <th width='4%' scope='col'><div align='center'>วันที่นัด</div></th>
	<th width='9%' scope='col'><div align='center'>ประเภทการนัด</div></th>
	<th width='9%' scope='col'><div align='center'>##</div></th>
	<th width='9%' scope='col'><div align='center'>วันที่มารับบริการ</div></th>
	
  </tr>";
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$title = getTitle($row[prename]);
	
	if($row[appotype] == "1"){$apptypename = "รับยา";}elseif($row[appotype] == "2"){$apptypename = "ฟังผล";}elseif($row[appotype] == "3"){$apptypename = "ทำแผล";}elseif($row[appotype] == "4"){$apptypename = "เจาะเลือด";}elseif($row[appotype] == "5"){$apptypename = "ตรวจน้ำตาล(DTX)";}elseif($row[appotype] == "6"){$apptypename = "วัดความดันฯ";}else{$apptypename = retDatets($row[appodate]);}
	if($row[appodate] == ""){$appsick = "";}else{$appsick = retDatets($row[appodate]);}
	if($row[datechk] == ""){$sick = "";}else{$sick = retDatets($row[datechk]);}
	if($row[datechk] == ""){$sicksign = "ขาดนัด";}else{$sicksign = "มาตามนัด";}
++$i;
	if(($i%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$i</div></td>
	<td><div align='center'>$row[idcard]</div></td>
    <td>$row[pname]</td>
	<td><div align='center'>$row[age]</div></td>
    <td><div align='center'>$row[hno]</div></td>
    <td><div align='center'>$moo</div></td>
	<td><div align='center'>$appsick</div></td>
	<td><div align='center'>$apptypename</div></td>
	<td><div align='center'>$sicksign</div></td>
	<td><div align='center'>$sick</div></td>
  </tr>";
}
$txt .= "</table><br>";  
echo $txt;
}

function epi(){ //function นัด epi
$village = $_GET[village];
if($village == "00000000"){
	$wvill = "";
}else{
	$wvill = " AND perapp.villcode='$village' ";	
}
if($village == "00000000"){
	$mu = "ทุกหมู่บ้าน";
}else{
	$mu = getvillagename($village);	
}
$chk_ncd = $_GET[chk_ncd];
if($chk_ncd == "2"){
	$chkncd = " AND vper.visitdate is not null";	
}else if($chk_ncd == "3"){
	$chkncd = " AND vper.visitdate is null";	
}else{
	$chkncd = "";	
}

$str = $_GET[str];
$strx = retDatets($str);
	
$sql = "SELECT
perapp.*,
vper.visitdate as datechk
FROM
(SELECT
person.pcucodeperson,
person.pid,
person.idcard,
person.fname,
concat(ctitle.titlename, person.fname , '  ' , person.lname) AS pname,
person.birth,
ROUND(DATEDIFF(now(),person.birth)/30.44) AS age,
house.villcode,
house.hno,
house.hcode,
house.xgis,
house.ygis,
visitepiappoint.dateappoint,
GROUP_CONCAT(visitepiappoint.vaccinecode) as vaccinex
FROM
house
INNER JOIN person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
left JOIN ctitle on person.prename = ctitle.titlecode
INNER JOIN visitepiappoint on person.pcucodeperson = visitepiappoint.pcucode and person.pid = visitepiappoint.pid
where visitepiappoint.dateappoint = '$str'
GROUP BY visitepiappoint.pcucodeperson,visitepiappoint.pid
ORDER BY visitepiappoint.dateappoint,house.villcode) as perapp
left JOIN (SELECT visit.* FROM visit WHERE visit.visitdate = '$str') as vper 
on perapp.pcucodeperson = vper.pcucodeperson and perapp.pid = vper.pid
where perapp.pcucodeperson is not null $chkncd $wvill
order by perapp.villcode,perapp.fname";

$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>รายงานการนัดรับวัคซีน';
$txt .= "<br>ข้อมูลการนัดวันที่ $_GET[str] $mu</b></p><br><b>$hosp</b><table width='99%' border='0' cellspacing='1' cellpadding='1' class='table table-striped table-hover table-bordered'>
  <tr>
    <th width='4%' scope='col'><div align='center'>ลำดับ</div></th>
	<th width='10%' scope='col'><div align='center'>เลขบัตรประชาชน</div></th>
    <th width='10%' scope='col'><div align='center'>ชื่อ - สกุล</div></th>
	<th width='5%' scope='col'><div align='center'>อายุ</div></th>
    <th width='6%' scope='col'><div align='center'>บ้านเลขที่</div></th>
	<th width='4%' scope='col'><div align='center'>หมู่ที่</div></th>
    <th width='4%' scope='col'><div align='center'>วันที่นัด</div></th>
	<th width='4%' scope='col'><div align='center'>นัดวัคซีน</div></th>
	<th width='9%' scope='col'><div align='center'>##</div></th>
	<th width='9%' scope='col'><div align='center'>วันที่มารับบริการ</div></th>
	
  </tr>";
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$title = getTitle($row[prename]);
	if($row[dateappoint] == ""){$appsick = "";}else{$appsick = retDatets($row[dateappoint]);}
	if($row[datechk] == ""){$sick = "";}else{$sick = retDatets($row[datechk]);}
	if($row[datechk] == ""){$sicksign = "ขาดนัด";}else{$sicksign = "มาตามนัด";}
++$i;
	if(($i%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$i</div></td>
	<td><div align='center'>$row[idcard]</div></td>
    <td>$row[pname]</td>
	<td><div align='center'>$row[age]</div></td>
    <td><div align='center'>$row[hno]</div></td>
    <td><div align='center'>$moo</div></td>
	<td><div align='center'>$appsick</div></td>
	<td><div align='center'>$row[vaccinex]</div></td>
	<td><div align='center'>$sicksign</div></td>
	<td><div align='center'>$sick</div></td>
  </tr>";
}
$txt .= "</table><br>";  
echo $txt;
}

function anc(){ //function นัด anc
$village = $_GET[village];
if($village == "00000000"){
	$wvill = "";
}else{
	$wvill = " AND perapp.villcode='$village' ";	
}
if($village == "00000000"){
	$mu = "ทุกหมู่บ้าน";
}else{
	$mu = getvillagename($village);	
}
$chk_ncd = $_GET[chk_ncd];
if($chk_ncd == "2"){
	$chkncd = " AND vper.visitdate is not null";	
}else if($chk_ncd == "3"){
	$chkncd = " AND vper.visitdate is null";	
}else{
	$chkncd = "";	
}

$str = $_GET[str];
$strx = retDatets($str);
	
$sql = "SELECT
perapp.*,
vper.visitdate as datechk
FROM
(
SELECT
person.pcucodeperson,
person.pid,
person.idcard,
person.fname,
concat(ctitle.titlename, person.fname , '  ' , person.lname) AS pname,
person.birth,
ROUND(DATEDIFF(now(),person.birth)/365.25) AS age,
house.villcode,
house.hno,
house.hcode,
house.xgis,
house.ygis,
visitanc.pregage,
visitanc.datecheck,
visitanc.dateappointcheck
FROM
house
INNER JOIN person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
INNER JOIN visitanc ON person.pcucodeperson = visitanc.pcucodeperson AND person.pid = visitanc.pid
LEFT JOIN ctitle ON person.prename = ctitle.titlecode
where visitanc.dateappointcheck = '$str') as perapp
left JOIN (SELECT visit.* FROM visit WHERE visit.visitdate = '$str') as vper 
on perapp.pcucodeperson = vper.pcucodeperson and perapp.pid = vper.pid
where perapp.pcucodeperson is not null $chkncd $wvill
order by perapp.villcode,perapp.fname";

$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>รายงานการนัดฝากครรภ์';
$txt .= "<br>ข้อมูลการนัดวันที่ $_GET[str] $mu</b></p><br><b>$hosp</b><table width='99%' border='0' cellspacing='1' cellpadding='1' class='table table-striped table-hover table-bordered'>
  <tr>
    <th width='4%' scope='col'><div align='center'>ลำดับ</div></th>
	<th width='10%' scope='col'><div align='center'>เลขบัตรประชาชน</div></th>
    <th width='10%' scope='col'><div align='center'>ชื่อ - สกุล</div></th>
	<th width='5%' scope='col'><div align='center'>อายุ</div></th>
    <th width='6%' scope='col'><div align='center'>บ้านเลขที่</div></th>
	<th width='4%' scope='col'><div align='center'>หมู่ที่</div></th>
    <th width='4%' scope='col'><div align='center'>วันที่นัด</div></th>
	<th width='9%' scope='col'><div align='center'>อายุครรภ์(สัปดาห์)</div></th>
	<th width='9%' scope='col'><div align='center'>##</div></th>
	<th width='9%' scope='col'><div align='center'>วันที่มารับบริการ</div></th>
	
  </tr>";
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$title = getTitle($row[prename]);
	if($row[dateappointcheck] == ""){$appsick = "";}else{$appsick = retDatets($row[dateappointcheck]);}
	if($row[datechk] == ""){$sick = "";}else{$sick = retDatets($row[datechk]);}
	if($row[datechk] == ""){$sicksign = "ขาดนัด";}else{$sicksign = "มาตามนัด";}
++$i;
	if(($i%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$i</div></td>
	<td><div align='center'>$row[idcard]</div></td>
    <td>$row[pname]</td>
	<td><div align='center'>$row[age]</div></td>
    <td><div align='center'>$row[hno]</div></td>
    <td><div align='center'>$moo</div></td>
	<td><div align='center'>$appsick</div></td>
	<td><div align='center'>$row[pregage]</div></td>
	<td><div align='center'>$sicksign</div></td>
	<td><div align='center'>$sick</div></td>
  </tr>";
}
$txt .= "</table><br>";  
echo $txt;
}

 
 function fp(){ //function นัด fp
$village = $_GET[village];
if($village == "00000000"){
	$wvill = "";
}else{
	$wvill = " AND perapp.villcode='$village' ";	
}
if($village == "00000000"){
	$mu = "ทุกหมู่บ้าน";
}else{
	$mu = getvillagename($village);	
}
$chk_ncd = $_GET[chk_ncd];
if($chk_ncd == "2"){
	$chkncd = " AND vper.visitdate is not null";	
}else if($chk_ncd == "3"){
	$chkncd = " AND vper.visitdate is null";	
}else{
	$chkncd = "";	
}

$str = $_GET[str];
$strx = retDatets($str);
	
$sql = "SELECT
perapp.*,
vper.visitdate as datechk
FROM
(
SELECT
person.pcucodeperson,
person.pid,
person.idcard,
person.fname,
concat(ctitle.titlename, person.fname , '  ' , person.lname) AS pname,
person.birth,
ROUND(DATEDIFF(now(),person.birth)/365.25) AS age,
house.villcode,
house.hno,
house.hcode,
house.xgis,
house.ygis,
visitfp.datedue,
cdrug.drugname,
cdrug.drugtypesub,
visitfp.datefp
FROM
house
INNER JOIN person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
INNER JOIN visitfp ON person.pcucodeperson = visitfp.pcucodeperson AND person.pid = visitfp.pid
inner JOIN cdrug on visitfp.fpcode = cdrug.drugcode
LEFT JOIN ctitle ON person.prename = ctitle.titlecode
where visitfp.datedue = '$str') as perapp
left JOIN (SELECT visit.* FROM visit WHERE visit.visitdate = '$str') as vper 
on perapp.pcucodeperson = vper.pcucodeperson and perapp.pid = vper.pid
where perapp.pcucodeperson is not null $chkncd $wvill
order by perapp.villcode,perapp.fname";

$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>รายงานการนัดวางแผนครอบครัว';
$txt .= "<br>ข้อมูลการนัดวันที่ $_GET[str] $mu</b></p><br><b>$hosp</b><table width='99%' border='0' cellspacing='1' cellpadding='1' class='table table-striped table-hover table-bordered'>
  <tr>
    <th width='4%' scope='col'><div align='center'>ลำดับ</div></th>
	<th width='10%' scope='col'><div align='center'>เลขบัตรประชาชน</div></th>
    <th width='10%' scope='col'><div align='center'>ชื่อ - สกุล</div></th>
	<th width='5%' scope='col'><div align='center'>อายุ</div></th>
    <th width='6%' scope='col'><div align='center'>บ้านเลขที่</div></th>
	<th width='4%' scope='col'><div align='center'>หมู่ที่</div></th>
    <th width='4%' scope='col'><div align='center'>วันที่นัด</div></th>
	<th width='4%' scope='col'><div align='center'>ประเภทการนัด</div></th>
	<th width='9%' scope='col'><div align='center'>##</div></th>
	<th width='9%' scope='col'><div align='center'>วันที่มารับบริการ</div></th>
	
  </tr>";
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$title = getTitle($row[prename]);
	
	if($row[drugtypesub] == "0"){$apptypename = "วชย.ทดสอบตั้งครรภ์";}elseif($row[drugtypesub] == "1"){$apptypename = "ยาเม็ด";}elseif($row[drugtypesub] == "2"){$apptypename = "ยาฉีด";}elseif($row[drugtypesub] == "3"){$apptypename = "ยาฝัง";}elseif($row[drugtypesub] == "4"){$apptypename = "ห่วง";}elseif($row[drugtypesub] == "5"){$apptypename = "ถุงยางอนามัย";}elseif($row[drugtypesub] == "6"){$apptypename = "หมันชาย";}else{$apptypename = "หมันหญิง";}
	if($row[datedue] == ""){$appsick = "";}else{$appsick = retDatets($row[datedue]);}
	if($row[datechk] == ""){$sick = "";}else{$sick = retDatets($row[datechk]);}
	if($row[datechk] == ""){$sicksign = "ขาดนัด";}else{$sicksign = "มาตามนัด";}
++$i;
	if(($i%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$i</div></td>
	<td><div align='center'>$row[idcard]</div></td>
    <td>$row[pname]</td>
	<td><div align='center'>$row[age]</div></td>
    <td><div align='center'>$row[hno]</div></td>
    <td><div align='center'>$moo</div></td>
	<td><div align='center'>$appsick</div></td>
	<td><div align='center'>$apptypename</div></td>
	<td><div align='center'>$sicksign</div></td>
	<td><div align='center'>$sick</div></td>
  </tr>";
}
$txt .= "</table><br>";  
echo $txt;
}
?>       
</body>
</html>
