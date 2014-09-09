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
$village = $_GET[village];
if($village == "00000000"){
	$mu = "ทุกหมู่บ้าน";
}else{
	$mu = getvillagename($village);	
}
if($village == '00000000'){$ect2 = "";}else{$ect2 = " h.villcode = '$village' AND ";} 
$ptype = $_GET[ptype];
	if($ptype == '00'){$ect = "";}else{$ect = " pt.persontypecode = '$ptype' AND ";}	
	if($ptype == '00'){$ect3 = "บุคคลสำคัญ";}else{$ect3 = persontype($ptype);}	
$sql = "SELECT p.prename,CONCAT(p.fname,' ',p.lname) AS pname, p.telephoneperson, h.hno,h.villcode,h.xgis,h.ygis,pt.persontypename
				FROM house AS h
				Inner Join person AS p ON h.pcucode = p.pcucodeperson AND h.hcode = p.hcode
				Inner Join persontype AS ps ON p.pcucodeperson = ps.pcucodeperson AND p.pid = ps.pid
				Inner Join cpersontype AS pt ON ps.typecode = pt.persontypecode
				WHERE $ect2 $ect ((p.dischargetype is null) or (p.dischargetype = '9')) AND
				ps.dateretire IS NULL AND
				SUBSTRING(h.villcode,7,2) <> '00'
				ORDER BY h.villcode,h.hno";

$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>';
$txt .= "$ect3 $mu</b></p><table width='98%' border='0' cellspacing='1' cellpadding='1' class='table table-striped table-hover table-bordered'>
  <tr>
    <th width='8%' scope='col'><div align='center'>ลำดับ</div></th>
    <th width='22%' scope='col'><div align='center'>ชื่อ - สกุล</div></th>
    <th width='8%' scope='col'><div align='center'>บ้านเลขที่</div></th>
    <th width='8%' scope='col'><div align='center'>หมู่ที่</div></th>
    <th width='20%' scope='col'><div align='center'>ตำแหน่ง</div></th>
	<th width='15%' scope='col'><div align='center'>โทรศัพท์</div></th>
  </tr>";
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$title = getTitle($row[prename]);
++$i;
	if(($i%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$i</div></td>
    <td>$title$row[pname]</td>
    <td>&nbsp;$row[hno]</td>
    <td>&nbsp;$moo</td>
    <td>&nbsp;$row[persontypename]</td>
	<td>&nbsp;$row[telephoneperson]</td>
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
