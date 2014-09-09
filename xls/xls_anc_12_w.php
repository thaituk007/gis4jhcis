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
$str = retdaterangstr($_GET[str]);
$sto = retdaterangsto($_GET[str]);
$strx = retDatets($str);
$stox = retDatets($sto);
if($villcode == "00000000"){
	$mu = "ทุกหมู่บ้าน";
}else{
	$mu = getvillagename($villcode);	
}	
$sql = "select * from (SELECT 
village.pcucode, 
person.pid, 
person.idcard, 
concat(ctitle.titlename, person.fname , '  ' , person.lname) AS pname, 
village.villcode,
village.villname,
house.hno,
house.hcode,
house.xgis,
house.ygis,
person.birth,
ROUND(DATEDIFF(now(),person.birth)/365.25) AS age,
visitancpregnancy.edc,
visitanc.pregno,
visitancpregnancy.lmp,
min(visitanc.datecheck) as first_visit_date,
ROUND(DATEDIFF(min(visitanc.datecheck) ,visitancpregnancy.lmp) /7) AS agepreg,
case when ROUND(DATEDIFF(visitanc.datecheck ,visitancpregnancy.lmp) /7)  < 12 then 1 else 0 end as chk
FROM (((house INNER JOIN village ON (house.villcode = village.villcode) AND (house.pcucode = village.pcucode)) INNER JOIN person ON (house.hcode = person.hcode) AND (house.pcucode = person.pcucodeperson)) INNER JOIN visitancpregnancy ON (person.pid = visitancpregnancy.pid) AND (person.pcucodeperson = visitancpregnancy.pcucodeperson)) INNER JOIN visitanc ON (visitancpregnancy.pregno = visitanc.pregno) AND (visitancpregnancy.pid = visitanc.pid) AND (visitancpregnancy.pcucodeperson = visitanc.pcucodeperson) inner join ctitle on person.prename = ctitle.titlecode
WHERE visitanc.datecheck Between '$str' And '$sto' AND (person.dischargetype Is Null Or person.dischargetype='9') and right(house.villcode,2) <> '00'  $wvill
GROUP BY village.pcucode, person.pid
order by village.pcucode, village.villcode, person.fname) as tmp_anc";

$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>รายงานหญิงตั้งครรภ์ที่ฝากครรภ์ครั้งแรกก่อน 12 สัปดาห์</b><br>';
$txt .= "<b>$mu<p div align='center' class='text-danger'>ข้อมูลระหว่างวันที่  $strx ถึง $stox </p></div></b></p><b>$hosp</b><div class='table-responsive'><table class='table table-striped table-hover table-bordered' width='100%' border='0' cellspacing='1' cellpadding='1'>
  <tr class='active'>
    <th width='8%' scope='col'><div align='center'>ลำดับ</div></th>
    <th width='22%' scope='col'><div align='center'>ชื่อ - สกุล</div></th>
	<th width='10%' scope='col'><div align='center'>ว/ด/ป เกิด</div></th>
	<th width='8%' scope='col'><div align='center'>อายุ</div></th>
    <th width='8%' scope='col'><div align='center'>บ้านเลขที่</div></th>
    <th width='8%' scope='col'><div align='center'>หมู่ที่</div></th>
	<th width='8%' scope='col'><div align='center'>LMP</div></th>
	<th width='8%' scope='col'><div align='center'>EDC</div></th>
	<th width='5%' scope='col'><div align='center'>ครรภ์ที่</div></th>
	<th width='10%' scope='col'><div align='center'>อายุครรภ์นับจากLMPถึงวันที่ฝากครรภ์ครั้งแรก</div></th>
	<th width='10%' scope='col'><div align='center'>วันที่ฝากครรภ์ครั้งแรก</div></th>
  </tr>";
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$lmp = retDatets($row[lmp]);
	$edc = retDatets($row[edc]);
	$birth = retDatets($row[birth]);
	$first_visit_date = retDatets($row[first_visit_date]);
++$i;
	if(($i%2) == 1){$cr = " class='info'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$i</div></td>
    <td>$row[pname]</td>
	<td><div align='center'>&nbsp;$birth</div></td>
	<td><div align='center'>&nbsp;$row[age]</div></td>
    <td><div align='center'>&nbsp;$row[hno]</div></td>
    <td><div align='center'>&nbsp;$moo</div></td>
    <td><div align='center'>&nbsp;$lmp</div></td>
	<td><div align='center'>&nbsp;$edc</div></td>
	<td><div align='center'>&nbsp;$row[pregno]</div></td>
	<td><div align='center'>&nbsp;$row[agepreg]</div></td>
	<td><div align='center'>&nbsp;$first_visit_date</div></td>
  </tr>";
}
$txt .= "</table><br>";  
echo $txt;
?>
<?php
}
else{
		header("Location: ../main/index.php");
		}
		?>
        
</body>
</html>
