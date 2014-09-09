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
$village = $_GET[village];
if($village == "00000000"){
	$wvill = "";
}else{
	$wvill = " and h.villcode='$village' ";	
}
if($village == "00000000"){
	$mu = "ทุกหมู่บ้าน";
}else{
	$mu = getvillagename($village);	
}
$getage = $_GET[getage];
if($getage == "35"){
	$gage = "AND FLOOR((TO_DAYS(NOW())-TO_DAYS(p.birth))/365.25) between 30 and 39";
}elseif($getage == "20"){
	$gage = "AND FLOOR((TO_DAYS(NOW())-TO_DAYS(p.birth))/365.25) < 30";
}elseif($getage == "30"){
	$gage = "AND FLOOR((TO_DAYS(NOW())-TO_DAYS(p.birth))/365.25) > 29";
}elseif($getage == "40"){
	$gage = "AND FLOOR((TO_DAYS(NOW())-TO_DAYS(p.birth))/365.25) > 39";
}else{
	$gage = "";
}
if($getage == "35"){
	$gagename = "อายุ 30 - 39 ปี";
}elseif($getage == "20"){
	$gagename = "อายุต่ำกว่า 30 ปี";
}elseif($getage == "30"){
	$gagename = "อายุ 30 ปี ขึ้นไป";
}elseif($getage == "40"){
	$gagename = "อายุ 40 ปี ขึ้นไป";
}else{
	$gagename = "ทั้งหมด";
}
$str = retdaterangstr($_GET[str]);
$sto = retdaterangsto($_GET[str]);
$strx = retDatets($str);
$stox = retDatets($sto);
$live_type = $_GET[live_type];
if($live_type == '2'){$live_type2 = "p.typelive in ('0','1','2') and";}elseif($live_type == '1'){$live_type2 = "p.typelive in ('0','1','3') and";}else{$live_type2 = "p.typelive in ('0','1','2','3') and";}
if($live_type == '2'){$live_type_name = "ตามทะเบียนบ้าน(0,1,2)";}elseif($live_type == '1'){$live_type_name = "ที่อาศัยอยู่จริง (0,1,3)";}else{$live_type_name = "ทั้งหมดในเขตรับผิดชอบ(0,1,2,3)";}			
$sql = "select village.villno
,village.villname
,village.villcode
,count(person.pid) as pop
,count(case when left(cancer.datecheck,4) between '2010' and '2013' then 1 else null end ) as chk
,concat(round(count(case when left(cancer.datecheck,4) between '2010' and '2013' then 1 else null end )* 100 /count(person.pid),2) ) as percent
,count(case when left(cancer.datecheck,4) = '2010' then substr(cancer.datecheck,1,4)+543 else null end) as '2553'
,count(case when left(cancer.datecheck,4) = '2011' then substr(cancer.datecheck,1,4)+543 else null end) as '2554'
,count(case when left(cancer.datecheck,4) = '2012' then substr(cancer.datecheck,1,4)+543 else null end) as '2555'
,count(case when left(cancer.datecheck,4) = '2013' then substr(cancer.datecheck,1,4)+543 else null end) as '2556'
,count(case when left(cancer.datecheck,4) = '2014' then substr(cancer.datecheck,1,4)+543 else null end) as '2557'
from person 
inner join house on person.hcode = house.hcode and person.pcucodeperson = house.pcucode
inner join village on house.villcode = village.villcode and village.villno <>'0'
left join ctitle on person.prename = ctitle.titlecode
left join (select visit.visitno,visit.pid,max(visitlabcancer.datecheck) as datecheck,visitlabcancer.typecancer 
from visit inner join visitlabcancer on visit.visitno = visitlabcancer.visitno and visit.pcucode = visitlabcancer.pcucode
where visitlabcancer.typecancer in ('2','3')
group by visitno,pid,typecancer)cancer on person.pid = cancer.pid 
where person.sex = '2' 
and getAgeYearNum(person.birth,'2013-01-01') between 30 and 60
group by village.villno,village.villname";

$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>จำนวนประชากร';
$txt .= "ที่ได้รับการตรวจคัดกรองมะเร็งปากมดลูก</b><br><b>  $mu </b></p><b>$hosp</b><table width='95%' border='0' cellspacing='1' cellpadding='1' class='table table-striped table-hover table-bordered'>
  <tr>
    <th width='5%' scope='col'><div align='center'>ลำดับ</div></th>
    <th width='12%' scope='col'><div align='center'>หมู่บ้าน</div></th>
	<th width='8%' scope='col'><div align='center'>หมู่ที่</div></th>
    <th width='8%' scope='col'><div align='center'>จำนวนเป้าหมาย</div></th>
    <th width='5%' scope='col'><div align='center'>ได้รับการตรวจ</div></th>
	<th width='5%' scope='col'><div align='center'>ร้อยละ</div></th>
	<th width='5%' scope='col'><div align='center'>2553</div></th>
	<th width='5%' scope='col'><div align='center'>2554</div></th>
	<th width='5%' scope='col'><div align='center'>2555</div></th>
	<th width='5%' scope='col'><div align='center'>2556</div></th>
	<th width='5%' scope='col'><div align='center'>2557</div></th>
  </tr>";
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$sum_pop = $sum_pop+$row[pop];
	$sum_chk = $sum_chk+$row[chk];
	$sum_2553 = $sum_2553+$row[2553];
	$sum_2554 = $sum_2554+$row[2554];
	$sum_2555 = $sum_2555+$row[2555];
	$sum_2556 = $sum_2556+$row[2556];
	$sum_2557 = $sum_2557+$row[2557];
if($sum_pop == "0"){
	$sum_percen = "0";
}else{
	$sum_percen = $sum_chk/$sum_pop*100;	
}
	$sum_percent1 = number_format($sum_percen, 2, '.', '');
++$i;
	if(($i%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$i</div></td>
    <td>$row[villname]</td>
    <td><div align='center'>$moo</div></td>
    <td><div align='center'>$row[pop]</div></td>
	<td><div align='center'>$row[chk]</div></td>
	<td><div align='center'>$row[percent]</div></td>
	<td><div align='center'>$row[2553]</div></td>
	<td><div align='center'>$row[2554]</div></td>
	<td><div align='center'>$row[2555]</div></td>
	<td><div align='center'>$row[2556]</div></td>
	<td><div align='center'>$row[2557]</div></td>
  </tr>
  ";
}
$txt .= "<tr>
  <td>&nbsp;&nbsp;</td>
  <td><div align='center'>&nbsp;รวม</td>
  <td>&nbsp;&nbsp;</td>
  <td><div align='center'>$sum_pop</div></td>
  <td><div align='center'>$sum_chk</div></td>
  <td><div align='center'>$sum_percent1</div></td>
  <td><div align='center'>$sum_2553</div></td>
  <td><div align='center'>$sum_2554</div></td>
  <td><div align='center'>$sum_2555</div></td>
  <td><div align='center'>$sum_2556</div></td>
  <td><div align='center'>$sum_2557</div></td>
  </tr></table><p div align='right' class='text-danger'>ระหว่าง ปี 2553 ถึง 2557</p></div><br>";
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
