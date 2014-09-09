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
$villcode = $_GET[village];
if($villcode == "00000000"){
	$wvill = "";
}else{
	$wvill = " AND house.villcode='$villcode' ";	
}
$chk_stool = $_GET[chk_stool];
if($chk_stool == "1"){
	$chksto = "";
}elseif($chk_stool == "2"){
	$chksto = "and cancer.result is not null";
}elseif($chk_stool == "3"){
	$chksto = "and cancer.result in ('1','2','5','6','9')";		
}else{
	$chksto = "and cancer.result is null";		
}
$live_type = $_GET[live_type];
if($live_type == '2'){$live_type2 = "and person.typelive in ('0','1','2')";}elseif($live_type == '1'){$live_type2 = "and person.typelive in ('0','1','3')";}else{$live_type2 = "and person.typelive in ('0','1','2','3')";}
if($live_type == '2'){$live_type_name = "ตามทะเบียนบ้าน(0,1,2)";}elseif($live_type == '1'){$live_type_name = "ที่อาศัยอยู่จริง (0,1,3)";}else{$live_type_name = "ทั้งหมดในเขตรับผิดชอบ(0,1,2,3)";}	
$getage = $_GET[getage];
if($getage == "1"){
	$gage = "AND getAgeYearNum(person.birth,'$str') between 30 and 60";
}elseif($getage == "2"){
	$gage = "AND getAgeYearNum(person.birth,'$str') < 30";
}elseif($getage == "3"){
	$gage = "AND getAgeYearNum(person.birth,'$str') > 60";
}else{
	$gage = "";
}
if($getage == "1"){
	$gagename = "อายุ 30 - 60 ปี";
}elseif($getage == "2"){
	$gagename = "อายุต่ำกว่า 30 ปี";
}elseif($getage == "3"){
	$gagename = "อายุ 60 ปี ขึ้นไป";
}else{
	$gagename = "ทั้งหมด";
}		
$sql = "select
house.villcode,
village.villname,
sum(case when getageyearnum(person.birth,'$str') between 30 and 60 then 1 else 0 end) as pop,
sum(case when getageyearnum(person.birth,'$str') between 30 and 60 and cancer.pid is not null then 1 else 0 end) as chk30,
sum(case when getageyearnum(person.birth,'$str') between 30 and 60 and cancer.result in ('1','2','5','6','9') then 1 else 0 end) as chk_ab30,
sum(case when getageyearnum(person.birth,'$str') < 30 and cancer.pid is not null then 1 else 0 end) as chk29,
sum(case when getageyearnum(person.birth,'$str') < 30 and cancer.result in ('1','2','5','6','9') then 1 else 0 end) as chk_ab29,
sum(case when getageyearnum(person.birth,'$str') > 60 and cancer.pid is not null then 1 else 0 end) as chk60,
sum(case when getageyearnum(person.birth,'$str') > 60 and cancer.result in ('1','2','5','6','9') then 1 else 0 end) as chk_ab60
from person 
inner join house on person.hcode = house.hcode and person.pcucodeperson = house.pcucode
inner join village on house.villcode = village.villcode and village.villno <>'0'
left join ctitle on person.prename = ctitle.titlecode
left join (select visit.visitno,visit.pid,visitlabcancer.datecheck as datecheck,visitlabcancer.typecancer, visitlabcancer.result,
visitlabcancer.hosservice,
visitlabcancer.hoslab
from visit inner join visitlabcancer on visit.visitno = visitlabcancer.visitno and visit.pcucode = visitlabcancer.pcucode
where visitlabcancer.typecancer in ('2','3') and visitlabcancer.datecheck between '$str' and '$sto'
group by visitlabcancer.pid)cancer on person.pid = cancer.pid 
where ((person.dischargetype is null) or (person.dischargetype = '9')) and right(house.villcode,2) <> '00' and person.sex = '2' $wvill $live_type2
group by house.villcode
order by house.villcode";

$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>จำนวนประชากร';
$txt .= "ที่ได้รับการตรวจคัดกรองมะเร็งปากมดลูก</b><br><b> $mu </b></p>ประชากร $live_type_name <br><b>$hosp</b><table width='99%' border='0' cellspacing='1' cellpadding='1' class='table table-striped table-hover table-bordered'>
  <tr>
    <th width='5%' scope='col'><div align='center'>ลำดับ</div></th>
    <th width='12%' scope='col'><div align='center'>หมู่บ้าน</div></th>
	<th width='8%' scope='col'><div align='center'>หมู่ที่</div></th>
    <th width='10%' scope='col'><div align='center'>อายุ 30-60 ปี</div></th>
    <th width='10%' scope='col'><div align='center'>ได้รับการตรวจ</div></th>
	<th width='10%' scope='col'><div align='center'>ร้อยละ</div></th>
	<th width='10%' scope='col'><div align='center'>อายุต่ำกว่า 30 ปี</div></th>
    <th width='10%' scope='col'><div align='center'>ได้รับการตรวจ</div></th>
	<th width='10%' scope='col'><div align='center'>อายุมากกว่า 60 ปี</div></th>
    <th width='10%' scope='col'><div align='center'>ได้รับการตรวจ</div></th>
  </tr>";
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$villname = getvillagename($row[villcode]);
	$sum_pop = $sum_pop+$row[pop];
	$sum_chk30 = $sum_chk30+$row[chk30];
	$sum_chk_ab30 = $sum_chk_ab30+$row[chk_ab30];
	$sum_chk29 = $sum_chk29+$row[chk29];
	$sum_chk_ab29 = $sum_chk_ab29+$row[chk_ab29];
	$sum_chk60 = $sum_chk60+$row[chk60];
	$sum_chk_ab60 = $sum_chk_ab60+$row[chk_ab60];
if($row[pop] == "0"){
	$percen = "0";
}else{
	$percen = $row[chk30]/$row[pop]*100;	
}
	$percent = number_format($percen, 2, '.', '');
if($sum_pop == "0"){
	$sum_percen = "0";
}else{
	$sum_percen = $sum_chk30/$sum_pop*100;	
}
	$sum_percent1 = number_format($sum_percen, 2, '.', '');
++$i;
	if(($i%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$i</div></td>
    <td>$row[villname]</td>
    <td><div align='center'>$moo</div></td>
    <td><div align='center'>$row[pop]</div></td>
	<td><div align='center'>$row[chk30]</div></td>
	<td><div align='center'>$percent</div></td>
	<td><div align='center'>$row[chk29]</div></td>
	<td><div align='center'>$row[chk_ab29]</div></td>
	<td><div align='center'>$row[chk60]</div></td>
	<td><div align='center'>$row[chk_ab60]</div></td>
  </tr>
  ";
}
$txt .= "<tr>
  <td>&nbsp;&nbsp;</td>
  <td><div align='center'>&nbsp;รวม</td>
  <td>&nbsp;&nbsp;</td>
  <td><div align='center'>$sum_pop</div></td>
  <td><div align='center'>$sum_chk30</div></td>
  <td><div align='center'>$sum_percent1</div></td>
  <td><div align='center'>$sum_chk29</div></td>
  <td><div align='center'>$sum_chk_ab29</div></td>
  <td><div align='center'>$sum_chk60</div></td>
  <td><div align='center'>$sum_chk_ab60</div></td>
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
