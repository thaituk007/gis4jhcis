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
,CONVERT(concat(ifnull(titlename,ifnull(prename,'ไม่ระบุ') ),fname,' ',lname) USING utf8) as pname 
,v.pid
       ,CONVERT(case when person.subdistcodemoi is null  then 'นอกเขต' 
              when person.hnomoi is null then concat(' หมู่ที่ ',  person.`mumoi` ,' ต.',  csd.`subdistname` )
              when person.mumoi is null then concat(person.`hnomoi`  ,' ต.',  csd.`subdistname` )
              else concat(person.`hnomoi` ,' หมู่ที่ ',  person.`mumoi` ,' ต.',  csd.`subdistname` ) end   USING utf8)  AS address
       ,v.rightcode,rightname,v.visitno,v.pcucode,v.visitdate,chospital.hosname,
	   GROUP_CONCAT(concat(cdrug.drugname,'<br>')) as drugname,
	   GROUP_CONCAT(visitdrug.unit) as unit,
	   GROUP_CONCAT(visitdrug.dateupdate) as dateupdate,
	   v.username,
house.hno,
house.villcode,
house.xgis,
house.ygis
from visit v left join person on v.pid = person.pid and v.pcucodeperson = person.pcucodeperson
	left join ctitle on person.prename = ctitle.titlecode
        left join cright on v.rightcode = cright.rightcode
        left join house on person.hcode = house.hcode and person.pcucodeperson = house.pcucode
        left join village on house.villcode = village.villcode and house.pcucode = village.pcucode
        left join csubdistrict csd on csd.provcode = left(village.villcode,2) and csd.distcode = substring(village.villcode,3,2) and csd.subdistcode = substring(village.villcode,5,2)
	left join chospital on v.pcucode = chospital.hoscode
        left join visitdrug on v.visitno = visitdrug.visitno and v.pcucode = visitdrug.pcucode
        left join cdrug on visitdrug.drugcode = cdrug.drugcode
WHERE    cdrug.drugtype='10'    
 	and visitdate between '$str' and '$sto' and (v.flagservice <'04' OR v.flagservice is null OR length(trim(v.flagservice))=0 )
group by v.visitno,v.pcucode
order by visitdate,village.villcode";

$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>รายงานผู้รับบริการที่ได้รับยาสมุนไพร<br>';
$txt .= "<p div align='center' class='text-danger'>ข้อมูลระหว่างวันที่  $strx ถึง $stox </p></div></p><br>$hosp</b><br><table width='99%' border='0' cellspacing='1' cellpadding='1' class='table table-striped table-hover table-bordered'>
  <tr>
    <th width='5%' scope='col'>ลำดับ</th>
	<th width='11%' scope='col'>เลขบัตรประชาชน</th>
    <th width='11%' scope='col'>ชื่อ - สกุล</th>
	<th width='7%' scope='col'>ที่อยู่</th>
	<th width='7%' scope='col'>วันที่ใช้บริการ</th>
	<th width='20%' scope='col'>วินิจฉัย</th>
	<th width='14%' scope='col'>ยาสมุนไพร</th>
    <th width='10%' scope='col'>ผู้ให้บริการ</th>
	<th width='6%' scope='col'>จำนวน</th>
    <th width='8%' scope='col'>วันที่บันทึกข้อมูล</th>
  </tr>";
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$sick = retDatets($row[visitdate]);
	$dupdate = retDatets($row[dateupdate]);
	$uname = getusername($row[username]);
	$sqlv = "SELECT
visitdiag.visitno,
GROUP_CONCAT(concat('<br>',cdisease.diseasecode,'  ',cdisease.diseasenamethai)) as diagcoded
FROM
visitdiag
INNER JOIN cdisease ON visitdiag.diagcode = cdisease.diseasecode
where visitdiag.visitno = $row[visitno]
					ORDER BY  visitdiag.diagcode";
	$resultv = mysql_query($sqlv);
	$rowv=mysql_fetch_array($resultv);
	$diagcodex = $rowv[diagcoded];
++$x;
	if(($x%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$x</div></td>
	<td>$row[idcard]</td>
    <td>$row[pname]</td>
	<td>$row[hno] หมู่ที่ $moo</td>
    <td>$sick</td>
	<td>$diagcodex</td>
    <td>$row[drugname]</td>
	<td>$uname</td>
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
		header("Location: login.php");
		}
		?>
        
</body>
</html>
