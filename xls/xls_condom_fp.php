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
$str = retdaterangstr($_GET[str]);
$sto = retdaterangsto($_GET[str]);
$strx = retDatets($str);
$stox = retDatets($sto);	
$sql = "SELECT person.idcard
,CONVERT(concat(ifnull(c.titlename,ifnull(person.prename,'ไม่ระบุ') ),person.fname,' ' ,person.lname) using utf8) as pname 
,DATE_FORMAT(visitdate,'%Y-%m-%d') as visitdate
,office.offid
,v.pcucode
,if(v.pcucode = office.offid,'หน่วยบริการ' ,'ที่อื่น ' ) as pcu
,drugname
,CONVERT(concat(c.titlename,u.fname,' ' ,u.lname) using utf8) as uname
,curdate() as cdate
,visitfp.dateupdate,
house.hno,
house.villcode,
house.xgis,
house.ygis,
visitfp.unit,
cdrugunitsell.unitsellname
FROM person left join ctitle on person.prename = ctitle.titlecode
	left join visit  v on person.pid = v.pid  and person.pcucodeperson = v.pcucodeperson
	left join visitfp on v.visitno = visitfp.visitno and v.pcucode = visitfp.pcucode
	left join cdrug on visitfp.fpcode = cdrug.drugcode
	left join cdrugunitsell on cdrug.unitsell = cdrugunitsell.unitsellcode
	left join user u on v.username = u.username
	left join ctitle c on c.titlecode = u.prename
	left join office on v.pcucode = office.offid
	Inner Join house ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
WHERE    cdrug.drugcode like'CONDOM%'   
 	and visitdate between '$str' and '$sto' and (v.flagservice <'04' OR v.flagservice is null OR length(trim(v.flagservice))=0 )
GROUP BY v.visitno,v.pcucodeperson
ORDER BY house.villcode,v.visitdate";

$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>รายชื่อผู้ได้รับถุงยางอนามัยในงานวางแผนครอบครัว<br>';
$txt .= "ระหว่างวันที่  $strx ถึงวันที่ $stox</p><br>$hosp</b><br><table width='99%' border='0' cellspacing='1' cellpadding='1' class='table table-striped table-hover table-bordered'>
  <tr>
    <th width='5%' scope='col'>ลำดับ</th>
	<th width='13%' scope='col'>เลขบัตรประชาชน</th>
    <th width='16%' scope='col'>ชื่อ - สกุล</th>
	<th width='13%' scope='col'>ที่อยู่</th>
	<th width='10%' scope='col'>วันที่ใช้บริการ</th>
	<th width='16%' scope='col'>ยาสมุนไพร</th>
    <th width='16%' scope='col'>ผู้ให้บริการ</th>
	<th width='12%' scope='col'>จำนวน</th>
    <th width='10%' scope='col'>วันที่บันทึกข้อมูล</th>
  </tr>";
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$sick = retDatets($row[visitdate]);
	$dupdate = retDatets($row[dateupdate]);
++$x;
	if(($x%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$x</div></td>
	<td>$row[idcard]</td>
    <td>$row[pname]</td>
	<td>$row[hno] หมู่ที่ $moo</td>
    <td>$sick</td>
    <td>$row[drugname]</td>
	<td>$row[uname]</td>
	<td><div align='center'>$row[unit]&nbsp;&nbsp;$row[unitsellname]</td>
    <td>$dupdate</td>
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
