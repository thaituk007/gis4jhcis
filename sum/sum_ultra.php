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
$chk_ultra = $_GET[chk_ultra];
if($chk_ultra == "2"){
	$chksto = "and tmp.vitalcheck is not null";
}elseif($chk_ultra == "3"){
	$chksto = "and tmp.vitalcheck not like 'ปกติ' and tmp.vitalcheck is not null";
}elseif($chk_ultra == "4"){
	$chksto = "and tmp.vitalcheck is null";		
}else{
	$chksto = "";	
}
$str = retdaterangstr($_GET[str]);
$sto = retdaterangsto($_GET[str]);
$strx = retDatets($str);
$stox = retDatets($sto);
$ovyear = substr($sto,0,4);			
$sql = "SELECT
tmp2.pcucode,
tmp2.villcode,
tmp2.villname,
count(distinct tmp2.pid) as x,
count(distinct tmp2.ultrapid) as y
FROM
(SELECT
person.pcucodeperson,
person.pid,
person.idcard,
CONVERT(concat(ifnull(ctitle.titlename,ifnull(person.prename,'ไม่ระบุ') ),person.fname,' ' ,person.lname) using utf8) as pname,
ctitle.titlename,
person.fname,
person.lname,
person.birth,
getageyearnum(person.birth,visit.visitdate) AS age,
house.hno,
house.villcode,
village.villname,
house.xgis,
house.ygis,
house.usernamedoc,
visit.pcucode,
visit.visitno,
visit.visitdate,
visit.symptoms,
visit.vitalcheck,
group_concat(visitdiag.diagcode) as xx,
tmp.pid as ultrapid,
tmp.visitno as ultravisitno,
tmp.visitdate as ultravisitdate,
tmp.symptoms as ultrasys,
tmp.vitalcheck as ultravital,
tmp.diagcode as ultradiag
FROM
village
INNER JOIN house ON village.pcucode = house.pcucode AND village.villcode = house.villcode
INNER JOIN person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
INNER JOIN visit ON person.pcucodeperson = visit.pcucodeperson AND person.pid = visit.pid
INNER JOIN visitdiag ON visit.pcucode = visitdiag.pcucode AND visit.visitno = visitdiag.visitno
LEFT JOIN ctitle ON person.prename = ctitle.titlecode
left JOIN
(SELECT
v.pcucode,
v.visitno,
v.visitdate,
v.pcucodeperson,
v.pid,
v.symptoms,
v.vitalcheck,
vd.diagcode
FROM
visit v
INNER JOIN visitdiag vd ON v.pcucode = vd.pcucode AND v.visitno = vd.visitno
where v.visitdate between '$str' and '$sto' and v.symptoms like '%ตรวจอัลตร้าซาว%' and vd.diagcode like 'Z12.8') as tmp
on tmp.pcucodeperson = visit.pcucodeperson and tmp.pid = visit.pid
where right(house.villcode,2) <> '00' and getageyearnum(person.birth,visit.visitdate) > 39 and visit.visitdate between '$str' and '$sto' $wvill
group by visit.pcucode, visit.visitno
having xx like '%Z11.6%' and xx like '%B66%') as tmp2
group by tmp2.pcucode, tmp2.villname
order by tmp2.pcucode, tmp2.villcode";

$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>จำนวนประชาชนอายุ 40 ปี ขึ้นไป ที่ตรวจพบพยาธิใบไม้ตับ ได้รับการตรวจอัลตร้าซาวด์';
$txt .= "<br>$mu </b></p><br><b>$hosp</b><table width='95%' border='0' cellspacing='1' cellpadding='1' class='table table-striped table-hover table-bordered'>
  <tr>
    <th width='5%' scope='col'><div align='center'>ลำดับ</div></th>
    <th width='12%' scope='col'><div align='center'>หมู่บ้าน</div></th>
	<th width='8%' scope='col'><div align='center'>หมู่ที่</div></th>
    <th width='8%' scope='col'><div align='center'>ประชาชนอายุ40ปีขึ้นไปที่ตรวจพบพยาธิใบไม้ตับ</div></th>
    <th width='5%' scope='col'><div align='center'>ได้รับการตรวจอัลตร้าซาวด์</div></th>
	<th width='5%' scope='col'><div align='center'>ร้อยละ</div></th>
  </tr>";
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
if($row[x] == "0"){
	$percen = "0";
}else{
	$percen = ($row[y])/($row[x])*100;	
}
	$percent1 = number_format($percen, 2, '.', '');
	$sumx = $sumx+$row[x];
	$sumy = $sumy+$row[y];
if($sumx == "0"){
	$percen2 = "0";
}else{
	$percen2 = ($sumy)/($sumx)*100;	
}
	$sum_percent2 = number_format($percen2, 2, '.', '');
++$i;
	if(($i%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$i</div></td>
    <td>$row[villname]</td>
    <td><div align='center'>$moo</div></td>
    <td><div align='center'>$row[x]</div></td>
	<td><div align='center'>$row[y]</div></td>
	<td><div align='center'>$percent1</div></td>
  </tr>
  ";
}
$txt .= "<tr>
  <td>&nbsp;&nbsp;</td>
  <td><div align='center'>&nbsp;รวม</td>
  <td>&nbsp;&nbsp;</td>
  <td><div align='center'>$sumx</div></td>
  <td><div align='center'>$sumy</div></td>
  <td><div align='center'>$sum_percent2</div></td>
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
