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
if($villcode == "00000000"){
	$mu = "ทุกหมู่บ้าน";
}else{
	$mu = getvillagename($villcode);	
}
$chk_old = $_GET[chk_old];
if($chk_old == "8"){
	$chksto = "where vepi.pid is not null";
}elseif($chk_old == "1"){
	$chksto = "where vepi.pid is null";
}else{
	$chksto = "";	
}
$str = retdaterangstr($_GET[str]);
$sto = retdaterangsto($_GET[str]);
$strx = retDatets($str);
$stox = retDatets($sto);
$live_type = $_GET[live_type];
if($live_type == '2'){$live_type_name = "ตามทะเบียนบ้าน(0,1,2)";}elseif($live_type == '1'){$live_type_name = "ที่อาศัยอยู่จริง (0,1,3)";}else{$live_type_name = "ทั้งหมดในเขตรับผิดชอบ(0,1,2,3)";}	
if($live_type == '2'){$live_type2 = "and person.typelive in ('0','1','2')";}elseif($live_type == '1'){$live_type2 = "and person.typelive in ('0','1','3')";}else{$live_type2 = "and person.typelive in ('0','1','2','3')";}		
$sql = "SELECT
pepi.pcucodeperson,
pepi.villcode,
count(DISTINCT concat(pepi.pcucodeperson,pepi.pid)) as pchild,
COUNT(DISTINCT IF(vepi.growdevelop is not NULL, CONCAT(pepi.pcucodeperson,pepi.pid), NULL)) AS devchk,
COUNT(DISTINCT IF(vepi.growdevelop = '1', CONCAT(pepi.pcucodeperson,pepi.pid), NULL)) AS dev1,
COUNT(DISTINCT IF(vepi.growdevelop = '2', CONCAT(pepi.pcucodeperson,pepi.pid), NULL)) AS dev2,
COUNT(DISTINCT IF(vepi.growdevelop = '3', CONCAT(pepi.pcucodeperson,pepi.pid), NULL)) AS dev3,
COUNT(DISTINCT IF(vepi.growdevelop is NULL, CONCAT(pepi.pcucodeperson,pepi.pid), NULL)) AS devnochk
FROM
(SELECT
person.pcucodeperson,
person.pid,
person.idcard,
concat(ifnull(titlename,'..') ,fname,' ',lname) as pname,
person.birth,
FLOOR(datediff('$str',person.birth)/30.44) as age,
house.hno,
house.villcode,
house.xgis,
house.ygis
FROM
house
INNER JOIN person on person.pcucodeperson = house.pcucode and person.hcode = house.hcode
LEFT JOIN ctitle on ctitle.titlecode = person.prename
where FLOOR(datediff('$str',person.birth)/30.44) < 72 and ((person.dischargetype is null) or (person.dischargetype = '9')) $wvill $live_type2) as pepi
left JOIN
(SELECT
visitnutrition.pcucode,
visitnutrition.visitno,
visit.pid,
visit.visitdate,
visitnutrition.growdevelop
FROM
visit
INNER JOIN visitnutrition ON visit.pcucode = visitnutrition.pcucode AND visit.visitno = visitnutrition.visitno
where visitnutrition.growdevelop is not null and visitnutrition.growdevelop <> '' and visit.visitdate BETWEEN '$str' and '$sto'
and (visit.flagservice <'04' OR visit.flagservice is null OR length(trim(visit.flagservice))=0 )) as vepi
on pepi.pcucodeperson = vepi.pcucode and pepi.pid = vepi.pid
GROUP BY pepi.pcucodeperson,pepi.villcode
order by pepi.pcucodeperson,pepi.villcode";

$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>รายงานพัฒนาการเด็กอายุ 0 - 71 เดือน<br>';
$txt .= "$mu</b></p><b>$live_type_name</b><br><b>$hosp</b><table width='98%' border='0' cellspacing='1' cellpadding='1' class='table table-striped table-hover table-bordered'>
  <tr>
    <th width='4%' scope='col'><div align='center'>ลำดับ</div></th>
    <th width='10%' scope='col'><div align='center'>หมู่บ้าน</div></th>
	<th width='4%' scope='col'><div align='center'>หมู่ที่</div></th>
	<th width='4%' scope='col'><div align='center'>เด็ก 0 - 71 เดือน</div></th>
	<th width='4%' scope='col'><div align='center'>ได้รับการตรวจ</div></th>
    <th width='4%' scope='col'><div align='center'>ร้อยละ</div></th>
	<th width='4%' scope='col'><div align='center'>ปกติ</div></th>
	<th width='4%' scope='col'><div align='center'>สงสัยล่าช้า</div></th>
	<th width='4%' scope='col'><div align='center'>ล่าช้า</div></th>
	<th width='4%' scope='col'><div align='center'>ไม่ได้ตรวจ</div></th>
  </tr>";
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$villname = getvillagename($row[villcode]);
if($row[pchild] == "0"){
	$percen = "0";
}else{
	$percen = ($row[devchk])/($row[pchild])*100;	
}
	$percent1 = number_format($percen, 2, '.', '');
	$sumpchild = $sumpchild+$row[pchild];
	$sumdevchk = $sumdevchk+$row[devchk];
	$sumdev1 = $sumdev1+$row[dev1];
	$sumdev2 = $sumdev2+$row[dev2];
	$sumdev3 = $sumdev3+$row[dev3];
	$sumdevnochk = $sumdevnochk+$row[devnochk];
if($sumpchild == "0"){
	$percenx = "0";
}else{
	$percenx = ($sumdevchk)/($sumpchild)*100;	
}
	$percent1x = number_format($percenx, 2, '.', '');
++$i;
	if(($i%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$i</div></td>
    <td>$villname</td>
    <td><div align='center'>&nbsp;$moo</div></td>
	<td><div align='center'>$row[pchild]</div></td>
	<td><div align='center'>$row[devchk]</div></td>
    <td><div align='center'>$percent1</div></td>
	<td><div align='center'>$row[dev1]</div></td>
	<td><div align='center'>$row[dev2]</div></td>
	<td><div align='center'>$row[dev3]</div></td>
	<td><div align='center'>$row[devnochk]</div></td>
  </tr>
  ";
}
$txt .= "<tr>
  	<td>&nbsp;&nbsp;</td>
  	<td><div align='center'>&nbsp;รวม</td>
  	<td>&nbsp;&nbsp;</td>
	<td><div align='center'>$sumpchild</div></td>
	<td><div align='center'>$sumdevchk</div></td>
  	<td><div align='center'>$percent1x</div></td>
	<td><div align='center'>$sumdev1</div></td>
	<td><div align='center'>$sumdev2</div></td>
	<td><div align='center'>$sumdev3</div></td>
	<td><div align='center'>$sumdevnochk</div></td>
  </tr></table><p div align='right' class='text-danger'>ข้อมูลระหว่างวันที่  $strx ถึง $stox </p></div><br>";
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
