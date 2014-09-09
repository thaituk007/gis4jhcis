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
if($villcode == '00000000'){
	$wt = " ";
}else{
	$wt = " AND house.villcode='$villcode' ";
}
if($village == "00000000"){$ect3 = "ทุกหมู่บ้าน";}else{$ect3 = getvillagename($village);}
$sql = "SELECT
house.villcode,
house.hno,
house.pid,
house.ygis,
house.xgis,
if(instr(hno,'/')>0,substring(hno,1,instr(hno,'/')-1),hno) AS f,
concat(ctitle.titlename,`user`.fname,`user`.lname) as doctor,
concat(pct.titlename,p.fname,'  ',p.lname) as osm,
concat(poct.titlename,po.fname,'  ',po.lname) as hhouse
FROM
house
INNER JOIN `user` ON house.pcucode = `user`.pcucode AND house.usernamedoc = `user`.username
INNER JOIN ctitle ON `user`.prename = ctitle.titlecode
LEFT JOIN person as p on p.pcucodeperson = house.pcucode and p.pid = house.pidvola
left JOIN ctitle as pct on pct.titlecode = p.prename
LEFT JOIN person as po on po.pcucodeperson = house.pcucode and po.pid = house.pid
left JOIN ctitle as poct on poct.titlecode = po.prename
WHERE  SUBSTRING(villcode,7,2) <> '00' $wt ORDER BY villcode,length(f),f,hno";
$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>หลังคาเรือน</b></p>';
$txt .= "$ect3</b><br></p><b>$hosp</b><br><table width='95%' border='0' cellspacing='1' cellpadding='1' class='table table-striped table-hover table-bordered'>
  <tr>
    <th width='8%' scope='col'><div align='center'>ลำดับ</div></th>
    <th width='8%' scope='col'><div align='center'>บ้านเลขที่</div></th>
	<th width='8%' scope='col'><div align='center'>หมู่</div></th>
	<th width='22%' scope='col'><div align='center'>เจ้าบ้าน</div></th>
    <th width='22%' scope='col'><div align='center'>อสม.รับผิดชอบ</div></th>
    <th width='22%' scope='col'><div align='center'>หมอประจำบ้าน</div></th>
    <th width='15%' scope='col'><div align='center'>หมายเหตุ</div></th>
  </tr>";
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
++$x;
	if(($x%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$x</div></td>
    <td>&nbsp;$row[hno]</td>
    <td>&nbsp;$moo</td>
    <td>&nbsp;$row[hhouse]</td>
	<td>&nbsp;$row[osm]</td>
	<td>&nbsp;$row[doctor]</td>
    <td></td>
  </tr>";
}
$txt .= "</table>";  
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
