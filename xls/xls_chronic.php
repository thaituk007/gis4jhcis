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
$chronic = $_GET[chronic];
	$village = $_GET[village];
	if($village == "00000000"){
		$wvill = "";
	}else{
		$wvill = "AND house.villcode='$village'";	
	}
	if($village == "00000000"){
	$mu = "ทุกหมู่บ้าน";
}else{
	$mu = getvillagename($village);	
}
	if($chronic == '00'){$ect = "";}else{$ect = "having chronicc like '%$chronic%'";}	
$sql = "select villname,concat(ifnull(titlename,'..') ,fname,' ',lname) as pname, FLOOR((TO_DAYS(NOW())-TO_DAYS(person.birth))/365.25) as age, house.hno,house.villcode,house.xgis,house.ygis,person.idcard,person.pcucodeperson,person.pid,pc.datefirstdiag,pc.datedxfirst,pc.datedischart,
CASE
when pc.typedischart='01' then 'หาย'
when pc.typedischart='02' then 'ตาย'
when pc.typedischart='03' then 'ยังรักษาอยู่ฯ'
when pc.typedischart='04' then 'ไม่ทราบ(ไม่มีข้อมูล)'
when pc.typedischart='05' then 'รอการจำหน่าย/เฝ้าระวัง'
when pc.typedischart='06' then 'ยังรักษาอยู่ฯ'
when pc.typedischart='07' then 'ครบการรักษาฯ'
when pc.typedischart='08' then 'โรคอยู่ในภาวะสงบฯ'
when pc.typedischart='09' then 'ปฏิเสธการรักษาฯ'
when pc.typedischart='10' then 'ออกจากพื้นที่'
else null end AS typedischart,pc.cup
,group_concat(dc.groupcode) as chronicc
,group_concat(dc.groupname) as chronicx
FROM personchronic pc
left join person ON pc.pid = person.pid and pc.pcucodeperson = person.pcucodeperson
left join house ON person.hcode = house.hcode and person.pcucodeperson = house.pcucode
left join village ON house.villcode = village.villcode
left join ctitle ON person.prename = ctitle.titlecode
left Join cdisease d ON pc.chroniccode = d.diseasecode
left Join cdiseasechronic dc ON d.codechronic = dc.groupcode
where SUBSTRING(house.villcode,7,2) <> '00' AND pc.typedischart NOT IN  ('01', '02','07','10') and person.pid NOT IN (SELECT persondeath.pid FROM persondeath WHERE persondeath.pcucodeperson= person.pcucodeperson and (persondeath.deaddate IS NULL OR persondeath.deaddate<=now())) $wvill
group by pc.pcucodeperson, pc.pid
$ect
ORDER BY village.villcode,house.hno";

$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>รายชื่อผู้ป่วยโรคเรื้อรัง ';
$txt .= "  $mu </b></p><br><b>$hosp</b><table width='99%' border='0' cellspacing='1' cellpadding='1' class='table table-striped table-hover table-bordered'>
  <tr>
    <th width='5%' scope='col'><div align='center'>ลำดับ</div></th>
    <th width='14%' scope='col'><div align='center'>ชื่อ - สกุล</div></th>
	<th width='4%' scope='col'><div align='center'>อายุ</div></th>
    <th width='5%' scope='col'><div align='center'>บ้านเลขที่</div></th>
    <th width='4%' scope='col'><div align='center'>หมู่ที่</div></th>
    <th width='13%' scope='col'><div align='center'>โรคเรื้อรัง</div></th>
	<th width='9%' scope='col'><div align='center'>วันที่วินิจฉัยครั้งแรก</div></th>
	<th width='7%' scope='col'><div align='center'>สถานะปัจจุบัน</div></th>
	<th width='17%' scope='col'><div align='center'>รพ.ประจำ</div></th>
  </tr>";
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$title = getTitle($row[prename]);
	$pcuname = gethospname($row[cup]);
	if($row[datefirstdiag] == ""){$sick = "...";}else{$sick = retDatets($row[datefirstdiag]);}
	if($row[datedxfirst] == ""){$last = "...";}else{$last = retDatets($row[datedxfirst]);}
++$i;
	if(($i%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$i</div></td>
    <td>$row[pname]</td>
	<td><div align='center'>$row[age]</div></td>
    <td><div align='center'>$row[hno]</div></td>
    <td><div align='center'>$moo</div></td>
    <td>&nbsp;$row[chronicx]</td>
	<td>&nbsp;$sick</td>
	<td><div align='center'>$row[typedischart]</div></td>
	<td>&nbsp;$pcuname</td>
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
