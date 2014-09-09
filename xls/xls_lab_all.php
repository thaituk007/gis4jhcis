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
if($op === 'pregtest'){
	pregtest();
}else if($op === 'epi'){
    epi();
}else if($op === 'anc'){
    anc();
}else if($op === 'fp'){
    fp();
}
function pregtest(){ //function นัด pregtest
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
$village = $_GET[village];
if($village == "00000000"){
	$wvill = "";
}else{
	$wvill = " AND house.villcode='$village' ";	
}
if($village == "00000000"){
	$mu = "ทุกหมู่บ้าน";
}else{
	$mu = getvillagename($village);	
}
$str = retdaterangstr($_GET[str]);
$sto = retdaterangsto($_GET[str]);
$strx = retDatets($str);
$stox = retDatets($sto);
$sql = "SELECT
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
visitfp.pregtest,
visitfp.pregtestresult,
visit.username
FROM
house
INNER JOIN person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
LEFT JOIN ctitle on ctitle.titlecode = person.prename
INNER JOIN visit ON person.pcucodeperson = visit.pcucodeperson AND person.pid = visit.pid
INNER JOIN visitfp ON visit.pcucodeperson = visitfp.pcucodeperson AND visit.pid = visitfp.pid AND visit.visitdate = visitfp.datefp
where visitfp.pregtest = '17' and visit.visitdate between '$str' and '$sto' $wvill
order by visit.visitdate,person.fname";

$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>รายงานการตรวจทดสอบการตั้งครรภ์<br>';
$txt .= "ข้อมูลวันที่ $strx ถึง $stox $mu</b></p><b>$hosp</b><table width='99%' border='0' cellspacing='1' cellpadding='1' class='table table-striped table-hover table-bordered'>
  <tr>
    <th width='4%' scope='col'><div align='center'>ลำดับ</th>
	<th width='5%' scope='col'><div align='center'>HN</th>
    <th width='10%' scope='col'><div align='center'>ชื่อ - สกุล</th>
	<th width='5%' scope='col'><div align='center'>อายุ</th>
    <th width='6%' scope='col'><div align='center'>บ้านเลขที่</th>
	<th width='4%' scope='col'><div align='center'>หมู่ที่</th>
    <th width='4%' scope='col'><div align='center'>วันที่ตรวจ</th>
	<th width='9%' scope='col'><div align='center'>ผลการตรวจ</th>
	<th width='9%' scope='col'><div align='center'>ผู้ตรวจ</th>
  </tr>";
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$userv = getusername($row[username]);
	if($row[pregtestresult] == "0"){$pregtestname = "ไม่ตั้งครรภ์";}elseif($row[pregtestresult] == "1"){$pregtestname = "ตั้งครรภ์";}elseif($row[pregtestresult] == "3"){$pregtestname = "แปลผลไม่ได้";}else{$pregtestname = "";}
	if($row[visitdate] == ""){$appsick = "";}else{$appsick = retDatets($row[visitdate]);}
++$i;
	if(($i%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$i</div></td>
	<td><div align='center'>$row[pid]</div></td>
    <td>$row[pname]</td>
	<td><div align='center'>$row[age]</div></td>
    <td><div align='center'>$row[hno]</div></td>
    <td><div align='center'>$moo</div></td>
	<td><div align='center'>$appsick</div></td>
	<td><div align='center'>$pregtestname</div></td>
	<td><div align='left'>$userv</div></td>
  </tr>";
}
$txt .= "</table><br>";  
echo $txt;
}
?>
</body>
</html>