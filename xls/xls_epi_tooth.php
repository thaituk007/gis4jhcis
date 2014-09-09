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
	$wvill = " AND village.villcode='$villcode' ";	
}
if($villcode == "00000000"){
	$mu = "ทุกหมู่บ้าน";
}else{
	$mu = getvillagename($villcode);		
}
$str = $_GET[str];
$strx = retDatets($str);	
$sql = "select
*,
if(toothmilk is not null,'1','0') as chk
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
where  (person.dischargetype Is Null Or person.dischargetype='9') and right(house.villcode,2) <> '00' and ROUND(DATEDIFF('$str',person.birth)/30)  Between 9 And 24 $wvill and (visit.flagservice <'04' OR visit.flagservice is null OR length(trim(visit.flagservice))=0 )
GROUP BY person.pid) as tmp_epi
order by villcode, fname";

$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>รายงานเด็กอายุ 9 - 12 เดือน ที่มารับวัคซีนได้รับการตรวจฟัน</b><br>';
$txt .= "<b>ข้อมูล ณ วันที่ $strx $mu </b></p><br><b>$hosp</b><table width='98%' border='0' cellspacing='1' cellpadding='1' class='table table-striped table-hover table-bordered'>
  <tr>
    <th width='4%' scope='col'><div align='center'>ลำดับ</div></th>
    <th width='13%' scope='col'><div align='center'>ชื่อ - สกุล</div></th>
	<th width='8%' scope='col'><div align='center'>ว/ด/ป เกิด</div></th>
	<th width='3%' scope='col'><div align='center'>อายุ</div></th>
    <th width='4%' scope='col'><div align='center'>บ้านเลขที่</div></th>
    <th width='3%' scope='col'><div align='center'>หมู่ที่</div></th>
	<th width='7%' scope='col'><div align='center'>ฟันน้ำนม(ซี่)</div></th>
	<th width='7%' scope='col'><div align='center'>ฟันน้ำนมผู(ซี่)</div></th>
	<th width='7%' scope='col'><div align='center'>ฟันแท้(ซี่)</div></th>
	<th width='7%' scope='col'><div align='center'>ฟันแท้ผุ(ซี่)</div></th>
	<th width='7%' scope='col'><div align='center'>พบหินน้ำลาย</div></th>
	<th width='7%' scope='col'><div align='center'>พบเหงือกอักเสบ</div></th>
  </tr>";
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	if($row[chk] == "1"){$epi_chk = 'ได้รับการตรวจฟัน';}else{$epi_chk = 'ไม่ได้รับการตรวจฟัน';}
	if($row[chk] == "0"){$toothmilk = '---';}else{$toothmilk = $row[toothmilk];}
	if($row[chk] == "0"){$toothmilkcorrupt = '---';}else{$toothmilkcorrupt = $row[toothmilkcorrupt];}
	if($row[chk] == "0"){$toothpermanent = '---';}else{$toothpermanent = $row[toothpermanent];}
	if($row[chk] == "0"){$toothpermanentcorrupt = '---';}else{$toothpermanentcorrupt = $row[toothpermanentcorrupt];}
	if($row[chk] == "0"){$tartar = '---';}else{$tartar = $row[tartar];}
	if($row[chk] == "0"){$gumstatus = '---';}else{$gumstatus = $row[gumstatus];}
	$birth = retDatets($row[birth]);
++$i;
	if(($i%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$i</div></td>
    <td>$row[pname]</td>
	<td><div align='center'>&nbsp;$birth</div></td>
	<td><div align='center'>&nbsp;$row[age]</div></td>
    <td><div align='center'>&nbsp;$row[hno]</div></td>
    <td><div align='center'>&nbsp;$moo</div></td>
    <td><div align='center'>&nbsp;$toothmilk</div></td>
	<td><div align='center'>&nbsp;$toothmilkcorrupt</div></td>
	<td><div align='center'>&nbsp;$toothpermanent</div></td>
	<td><div align='center'>&nbsp;$toothpermanentcorrupt</div></td>
	<td><div align='center'>&nbsp;$tartar</div></td>
	<td><div align='center'>&nbsp;$gumstatus</div></td>
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
