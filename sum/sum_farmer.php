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
$live_type = $_GET[live_type];
if($live_type == '2'){$live_type_name = "ตามทะเบียนบ้าน(0,1,2)";}elseif($live_type == '1'){$live_type_name = "ที่อาศัยอยู่จริง (0,1,3)";}else{$live_type_name = "ทั้งหมดในเขตรับผิดชอบ(0,1,2,3)";}	
if($live_type == '2'){$live_type2 = "and person.typelive in ('0','1','2')";}elseif($live_type == '1'){$live_type2 = "and person.typelive in ('0','1','3')";}else{$live_type2 = "and person.typelive in ('0','1','2','3')";}
$str = retdaterangstr($_GET[str]);
$sto = retdaterangsto($_GET[str]);
$strx = retDatets($str);
$stox = retDatets($sto);		
$sql = "SELECT
village.pcucode,
village.villcode,
village.villname,
count(distinct person.pid) as cperson,
sum(case when vsb.pid is not null then 1 else 0 end) as cperchk,
sum(case when vsb.pid is null then 1 else 0 end) as cperchkno,
sum(case when vsb.labresultdigit = '0' then 1 else 0 end) as normal,
sum(case when vsb.labresultdigit = '1' then 1 else 0 end) as safe,
sum(case when vsb.labresultdigit = '2' then 1 else 0 end) as risk,
sum(case when vsb.labresultdigit = '3' then 1 else 0 end) as nosaft
FROM village
Inner Join house ON village.pcucode = house.pcucode AND village.villcode = house.villcode
INNER JOIN person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
INNER JOIN coccupa ON person.occupa = coccupa.occupacode
left Join ctitle ON person.prename = ctitle.titlecode
left join (SELECT vs.pcucodeperson, vs.pid, vs.visitdate, vslab.labresulttext, vslab.labresultdigit
FROM visit as vs 
INNER JOIN visitdiag as vsd ON vs.pcucode = vsd.pcucode AND vs.visitno = vsd.visitno
left join visitlabchcyhembmsse vslab on vs.pcucodeperson = vslab.pcucodeperson and vslab.pid = vs.pid and vslab.datecheck = vs.visitdate
where vs.visitdate between '$str' and '$sto' and vsd.diagcode = 'Z10.0' 
group by vs.pcucodeperson,vs.pid) as vsb
on person.pcucodeperson = vsb.pcucodeperson and person.pid = vsb.pid
where getAgeYearNum(person.birth,'$str') > 14 and ((person.dischargetype is null) or (person.dischargetype = '9'))  and SUBSTRING(house.villcode,7,2) <> '00' and coccupa.mapoccupa like '6%' $live_type2 $wvill
group by village.pcucode,village.villcode
order by village.pcucode,village.villcode";

$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>รายงานการตรวจสารเคมีในเลือดเกษตรที่อายุ 15 ปี ขึ้นไป</b><br>';
$txt .= "<b>$mu </b></p><br>$live_type_name<br><b>$hosp</b><table width='98%' border='0' cellspacing='1' cellpadding='1' class='table table-striped table-hover table-bordered'>
  <tr>
    <th width='4%' scope='col'><div align='center'>ลำดับ</div></th>
    <th width='10%' scope='col'><div align='center'>หมู่บ้าน</div></th>
	<th width='4%' scope='col'><div align='center'>หมู่ที่</div></th>
	<th width='4%' scope='col'><div align='center'>เกษตรกรอายุ15ปีขึ้นไป</div></th>
	<th width='4%' scope='col'><div align='center'>ได้รับการตรวจสารเคมีในเลือด</div></th>
	<th width='4%' scope='col'><div align='center'>ร้อยละ</div></th>
    <th width='4%' scope='col'><div align='center'>ปกติ</div></th>
	<th width='4%' scope='col'><div align='center'>ปลอดภัย</div></th>
	<th width='4%' scope='col'><div align='center'>เสี่ยง</div></th>
    <th width='4%' scope='col'><div align='center'>ไม่ปลอดภัย</div></th>
	<th width='4%' scope='col'><div align='center'>ยังไม่ได้ตรวจ</div></th>
  </tr>";
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
if($row[cperson] == "0"){
	$percen = "0";
}else{
	$percen = ($row[cperchk])/($row[cperson])*100;	
}
	$percent1 = number_format($percen, 2, '.', '');
	
	$sum_cperson = $sum_cperson+$row[cperson];
	$sum_cperchk = $sum_cperchk+$row[cperchk];
	$sum_cperchkno = $sum_cperchkno+$row[cperchkno];
	$sum_normal = $sum_normal+$row[normal];
	$sum_safe = $sum_safe+$row[safe];
	$sum_risk = $sum_risk+$row[risk];
	$sum_nosafe = $sum_nosafe+$row[nosafe];
if($sum_cperson == "0"){
	$percen = "0";
}else{
	$sum_percen = ($sum_cperchk)/($sum_cperson)*100;	
}
	$sum_percent1 = number_format($sum_percen, 2, '.', '');
++$i;
	if(($i%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$i</div></td>
    <td>$row[villname]</td>
    <td><div align='center'>&nbsp;$moo</div></td>
	<td><div align='center'>$row[cperson]</div></td>
	<td><div align='center'>$row[cperchk]</div></td>
    <td><div align='center'>$percent1</div></td>
	<td><div align='center'>$row[normal]</div></td>
	<td><div align='center'>$row[safe]</div></td>
	<td><div align='center'>$row[risk]</div></td>
	<td><div align='center'>$row[nosaft]</div></td>
	<td><div align='center'>$row[cperchkno]</div></td>
  </tr>
  ";
}
$txt .= "<tr>
  	<td>&nbsp;&nbsp;</td>
  	<td><div align='center'>&nbsp;รวม</td>
  	<td>&nbsp;&nbsp;</td>
	<td><div align='center'>$sum_cperson</div></td>
	<td><div align='center'>$sum_cperchk</div></td>
    <td><div align='center'>$sum_percent1</div></td>
	<td><div align='center'>$sum_normal</div></td>
	<td><div align='center'>$sum_safe</div></td>
	<td><div align='center'>$sum_risk</div></td>
	<td><div align='center'>$sum_nosaft</div></td>
	<td><div align='center'>$sum_cperchkno</div></td>
  </tr></table><p div align='right' class='text-danger'>ข้อมูลระหว่างวันที่  $strx ถึง $stox </p></div><br>";  
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
