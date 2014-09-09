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
$artype=$_GET[artype];
if($artype == '1'){
$radius=$_GET[rd]/1000*0.6214;	
$center_lat = $_GET["lat"];
$center_lng = $_GET["lng"];
$sql = "SELECT villcode,hno,pid,ygis,xgis,if(instr(hno,'/')>0,substring(hno,1,instr(hno,'/')-1),hno) as f
			FROM house 
			WHERE  SUBSTRING(villcode,7,2) <> '00' AND 
					ygis <> '' AND 
					ygis IS NOT NULL AND
					( 3959 * acos( cos( radians('$center_lat') ) * cos( radians( ygis ) ) * cos( radians( xgis ) - radians('$center_lng') ) + sin( radians('$center_lat') ) * sin( radians( ygis ) ) ) ) < '$radius'
			ORDER BY villcode,length(f),f,hno";
}else{
$latn = $_GET[latn];
$lnge = $_GET[lnge];
$lats = $_GET[lats];
$lngw = $_GET[lngw];	
$sql = "SELECT villcode,hno,pid,ygis,xgis,if(instr(hno,'/')>0,substring(hno,1,instr(hno,'/')-1),hno) as f
			FROM house 
			WHERE  SUBSTRING(villcode,7,2) <> '00' AND 
					ygis <> '' AND 
					ygis IS NOT NULL AND
					(ygis BETWEEN '$lats' AND '$latn') AND
					(xgis BETWEEN '$lngw' AND '$lnge')
			ORDER BY villcode,length(f),f,hno";

}
$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>ข้อมูลบ้านในพื้นที่ที่เลือก</b></p>';
$txt .= "<table width='95%' border='0' cellspacing='1' cellpadding='1' class='table table-striped table-hover table-bordered'>
  <tr>
    <th width='8%' scope='col'>ลำดับ</th>
    <th width='11%' scope='col'>บ้านเลขที่</th>
    <th width='22%' scope='col'>หมู่บ้าน</th>
    <th width='32%' scope='col'>หัวหน้าครอบครัว</th>
    <th width='27%' scope='col'>หมายเหตุ</th>
  </tr>";
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$hhouse = getPersonName($row[pid]);
++$i;
	if(($i%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$i</div></td>
    <td><div align='center'>$row[hno]</div></td>
    <td>&nbsp;$vill</td>
    <td>&nbsp;$hhouse</td>
    <td>&nbsp;</td>
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
