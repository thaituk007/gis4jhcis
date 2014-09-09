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
if($villcode == "00000000"){
	$mu = "ทุกหมู่บ้าน";
}else{
	$mu = getvillagename($villcode);	
}
$live_type = $_GET[live_type];
if($live_type == '2'){$live_type_name = "ตามทะเบียนบ้าน(0,1,2)";}elseif($live_type == '1'){$live_type_name = "ที่อาศัยอยู่จริง (0,1,3)";}else{$live_type_name = "ทั้งหมดในเขตรับผิดชอบ(0,1,2,3)";}	
if($live_type == '2'){$live_type2 = "and person.typelive in ('0','1','2')";}elseif($live_type == '1'){$live_type2 = "and person.typelive in ('0','1','3')";}else{$live_type2 = "and person.typelive in ('0','1','2','3')";}
$str = retdaterangstr($_GET[str]);
$sto = retdaterangsto($_GET[str]);
$strx = retDatets($str);
$stox = retDatets($sto);		
$sql = "select
tmp_per.pcucodeperson,
villcode,
villname,
count(distinct tmp_per.pid) as per,
sum(case when chk is not null then 1 else 0 end) as old_t,
sum(case when chk = 1 then 1 else 0 end) as old_lv1,
sum(case when chk = 2 then 1 else 0 end) as old_lv2,
sum(case when chk is null then 1 else 0 end) as old_no
from
(select
person.pcucodeperson,
person.pid,
person.fname, 
concat(ctitle.titlename, person.fname , '  ' , person.lname) AS pname,
person.birth,
ROUND(DATEDIFF(now(),person.birth)/365.25) AS age,
village.villcode,
village.villname,
house.hno,
house.hcode,
house.xgis,
house.ygis
FROM
village
Inner Join house ON village.pcucode = house.pcucode AND village.villcode = house.villcode
Inner Join person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
Inner Join ctitle ON person.prename = ctitle.titlecode
where  FLOOR((TO_DAYS(NOW())-TO_DAYS(person.birth))/365.25) >59 and  ((person.dischargetype is null) or (person.dischargetype = '9'))  and SUBSTRING(house.villcode,7,2) <> '00' $wvill $live_type2) as tmp_per
left join
(select
visit.pcucodeperson,
visit.pid,
visit.visitdate, 
max(visit.visitdate) as m_visit,
visit.visitno,
visit.symptoms,
visit.vitalcheck,
visitdiag.diagcode,
case when vitalcheck is null then 0 when vitalcheck not like '%ไม่ผ่าน%' then 1 when vitalcheck like '%ไม่ผ่าน%' then 2 else '9' end as chk
FROM
village
Inner Join house ON village.pcucode = house.pcucode and village.villcode = house.villcode
Inner Join person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
Inner Join ctitle ON person.prename = ctitle.titlecode
Inner Join visit ON person.pcucodeperson = visit.pcucodeperson AND person.pid = visit.pid
Inner Join visitdiag ON visit.pcucode = visitdiag.pcucode AND visit.visitno = visitdiag.visitno
where FLOOR((TO_DAYS(NOW())-TO_DAYS(person.birth))/365.25) >59 and  ((person.dischargetype is null) or (person.dischargetype = '9'))  and SUBSTRING(house.villcode,7,2) <> '00' $live_type2 and visit.symptoms like '%ตรวจสุขภาพผู้สูงอายุ%'
 and visit.visitdate between '$str' and '$sto' and visitdiag.diagcode in ('Z00.0') and (visit.flagservice <'04' OR visit.flagservice is null OR length(trim(visit.flagservice))=0 )
group by person.pid) as tmp_ncd
ON tmp_per.pcucodeperson = tmp_ncd.pcucodeperson AND tmp_per.pid = tmp_ncd.pid
group by villcode
order by tmp_per.pcucodeperson,villcode";

$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>รายงานการตรวจสุขภาพผู้สูงอายุมีสุขภาพที่พึงประสงค์</b><br>';
$txt .= "<b>ข้อมูลระหว่างวันที่ $strx ถึง $stox $mu </b></p><br><b>$hosp</b><table width='95%' border='0' cellspacing='1' cellpadding='1' class='table table-striped table-hover table-bordered'>
  <tr>
    <th width='4%' scope='col'><div align='center'>ลำดับ</div></th>
    <th width='10%' scope='col'><div align='center'>หมู่บ้าน</div></th>
	<th width='4%' scope='col'><div align='center'>หมู่ที่</div></th>
    <th width='7%' scope='col'><div align='center'>ผู้สูงอายุทั้งหมด</div></th>
    <th width='7%' scope='col'><div align='center'>ได้รับการตรวจ</div></th>
	<th width='7%' scope='col'><div align='center'>ร้อยละ</div></th>
	<th width='7%' scope='col'><div align='center'>ผู้สูงอายุสุขภาพดี ผ่าน</div></th>
	<th width='7%' scope='col'><div align='center'>ร้อยละ</div></th>
	<th width='7%' scope='col'><div align='center'>ผู้สูงอายุสุขภาพดี ไม่ผ่าน</div></th>
	<th width='7%' scope='col'><div align='center'>ร้อยละ</div></th>
	<th width='7%' scope='col'><div align='center'>ไม่ได้ตรวจ</div></th>
  </tr>";
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
if($row[per] == "0"){
	$percen = "0";
}else{
	$percen = ($row[old_t])/($row[per])*100;	
}
	$percent1 = number_format($percen, 2, '.', '');
if($row[old_t] == "0"){
	$percen2 = "0";
}else{
	$percen2 = ($row[old_lv1])/($row[old_t])*100;	
}
	$percent2 = number_format($percen2, 2, '.', '');
if($row[old_t] == "0"){
	$percen3 = "0";
}else{
	$percen3 = ($row[old_lv2])/($row[old_t])*100;	
}
	$percent3 = number_format($percen3, 2, '.', '');
	$sum_per = $sum_per+$row[per];
	$sum_old_t = $sum_old_t+$row[old_t];
	$sum_old_lv1 = $sum_old_lv1+$row[old_lv1];
	$sum_old_lv2 = $sum_old_lv2+$row[old_lv2];
	$sum_old_no = $sum_old_no+$row[old_no];
if($sum_per == "0"){
	$sum_percen = "0";
}else{
	$sum_percen = $sum_old_t/$sum_per*100;	
}
	$sum_percent1 = number_format($sum_percen, 2, '.', '');
if($sum_old_t == "0"){
	$sum_percen2 = "0";
}else{
	$sum_percen2 = $sum_old_lv1/$sum_old_t*100;	
}
	$sum_percent2 = number_format($sum_percen2, 2, '.', '');
if($sum_old_t == "0"){
	$sum_percen3 = "0";
}else{
	$sum_percen3 = $sum_old_lv2/$sum_old_t*100;	
}
	$sum_percent3 = number_format($sum_percen3, 2, '.', '');
++$i;
	if(($i%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$i</div></td>
    <td>$row[villname]</td>
    <td><div align='center'>&nbsp;$moo</div></td>
    <td><div align='center'>$row[per]</div></td>
	<td><div align='center'>$row[old_t]</div></td>
	<td><div align='center'>$percent1</div></td>
	<td><div align='center'>$row[old_lv1]</div></td>
	<td><div align='center'>$percent2</div></td>
	<td><div align='center'>$row[old_lv2]</div></td>
	<td><div align='center'>$percent3</div></td>
	<td><div align='center'>$row[old_no]</div></td>
  </tr>
  ";
}
$txt .= "<tr>
  <td>&nbsp;&nbsp;</td>
  <td><div align='center'>&nbsp;รวม</td>
  <td>&nbsp;&nbsp;</td>
  <td><div align='center'>$sum_per</div></td>
	<td><div align='center'>$sum_old_t</div></td>
	<td><div align='center'>$sum_percent1</div></td>
	<td><div align='center'>$sum_old_lv1</div></td>
	<td><div align='center'>$sum_percent2</div></td>
	<td><div align='center'>$sum_old_lv2</div></td>
	<td><div align='center'>$sum_percent3</div></td>
	<td><div align='center'>$sum_old_no</div></td>
  </tr></table>";  
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
