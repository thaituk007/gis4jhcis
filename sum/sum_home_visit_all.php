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
	$wvill = " and h.villcode='$villcode' ";	
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
sum(case when count_visit > 0 then 1 else 0 end) as per_old,
sum(count_visit) as old_visit
from
(SELECT
p.pcucodeperson,
p.pid,
p.fname,
CONCAT(ctitle.titlename,p.fname,' ',p.lname) AS pname,
h.hno,
h.villcode,
village.villname,
h.xgis,
h.ygis,
p.birth,
FLOOR((TO_DAYS(NOW())-TO_DAYS(p.birth))/365.25) AS age
FROM
village
inner join house AS h on h.pcucode = village.pcucode and h.villcode = village.villcode
Inner Join person AS p ON h.pcucode = p.pcucodeperson AND h.hcode = p.hcode
Inner Join cstatus ON p.marystatus = cstatus.statuscode
left Join ctitle ON p.prename = ctitle.titlecode
WHERE ((p.dischargetype is null) or (p.dischargetype = '9')) AND
				SUBSTRING(h.villcode,7,2) <> '00'
				$wvill ORDER BY h.villcode, p.fname
) as per
left join 
(SELECT
person.pcucodeperson as pcucodeperson1,
person.pid as pid1,
count(distinct visit.visitno) as count_visit,
visit.symptoms,
visit.vitalcheck,
visitdiag.diagcode,
max(visit.visitdate) as visitdate,
visithomehealthindividual.homehealthtype,
chomehealthtype.homehealthmeaning,
visithomehealthindividual.patientsign,
visithomehealthindividual.homehealthdetail,
visithomehealthindividual.homehealthresult,
visithomehealthindividual.homehealthplan,
visithomehealthindividual.dateappoint,
visithomehealthindividual.`user`,
concat(ctitle.titlename,`user`.fname,`user`.lname) as userh
from
village
INNER JOIN house ON village.pcucode = house.pcucode AND village.villcode = house.villcode
INNER JOIN person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
INNER JOIN visit ON person.pcucodeperson = visit.pcucodeperson AND person.pid = visit.pid
inner join visithomehealthindividual ON visit.pcucode = visithomehealthindividual.pcucode AND visit.visitno = visithomehealthindividual.visitno
INNER JOIN visitdiag ON visit.pcucode = visitdiag.pcucode AND visit.visitno = visitdiag.visitno
inner join `user` on `user`.username = visithomehealthindividual.`user`
left join ctitle on ctitle.titlecode = `user`.prename
inner join chomehealthtype on chomehealthtype.homehealthcode = visithomehealthindividual.homehealthtype
where visit.visitdate between '$str' and '$sto' and ((person.dischargetype is null) or (person.dischargetype = '9')) AND
				SUBSTRING(house.villcode,7,2) <> '00' AND 
				(visit.flagservice <'04' OR visit.flagservice is null OR length(trim(visit.flagservice))=0 )
group by visit.pid) as fp
on per.pcucodeperson = fp.pcucodeperson1 and per.pid = fp.pid1
group by villcode
order by villcode";

$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>ประชาชนที่ได้รับการเยี่ยมบ้าน</b><br>';
$txt .= "<b>$mu </b></p><br><b>$hosp</b><table width='95%' border='0' cellspacing='1' cellpadding='1' class='table table-striped table-hover table-bordered'>
  <tr>
    <th width='5%' scope='col'><div align='center'>ลำดับ</div></th>
    <th width='12%' scope='col'><div align='center'>หมู่บ้าน</div></th>
	<th width='8%' scope='col'><div align='center'>หมู่ที่</div></th>
    <th width='8%' scope='col'><div align='center'>ประชากรทั้งหมด</div></th>
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
	$percen = ($row[per_old])/($row[per])*100;	
}
if($row[per_old] == "0"){
	$percen2 = "0";
}else{
	$percen2 = ($row[old_visit])/($row[per_old]);	
}
	$percent1 = number_format($percen, 2, '.', '');
	$percent2 = number_format($percen2, 2, '.', '');
	$sum_per = $sum_per+$row[per];
	$sum_per_old = $sum_per_old+$row[per_old];
	$sum_old_visit = $sum_old_visit+$row[old_visit];
if($sum_per == "0"){
	$sum_percen = "0";
}else{
	$sum_percen = $sum_per_old/$sum_per*100;	
}
	$sum_percent1 = number_format($sum_percen, 2, '.', '');
if($sum_per_old == "0"){
	$sum_percen2 = "0";
}else{
	$sum_percen2 = $sum_old_visit/$sum_per_old;	
}
	$sum_percent2 = number_format($sum_percen2, 2, '.', '');
++$i;
	if(($i%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$i</div></td>
    <td>$row[villname]</td>
    <td><div align='center'>&nbsp;$moo</div></td>
    <td><div align='center'>$row[per]</div></td>
	<td><div align='center'>$row[per_old]</div></td>
	<td><div align='center'>$percent1</div></td>
	<td><div align='center'>$row[old_visit]</div></td>
	<td><div align='center'>$percent2</div></td>
  </tr>
  ";
}
$txt .= "<tr>
  <td>&nbsp;&nbsp;</td>
  <td><div align='center'>&nbsp;รวม</td>
  <td>&nbsp;&nbsp;</td>
  <td><div align='center'>$sum_per</div></td>
  <td><div align='center'>$sum_per_old</div></td>
  <td><div align='center'>$sum_percent1</div></td>
  <td><div align='center'>$sum_old_visit</div></td>
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
