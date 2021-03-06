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
	$wvill = " AND h.villcode='$villcode' ";	
}
if($villcode == "00000000"){
	$mu = "ทุกหมู่บ้าน";
}else{
	$mu = getvillagename($villcode);	
}
$str = retdaterangstr($_GET[str]);
$sto = retdaterangsto($_GET[str]);
$strx = retDatets($str);
$stox = retDatets($sto);
$live_type = $_GET[live_type];
if($live_type == '2'){$live_type_name = "ตามทะเบียนบ้าน(0,1,2)";}elseif($live_type == '1'){$live_type_name = "ที่อาศัยอยู่จริง (0,1,3)";}else{$live_type_name = "ทั้งหมดในเขตรับผิดชอบ(0,1,2,3)";}	
if($live_type == '2'){$live_type2 = " and p.typelive in ('0','1','2')";}elseif($live_type == '1'){$live_type2 = "and p.typelive in ('0','1','3')";}else{$live_type2 = "and p.typelive in ('0','1','2','3')";}	
$sql = "SELECT
anc5q.pcucodeperson,
anc5q.villcode,
COUNT(DISTINCT CONCAT(anc5q.pcucodeperson,'-',anc5q.pid)) as perall,
COUNT(DISTINCT IF(anc5q.anc1 is not null and anc5q.anc2 is not null and anc5q.anc3 is not null and anc5q.anc4 is not null and anc5q.anc5 is not null, CONCAT(anc5q.pcucodeperson,'-',anc5q.pid), NULL)) AS chk,
COUNT(DISTINCT IF(anc5q.anc1 is not null and anc5q.anc2 is not null and anc5q.anc3 is not null and anc5q.anc4 is not null and anc5q.anc5 is not null, CONCAT(anc5q.pcucodeperson,'-',anc5q.pid), NULL))/COUNT(DISTINCT CONCAT(anc5q.pcucodeperson,'-',anc5q.pid))*100 as percent
FROM
(select
*,
case when anc1 is not null and anc2 is not null and anc3 is not null and anc4 is not null and anc5 is not null then 1 else 0 end as chk
from
(
SELECT
ancperson.*,
(select DATE_FORMAT(MAX(v1.datecheck),'%Y-%m-%d') from visitanc v1 inner JOIN visitancpregnancy p1 on p1.pcucodeperson = v1.pcucodeperson and v1.pid = p1.pid and p1.pregno = v1.pregno where v1.pid = ancperson.pid and v1.pcucodeperson = ancperson.pcucodeperson and DATEDIFF(v1.datecheck,p1.lmp) <= 90 and v1.pregno = ancperson.pregno group by ancperson.pid,ancperson.pcucodeperson,ancperson.pregno) as anc1,
(select DATE_FORMAT(MAX(v2.datecheck),'%Y-%m-%d') from visitanc v2 inner JOIN visitancpregnancy p2 on p2.pcucodeperson = v2.pcucodeperson and v2.pid = p2.pid and p2.pregno = v2.pregno where v2.pid = ancperson.pid and v2.pcucodeperson = ancperson.pcucodeperson and DATEDIFF(v2.datecheck,p2.lmp) between 112 AND 146 and v2.pregno = ancperson.pregno group by ancperson.pid,ancperson.pcucodeperson,ancperson.pregno) as anc2,
(select DATE_FORMAT(MAX(v3.datecheck),'%Y-%m-%d') from visitanc v3 inner JOIN visitancpregnancy p3 on p3.pcucodeperson = v3.pcucodeperson and v3.pid = p3.pid and p3.pregno = v3.pregno where v3.pid = ancperson.pid and v3.pcucodeperson = ancperson.pcucodeperson and DATEDIFF(v3.datecheck,p3.lmp) between 168 AND 202 and v3.pregno = ancperson.pregno group by ancperson.pid,ancperson.pcucodeperson,ancperson.pregno) as anc3,
(select DATE_FORMAT(MAX(v4.datecheck),'%Y-%m-%d') from visitanc v4 inner JOIN visitancpregnancy p4 on p4.pcucodeperson = v4.pcucodeperson and v4.pid = p4.pid and p4.pregno = v4.pregno where v4.pid = ancperson.pid and v4.pcucodeperson = ancperson.pcucodeperson and DATEDIFF(v4.datecheck,p4.lmp) between 210 AND 244 and v4.pregno = ancperson.pregno group by ancperson.pid,ancperson.pcucodeperson,ancperson.pregno) as anc4,
(select DATE_FORMAT(MAX(v5.datecheck),'%Y-%m-%d') from visitanc v5 inner JOIN visitancpregnancy p5 on p5.pcucodeperson = v5.pcucodeperson and v5.pid = p5.pid and p5.pregno = v5.pregno where v5.pid = ancperson.pid and v5.pcucodeperson = ancperson.pcucodeperson and DATEDIFF(v5.datecheck,p5.lmp) between 252 AND 286 and v5.pregno = ancperson.pregno group by ancperson.pid,ancperson.pcucodeperson,ancperson.pregno) as anc5
FROM
(SELECT
a.pcucodeperson,
a.pid,
p.fname,
concat(c.titlename, p.fname , '  ' , p.lname) AS pname,
p.birth,
FLOOR(datediff('$str',p.birth)/365.25) as age,
h.hno,
h.villcode,
h.xgis,
h.ygis,
a.pregno,
ap.lmp,
DATEDIFF('$sto',ap.lmp) as page
FROM
house as h
INNER JOIN person as p on p.pcucodeperson = h.pcucodeperson and p.hcode = h.hcode
LEFT JOIN ctitle as c on c.titlecode = p.prename
INNER JOIN visitancpregnancy as ap on ap.pcucodeperson = p.pcucodeperson and ap.pid = p.pid 
INNER JOIN visitanc as a on ap.pcucodeperson = a.pcucodeperson and ap.pid = a.pid and ap.pregno = a.pregno
where DATEDIFF('$sto',ap.lmp) BETWEEN 286 and DATEDIFF('$sto','$str')+286 $live_type2 AND ((p.dischargetype is null) or (p.dischargetype = '9')) $wvill
GROUP BY a.pcucodeperson, a.pid, a.pregno) as ancperson) as tmp_anc
order by villcode, fname) as anc5q
GROUP BY anc5q.pcucodeperson,anc5q.villcode
order by anc5q.pcucodeperson,anc5q.villcode";

$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>รายงานหญิงตั้งครรภ์ฝากครรภ์ครบ 5 ครั้งตามเกณฑ์คุณภาพ</b><br>';
$txt .= "<b>$mu </b></p><b>$hosp</b><br>$live_type_name<table width='95%' border='0' cellspacing='1' cellpadding='1' class='table table-striped table-hover table-bordered'>
  <tr>
    <th width='5%' scope='col'><div align='center'>ลำดับ</div></th>
    <th width='12%' scope='col'><div align='center'>หมู่บ้าน</div></th>
	<th width='8%' scope='col'><div align='center'>หมู่ที่</div></th>
	<th width='6%' scope='col'><div align='center'>หญิงตั้งครรภ์ที่อายุครรภ์มากกว่า 40 สัปดาห์</div></th>
	<th width='6%' scope='col'><div align='center'>ฝากครรภ์ครบ 5 ครั้งคุณภาพ</div></th>
	<th width='6%' scope='col'><div align='center'>ร้อยละ</div></th>
  </tr>";
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$percent1 = number_format($row[percent], 2, '.', '');
	$sum_anc_deliver_4 = $sum_anc_deliver_4+$row[anc_deliver_4];
	$sum_perall = $sum_perall+$row[perall];
	$sum_chk = $sum_chk+$row[chk];
if($sum_perall == "0"){
	$sum_percen = "0";
}else{
	$sum_percen = $sum_chk/$sum_perall*100;	
}
	$sum_percent1 = number_format($sum_percen, 2, '.', '');
++$i;
	if(($i%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$i</div></td>
    <td>$vill</td>
    <td><div align='center'>&nbsp;$moo</div></td>
    <td><div align='center'>$row[perall]</div></td>
	<td><div align='center'>$row[chk]</div></td>
	<td><div align='center'>$percent1</div></td>
  </tr>
  ";
}
$txt .= "<tr>
  <td>&nbsp;&nbsp;</td>
  <td><div align='center'>&nbsp;รวม</td>
  <td>&nbsp;&nbsp;</td>
  <td><div align='center'>$sum_perall</div></td>
  <td><div align='center'>$sum_chk</div></td>
  <td><div align='center'>$sum_percent1</div></td>
  </tr></table><p div align='right' class='text-danger'>ข้อมูลระหว่างวันที่  $strx ถึง $stox </p></div><br>";  
echo $txt;
?>
<?php
}
else{
		header("Location: ../main/login.php");
		}
		?>
</body>
</html>
