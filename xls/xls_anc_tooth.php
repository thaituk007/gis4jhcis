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
	$wvill = " AND village.villcode='$villcode' ";	
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
*,
toothcheck as chk
from
(select
village.pcucode, 
person.pid, 
person.idcard,
person.fname, 
concat(ctitle.titlename, person.fname , '  ' , person.lname) AS pname, 
MAX(visitanc.pregno) as pregno,
if(visitancdeliver.datedeliver is null,'ยังไม่คลอด','คลอดแล้ว') as chk_deliver,
village.villcode,
village.villname,
house.hno,
house.hcode,
house.xgis,
house.ygis,
person.birth,
ROUND(DATEDIFF(now(),person.birth)/365.25) AS age,
if(max(visitanc.caries) is null,0,visitanc.caries) as caries,
if(max(visitanc.gumfail) = '0','ไม่มี','มี') as gumfail,
if(max(visitanc.tartar) = '0','ไม่มี','มี') as tartar,
max(visitanc.toothcheck) as toothcheck
FROM 
visitanc 
	left join person on person.pid = visitanc.pid and person.pcucodeperson = visitanc.pcucodeperson
  	left join ctitle on person.prename = ctitle.titlecode
   	left join visitlabblood on visitanc.pid = visitlabblood.pid and visitanc.pcucodeperson = visitlabblood.pcucodeperson
	left join house on person.hcode = house.hcode and person.pcucodeperson = house.pcucode
	left join village on house.villcode = village.villcode and house.pcucode = village.pcucode
	left join visitancdeliver on visitancdeliver.pid = person.pid and visitancdeliver.pcucodeperson = person.pcucodeperson
WHERE SUBSTRING(house.villcode,7,2) <> '00' and visitanc.datecheck between '$str' and '$sto'
and (birth IS NOT NULL OR birth NOT LIKE '0000%') $wvill
GROUP BY visitanc.pcucodeperson,visitanc.pid) as tmp_anc
order by villcode, fname";

$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>รายงานหญิงตั้งครรภ์ที่ได้รับการตรวจฟัน</b><br>';
$txt .= "$mu </b></p><p div align='center' class='text-danger'>ข้อมูลระหว่างวันที่  $strx ถึง $stox </p></div><b>$hosp</b><table width='98%' border='0' cellspacing='1' cellpadding='1' class='table table-striped table-hover table-bordered'>
  <tr>
    <th width='4%' scope='col'><div align='center'>ลำดับ</div></th>
    <th width='13%' scope='col'><div align='center'>ชื่อ - สกุล</div></th>
	<th width='7%' scope='col'><div align='center'>ว/ด/ป เกิด</div></th>
	<th width='3%' scope='col'><div align='center'>อายุ</div></th>
    <th width='4%' scope='col'><div align='center'>บ้านเลขที่</div></th>
    <th width='3%' scope='col'><div align='center'>หมู่ที่</div></th>
	<th width='4%' scope='col'><div align='center'>ครรภ์ที่</div></th>
	<th width='10%' scope='col'><div align='center'>สถานะ</div></th>
	<th width='7%' scope='col'><div align='center'>ได้รับการตรวจฟัน</div></th>
	<th width='7%' scope='col'><div align='center'>พบฟันผุที่ยังไม่ได้อุด</div></th>
	<th width='7%' scope='col'><div align='center'>พบเหงือกอักเสบ</div></th>
	<th width='7%' scope='col'><div align='center'>พบหินน้ำลาย</div></th>
  </tr>";
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	if($row[chk] == "1"){$anc_chk = 'ได้ตรวจ';}else{$anc_chk = 'ไม่ได้ตรวจ';}
	if($row[chk] == "0"){$caries = '---';}else{$caries = $row[caries];}
	if($row[chk] == "0"){$gumfail = '---';}else{$gumfail = $row[gumfail];}
	if($row[chk] == "0"){$tartar = '---';}else{$tartar = $row[tartar];}
	$birth = retDatets($row[birth]);
++$i;
	if(($i%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$i</div></td>
    <td>$row[pname]</td>
	<td><div align='center'>&nbsp;$birth</div></td>
	<td><div align='center'>&nbsp;$row[age]</div></td>
    <td><div align='center'>&nbsp;$row[hno]</div></td>
    <td><div align='center'>&nbsp;$moo</div></td>
    <td><div align='center'>&nbsp;$row[pregno]</div></td>
	<td><div align='center'>&nbsp;$row[chk_deliver]</div></td>
	<td><div align='center'>&nbsp;$anc_chk</div></td>
	<td><div align='center'>&nbsp;$caries</div></td>
	<td><div align='center'>&nbsp;$gumfail</div></td>
	<td><div align='center'>&nbsp;$tartar</div></td>
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
