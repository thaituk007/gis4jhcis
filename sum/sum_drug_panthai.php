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
$str = retdaterangstr($_GET[str]);
$sto = retdaterangsto($_GET[str]);
$strx = retDatets($str);
$stox = retDatets($sto);
	$sql = "SELECT
count(distinct tmp.pid) as countofpid, 
count(tmp.visitno) as countofvisitno,
village.villcode,
village.villname
from
village
left join
(SELECT person.idcard, person.pid
,CONVERT(concat(ifnull(c.titlename,ifnull(person.prename,'ไม่ระบุ') ),person.fname,' ' ,person.lname) using utf8) as pname 
,DATE_FORMAT(visitdate,'%Y-%m-%d') as visitdate
,office.offid
,v.pcucode
,if(v.pcucode = office.offid,'หน่วยบริการ' ,'ที่อื่น ' ) as pcu
,drugname
,CONVERT(concat(c.titlename,u.fname,' ' ,u.lname) using utf8) as uname
,count(visitdrug.drugcode) as ctime
,DATE_FORMAT(visitdrug.dateupdate,'%Y-%m-%d') as dateupdate
,curdate() as cdate,
house.hno,
house.villcode,
house.xgis,
house.ygis,
v.visitno,
village.villname
FROM person left join ctitle on person.prename = ctitle.titlecode
	left join visit  v on person.pid = v.pid  and person.pcucodeperson = v.pcucodeperson
	left join visitdrug on v.visitno = visitdrug.visitno and v.pcucode = visitdrug.pcucode
	left join cdrug on visitdrug.drugcode = cdrug.drugcode
	left join user u on v.username = u.username
	left join ctitle c on c.titlecode = u.prename
	left join office on v.pcucode = office.offid
	Inner Join house ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
  Inner Join village ON house.pcucode = village.pcucode AND house.villcode = village.villcode
WHERE    cdrug.drugtype='10'
 	and visitdate between '$str' and '$sto' and (v.flagservice <'04' OR v.flagservice is null OR length(trim(v.flagservice))=0 )
GROUP BY v.visitno,v.pcucodeperson
ORDER BY v.visitdate) as tmp
on tmp.pcucode = village.pcucode and tmp.villcode = village.villcode
where village.villname is not null
group by village.pcucode, village.villcode";
$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>จำนวนผู้รับบริการที่ได้รับยาสมุนไพร<br>';
$txt .= "</p></div></b></p><br><b>$hosp</b><br><table width='99%' border='0' cellspacing='1' cellpadding='1' class='table table-striped table-hover table-bordered'>
  <tr>
    <th width='8%' scope='col'>ลำดับ</th>
    <th width='22%' scope='col'>หมู่บ้าน</th>
	<th width='8%' scope='col'>หมู่ที่</th>
    <th width='8%' scope='col'>จำนวนผู้รับบริการ(คน)</th>
	<th width='8%' scope='col'>จำนวนผู้รับบริการ(ครั้ง)</th>
    <th width='8%' scope='col'>หมายเหตุ</th>
  </tr>";
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$title = getTitle($row[prename]);
	$sum_pid = $sum_pid+$row[countofpid];
	$sum_visit = $sum_visit+$row[countofvisitno];
++$x;
	if(($x%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$x</div></td>
    <td>$row[villname]</td>
    <td><div align='center'>$moo</td>
    <td><div align='center'>$row[countofpid]</td>
	<td><div align='center'>$row[countofvisitno]</td>
    <td></td>
  </tr>
  ";
}
$txt .= "<tr>
  <td>&nbsp;&nbsp;</td>
  <td><div align='center'>&nbsp;รวม</td>
  <td>&nbsp;&nbsp;</td>
  <td><div align='center'>$sum_pid</div></td>
  <td><div align='center'>$sum_visit</div></td>
  <td>&nbsp;&nbsp;</td>
  </tr></table><p div align='right' class='text-danger'>ข้อมูลระหว่างวันที่  $strx ถึง $stox </p></div><br>";  
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
