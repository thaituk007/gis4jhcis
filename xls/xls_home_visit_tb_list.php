<?php 
session_start();
if($_SESSION[username]){
include("../includes/conndb.php"); 
include("../includes/config.inc.php"); 
$nstr = retdaterangstr($_GET[str]);
$nsto = retdaterangsto($_GET[str]);
$str=retDatets($nstr);
$sto=retDatets($nsto);
$pid=$_GET[pid];
$pnameh = getPersonName($pid);
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
<body">
<?php

$txt = "<b>ประวัติการเยี่ยมบ้านของ $pnameh ระหว่างวันที่  $str - $sto</b>";
$txt .= "<table width='100%' border='0' cellspacing='0' cellpadding='0' class='table table-striped table-hover table-bordered'>
  <tr>
    <th width='5%' scope='col'><div align='center'>ลำดับ</div></th>
    <th width='14%' scope='col'><div align='center'>ชื่อ - สกุล</div></th>
    <th width='7%' scope='col'><div align='center'>วันที่เยี่ยม</div></th>
	<th width='15%' scope='col'><div align='center'>สภาพ/อาการของเป้าหมาย</div></th>
	<th width='30%' scope='col'><div align='center'>กิจกรรม</div></th>
	<th width='15%' scope='col'><div align='center'>ประเมินผล</div></th>
	<th width='7%' scope='col'><div align='center'>วันนัดครั้งต่อไป</div></th>
	<th width='14%' scope='col'><div align='center'>ผู้เยี่ยม</div></th>
  </tr>";		
$sql = "SELECT
visit.pcucodeperson,
visit.pid,
visit.visitno,
visit.visitdate,
chomehealthtype.homehealthmeaning,
visithomehealthindividual.patientsign,
visithomehealthindividual.homehealthdetail,
visithomehealthindividual.homehealthresult,
visithomehealthindividual.homehealthplan,
visithomehealthindividual.dateappoint,
concat(ctitle.titlename,`user`.fname,`user`.lname) as userh,
visithomehealthindividual.`user`
FROM
visit
Inner Join visithomehealthindividual ON visit.pcucode = visithomehealthindividual.pcucode AND visit.visitno = visithomehealthindividual.visitno
Inner Join chomehealthtype ON visithomehealthindividual.homehealthtype = chomehealthtype.homehealthcode
INNER JOIN `user` ON visit.pcucodeperson = `user`.pcucode AND visithomehealthindividual.`user` = `user`.username
left JOIN ctitle ON `user`.prename = ctitle.titlecode
where visit.visitdate between '$nstr' and '$nsto' and (visit.flagservice <'04' OR visit.flagservice is null OR length(trim(visit.flagservice))=0 ) and visit.pid = $pid
order by visit.visitdate";
$result = mysql_query($sql);
while($row=mysql_fetch_array($result)) {
	if($row[visitdate] == ""){$sickre = "";}else{$sickre = retDatets($row[visitdate]);}
	$pname = getPersonName($pid);
++$i;
$txt .= "<tr>
    <td><div align='center'>$i</div></td>
    <td>$pname</td>
	<td><div align='center'>$sickre</div></td>
	<td>$row[patientsign]</td>
	<td>$row[homehealthdetail]</td>
	<td>$row[homehealthresult]</td>
	<td><div align='center'>$dateappoint</div></td>
	<td>$row[userh]</td>
  </tr>";
}
$txt .= "</table>";
echo $txt;
}
else{
		header("Location: login.php");
		}
?>
</body>
</html>