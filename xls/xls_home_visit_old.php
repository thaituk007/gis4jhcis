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
$chk_stool = $_GET[chk_stool];
if($chk_stool == "1"){
	$chksto = "";
}elseif($chk_stool == "2"){
	$chksto = "where count_visit is not null";	
}else{
	$chksto = "where count_visit is null";	
}
$str = retdaterangstr($_GET[str]);
$sto = retdaterangsto($_GET[str]);
$strx = retDatets($str);
$stox = retDatets($sto);
$sql = "select
*
from
(SELECT
p.pcucodeperson,
p.pid,
p.fname,
CONCAT(ctitle.titlename,p.fname,' ',p.lname) AS pname,
h.hno,
h.villcode,
h.xgis,
h.ygis,
p.birth,
FLOOR((TO_DAYS(NOW())-TO_DAYS(p.birth))/365.25) AS age
FROM
house AS h
Inner Join person AS p ON h.pcucode = p.pcucodeperson AND h.hcode = p.hcode
Inner Join cstatus ON p.marystatus = cstatus.statuscode
left Join ctitle ON p.prename = ctitle.titlecode
WHERE ((p.dischargetype is null) or (p.dischargetype = '9')) AND
				SUBSTRING(h.villcode,7,2) <> '00' AND
				FLOOR((TO_DAYS(NOW())-TO_DAYS(p.birth))/365.25) > 59 $wvill ORDER BY h.villcode, p.fname
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
visithomehealthindividual.patientsign,
visithomehealthindividual.homehealthdetail,
visithomehealthindividual.homehealthresult,
visithomehealthindividual.homehealthplan,
visithomehealthindividual.dateappoint,
visithomehealthindividual.`user`,
concat(ctitle.titlename,`user`.fname,'  ',`user`.lname) as userh
from
village
INNER JOIN house ON village.pcucode = house.pcucode AND village.villcode = house.villcode
INNER JOIN person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
INNER JOIN visit ON person.pcucodeperson = visit.pcucodeperson AND person.pid = visit.pid
inner join visithomehealthindividual ON visit.pcucode = visithomehealthindividual.pcucode AND visit.visitno = visithomehealthindividual.visitno
INNER JOIN visitdiag ON visit.pcucode = visitdiag.pcucode AND visit.visitno = visitdiag.visitno
inner join `user` on `user`.username = visithomehealthindividual.`user`
left join ctitle on ctitle.titlecode = `user`.prename
where visit.visitdate between '$str' and '$sto' and ((person.dischargetype is null) or (person.dischargetype = '9')) AND
				SUBSTRING(house.villcode,7,2) <> '00' AND 
				FLOOR((TO_DAYS(NOW())-TO_DAYS(person.birth))/365.25) > 59 and (visit.flagservice <'04' OR visit.flagservice is null OR length(trim(visit.flagservice))=0 )
group by visit.pid) as fp
on per.pcucodeperson = fp.pcucodeperson1 and per.pid = fp.pid1
$chksto
order by villcode, fname";

$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>ผู้สูงอายุที่ได้รับการเยี่ยมบ้าน</b><br>';
$txt .= "<b>ข้อมูลระหว่างวันที่ $strx ถึง $stox  $mu </b></p><br><b>$hosp</b><table width='98%' border='0' cellspacing='1' cellpadding='1' class='table table-striped table-hover table-bordered'>
  <tr>
    <th width='4%' scope='col'><div align='center'>ลำดับ</div></th>
    <th width='9%' scope='col'><div align='center'>ชื่อ - สกุล</div></th>
	<th width='3%' scope='col'><div align='center'>อายุ</div></th>
    <th width='4%' scope='col'><div align='center'>บ้านเลขที่</div></th>
    <th width='3%' scope='col'><div align='center'>หมู่ที่</div></th>
	<th width='4%' scope='col'><div align='center'>เยี่ยม(ครั้ง)</div></th>
    <th width='7%' scope='col'><div align='center'>วันที่เยี่ยมล่าสุด</div></th>
	<th width='10%' scope='col'><div align='center'>สภาพ/อาการของเป้าหมาย</div></th>
	<th width='25%' scope='col'><div align='center'>กิจกรรม</div></th>
	<th width='15%' scope='col'><div align='center'>ประเมินผล</div></th>
	<th width='7%' scope='col'><div align='center'>วันนัดครั้งต่อไป</div></th>
	<th width='12%' scope='col'><div align='center'>ผู้เยี่ยม</div></th>
  </tr>";
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$birth = retDatets($row[birth]);
	if($row[dateappoint] == ""){$dateappoint = "";}else{$dateappoint = retDatets($row[dateappoint]);}
	if($row[visitdate] == ""){$sickre = "";}else{$sickre = retDatets($row[visitdate]);}
	if($row[visitdate] == ""){$sick = "--/--/----";}else{$sick = retDatets($row[visitdate]);}
	if($row[count_visit] == ""){$count_visit = "--";}else{$count_visit = $row[count_visit];}
	if($row[count_visit] != ""){$chk = "ได้รับการเยี่ยม";}else{$chk = "ไม่ได้เยี่ยม";}
++$i;
	if(($i%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$i</div></td>
    <td>$row[pname]</td>
	<td><div align='center'>&nbsp;$row[age]</div></td>
    <td><div align='center'>&nbsp;$row[hno]</div></td>
    <td><div align='center'>&nbsp;$moo</div></td>
    <td><div align='center'>&nbsp;$row[count_visit]</div></td>
	<td><div align='center'>&nbsp;$sickre</div></td>
	<td>&nbsp;$row[patientsign]</td>
	<td>&nbsp;$row[homehealthdetail]</td>
	<td>&nbsp;$row[homehealthresult]</td>
	<td><div align='center'>&nbsp;$dateappoint</div></td>
	<td>&nbsp;$row[userh]</td>
  </tr>";
}
$txt .= "</table><br>";  
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
