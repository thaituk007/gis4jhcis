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
$village = $_GET[village];
if($village == "00000000"){
	$wvill = "";
}elseif($village == "xxx"){
	$wvill = " AND right(h.villcode,2)='00'";	
}else{
	$wvill = " AND h.villcode='$village'";	
}
if($village == "00000000"){
	$mu = "ทุกหมู่บ้าน";
}else{
	$mu = getvillagename($village);	
}
$str = retdaterangstr($_GET[str]);
$sto = retdaterangsto($_GET[str]);
$strx = retDatets($str);
$stox = retDatets($sto);
$ds = $_GET[ds];
	if($ds == '00'){$ect = "";}else{$ect = " dc.group506code = '$ds' AND ";}	
$sql = "SELECT p.pid, p.idcard, CONCAT(t.titlename,p.fname,' ',p.lname) AS pname,getageyearnum(p.birth,vd.sickdatestart) as age, d.diseasecode, h.hno,h.villcode,co.occupaname, vd.sickdatestart,dc.group506name
							FROM
							house AS h
							Inner Join person AS p ON h.pcucode = p.pcucodeperson AND h.hcode = p.hcode
							Inner Join visit AS v ON p.pcucodeperson = v.pcucodeperson AND p.pid = v.pid
							Inner Join visitdiag506address AS vd ON v.pcucode = vd.pcucode AND v.visitno = vd.visitno
							Inner Join cdisease AS d ON vd.diagcode = d.diseasecode
							Inner Join cdisease506 AS dc ON d.code506 = dc.group506code
							left Join ctitle AS t ON p.prename = t.titlecode
							left join coccupa co on p.occupa = co.occupacode
							WHERE $ect vd.sickdatestart BETWEEN  '$str' AND '$sto' $wvill
							ORDER BY h.villcode,h.hno";

$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>รายชื่อผู้ป่วยด้วยโรคที่ต้องเฝ้าระวังทางระบาดวิทยา <br>';
$txt .= "$mu</p><br>$hosp</b><div class='table-responsive'><table class='table table-striped table-hover table-bordered' width='100%' border='0' cellspacing='1' cellpadding='1'>
  <tr>
    <th width='4%' scope='col'><div align='center'>ลำดับ</div></th>
	<th width='10%' scope='col'><div align='center'>เลขบัตรประชาชน</div></th>
    <th width='10%' scope='col'><div align='center'>ชื่อ - สกุล</div></th>
	<th width='5%' scope='col'><div align='center'>อายุ</div></th>
    <th width='6%' scope='col'><div align='center'>บ้านเลขที่</div></th>
    <th width='4%' scope='col'><div align='center'>หมู่ที่</div></th>
    <th width='15%' scope='col'><div align='center'>อาชีพ</div></th>
	<th width='9%' scope='col'><div align='center'>วันที่ป่วย</div></th>
	<th width='9%' scope='col'><div align='center'>รหัส ICD10</div></th>
	<th width='20%' scope='col'><div align='center'>โรค 506</div></th>
  </tr>";
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$title = getTitle($row[prename]);
	if($row[sickdatestart] == ""){$sick = "";}else{$sick = retDatets($row[sickdatestart]);}
++$i;
	if(($i%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$i</div></td>
	<td><div align='center'>$row[idcard]</div></td>
    <td>$row[pname]</td>
	<td><div align='center'>$row[age]</div></td>
    <td><div align='center'>$row[hno]</div></td>
    <td><div align='center'>$moo</div></td>
    <td>$row[occupaname]</td>
	<td><div align='center'>$sick</div></td>
	<td>$row[para]&nbsp;&nbsp;$row[diseasecode]</td>
	<td>$row[group506name]</td>
  </tr>";
}
$txt .= "</table><p div align='right' class='text-danger'>ข้อมูลระหว่างวันที่  $strx ถึง $stox </p></div><br>";  
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
