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
	$wvill = " AND h.villcode='$villcode' ";	
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
$sql = "select
pname,
hno,
villcode,
xgis,
ygis,
birth,
age,
fptype,
if(fpname is null,'ไม่ระบุ',fpname) as fpname,
datesurvey,
statusname,
reasonnofp1
from
(SELECT
p.pcucodeperson,
p.pid,
CONCAT(ctitle.titlename,p.fname,' ',p.lname) AS pname,
h.hno,
h.villcode,
h.xgis,
h.ygis,
p.birth,
FLOOR((TO_DAYS(NOW())-TO_DAYS(p.birth))/365.25) AS age,
cstatus.statusname
FROM
house AS h
Inner Join person AS p ON h.pcucode = p.pcucodeperson AND h.hcode = p.hcode
Inner Join cstatus ON p.marystatus = cstatus.statuscode
Inner Join ctitle ON p.prename = ctitle.titlecode
WHERE p.sex = '2' and ((p.dischargetype is null) or (p.dischargetype = '9')) AND
				SUBSTRING(h.villcode,7,2) <> '00' AND
				(FLOOR((TO_DAYS(NOW())-TO_DAYS(p.birth))/365.25) between 20 and 44) and p.marystatus in ('f','2') $wvill ORDER BY h.villcode,h.hno*1) as per
left join 
(SELECT
women.pcucodeperson as pcucodeperson1,
women.pid as pid1,
women.fptype,
if(women.fptype is null,null,if(women.fptype = '1','ยาเม็ด',if(women.fptype = '2','ยาฉีด',if(women.fptype = '3','ห่วงอนามัย',if(women.fptype = '4','ยาฝัง',if(women.fptype = '5','ถุงยางอนามัย',if(women.fptype = '6','หมันชาย',if(women.fptype = '7','หมันหญิง','ไม่ได้คุม')))))))) as fpname,
women.reasonnofp,
women.childalive,
women.motherpomotion,
women.datesurvey,
women.flag18fileexpo,
women.dateupdate,
if(women.fptype = '0',if(women.reasonnofp = '1','ต้องการบุตร',if(women.reasonnofp = '2','หมันธรรมชาติ',if(women.reasonnofp = '3','อื่นๆ',null))),null) as reasonnofp1
FROM
women
where women.datesurvey between '$str' and '$sto') as fp
on per.pcucodeperson = fp.pcucodeperson1 and per.pid = fp.pid1
order by villcode, hno*1";

$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>หญิงอายุ 20 - 49 ปี วางแผนครอบครัว</b><br>';
$txt .= "<b>ข้อมูลระหว่างวันที่ $strx ถึง $stox หมู่ที่ $mu </b></p><br><b>$hosp</b><table width='95%' border='0' cellspacing='1' cellpadding='1' class='table table-striped table-hover table-bordered'>
  <tr>
    <th width='8%' scope='col'><div align='center'>ลำดับ</div></th>
    <th width='22%' scope='col'><div align='center'>ชื่อ - สกุล</div></th>
	<th width='8%' scope='col'><div align='center'>อายุ</div></th>
    <th width='8%' scope='col'><div align='center'>บ้านเลขที่</div></th>
    <th width='8%' scope='col'><div align='center'>หมู่ที่</div></th>
    <th width='12%' scope='col'><div align='center'>สถานะภาพ</div></th>
	<th width='12%' scope='col'><div align='center'>วิธีคุมกำเนิด</div></th>
	<th width='12%' scope='col'><div align='center'>สาเหตุที่ไม่คุมกำเนิด</div></th>
	<th width='12%' scope='col'><div align='center'>วันที่สำรวจ</div></th>
  </tr>";
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$title = getTitle($row[prename]);
++$i;
	if(($i%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$i</div></td>
    <td>$title$row[pname]</td>
	<td>&nbsp;$row[age]</td>
    <td>&nbsp;$row[hno]</td>
    <td>&nbsp;$moo</td>
    <td>&nbsp;$row[statusname]</td>
	<td>&nbsp;$row[fpname]</td>
	<td>&nbsp;$row[reasonnofp1]</td>
	<td>&nbsp;$row[datesurvey]</td>
  </tr>";
}
$txt .= "</table><br>";  
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
