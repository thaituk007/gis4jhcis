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
pcucodeperson,
villcode,
villname,
count(distinct tmp_per.pid) as per,
sum(case when chk is not null then 1 else 0 end) as per_ncd,
sum(case when sex = '1' and chk is not null then 1 else 0 end) as per_ncd_m,
sum(case when sex = '2' and chk is not null then 1 else 0 end) as per_ncd_f,
sum(case when chk = 0 then 1 else 0 end) as per_nr,
sum(case when sex = '1' and chk = 0 then 1 else 0 end) as per_nr_m,
sum(case when sex = '2' and chk = 0 then 1 else 0 end) as per_nr_f,
sum(case when chk = 1 then 1 else 0 end) as per_ob,
sum(case when sex = '1' and chk = 1 then 1 else 0 end) as per_ob_m,
sum(case when sex = '2' and chk = 1 then 1 else 0 end) as per_ob_f,
sum(case when chk is null then 1 else 0 end) as per_no
from
(select
person.pcucodeperson,
person.pid,
person.fname, 
concat(ctitle.titlename, person.fname , '  ' , person.lname) AS pname,
person.birth,
person.sex,
ROUND(DATEDIFF(now(),person.birth)/365.25) AS age,
village.villcode,
village.villname,
house.hno,
house.hcode,
house.xgis,
house.ygis
FROM
village
Inner Join house ON village.pcucode = house.pcucode AND village.villcode = house.villcode
Inner Join person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
Inner Join ctitle ON person.prename = ctitle.titlecode
where ROUND(DATEDIFF(now(),person.birth)/365.25) > 14 and SUBSTRING(house.villcode,7,2) <> '00' AND (person.dischargetype Is Null Or person.dischargetype='9') $wvill) as tmp_per
left join
(select 
ncd_person_ncd_screen.pcucode,
ncd_person_ncd_screen.pid,
ncd_person_ncd_screen.screen_date,
ncd_person_ncd_screen.weight,
ncd_person_ncd_screen.height,
ncd_person_ncd_screen.bmi,
ncd_person_ncd_screen.waist,
if(ncd_person_ncd_screen.waist is null,null,if( (person.sex='1' and ncd_person_ncd_screen.waist >89 ) or (person.sex='2' and ncd_person_ncd_screen.waist >79),1,0)) as chk
FROM  ncd_person_ncd_screen
inner join person on ncd_person_ncd_screen.pcucode = person.pcucodeperson and ncd_person_ncd_screen.pid = person.pid
where ncd_person_ncd_screen.screen_date between '$str' and '$sto') as tmp_ncd
ON tmp_per.pcucodeperson = tmp_ncd.pcucode AND tmp_per.pid = tmp_ncd.pid
group by pcucodeperson, villcode";

$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>รายงานประชาชนอายุ 15 ปีขึ้นไป ที่ได้รับการวัดรอบเอว</b><br>';
$txt .= "<b>$mu </b></p><br><b>$hosp</b><table width='95%' border='0' cellspacing='1' cellpadding='1' class='table table-striped table-hover table-bordered'>
  <tr>
    <th width='4%' scope='col'>ลำดับ</th>
    <th width='9%' scope='col'>หมู่บ้าน</th>
	<th width='4%' scope='col'>หมู่ที่</th>
    <th width='6%' scope='col'>ประชาชนอายุ 15 ปีขึ้นไป</th>
    <th width='6%' scope='col'>ได้รับการวัดรอบเอวชาย</th>
	<th width='6%' scope='col'>ได้รับการวัดรอบเอวหญิง</th>
	<th width='6%' scope='col'>ได้รับการวัดรอบเอวทั้งหมด</th>
	<th width='6%' scope='col'>ร้อยละ</th>
	<th width='6%' scope='col'>รอบเอวปกติชาย</th>
	<th width='6%' scope='col'>รอบเอวปกติหญิง</th>
	<th width='6%' scope='col'>รอบเอวปกติทั้งหมด</th>
	<th width='6%' scope='col'>ร้อยละ</th>
	<th width='6%' scope='col'>รอบเอวเกินชาย</th>
	<th width='6%' scope='col'>รอบเอวเกินหญิง</th>
	<th width='6%' scope='col'>รอบเอวเกินทั้งหมด</th>
	<th width='6%' scope='col'>ร้อยละ</th>
	<th width='6%' scope='col'>ยังไม่ได้วัดรอบเอว</th>
  </tr>";
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
if($row[per] == "0"){
	$percen = "0";
}else{
	$percen = ($row[per_ncd])/($row[per])*100;	
}
	$percent1 = number_format($percen, 2, '.', '');
if($row[per_ncd] == "0"){
	$percen2 = "0";
}else{
	$percen2 = ($row[per_nr])/($row[per_ncd])*100;	
}
	$percent2 = number_format($percen2, 2, '.', '');
if($row[per_ncd] == "0"){
	$percen3 = "0";
}else{
	$percen3 = ($row[per_ob])/($row[per_ncd])*100;	
}
	$percent3 = number_format($percen3, 2, '.', '');
	$sum_per = $sum_per+$row[per];
	$sum_per_ncd = $sum_per_ncd+$row[per_ncd];
	$sum_per_ncd_m = $sum_per_ncd_m+$row[per_ncd_m];
	$sum_per_ncd_f = $sum_per_ncd_f+$row[per_ncd_f];
	$sum_per_nr = $sum_per_nr+$row[per_nr];
	$sum_per_nr_m = $sum_per_nr_m+$row[per_nr_m];
	$sum_per_nr_f = $sum_per_nr_f+$row[per_nr_f];
	$sum_per_ob = $sum_per_ob+$row[per_ob];
	$sum_per_ob_m = $sum_per_ob_m+$row[per_ob_m];
	$sum_per_ob_f = $sum_per_ob_f+$row[per_ob_f];
	$sum_per_no = $sum_per_no+$row[per_no];
if($sum_per == "0"){
	$sum_percen = "0";
}else{
	$sum_percen = $sum_per_ncd/$sum_per*100;	
}
	$sum_percent1 = number_format($sum_percen, 2, '.', '');
if($sum_per_ncd == "0"){
	$sum_percen2 = "0";
}else{
	$sum_percen2 = $sum_per_nr/$sum_per_ncd*100;	
}
	$sum_percent2 = number_format($sum_percen2, 2, '.', '');
if($sum_per_ncd == "0"){
	$sum_percen3 = "0";
}else{
	$sum_percen3 = $sum_per_ob/$sum_per_ncd*100;	
}
	$sum_percent3 = number_format($sum_percen3, 2, '.', '');
++$i;
	if(($i%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$i</div></td>
    <td>$row[villname]</td>
    <td><div align='center'>&nbsp;$moo</div></td>
    <td><div align='center'>$row[per]</div></td>
	<td><div align='center'>$row[per_ncd_m]</div></td>
	<td><div align='center'>$row[per_ncd_f]</div></td>
	<td><div align='center'>$row[per_ncd]</div></td>
	<td><div align='center'>$percent1</div></td>
	<td><div align='center'>$row[per_nr_m]</div></td>
	<td><div align='center'>$row[per_nr_f]</div></td>
	<td><div align='center'>$row[per_nr]</div></td>
	<td><div align='center'>$percent2</div></td>
	<td><div align='center'>$row[per_ob_m]</div></td>
	<td><div align='center'>$row[per_ob_f]</div></td>
	<td><div align='center'>$row[per_ob]</div></td>
	<td><div align='center'>$percent3</div></td>
	<td><div align='center'>$row[per_no]</div></td>
  </tr>
  ";
}
$txt .= "<tr>
  <td>&nbsp;&nbsp;</td>
  <td><div align='center'>&nbsp;รวม</td>
  <td>&nbsp;&nbsp;</td>
  <td><div align='center'>$sum_per</div></td>
	<td><div align='center'>$sum_per_ncd_m</div></td>
	<td><div align='center'>$sum_per_ncd_f</div></td>
	<td><div align='center'>$sum_per_ncd</div></td>
	<td><div align='center'>$sum_percent1</div></td>
	<td><div align='center'>$sum_per_nr_m</div></td>
	<td><div align='center'>$sum_per_nr_f</div></td>
	<td><div align='center'>$sum_per_nr</div></td>
	<td><div align='center'>$sum_percent2</div></td>
	<td><div align='center'>$sum_per_ob_m</div></td>
	<td><div align='center'>$sum_per_ob_f</div></td>
	<td><div align='center'>$sum_per_ob</div></td>
	<td><div align='center'>$sum_percent3</div></td>
	<td><div align='center'>$sum_per_no</div></td>
  </tr>";
$txt .= "</table><p div align='right' class='text-danger'>ข้อมูลระหว่างวันที่  $strx ถึง $stox </p></div><br>";  
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