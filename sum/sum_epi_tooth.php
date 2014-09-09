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
$str = $_GET[str];
$strx = retDatets($str);		
$sql = "select
pcucode,
villcode,
villname,
count(distinct pid) as per,
sum(case when toothmilk is not null then 1 else 0 end) as chk_tooth,
sum(case when toothmilk is not null and toothmilkcorrupt > 0 then 1 else 0 end) as tooth_rupt1,
sum(case when toothmilk is not null and toothpermanentcorrupt > 0 then 1 else 0 end) as tooth_rupt2,
sum(case when toothmilk is not null and tartar > 0  then 1 else 0 end) as tooth_rupt3,
sum(case when toothmilk is not null and gumstatus > 0  then 1 else 0 end) as tooth_rupt4
from
(SELECT
village.pcucode, 
person.pid, 
person.idcard,
person.fname, 
concat(ctitle.titlename, person.fname , '  ' , person.lname) AS pname, 
person.birth,
village.villcode,
village.villname,
house.hno,
house.hcode,
house.xgis,
house.ygis,
ROUND(DATEDIFF('$str',person.birth)/30) AS age,
max(visitdentalcheck.toothmilk) as toothmilk,
max(visitdentalcheck.toothmilkcorrupt) as toothmilkcorrupt,
max(visitdentalcheck.toothpermanent) as toothpermanent,
max(visitdentalcheck.toothpermanentcorrupt) as toothpermanentcorrupt,
max(visitdentalcheck.tartar) as tartar,
max(visitdentalcheck.gumstatus) as gumstatus
from
village 
INNER JOIN house ON village.villcode = house.villcode AND village.pcucode = house.pcucode 
INNER JOIN person ON house.hcode = person.hcode AND house.pcucode = person.pcucodeperson
 INNER JOIN visit ON person.pid = visit.pid AND person.pcucodeperson = visit.pcucodeperson
INNER JOIN visitepi ON visit.pid = visitepi.pid AND visit.visitno = visitepi.visitno AND visit.pcucode = visitepi.pcucode
INNER JOIN ctitle ON person.prename = ctitle.titlecode
left JOIN visitdentalcheck ON visit.pcucode = visitdentalcheck.pcucode AND visit.visitno = visitdentalcheck.visitno
where  (person.dischargetype Is Null Or person.dischargetype='9') and right(house.villcode,2) <> '00' and ROUND(DATEDIFF('$str',person.birth)/30)  Between 9 And 24 and (visit.flagservice <'04' OR visit.flagservice is null OR length(trim(visit.flagservice))=0 )
GROUP BY person.pid) as tmp_epi
group by pcucode, villcode
order by villcode, villcode";

$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>รายงานเด็กอายุ 9 - 24 เดือนที่มารับวัคซีนได้รับการตรวจฟัน</b><br>';
$txt .= "<b>$mu </b></p><br><b>$hosp</b><table width='95%' border='0' cellspacing='1' cellpadding='1' class='table table-striped table-hover table-bordered'>
  <tr>
    <th width='5%' scope='col'><div align='center'>ลำดับ</div></th>
    <th width='10%' scope='col'><div align='center'>หมู่บ้าน</div></th>
	<th width='6%' scope='col'><div align='center'>หมู่ที่</div></th>
    <th width='8%' scope='col'><div align='center'>เด็กอายุ 9 - 24 เดือน ที่มารับวัคซีน</div></th>
    <th width='8%' scope='col'><div align='center'>ได้รับการตรวจฟัน</div></th>
	<th width='6%' scope='col'><div align='center'>ร้อยละ</div></th>
	<th width='8%' scope='col'><div align='center'>ฟันน้ำนมผุ</div></th>
	<th width='6%' scope='col'><div align='center'>ร้อยละ</div></th>
	<th width='8%' scope='col'><div align='center'>ฟันแท้ผุ</div></th>
	<th width='6%' scope='col'><div align='center'>ร้อยละ</div></th>
	<th width='8%' scope='col'><div align='center'>พบหินน้ำลาย</div></th>
	<th width='6%' scope='col'><div align='center'>ร้อยละ</div></th>
	<th width='8%' scope='col'><div align='center'>เหงือกอักเสบ</div></th>
	<th width='6%' scope='col'><div align='center'>ร้อยละ</div></th>
  </tr>";
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
if($row[per] == "0"){
	$percen = "0";
}else{
	$percen = ($row[chk_tooth])/($row[per])*100;	
}
	$percent1 = number_format($percen, 2, '.', '');
if($row[chk_tooth] == "0"){
	$percen2 = "0";
}else{
	$percen2 = ($row[tooth_rupt1])/($row[chk_tooth])*100;	
}
	$percent2 = number_format($percen2, 2, '.', '');
if($row[chk_tooth] == "0"){
	$percen3 = "0";
}else{
	$percen3 = ($row[tooth_rupt2])/($row[chk_tooth])*100;	
}
	$percent3 = number_format($percen3, 2, '.', '');
if($row[chk_tooth] == "0"){
	$percen4 = "0";
}else{
	$percen4 = ($row[tooth_rupt3])/($row[chk_tooth])*100;	
}
	$percent4 = number_format($percen4, 2, '.', '');
if($row[chk_tooth] == "0"){
	$percen5 = "0";
}else{
	$percen5 = ($row[tooth_rupt4])/($row[chk_tooth])*100;	
}
	$percent5 = number_format($percen5, 2, '.', '');
	$sum_per = $sum_per+$row[per];
	$sum_chk_tooth = $sum_chk_tooth+$row[chk_tooth];
	$sum_tooth_rupt1 = $sum_tooth_rupt1+$row[tooth_rupt1];
	$sum_tooth_rupt2 = $sum_tooth_rupt2+$row[tooth_rupt2];
	$sum_tooth_rupt3 = $sum_tooth_rupt3+$row[tooth_rupt3];
	$sum_tooth_rupt4 = $sum_tooth_rupt4+$row[tooth_rupt4];
if($sum_per == "0"){
	$sum_percen = "0";
}else{
	$sum_percen = $sum_chk_tooth/$sum_per*100;	
}
	$sum_percent1 = number_format($sum_percen, 2, '.', '');
if($sum_chk_tooth == "0"){
	$sum_percen2 = "0";
}else{
	$sum_percen2 = $sum_tooth_rupt1/$sum_chk_tooth*100;	
}
	$sum_percent2 = number_format($sum_percen2, 2, '.', '');
if($sum_chk_tooth == "0"){
	$sum_percen3 = "0";
}else{
	$sum_percen3 = $sum_tooth_rupt2/$sum_chk_tooth*100;	
}
	$sum_percent3 = number_format($sum_percen3, 2, '.', '');
if($sum_chk_tooth == "0"){
	$sum_percen4 = "0";
}else{
	$sum_percen4 = $sum_tooth_rupt3/$sum_chk_tooth*100;	
}
	$sum_percent4 = number_format($sum_percen4, 2, '.', '');
if($sum_chk_tooth == "0"){
	$sum_percen5 = "0";
}else{
	$sum_percen5 = $sum_tooth_rupt4/$sum_chk_tooth*100;	
}
	$sum_percent5 = number_format($sum_percen5, 2, '.', '');
++$i;
	if(($i%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$i</div></td>
    <td>$row[villname]</td>
    <td><div align='center'>&nbsp;$moo</div></td>
    <td><div align='center'>$row[per]</div></td>
	<td><div align='center'>$row[chk_tooth]</div></td>
	<td><div align='center'>$percent1</div></td>
	<td><div align='center'>$row[tooth_rupt1]</div></td>
	<td><div align='center'>$percent2</div></td>
	<td><div align='center'>$row[tooth_rupt2]</div></td>
	<td><div align='center'>$percent3</div></td>
	<td><div align='center'>$row[tooth_rupt3]</div></td>
	<td><div align='center'>$percent4</div></td>
	<td><div align='center'>$row[tooth_rupt4]</div></td>
	<td><div align='center'>$percent5</div></td>
  </tr>
  ";
}
$txt .= "<tr>
  <td>&nbsp;&nbsp;</td>
  <td><div align='center'>&nbsp;รวม</td>
  <td>&nbsp;&nbsp;</td>
  <td><div align='center'>$sum_per</div></td>
  <td><div align='center'>$sum_chk_tooth</div></td>
  <td><div align='center'>$sum_percent1</div></td>
  <td><div align='center'>$sum_tooth_rupt1</div></td>
  <td><div align='center'>$sum_percent2</div></td>
  <td><div align='center'>$sum_tooth_rupt2</div></td>
  <td><div align='center'>$sum_percent3</div></td>
  <td><div align='center'>$sum_tooth_rupt3</div></td>
  <td><div align='center'>$sum_percent4</div></td>
  <td><div align='center'>$sum_tooth_rupt4</div></td>
  <td><div align='center'>$sum_percent5</div></td>
  </tr></table><p div align='right' class='text-danger'>ข้อมูล ณ วันที่  $strx</p></div><br>";    
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
