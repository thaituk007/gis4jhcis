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
$str = retdaterangstr($_GET[str]);
$sto = retdaterangsto($_GET[str]);
$strx = retDatets($str);
$stox = retDatets($sto);	
$sql = "select
villcode,villname,
count(distinct pid) as anc_all,
sum(case when toothcheck = '1' then 1 else 0 end) as anc_tooth,
sum(case when toothcheck = '1' and caries != 0 then 1 else 0 end) as anc_caries,
sum(case when toothcheck = '1'  and gumfail like 'มี' then 1 else 0 end) as anc_gumfail,
sum(case when toothcheck = '1'  and tartar like 'มี' then 1 else 0 end) as anc_tartar
from
(select
village.pcucode, 
person.pid, 
person.idcard, 
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
group by villcode";

$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>รายงานหญิงตั้งครรภ์ที่ได้รับการตรวจฟัน</b><br>';
$txt .= "<b>$mu </b></p><b>$hosp</b><table width='95%' border='0' cellspacing='1' cellpadding='1' class='table table-striped table-hover table-bordered'>
  <tr>
    <th width='5%' scope='col'><div align='center'>ลำดับ</div></th>
    <th width='12%' scope='col'><div align='center'>หมู่บ้าน</div></th>
	<th width='10%' scope='col'><div align='center'>หมู่ที่</div></th>
    <th width='10%' scope='col'><div align='center'>หญิงตั้งครรภ์ทั้งหมด</div></th>
    <th width='10%' scope='col'><div align='center'>ได้รับการตรวจฟัน</div></th>
	<th width='7%' scope='col'><div align='center'>ร้อยละ</div></th>
	<th width='10%' scope='col'><div align='center'>พบฟันผุที่ยังไม่ได้อุด</div></th>
	<th width='7%' scope='col'><div align='center'>ร้อยละ</div></th>
	<th width='10%' scope='col'><div align='center'>พบเหงือกอักเสย</div></th>
	<th width='7%' scope='col'><div align='center'>ร้อยละ</div></th>
	<th width='10%' scope='col'><div align='center'>พบหินน้ำลาย</div></th>
	<th width='7%' scope='col'><div align='center'>ร้อยละ</div></th>
  </tr>";
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
if($row[anc_all] == "0"){
	$percen = "0";
}else{
	$percen = ($row[anc_tooth])/($row[anc_all])*100;	
}
	$percent1 = number_format($percen, 2, '.', '');
if($row[anc_tooth] == "0"){
	$percen2 = "0";
}else{
	$percen2 = ($row[anc_caries])/($row[anc_tooth])*100;	
}
	$percent2 = number_format($percen2, 2, '.', '');
if($row[anc_tooth] == "0"){
	$percen3 = "0";
}else{
	$percen3 = ($row[anc_gumfail])/($row[anc_tooth])*100;	
}
	$percent3 = number_format($percen3, 2, '.', '');
if($row[anc_tooth] == "0"){
	$percen4 = "0";
}else{
	$percen4 = ($row[anc_tartar])/($row[anc_tooth])*100;	
}
	$percent4 = number_format($percen4, 2, '.', '');
	$sum_anc_all = $sum_anc_all+$row[anc_all];
	$sum_anc_tooth = $sum_anc_tooth+$row[anc_tooth];
	$sum_anc_caries = $sum_anc_caries+$row[anc_caries];
	$sum_anc_gumfail = $sum_anc_gumfail+$row[anc_gumfail];
	$sum_anc_tartar = $sum_anc_tartar_4+$row[anc_tartar];
if($sum_anc_all == "0"){
	$sum_percen = "0";
}else{
	$sum_percen = $sum_anc_tooth/$sum_anc_all*100;	
}
	$sum_percent1 = number_format($sum_percen, 2, '.', '');
if($sum_anc_tooth == "0"){
	$sum_percen2 = "0";
}else{
	$sum_percen2 = $sum_anc_caries/$sum_anc_tooth*100;	
}
	$sum_percent2 = number_format($sum_percen2, 2, '.', '');
if($sum_anc_tooth == "0"){
	$sum_percen3 = "0";
}else{
	$sum_percen3 = $sum_anc_gumfail/$sum_anc_tooth*100;	
}
	$sum_percent3 = number_format($sum_percen3, 2, '.', '');
if($sum_anc_tooth == "0"){
	$sum_percen4 = "0";
}else{
	$sum_percen4 = $sum_anc_tartar/$sum_anc_tooth*100;	
}
	$sum_percent4 = number_format($sum_percen4, 2, '.', '');
++$i;
	if(($i%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$i</div></td>
    <td>$row[villname]</td>
    <td><div align='center'>&nbsp;$moo</div></td>
    <td><div align='center'>$row[anc_all]</div></td>
	<td><div align='center'>$row[anc_tooth]</div></td>
	<td><div align='center'>$percent1</div></td>
	<td><div align='center'>$row[anc_caries]</div></td>
	<td><div align='center'>$percent2</div></td>
	<td><div align='center'>$row[anc_gumfail]</div></td>
	<td><div align='center'>$percent3</div></td>
	<td><div align='center'>$row[anc_tartar]</div></td>
	<td><div align='center'>$percent4</div></td>
  </tr>
  ";
}
$txt .= "<tr>
  <td>&nbsp;&nbsp;</td>
  <td><div align='center'>&nbsp;รวม</td>
  <td>&nbsp;&nbsp;</td>
  <td><div align='center'>$sum_anc_all</div></td>
  <td><div align='center'>$sum_anc_tooth</div></td>
  <td><div align='center'>$sum_percent1</div></td>
  <td><div align='center'>$sum_anc_caries</div></td>
  <td><div align='center'>$sum_percent2</div></td>
  <td><div align='center'>$sum_anc_gumfail</div></td>
  <td><div align='center'>$sum_percent3</div></td>
  <td><div align='center'>$sum_anc_tartar</div></td>
  <td><div align='center'>$sum_percent4</div></td>
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
