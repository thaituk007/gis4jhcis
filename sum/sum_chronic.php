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
	if($chronic == '00'){$ect = "";}else{$ect = "AND dc.groupcode = '$chronic'";}			
$sql = "select pc.pcucodeperson,village.villname,house.villcode
,sum(case when codechronic ='01' then 1 else 0 end) as chronic01
,sum(case when codechronic ='02' then 1 else 0 end) as chronic02
,sum(case when codechronic ='03' then 1 else 0 end) as chronic03
,sum(case when codechronic ='04' then 1 else 0 end) as chronic04
,sum(case when codechronic ='05' then 1 else 0 end) as chronic05
,sum(case when codechronic ='06' then 1 else 0 end) as chronic06
,sum(case when codechronic ='07' then 1 else 0 end) as chronic07
,sum(case when codechronic ='08' then 1 else 0 end) as chronic08
,sum(case when codechronic ='09' then 1 else 0 end) as chronic09
,sum(case when codechronic ='10' then 1 else 0 end) as chronic10
,sum(case when codechronic ='11' then 1 else 0 end) as chronic11
,sum(case when codechronic ='12' then 1 else 0 end) as chronic12
,sum(case when codechronic ='13' then 1 else 0 end) as chronic13
,sum(case when codechronic ='14' then 1 else 0 end) as chronic14
,sum(case when codechronic ='15' then 1 else 0 end) as chronic15
,sum(case when codechronic ='16' then 1 else 0 end) as chronic16
,sum(case when codechronic ='17' then 1 else 0 end) as chronic17
,sum(case when codechronic ='18' then 1 else 0 end) as chronic18
,sum(case when codechronic ='19' then 1 else 0 end) as chronic19
,sum(case when codechronic ='20' then 1 else 0 end) as chronic20
FROM personchronic pc
left join person ON pc.pid = person.pid and pc.pcucodeperson = person.pcucodeperson
left join house ON person.hcode = house.hcode and person.pcucodeperson = house.pcucode
left join village ON house.villcode = village.villcode
left join ctitle ON person.prename = ctitle.titlecode
left join cdisease ON pc.chroniccode = cdisease.diseasecode
where SUBSTRING(house.villcode,7,2) <> '00' AND pc.typedischart NOT IN  ('01', '02','07','10') and person.pid NOT IN (SELECT persondeath.pid FROM persondeath WHERE persondeath.pcucodeperson= person.pcucodeperson and (persondeath.deaddate IS NULL OR persondeath.deaddate<=now())) $wvill
group by house.villcode
ORDER BY village.villcode";

$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>จำนวนผู้ป่วยโรคเรื้อรังจำแนกรายหมู่บ้านรายโรค';
$txt .= " $mu </b></p><br><b>$hosp</b><table width='99%' border='0' cellspacing='1' cellpadding='1' class='table table-striped table-hover table-bordered'>
  <tr>
    <th width='5%' scope='col'><div align='center'>ลำดับ</div></th>
    <th width='7' scope='col'><div align='center'>หมู่บ้าน</div></th>
	<th width='4%' scope='col'><div align='center'>หมู่ที่</div></th>
    <th width='4%' scope='col'><div align='center'>ความดันสูง</div></th>
    <th width='4%' scope='col'><div align='center'>โรคหอบหืด</div></th>
	<th width='4%' scope='col'><div align='center'>หัวใจขาดเลือด</div></th>
	<th width='4%' scope='col'><div align='center'>มะเร็ง</div></th>
	<th width='4%' scope='col'><div align='center'>โลหิตจาง</div></th>
	<th width='4%' scope='col'><div align='center'>โรคซึมเศร้า</div></th>
	<th width='4%' scope='col'><div align='center'>หลอดเลือดสมอง</div></th>
	<th width='4%' scope='col'><div align='center'>อัมพฤกษ์ อัมพาต</div></th>
	<th width='4%' scope='col'><div align='center'>ไตวาย</div></th>
	<th width='4%' scope='col'><div align='center'>เบาหวาน</div></th>
	<th width='4%' scope='col'><div align='center'>หลอดลมอักเสบเรื้อรัง</div></th>
	<th width='4%' scope='col'><div align='center'>ถุงลมโป่งพอง</div></th>
	<th width='4%' scope='col'><div align='center'>โรคหัวใจ</div></th>
	<th width='4%' scope='col'><div align='center'>พิษสุราเรื้อรัง</div></th>
	<th width='4%' scope='col'><div align='center'>ทางเดินหายใจอุดตันเรื้อรัง</div></th>
	<th width='4%' scope='col'><div align='center'>วัณโรค</div></th>
	<th width='4%' scope='col'><div align='center'>โรคอ้วน</div></th>
	<th width='4%' scope='col'><div align='center'>โรคเอดส์</div></th>
	<th width='4%' scope='col'><div align='center'>โรคตับ</div></th>
	<th width='4%' scope='col'><div align='center'>โรคข้อและรูมาตอย</div></th>
  </tr>";
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$sum_chronic01 = $sum_chronic01+$row[chronic01];
	$sum_chronic02 = $sum_chronic02+$row[chronic02];
	$sum_chronic03 = $sum_chronic03+$row[chronic03];
	$sum_chronic04 = $sum_chronic04+$row[chronic04];
	$sum_chronic05 = $sum_chronic05+$row[chronic05];
	$sum_chronic06 = $sum_chronic06+$row[chronic06];
	$sum_chronic07 = $sum_chronic07+$row[chronic07];
	$sum_chronic08 = $sum_chronic08+$row[chronic08];
	$sum_chronic09 = $sum_chronic09+$row[chronic09];
	$sum_chronic10 = $sum_chronic10+$row[chronic10];
	$sum_chronic11 = $sum_chronic11+$row[chronic11];
	$sum_chronic12 = $sum_chronic12+$row[chronic12];
	$sum_chronic13 = $sum_chronic13+$row[chronic13];
	$sum_chronic14 = $sum_chronic14+$row[chronic14];
	$sum_chronic15 = $sum_chronic15+$row[chronic15];
	$sum_chronic16 = $sum_chronic16+$row[chronic16];
	$sum_chronic17 = $sum_chronic17+$row[chronic17];
	$sum_chronic18 = $sum_chronic18+$row[chronic18];
	$sum_chronic19 = $sum_chronic19+$row[chronic19];
	$sum_chronic20 = $sum_chronic20+$row[chronic20];
++$i;
	if(($i%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$i</div></td>
    <td>$row[villname]</td>
    <td><div align='center'>$moo</div></td>
    <td><div align='center'>$row[chronic01]</div></td>
	<td><div align='center'>$row[chronic02]</div></td>
	<td><div align='center'>$row[chronic03]</div></td>
	<td><div align='center'>$row[chronic04]</div></td>
	<td><div align='center'>$row[chronic05]</div></td>
	<td><div align='center'>$row[chronic06]</div></td>
	<td><div align='center'>$row[chronic07]</div></td>
	<td><div align='center'>$row[chronic08]</div></td>
	<td><div align='center'>$row[chronic09]</div></td>
	<td><div align='center'>$row[chronic10]</div></td>
	<td><div align='center'>$row[chronic11]</div></td>
	<td><div align='center'>$row[chronic12]</div></td>
	<td><div align='center'>$row[chronic13]</div></td>
	<td><div align='center'>$row[chronic14]</div></td>
	<td><div align='center'>$row[chronic15]</div></td>
	<td><div align='center'>$row[chronic16]</div></td>
	<td><div align='center'>$row[chronic17]</div></td>
	<td><div align='center'>$row[chronic18]</div></td>
	<td><div align='center'>$row[chronic19]</div></td>
	<td><div align='center'>$row[chronic20]</div></td>
  </tr>
  ";
}
$txt .= "<tr>
  	<td>&nbsp;&nbsp;</td>
  	<td><div align='center'>&nbsp;รวม</td>
  	<td>&nbsp;&nbsp;</td>
  	<td><div align='center'>$sum_chronic01</div></td>
	<td><div align='center'>$sum_chronic02</div></td>
	<td><div align='center'>$sum_chronic03</div></td>
	<td><div align='center'>$sum_chronic04</div></td>
	<td><div align='center'>$sum_chronic05</div></td>
	<td><div align='center'>$sum_chronic06</div></td>
	<td><div align='center'>$sum_chronic07</div></td>
	<td><div align='center'>$sum_chronic08</div></td>
	<td><div align='center'>$sum_chronic09</div></td>
	<td><div align='center'>$sum_chronic10</div></td>
	<td><div align='center'>$sum_chronic11</div></td>
	<td><div align='center'>$sum_chronic12</div></td>
	<td><div align='center'>$sum_chronic13</div></td>
	<td><div align='center'>$sum_chronic14</div></td>
	<td><div align='center'>$sum_chronic15</div></td>
	<td><div align='center'>$sum_chronic16</div></td>
	<td><div align='center'>$sum_chronic17</div></td>
	<td><div align='center'>$sum_chronic18</div></td>
	<td><div align='center'>$sum_chronic19</div></td>
	<td><div align='center'>$sum_chronic20</div></td>
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
