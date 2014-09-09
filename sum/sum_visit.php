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
$villcode = $_GET[village];
if($villcode == "00000000"){
	$wvill = "";
}else{
	$wvill = " AND house.villcode='$villcode' ";	
}
$chk_v = $_GET[chk_v];
if($chk_v == "0"){
	$chksto = "";
}else{
	$chksto = "and visitdiag.diagcode not like 'Z%'";
}
if($chk_v == "0"){
	$chkston = "แสดงทุกบริการ";
}else{
	$chkston = "เฉพาะOPD (ไม่นับรหัส Z)";
}
$str = retdaterangstr($_GET[str]);
$sto = retdaterangsto($_GET[str]);
$strx = retDatets($str);
$stox = retDatets($sto);
	$sql = "SELECT
count(distinct tmp.pid) as countofpid, 
count(tmp.visitno) as countofvisitno,
village.villcode,
village.villname
from
village
left join
(SELECT 
person.pid,
person.idcard,
CONVERT(concat(ifnull(c.titlename,ifnull(person.prename,'ไม่ระบุ') ),person.fname,' ' ,person.lname) using utf8) as pname,
house.hno,
house.villcode,
house.xgis,
house.ygis,
v.pcucode,
v.visitno,
v.visitdate,
v.symptoms,
v.vitalcheck,
GROUP_CONCAT(visitdiag.diagcode) as gdiagcode,
GROUP_CONCAT(cdisease.diseasename) as gdiagname,
GROUP_CONCAT(cdisease.diseasenamethai) as gdiagnamethai,
v.username
FROM
village
INNER JOIN house ON village.pcucode = house.pcucode AND village.villcode = house.villcode
INNER JOIN person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
left join ctitle c on c.titlecode = person.prename
INNER JOIN visit v ON person.pcucodeperson = v.pcucodeperson AND person.pid = v.pid
INNER JOIN visitdiag ON v.pcucode = visitdiag.pcucode AND v.visitno = visitdiag.visitno
inner join cdisease on visitdiag.diagcode = cdisease.diseasecode
WHERE v.visitdate between '$str' and '$sto' and (v.flagservice <'04' OR v.flagservice is null OR length(trim(v.flagservice))=0 ) $wvill $chksto
group by v.pcucode,v.visitno
order by v.visitdate desc, person.fname) as tmp
on tmp.pcucode = village.pcucode and tmp.villcode = village.villcode
where village.villname is not null
group by village.pcucode, village.villcode";
$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>จำนวนผู้รับบริการ ';
$txt .= "$mu</b></p><b>$hosp</b><br>$chkston<br><table width='99%' border='0' cellspacing='1' cellpadding='1' class='table table-striped table-hover table-bordered'>
  <tr>
    <th width='8%' scope='col'><div align='center'>ลำดับ</div></th>
    <th width='22%' scope='col'><div align='center'>หมู่บ้าน</div></th>
	<th width='8%' scope='col'><div align='center'>หมู่ที่</div></th>
    <th width='8%' scope='col'><div align='center'>จำนวนผู้รับบริการ(คน)</div></th>
	<th width='8%' scope='col'><div align='center'>จำนวนผู้รับบริการ(ครั้ง)</div></th>
	<th width='8%' scope='col'><div align='center'>ครั้ง : คน</div></th>
    <th width='8%' scope='col'><div align='center'>หมายเหตุ</div></th>
  </tr>";
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$title = getTitle($row[prename]);
if($row[countofpid] == "0"){
	$pidvisit = "0";
}else{
	$pidvisit = $row[countofvisitno]/$row[countofpid];	
}
	$pid_visit = number_format($pidvisit, 2, '.', '');
	$sum_pid = $sum_pid+$row[countofpid];
	$sum_visit = $sum_visit+$row[countofvisitno];
if($sum_pid == "0"){
	$sum_pidvisit = "0";
}else{
	$sum_pidvisit = $sum_visit/$sum_pid;	
}
	$sum_pid_visit = number_format($sum_pidvisit, 2, '.', '');
++$x;
	if(($x%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$x</div></td>
    <td>$row[villname]</td>
    <td><div align='center'>$moo</td>
    <td><div align='center'>$row[countofpid]</td>
	<td><div align='center'>$row[countofvisitno]</td>
	<td><div align='center'>$pid_visit</td>
    <td></td>
  </tr>
  ";
}
$txt .= "<tr>
  <td>&nbsp;&nbsp;</td>
  <td><div align='center'>&nbsp;รวม</td>
  <td>&nbsp;&nbsp;</td>
  <td><div align='center'>$sum_pid</div></td>
  <td><div align='center'>$sum_visit</div></td>
  <td><div align='center'>$sum_pid_visit</div></td>
  <td>&nbsp;&nbsp;</td>
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
