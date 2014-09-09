<?php 
session_start();
if($_SESSION[username]){
include("../includes/conndb.php"); 
include("../includes/config.inc.php"); 
$nstr = retdaterangstr($_GET[str]);
$nsto = retdaterangsto($_GET[str]);
$str=retDatets($nstr);
$sto=retDatets($nsto);
$hcode=$_GET[hcode];

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

$txt = "<b>การตรวจพยาธิระหว่างวันที่ $str - $sto</b>";
$txt .= "<table width='100%' border='0' cellspacing='0' cellpadding='0' class='table table-striped table-hover table-bordered'>
  <tr>
    <th width='5%' scope='col'><div align='center'>ลำดับ</div></th>
    <th width='25%' scope='col'><div align='center'>ชื่อ - สกุล</div></th>
    <th width='6%' scope='col'><div align='center'>อายุ</div></th>
    <th width='48%' scope='col'><div align='center'>ผลการตรวจ</div></th>
    <th width='16%' scope='col'><div align='center'>วันที่ตรวจ</div></th>
  </tr>";
 $sql ="SELECT CONCAT(ctitle.titlename,p.fname,' ',p.lname) AS pname,YEAR(NOW())-YEAR(p.birth) AS age,p.pid
			FROM house AS h
			Inner Join person AS p ON h.pcucode = p.pcucodeperson AND h.hcode = p.hcode
			Inner Join ctitle ON p.prename = ctitle.titlecode
			WHERE ((p.dischargetype is null) or (p.dischargetype = '9')) AND FLOOR((TO_DAYS(NOW())-TO_DAYS(p.birth))/365.25) > 29 and h.hcode=$hcode
			ORDER BY p.birth"; 
$result = mysql_query($sql);
while($row=mysql_fetch_array($result)) {	
$i++;		
$sqlv = "SELECT
			visit.vitalcheck,
			visit.visitdate 
			FROM
village
INNER JOIN house ON village.pcucode = house.pcucode AND village.villcode = house.villcode
INNER JOIN person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
INNER JOIN visit ON person.pcucodeperson = visit.pcucodeperson AND person.pid = visit.pid
INNER JOIN visitdiag ON visit.pcucode = visitdiag.pcucode AND visit.visitno = visitdiag.visitno
where visit.visitdate between '$nstr' and '$nsto' and ((person.dischargetype is null) or (person.dischargetype = '9')) AND
				SUBSTRING(house.villcode,7,2) <> '00' AND 
				FLOOR((TO_DAYS(NOW())-TO_DAYS(person.birth))/365.25) > 29 and visit.symptoms like '%พยาธิ%' and visit.vitalcheck like '%พบ%' and visitdiag.diagcode = 'Z11.6' AND visit.pid = $row[pid] 
			ORDER BY visit.visitdate DESC";
$resultv = mysql_query($sqlv);
$rowv=mysql_fetch_array($resultv);
if($rowv[1]){
	$ndate = retDatets($rowv[1]);			
}else{$ndate = "";}
$txt .= "<tr>
    <td><div align='center'>$i</div></td>
    <td>$row[pname]</td>
    <td><div align='center'>$row[age]</div></td>
    <td>$rowv[0]</td>
    <td><div align='center'>$ndate</div></td>
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