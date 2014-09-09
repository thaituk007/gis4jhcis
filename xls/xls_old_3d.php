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
$chk_old = $_GET[chk_old];
if($chk_old == "1"){
	$chksto = "and person.candobedhomesocial = 1";
}elseif($chk_old == "2"){
	$chksto = "and person.candobedhomesocial = 2";	
}elseif($chk_old == "3"){
	$chksto = "and person.candobedhomesocial = 3";
}elseif($chk_old == "8"){
	$chksto = "and person.candobedhomesocial in (1,2,3)";
}elseif($chk_old == "9"){
	$chksto = "";	
}else{
	$chksto = "and person.candobedhomesocial is null";
}
if($villcode == "00000000"){
	$mu = "ทุกหมู่บ้าน";
}else{
	$mu = getvillagename($villcode);	
}
$str = date("Y-d-m");
$strd = substr($str,8,2);
$strm = substr($str,5,2);
$stryT = substr($str,0,4);
$stryF = substr($str,0,4)-1;
$dx = $strm."".$strd;
if($dx > "1001"){$daymidyear = $stryT."-10-01";}else{$daymidyear = $stryF."-10-01";}
$sql = "SELECT
person.pcucodeperson,
person.pid,
person.fname, 
concat(ctitle.titlename, person.fname , '  ' , person.lname) AS pname,
person.birth,
ROUND(DATEDIFF('$daymidyear',person.birth)/365.25) AS age,
village.villcode,
village.villname,
house.hno,
house.hcode,
house.xgis,
house.ygis,
person.candobedhomesocial as chk,
person.dateupdate
FROM
village
INNER JOIN house ON village.pcucode = house.pcucode AND village.villcode = house.villcode
INNER JOIN person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
left join ctitle on ctitle.titlecode = person.prename
where getAgeYearNum(person.birth,'$daymidyear') > 59 and ((person.dischargetype is null) or (person.dischargetype = '9'))  and SUBSTRING(house.villcode,7,2) <> '00' $chksto $wvill
order by person.pcucodeperson,village.villcode,person.fname";

$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>รายงานการประเมินสุขภาพผู้สูงอายุ (ติดสังคม, ติดบ้าน, ติดเตียง)</b><br>';
$txt .= "$mu</b></p><br><b>$hosp</b><table width='98%' border='0' cellspacing='1' cellpadding='1' class='table table-striped table-hover table-bordered'>
  <tr>
    <th width='4%' scope='col'><div align='center'>ลำดับ</div></th>
    <th width='13%' scope='col'><div align='center'>ชื่อ - สกุล</div></th>
	<th width='8%' scope='col'><div align='center'>ว/ด/ป เกิด</div></th>
	<th width='5%' scope='col'><div align='center'>อายุ</div></th>
    <th width='7%' scope='col'><div align='center'>บ้านเลขที่</div></th>
    <th width='4%' scope='col'><div align='center'>หมู่ที่</div></th>
	<th width='4%' scope='col'><div align='center'>วันที่สำรวจ</div></th>
	<th width='15%' scope='col'><div align='center'>ประเภทผู้สูงอายุ</div></th>
	<th width='15%' scope='col'><div align='center'>หมายเหตุ</div></th>
  </tr>";
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	if($row[chk] == ""){$old_chk = 'ยังไม่ได้ประเมิน';}elseif($row[chk] == 3){$old_chk = 'ติดเตียง';}elseif($row[chk] == 2){$old_chk = 'ติดบ้าน';}elseif($row[chk] == 1){$old_chk = 'ติดสังคม';}else{$old_chk = 'ไม่ทราบ';}
	$birth = retDatets($row[birth]);
	if($row[dateupdate] == ""){$visitdate = '';}else{$visitdate = retDatets($row[dateupdate]);}
	if($row[chk] == ""){$mark_old = 'ยังไม่ได้ประเมิน';}else{$mark_old = '';}
++$i;
	if(($i%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$i</div></td>
    <td>$row[pname]</td>
	<td><div align='center'>&nbsp;$birth</div></td>
	<td><div align='center'>&nbsp;$row[age]</div></td>
    <td><div align='center'>&nbsp;$row[hno]</div></td>
    <td><div align='center'>&nbsp;$moo</div></td>
    <td><div align='center'>&nbsp;$visitdate</div></td>
	<td><div align='center'>&nbsp;$old_chk</div></td>
	<td><div align='center'>&nbsp;$mark_old</div></td>
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
