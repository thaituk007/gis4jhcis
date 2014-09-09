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
$villcode = $_GET[village];
if($villcode == '00000000'){
	$wt = " ";
}else{
	$wt = " AND villcode='$villcode' ";
}
if($villcode == "00000000"){$ect3 = "ทุกหมู่บ้าน";}else{$ect3 = getvillagename($villcode);}
$sql = "SELECT
person.idcard as cid,
concat(ctitle.titlename,
person.fname,'  ',
person.lname) as pname, 
person.birth,
house.hno,
house.villcode,
house.xgis,
house.ygis
FROM
person
Inner Join house ON person.pcucodeperson = house.pcucode AND person.hcode = house.hcode
inner join ctitle on person.prename = ctitle.titlecode
where 
Right(11-((Left(person.idcard,1)*13)+(Mid(person.idcard,2,1)*12)+(Mid(person.idcard,3,1)*11)+(Mid(person.idcard,4,1)*10)+(Mid(person.idcard,5,1)*9)+(Mid(person.idcard,6,1)*8)+(Mid(person.idcard,7,1)*7)+(Mid(person.idcard,8,1)*6)+(Mid(person.idcard,9,1)*5)+(Mid(person.idcard,10,1)*4)+(Mid(person.idcard,11,1)*3)+(Mid(person.idcard,12,1)*2)) Mod 11,1) <> Right(person.idcard,1) $wt
order by house.villcode";
$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>รายชื่อประชาชนที่เลขบัตรผิด<br>';
$txt .= "$ect3</b></p><table width='95%' border='0' cellspacing='1' cellpadding='1' class='table table-striped table-hover table-bordered'>
  <tr>
    <th width='8%' scope='col'><div align='center'>ลำดับ</div></th>
    <th width='22%' scope='col'><div align='center'>ชื่อ - สกุล</div></th>
	<th width='30%' scope='col'><div align='center'>เลขบัตรประชาชน</div></th>
    <th width='8%' scope='col'><div align='center'>บ้านเลขที่</div></th>
    <th width='8%' scope='col'><div align='center'>หมู่ที่</div></th>
  </tr>";
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
++$i;
	if(($i%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$i</div></td>
    <td>&nbsp;$row[pname]</td>
	<td>&nbsp;$row[cid]</td>
    <td>&nbsp;$row[hno]</td>
    <td>&nbsp;$moo</td>
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
