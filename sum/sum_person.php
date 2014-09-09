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
$datemask = $_GET[datemask];
$strx = retDatets($datemask);
$age = $_GET[age];
$ect0 = "'10000'";
if(strpos($age,",",0) > 0){
	$listage = explode(',',$age);
	foreach ($listage as $a){
		if(strpos($a,"-",0) > 0){
			list($str,$end) = split("-",$a,2);
			for($i = $str; $i <= $end; $i++){
				$ect0 .= ",'".$i."'";
			}
		}else{
			$ect0 .= ",'".$a."'";
		}
	}
}else{
		if(strpos($age,"-",0) > 0){
			list($str,$end) = split("-",$age,2);
			for($i = $str; $i <= $end; $i++){
				$ect0 .= ",'".$i."'";
			}
		}else{
			$ect0 .= ",'".$age."'";
		}
}
$sex = $_GET[sex];
if($sex == '0'){$ect1_name = "";}elseif($sex == '1'){$ect1_name = "เพศชาย";}else{$ect1_name = "เพศหญิง";}
if($sex == '0'){$ect1 = "";}else{$ect1 = " p.sex = '$sex' AND ";}
$village = $_GET[village];
if($village == "00000000"){
	$mu = "ทุกหมู่บ้าน";
}else{
	$mu = getvillagename($village);	
}
if($village == '00000000'){$ect2 = "";}else{$ect2 = " h.villcode = '$village' AND ";}
$live_type = $_GET[live_type];
if($live_type == '2'){$live_type_name = "ตามทะเบียนบ้าน(0,1,2)";}elseif($live_type == '1'){$live_type_name = "ที่อาศัยอยู่จริง (0,1,3)";}else{$live_type_name = "ทั้งหมดในเขตรับผิดชอบ(0,1,2,3)";}	
if($live_type == '2'){$live_type2 = "and p.typelive in ('0','1','2')";}elseif($live_type == '1'){$live_type2 = "and p.typelive in ('0','1','3')";}else{$live_type2 = "and p.typelive in ('0','1','2','3')";}
	$sql = "SELECT count(distinct p.pid) as countofpid,h.villcode,v.villname
				FROM house AS h
				Inner Join person AS p ON h.pcucode = p.pcucodeperson AND h.hcode = p.hcode
				inner join village v on h.pcucode = v.pcucode and h.villcode = v.villcode
				WHERE $ect1 $ect2 ((p.dischargetype is null) or (p.dischargetype = '9')) AND
				SUBSTRING(h.villcode,7,2) <> '00' AND
				FLOOR((TO_DAYS('$datemask')-TO_DAYS(p.birth))/365.25) IN($ect0) $live_type2
				group by h.villcode
				ORDER BY h.villcode";
$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>จำนวนประชาชน';
$txt .= "$ect1_name ที่อายุระหว่าง&nbsp;&nbsp;$age&nbsp;&nbsp;ปี  $mu</b><br></p>$live_type_name<br><b>$hosp</b><br><table class='table table-striped table-hover table-bordered' width='95%' border='0' cellspacing='1' cellpadding='1'>
  <tr>
    <th width='8%' scope='col'><div align='center'>ลำดับ</div></th>
    <th width='22%' scope='col'><div align='center'>หมู่บ้าน</div></th>
	<th width='8%' scope='col'><div align='center'>หมู่ที่</div></th>
    <th width='8%' scope='col'><div align='center'>จำนวน</div></th>
    <th width='8%' scope='col'><div align='center'>หมายเหตุ</div></th>
  </tr>";
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$title = getTitle($row[prename]);
	$count_pid = $row[countofpid];
	$sum_pid = $sum_pid+$count_pid;
++$x;
	if(($x%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$x</div></td>
    <td>$row[villname]</td>
    <td><div align='center'>$moo</td>
    <td><div align='center'>$row[countofpid]</td>
    <td></td>
  </tr>
  ";
}
$txt .= "<tr>
  <td>&nbsp;&nbsp;</td>
  <td><div align='center'>&nbsp;รวม</td>
  <td>&nbsp;&nbsp;</td>
  <td><div align='center'>$sum_pid</div></td>
  <td>&nbsp;&nbsp;</td>
  </tr></table><p div align='right' class='text-danger'>คำนวนอายุถึงวันที่ $strx </p></div><br>";  
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
