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
	$wvill = " and house.villcode='$villcode' ";	
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
count(distinct pid) as per,
count(distinct pid1) as per_chronic,
count(distinct visitno) as chronic_visit
from
(SELECT
person.pcucodeperson,
person.pid,
person.fname,
CONCAT(ctitle.titlename,person.fname,' ',person.lname) AS pname,
person.birth,
getageyearnum(person.birth,now()) as age,
house.hcode,
house.hno,
right(house.villcode,2) as moo,
house.villcode,
village.villname,
house.xgis,
house.ygis,
group_concat(personchronic.chroniccode) as chronic_code,
group_concat(cdiseasechronic.groupname) as chronic_name
FROM
personchronic
inner Join person ON personchronic.pcucodeperson = person.pcucodeperson AND personchronic.pid = person.pid
Inner Join house ON person.pcucodeperson = house.pcucode AND person.hcode = house.hcode
Inner Join village ON house.pcucode = village.pcucode AND house.villcode = village.villcode
left Join ctitle ON person.prename = ctitle.titlecode
Inner Join cdisease ON personchronic.chroniccode = cdisease.diseasecode
Inner Join cdiseasechronic ON cdisease.codechronic = cdiseasechronic.groupcode
where person.pid NOT IN (SELECT persondeath.pid FROM persondeath WHERE persondeath.pcucodeperson= person.pcucodeperson and (persondeath.deaddate IS NULL OR persondeath.deaddate<=now()))  and right(house.villcode,2) <> '00' and cdiseasechronic.groupcode = '16' $wvill
group by personchronic.pcucodeperson,personchronic.pid
order by house.villcode,person.fname) as per_chronic 
left join
(SELECT
visit.pcucodeperson as pcucodeperson1,
visit.pid as pid1,
visit.visitno,
visit.visitdate,
chomehealthtype.homehealthmeaning,
visithomehealthindividual.patientsign,
visithomehealthindividual.homehealthdetail,
visithomehealthindividual.homehealthresult,
visithomehealthindividual.homehealthplan,
visithomehealthindividual.dateappoint,
concat(ctitle.titlename,`user`.fname,`user`.lname) as userh,
visithomehealthindividual.`user`
FROM
visit
Inner Join visithomehealthindividual ON visit.pcucode = visithomehealthindividual.pcucode AND visit.visitno = visithomehealthindividual.visitno
Inner Join chomehealthtype ON visithomehealthindividual.homehealthtype = chomehealthtype.homehealthcode
INNER JOIN `user` ON visit.pcucodeperson = `user`.pcucode AND visithomehealthindividual.`user` = `user`.username
left JOIN ctitle ON `user`.prename = ctitle.titlecode
where visit.visitdate between '$str' and '$sto' and (visit.flagservice <'04' OR visit.flagservice is null OR length(trim(visit.flagservice))=0)) as per_homevisit
on per_chronic.pcucodeperson = per_homevisit.pcucodeperson1 and per_chronic.pid = per_homevisit.pid1
group by villcode
order by villcode";

$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>จำนวนผู้ป่วยวัณโรคที่ได้รับการเยี่ยมบ้าน</b><br>';
$txt .= "<b> $mu </b></p><br><b>$hosp</b><table width='95%' border='0' cellspacing='1' cellpadding='1' class='table table-striped table-hover table-bordered'>
  <tr>
    <th width='5%' scope='col'><div align='center'>ลำดับ</div></th>
    <th width='12%' scope='col'><div align='center'>หมู่บ้าน</div></th>
	<th width='8%' scope='col'><div align='center'>หมู่ที่</div></th>
    <th width='8%' scope='col'><div align='center'>ผู้ป่วยวัณโรคทั้งหมด</div></th>
    <th width='6%' scope='col'><div align='center'>ได้รับการเยี่ยมบ้าน(คน)</div></th>
	<th width='6%' scope='col'><div align='center'>ร้อยละ</div></th>
	<th width='6%' scope='col'><div align='center'>ได้รับการเยี่ยมบ้าน(ครั้ง)</div></th>
	<th width='6%' scope='col'><div align='center'>ครั้ง : คน</div></th>
  </tr>";
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
if($row[per] == "0"){
	$percen = "0";
}else{
	$percen = ($row[per_chronic])/($row[per])*100;	
}
if($row[per_chronic] == "0"){
	$percen2 = "0";
}else{
	$percen2 = ($row[chronic_visit])/($row[per_chronic]);	
}
	$percent1 = number_format($percen, 2, '.', '');
	$percent2 = number_format($percen2, 2, '.', '');
	$sum_per = $sum_per+$row[per];
	$sum_per_chronic = $sum_per_chronic+$row[per_chronic];
	$sum_chronic_visit = $sum_chronic_visit+$row[chronic_visit];
if($sum_per == "0"){
	$sum_percen = "0";
}else{
	$sum_percen = $sum_per_chronic/$sum_per*100;	
}
	$sum_percent1 = number_format($sum_percen, 2, '.', '');
if($sum_per_chronic == "0"){
	$sum_percen2 = "0";
}else{
	$sum_percen2 = $sum_chronic_visit/$sum_per_chronic;	
}
	$sum_percent2 = number_format($sum_percen2, 2, '.', '');
++$i;
	if(($i%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$i</div></td>
    <td>$row[villname]</td>
    <td><div align='center'>&nbsp;$moo</div></td>
    <td><div align='center'>$row[per]</div></td>
	<td><div align='center'>$row[per_chronic]</div></td>
	<td><div align='center'>$percent1</div></td>
	<td><div align='center'>$row[chronic_visit]</div></td>
	<td><div align='center'>$percent2</div></td>
  </tr>
  ";
}
$txt .= "<tr>
  <td>&nbsp;&nbsp;</td>
  <td><div align='center'>&nbsp;รวม</td>
  <td>&nbsp;&nbsp;</td>
  <td><div align='center'>$sum_per</div></td>
  <td><div align='center'>$sum_per_chronic</div></td>
  <td><div align='center'>$sum_percent1</div></td>
  <td><div align='center'>$sum_chronic_visit</div></td>
  <td><div align='center'>$sum_percent2</div></td>
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
