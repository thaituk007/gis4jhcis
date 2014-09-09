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
$sql = "SELECT
h.villcode,
village.villname,
sum(case when p.marystatus in ('f','2') then 1 else 0 end) as per_all,
sum(case when p.marystatus in ('f','2') and women.fptype <> '0'and women.fptype <> '' and women.datesurvey between '$str' and '$sto' then 1 else 0 end) as fp_all,
sum(case when p.marystatus in ('f','2') and women.fptype = '1' and women.datesurvey between '$str' and '$sto' then 1 else 0 end) as fp_tab,
sum(case when p.marystatus in ('f','2') and women.fptype = '2' and women.datesurvey between '$str' and '$sto' then 1 else 0 end) as fp_inj,
sum(case when p.marystatus in ('f','2') and women.fptype = '3' and women.datesurvey between '$str' and '$sto' then 1 else 0 end) as fp_h,
sum(case when p.marystatus in ('f','2') and women.fptype = '4' and women.datesurvey between '$str' and '$sto' then 1 else 0 end) as fp_ph,
sum(case when p.marystatus in ('f','2') and women.fptype = '5' and women.datesurvey between '$str' and '$sto' then 1 else 0 end) as fp_condom,
sum(case when p.marystatus in ('f','2') and women.fptype = '6' and women.datesurvey between '$str' and '$sto' then 1 else 0 end) as fp_m,
sum(case when p.marystatus in ('f','2') and women.fptype = '7' and women.datesurvey between '$str' and '$sto' then 1 else 0 end) as fp_f,
sum(case when p.marystatus in ('f','2') and women.fptype = '0' and women.datesurvey between '$str' and '$sto' then 1 else 0 end) as fp_no,
sum(case when p.marystatus in ('f','2') and (women.fptype is null or women.datesurvey not between '$str' and '$sto') then 1 else 0 end) as fp_null
FROM
house AS h
Inner Join person AS p ON h.pcucode = p.pcucodeperson AND h.hcode = p.hcode
Left Join women ON p.pcucodeperson = women.pcucodeperson AND p.pid = women.pid
Inner Join cstatus ON p.marystatus = cstatus.statuscode
Inner Join ctitle ON p.prename = ctitle.titlecode
inner join village on h.pcucode = village.pcucode and h.villcode = village.villcode
WHERE p.sex = '2' and ((p.dischargetype is null) or (p.dischargetype = '9')) AND
				SUBSTRING(h.villcode,7,2) <> '00' AND
				FLOOR((TO_DAYS(NOW())-TO_DAYS(p.birth))/365.25) < 20 $wvill
group by h.villcode
ORDER BY h.villcode";

$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>จำนวนหญิงอายุต่ำกว่า 20 ปี ที่อยู่กินกับสามีได้รับการวางแผนครอบครัว</b><br>';
$txt .= "<b>$mu </b></p><br><b>$hosp</b><table width='95%' border='0' cellspacing='1' cellpadding='1' class='table table-striped table-hover table-bordered'>
  <tr>
    <th width='5%' scope='col'><div align='center'>ลำดับ</div></th>
    <th width='12%' scope='col'><div align='center'>หมู่บ้าน</div></th>
	<th width='8%' scope='col'><div align='center'>หมู่ที่</div></th>
    <th width='8%' scope='col'><div align='center'>หญิงอายุ <20 ปี อยุ่กินกับสามี</div></th>
    <th width='6%' scope='col'><div align='center'>คุมกำเนิด</div></th>
	<th width='6%' scope='col'><div align='center'>ร้อยละ</div></th>
	<th width='6%' scope='col'><div align='center'>ยาเม็ด</div></th>
	<th width='6%' scope='col'><div align='center'>ยาฉีด</div></th>
	<th width='6%' scope='col'><div align='center'>ห่วงอนามัย</div></th>
	<th width='6%' scope='col'><div align='center'>ยาฝัง</div></th>
	<th width='6%' scope='col'><div align='center'>ถุงยางอนามัย</div></th>
	<th width='6%' scope='col'><div align='center'>หมันชาย</div></th>
	<th width='6%' scope='col'><div align='center'>หมันหญิง</div></th>
	<th width='6%' scope='col'><div align='center'>ไม่ได้คุม</div></th>
	<th width='6%' scope='col'><div align='center'>ไม่ระบุ</div></th>
  </tr>";
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$title = getTitle($row[prename]);
if($row[per_all] == "0"){
	$percen = "0";
}else{
	$percen = ($row[fp_all])/($row[per_all])*100;	
}
	$percent1 = number_format($percen, 2, '.', '');
	$sum_per_all = $sum_per_all+$row[per_all];
	$sum_fp_all = $sum_fp_all+$row[fp_all];
	$sum_fp_tab = $sum_fp_tab+$row[fp_tab];
	$sum_fp_inj = $sum_fp_inj+$row[fp_inj];
	$sum_fp_h = $sum_fp_h+$row[fp_h];
	$sum_fp_ph = $sum_fp_ph+$row[fp_ph];
	$sum_fp_condom = $sum_fp_condom+$row[fp_condom];
	$sum_fp_m = $sum_fp_m+$row[fp_m];
	$sum_fp_f = $sum_fp_f+$row[fp_f];
	$sum_fp_no = $sum_fp_no+$row[fp_no];
	$sum_fp_null = $sum_fp_null+$row[fp_null];
if($sum_per_all == "0"){
	$sum_percen = "0";
}else{
	$sum_percen = $sum_fp_all/$sum_per_all*100;	
}
	$sum_percent1 = number_format($sum_percen, 2, '.', '');
++$i;
	if(($i%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$i</div></td>
    <td>$row[villname]</td>
    <td><div align='center'>&nbsp;$moo</div></td>
    <td><div align='center'>$row[per_all]</div></td>
	<td><div align='center'>$row[fp_all]</div></td>
	<td><div align='center'>$percent1</div></td>
	<td><div align='center'>$row[fp_tab]</div></td>
	<td><div align='center'>$row[fp_inj]</div></td>
	<td><div align='center'>$row[fp_h]</div></td>
	<td><div align='center'>$row[fp_ph]</div></td>
	<td><div align='center'>$row[fp_condom]</div></td>
	<td><div align='center'>$row[fp_m]</div></td>
	<td><div align='center'>$row[fp_f]</div></td>
	<td><div align='center'>$row[fp_no]</div></td>
	<td><div align='center'>$row[fp_null]</div></td>
  </tr>
  ";
}
$txt .= "<tr>
  <td>&nbsp;&nbsp;</td>
  <td><div align='center'>&nbsp;รวม</td>
  <td>&nbsp;&nbsp;</td>
  <td><div align='center'>$sum_per_all</div></td>
  <td><div align='center'>$sum_fp_all</div></td>
  <td><div align='center'>$sum_percent1</div></td>
  <td><div align='center'>$sum_fp_tab</div></td>
  <td><div align='center'>$sum_fp_inj</div></td>
  <td><div align='center'>$sum_fp_h</div></td>
  <td><div align='center'>$sum_fp_ph</div></td>
  <td><div align='center'>$sum_fp_condom</div></td>
  <td><div align='center'>$sum_fp_m</div></td>
  <td><div align='center'>$sum_fp_f</div></td>
  <td><div align='center'>$sum_fp_no</div></td>
  <td><div align='center'>$sum_fp_null</div></td>
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
