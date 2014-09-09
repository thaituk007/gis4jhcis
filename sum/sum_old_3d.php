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
$str = date("Y-d-m");
$strd = substr($str,8,2);
$strm = substr($str,5,2);
$stryT = substr($str,0,4);
$stryF = substr($str,0,4)-1;
$dx = $strm."".$strd;
if($dx > "1001"){$daymidyear = $stryT."-10-01";}else{$daymidyear = $stryF."-10-01";}		
$sql = "select
pcucodeperson,
villcode,
villname,
sum(case when sex = '1' or sex = '2' then 1 else 0 end) as per,
sum(case when sex = '1' then 1 else 0 end) as per_m,
sum(case when sex = '2' then 1 else 0 end) as per_f,
sum(case when chk is not null then 1 else 0 end) as old_t,
sum(case when sex = '1' and chk is not null then 1 else 0 end) as old_t_m,
sum(case when sex = '2' and chk is not null then 1 else 0 end) as old_t_f,
sum(case when chk = 1 then 1 else 0 end) as old_lv1,
sum(case when sex = '1' and chk = 1 then 1 else 0 end) as old_lv1_m,
sum(case when sex = '2' and chk = 1 then 1 else 0 end) as old_lv1_f,
sum(case when chk = 2 then 1 else 0 end) as old_lv2,
sum(case when sex = '1' and chk = 2 then 1 else 0 end) as old_lv2_m,
sum(case when sex = '2' and chk = 2 then 1 else 0 end) as old_lv2_f,
sum(case when chk = 3 then 1 else 0 end) as old_lv3,
sum(case when sex = '1' and chk = 3 then 1 else 0 end) as old_lv3_m,
sum(case when sex = '2' and chk = 3 then 1 else 0 end) as old_lv3_f,
sum(case when chk is null then 1 else 0 end) as old_no,
sum(case when sex = '1' and chk is null then 1 else 0 end) as old_no_m,
sum(case when sex = '2' and chk is null then 1 else 0 end) as old_no_f
from
(SELECT
person.pcucodeperson,
person.pid,
person.fname, 
concat(ctitle.titlename, person.fname , '  ' , person.lname) AS pname,
person.birth,
person.sex,
ROUND(DATEDIFF('$daymidyear',person.birth)/365.25) AS age,
village.villcode,
village.villname,
house.hno,
house.hcode,
house.xgis,
house.ygis,
person.candobedhomesocial as chk,
person.dateupdate
FROM
village
INNER JOIN house ON village.pcucode = house.pcucode AND village.villcode = house.villcode
INNER JOIN person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
left join ctitle on ctitle.titlecode = person.prename
where getAgeYearNum(person.birth,'$daymidyear') > 59 and ((person.dischargetype is null) or (person.dischargetype = '9'))  and SUBSTRING(house.villcode,7,2) <> '00' $wvill
order by person.pcucodeperson,village.villcode,person.fname) as tmpold
GROUP BY pcucodeperson,villcode
order by pcucodeperson,villcode";

$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>รายงานการประเมินสุขภาพผู้สูงอายุ (ติดสังคม, ติดบ้าน, ติดเตียง)</b><br>';
$txt .= "$mu </b></p><br><b>$hosp</b><table width='95%' border='0' cellspacing='1' cellpadding='1' class='table table-striped table-hover table-bordered'>
  <tr>
    <th width='4%' scope='col'><div align='center'>ลำดับ</div></th>
    <th width='10%' scope='col'><div align='center'>หมู่บ้าน</div></th>
	<th width='4%' scope='col'><div align='center'>หมู่ที่</div></th>
	<th width='4%' scope='col'><div align='center'>ผู้สูงอายุชาย</div></th>
	<th width='4%' scope='col'><div align='center'>ผู้สูงอายุหญิง</div></th>
    <th width='4%' scope='col'><div align='center'>ผู้สูงอายุทั้งหมด</div></th>
	<th width='4%' scope='col'><div align='center'>ได้รับการประเมินชาย</div></th>
	<th width='4%' scope='col'><div align='center'>ได้รับการประเมินหญิง</div></th>
    <th width='4%' scope='col'><div align='center'>ได้รับการประเมิน</div></th>
	<th width='4%' scope='col'><div align='center'>ร้อยละ</div></th>
	<th width='4%' scope='col'><div align='center'>ติดสังคมชาย</div></th>
	<th width='4%' scope='col'><div align='center'>ติดสังคมหญิง</div></th>
	<th width='4%' scope='col'><div align='center'>ติดสังคม</div></th>
	<th width='4%' scope='col'><div align='center'>ร้อยละ</div></th>
	<th width='4%' scope='col'><div align='center'>ติดบ้านชาย</div></th>
	<th width='4%' scope='col'><div align='center'>ติดบ้านหญิง</div></th>
	<th width='4%' scope='col'><div align='center'>ติดบ้าน</div></th>
	<th width='4%' scope='col'><div align='center'>ร้อยละ</div></th>
	<th width='4%' scope='col'><div align='center'>ติดเตียงชาย</div></th>
	<th width='4%' scope='col'><div align='center'>ติดเตียงหญิง</div></th>
	<th width='4%' scope='col'><div align='center'>ติดเตียง</div></th>
	<th width='4%' scope='col'><div align='center'>ร้อยละ</div></th>
	<th width='4%' scope='col'><div align='center'>ไม่ได้ประเมินชาย</div></th>
	<th width='4%' scope='col'><div align='center'>ไม่ได้ประเมินหญิง</div></th>
	<th width='4%' scope='col'><div align='center'>ไม่ได้ประเมิน</div></th>
  </tr>";
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
if($row[per] == "0"){
	$percen = "0";
}else{
	$percen = ($row[old_t])/($row[per])*100;	
}
	$percent1 = number_format($percen, 2, '.', '');
if($row[old_t] == "0"){
	$percen2 = "0";
}else{
	$percen2 = ($row[old_lv1])/($row[old_t])*100;	
}
	$percent2 = number_format($percen2, 2, '.', '');
if($row[old_t] == "0"){
	$percen3 = "0";
}else{
	$percen3 = ($row[old_lv2])/($row[old_t])*100;	
}
	$percent3 = number_format($percen3, 2, '.', '');
if($row[old_t] == "0"){
	$percen4 = "0";
}else{
	$percen4 = ($row[old_lv3])/($row[old_t])*100;	
}
	$percent4 = number_format($percen4, 2, '.', '');
	$sum_per_m = $sum_per_m+$row[per_m];
	$sum_per_f = $sum_per_f+$row[per_f];
	$sum_per = $sum_per+$row[per];
	$sum_old_t_m = $sum_old_t_m+$row[old_t_m];
	$sum_old_t_f = $sum_old_t_f+$row[old_t_f];
	$sum_old_t = $sum_old_t+$row[old_t];
	$sum_old_lv1_m = $sum_old_lv1_m+$row[old_lv1_m];
	$sum_old_lv1_f = $sum_old_lv1_f+$row[old_lv1_f];
	$sum_old_lv1 = $sum_old_lv1+$row[old_lv1];
	$sum_old_lv2_m = $sum_old_lv2_m+$row[old_lv2_m];
	$sum_old_lv2_f = $sum_old_lv2_f+$row[old_lv2_f];
	$sum_old_lv2 = $sum_old_lv2+$row[old_lv2];
	$sum_old_lv3_m = $sum_old_lv3_m+$row[old_lv3_m];
	$sum_old_lv3_f = $sum_old_lv3_f+$row[old_lv3_f];
	$sum_old_lv3 = $sum_old_lv3+$row[old_lv3];
	$sum_old_no_m = $sum_old_no_m+$row[old_no_m];
	$sum_old_no_f = $sum_old_no_f+$row[old_no_f];
	$sum_old_no = $sum_old_no+$row[old_no];
if($sum_per == "0"){
	$sum_percen = "0";
}else{
	$sum_percen = $sum_old_t/$sum_per*100;	
}
	$sum_percent1 = number_format($sum_percen, 2, '.', '');
if($sum_old_t == "0"){
	$sum_percen2 = "0";
}else{
	$sum_percen2 = $sum_old_lv1/$sum_old_t*100;	
}
	$sum_percent2 = number_format($sum_percen2, 2, '.', '');
if($sum_old_t == "0"){
	$sum_percen3 = "0";
}else{
	$sum_percen3 = $sum_old_lv2/$sum_old_t*100;	
}
	$sum_percent3 = number_format($sum_percen3, 2, '.', '');
if($sum_old_t == "0"){
	$sum_percen4 = "0";
}else{
	$sum_percen4 = $sum_old_lv3/$sum_old_t*100;	
}
	$sum_percent4 = number_format($sum_percen4, 2, '.', '');
++$i;
	if(($i%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$i</div></td>
    <td>$row[villname]</td>
    <td><div align='center'>&nbsp;$moo</div></td>
	<td><div align='center'>$row[per_m]</div></td>
	<td><div align='center'>$row[per_f]</div></td>
    <td><div align='center'>$row[per]</div></td>
	<td><div align='center'>$row[old_t_m]</div></td>
	<td><div align='center'>$row[old_t_f]</div></td>
	<td><div align='center'>$row[old_t]</div></td>
	<td><div align='center'>$percent1</div></td>
	<td><div align='center'>$row[old_lv1_m]</div></td>
	<td><div align='center'>$row[old_lv1_f]</div></td>
	<td><div align='center'>$row[old_lv1]</div></td>
	<td><div align='center'>$percent2</div></td>
	<td><div align='center'>$row[old_lv2_m]</div></td>
	<td><div align='center'>$row[old_lv2_f]</div></td>
	<td><div align='center'>$row[old_lv2]</div></td>
	<td><div align='center'>$percent3</div></td>
	<td><div align='center'>$row[old_lv3_m]</div></td>
	<td><div align='center'>$row[old_lv3_f]</div></td>
	<td><div align='center'>$row[old_lv3]</div></td>
	<td><div align='center'>$percent4</div></td>
	<td><div align='center'>$row[old_no_m]</div></td>
	<td><div align='center'>$row[old_no_f]</div></td>
	<td><div align='center'>$row[old_no]</div></td>
  </tr>
  ";
}
$txt .= "<tr>
  	<td>&nbsp;&nbsp;</td>
  	<td><div align='center'>&nbsp;รวม</td>
  	<td>&nbsp;&nbsp;</td>
	<td><div align='center'>$sum_per_m</div></td>
	<td><div align='center'>$sum_per_f</div></td>
  	<td><div align='center'>$sum_per</div></td>
	<td><div align='center'>$sum_old_t_m</div></td>
	<td><div align='center'>$sum_old_t_f</div></td>
	<td><div align='center'>$sum_old_t</div></td>
	<td><div align='center'>$sum_percent1</div></td>
	<td><div align='center'>$sum_old_lv1_m</div></td>
	<td><div align='center'>$sum_old_lv1_f</div></td>
	<td><div align='center'>$sum_old_lv1</div></td>
	<td><div align='center'>$sum_percent2</div></td>
	<td><div align='center'>$sum_old_lv2_m</div></td>
	<td><div align='center'>$sum_old_lv2_f</div></td>
	<td><div align='center'>$sum_old_lv2</div></td>
	<td><div align='center'>$sum_percent3</div></td>
	<td><div align='center'>$sum_old_lv3_m</div></td>
	<td><div align='center'>$sum_old_lv3_f</div></td>
	<td><div align='center'>$sum_old_lv3</div></td>
	<td><div align='center'>$sum_percent4</div></td>
	<td><div align='center'>$sum_old_no_m</div></td>
	<td><div align='center'>$sum_old_no_f</div></td>
	<td><div align='center'>$sum_old_no</div></td>
  </tr></table>";  
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
