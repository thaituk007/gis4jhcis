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
$dx = date("md");
$yx = date("Y");
$yy = date("Y")-1;
if($dx > "1001"){$daymidyear = $yx."-10-01";}else{$daymidyear = $yy."-10-01";}
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
$village = $_GET[village];
if($village == "00000000"){
	$wvill = "";
}else{
	$wvill = " and h.villcode='$village' ";	
}
if($village == "00000000"){
	$mu = "ทุกหมู่บ้าน";
}else{
	$mu = getvillagename($village);	
}
$getage = $_GET[getage];
if($getage == "35"){
	$gage = "AND FLOOR((TO_DAYS('$daymidyear')-TO_DAYS(p.birth))/365.25) between 30 and 39";
}elseif($getage == "20"){
	$gage = "AND FLOOR((TO_DAYS('$daymidyear')-TO_DAYS(p.birth))/365.25) < 30";
}elseif($getage == "30"){
	$gage = "AND FLOOR((TO_DAYS('$daymidyear')-TO_DAYS(p.birth))/365.25) > 29";
}elseif($getage == "40"){
	$gage = "AND FLOOR((TO_DAYS('$daymidyear')-TO_DAYS(p.birth))/365.25) > 39";
}else{
	$gage = "";
}
if($getage == "35"){
	$gagename = "อายุ 30 - 39 ปี";
}elseif($getage == "20"){
	$gagename = "อายุต่ำกว่า 30 ปี";
}elseif($getage == "30"){
	$gagename = "อายุ 30 ปี ขึ้นไป";
}elseif($getage == "40"){
	$gagename = "อายุ 40 ปี ขึ้นไป";
}else{
	$gagename = "ทั้งหมด";
}
$str = retdaterangstr($_GET[str]);
$sto = retdaterangsto($_GET[str]);
$strx = retDatets($str);
$stox = retDatets($sto);
$live_type = $_GET[live_type];
if($live_type == '2'){$live_type2 = "p.typelive in ('0','1','2') and";}elseif($live_type == '1'){$live_type2 = "p.typelive in ('0','1','3') and";}else{$live_type2 = "p.typelive in ('0','1','2','3') and";}
if($live_type == '2'){$live_type_name = "ตามทะเบียนบ้าน(0,1,2)";}elseif($live_type == '1'){$live_type_name = "ที่อาศัยอยู่จริง (0,1,3)";}else{$live_type_name = "ทั้งหมดในเขตรับผิดชอบ(0,1,2,3)";}			
$sql = "select
pcucodeperson,
villcode,
villname,
count(distinct pid) as per,
count(distinct pid1) as count_stool1,
count(distinct pid2) as count_ov1,
sum(case when para like '%B66%' then 1 else 0 end) as ov,
sum(case when para like '%B68%' or para like '%B69%' or para like '%B70%' or para like '%B71%' then 1 else 0 end) as teania,
sum(case when para like '%B76%'  then 1 else 0 end) as hookworm,
sum(case when para like '%B77%'  then 1 else 0 end) as ascar,
sum(case when para like '%B78%'  then 1 else 0 end) as strong,
sum(case when para like '%B79%'  then 1 else 0 end) as trichu,
sum(case when para like '%B80%'  then 1 else 0 end) as entero,
sum(case when para like '%B83.1%'  then 1 else 0 end) as gnatho,
sum(case when para in ('B83.8','B83.9','B83')  then 1 else 0 end) as orther,
sum(case when para like 'B82%' then 1 else 0 end) as proto
from
(SELECT
p.pcucodeperson,
p.pid,
p.fname,
CONCAT(ctitle.titlename,p.fname,' ',p.lname) AS pname,
h.hno,
village.villname,
h.villcode,
h.xgis,
h.ygis,
p.birth,
p.typelive,
FLOOR((TO_DAYS('$daymidyear')-TO_DAYS(p.birth))/365.25) AS age
FROM
village
inner join house AS h on village.pcucode = h.pcucode and village.villcode = h.villcode
Inner Join person AS p ON h.pcucode = p.pcucodeperson AND h.hcode = p.hcode
Inner Join cstatus ON p.marystatus = cstatus.statuscode
Inner Join ctitle ON p.prename = ctitle.titlecode
WHERE $live_type2 ((p.dischargetype is null) or (p.dischargetype = '9')) AND
				SUBSTRING(h.villcode,7,2) <> '00' $gage $wvill ORDER BY h.villcode,h.hno*1
) as per
left join 
(SELECT
visit.pcucodeperson as pcucodeperson1,
visit.pid as pid1,
visit.visitno as visitno1,
visitdiag.diagcode,
visit.visitdate
FROM
visit
INNER JOIN visitdiag ON visit.pcucode = visitdiag.pcucode AND visit.visitno = visitdiag.visitno
where visit.visitdate between '$str' and '$sto' and visitdiag.diagcode = 'Z11.6' and (visit.flagservice <'04' OR visit.flagservice is null OR length(trim(visit.flagservice))=0 )) as fp
on per.pcucodeperson = fp.pcucodeperson1 and per.pid = fp.pid1
left join
(SELECT
visit.pcucodeperson as pcucodeperson2,
visit.pid as pid2,
visit.visitno as visitno2,
GROUP_CONCAT(visitdiag.diagcode) as para,
GROUP_CONCAT(cdisease.diseasenamethai) as diseasenamethai
FROM
visit
INNER JOIN visitdiag ON visit.pcucode = visitdiag.pcucode AND visit.visitno = visitdiag.visitno
inner join cdisease on visitdiag.diagcode = cdisease.diseasecode
where visit.visitdate between '$str' and '$sto' and visitdiag.diagcode like 'B%' and (visit.flagservice <'04' OR visit.flagservice is null OR length(trim(visit.flagservice))=0 ) 
group by visit.pcucode,visit.visitno) as para
on para.pcucodeperson2 = fp.pcucodeperson1 and para.pid2 = fp.pid1 and para.visitno2 = fp.visitno1
group by pcucodeperson, villcode
order by pcucodeperson, villcode";

$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>จำนวนประชากร';
$txt .= " $gagename ที่ได้รับการตรวจพยาธิใบไม้ตับ</b><br><b>  $mu </b></p>ประชากร $live_type_name<br><b>$hosp</b><table width='95%' border='0' cellspacing='1' cellpadding='1' class='table table-striped table-hover table-bordered'>
  <tr>
    <th width='5%' scope='col'><div align='center'>ลำดับ</div></th>
    <th width='12%' scope='col'><div align='center'>หมู่บ้าน</div></th>
	<th width='8%' scope='col'><div align='center'>หมู่ที่</div></th>
    <th width='8%' scope='col'><div align='center'>ประชาชน$gagename</div></th>
    <th width='5%' scope='col'><div align='center'>ได้รับการตรวจ</div></th>
	<th width='5%' scope='col'><div align='center'>ร้อยละ</div></th>
	<th width='5%' scope='col'><div align='center'>พบพยาธิ</div></th>
	<th width='5%' scope='col'><div align='center'>ร้อยละ</div></th>
	<th width='5%' scope='col'><div align='center'>พยาธิใบไม้ตับ</div></th>
	<th width='5%' scope='col'><div align='center'>ร้อยละ</div></th>
	<th width='5%' scope='col'><div align='center'>พยาธิตัวตืด</div></th>
	<th width='5%' scope='col'><div align='center'>พยาธิปากขอ</div></th>
	<th width='5%' scope='col'><div align='center'>พยาธิไส้เดือน</div></th>
	<th width='5%' scope='col'><div align='center'>พยาธิสตรองจีลอยด์</div></th>
	<th width='5%' scope='col'><div align='center'>พยาธิแส้ม้า</div></th>
	<th width='5%' scope='col'><div align='center'>พยาธิเข็มหมุด</div></th>
	<th width='5%' scope='col'><div align='center'>พยาธิตัวจี๊ด</div></th>
	<th width='5%' scope='col'><div align='center'>พยาธิอื่น</div></th>
	<th width='5%' scope='col'><div align='center'>ปรสิตลำไส้อิ่น</div></th>
  </tr>";
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
if($row[per] == "0"){
	$percen = "0";
}else{
	$percen = ($row[count_stool1])/($row[per])*100;	
}
if($row[count_stool1] == "0"){
	$percen2 = "0";
}else{
	$percen2 = ($row[count_ov1])/($row[count_stool1])*100;	
}
if($row[count_stool1] == "0"){
	$percen3 = "0";
}else{
	$percen3 = ($row[ov])/($row[count_stool1])*100;	
}
	$percent1 = number_format($percen, 2, '.', '');
	$percent2 = number_format($percen2, 2, '.', '');
	$percent3 = number_format($percen3, 2, '.', '');
	$sum_per = $sum_per+$row[per];
	$sum_count_stool1 = $sum_count_stool1+$row[count_stool1];
	$sum_count_ov1 = $sum_count_ov1+$row[count_ov1];
	$sum_ov = $sum_ov+$row[ov];
	$sum_teania = $sum_teania+$row[teania];
	$sum_hookworm = $sum_hookworm+$row[hookworm];
	$sum_ascar = $sum_ascar+$row[ascar];
	$sum_strong = $sum_strong+$row[strong];
	$sum_trichu = $sum_trichu+$row[trichu];
	$sum_entero = $sum_entero+$row[entero];
	$sum_gnatho = $sum_gnatho+$row[gnatho];
	$sum_orther = $sum_orther+$row[orther];
	$sum_proto = $sum_proto+$row[proto];
if($sum_per == "0"){
	$sum_percen = "0";
}else{
	$sum_percen = $sum_count_stool1/$sum_per*100;	
}
	$sum_percent1 = number_format($sum_percen, 2, '.', '');
if($sum_count_stool1 == "0"){
	$sum_percen2 = "0";
}else{
	$sum_percen2 = $sum_count_ov1/$sum_count_stool1*100;	
}
	$sum_percent2 = number_format($sum_percen2, 2, '.', '');
if($sum_count_stool1 == "0"){
	$sum_percen3 = "0";
}else{
	$sum_percen3 = $sum_ov/$sum_count_stool1*100;	
}
	$sum_percent3 = number_format($sum_percen3, 2, '.', '');
++$i;
	if(($i%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$i</div></td>
    <td>$row[villname]</td>
    <td><div align='center'>$moo</div></td>
    <td><div align='center'>$row[per]</div></td>
	<td><div align='center'>$row[count_stool1]</div></td>
	<td><div align='center'>$percent1</div></td>
	<td><div align='center'>$row[count_ov1]</div></td>
	<td><div align='center'>$percent2</div></td>
	<td><div align='center'>$row[ov]</div></td>
	<td><div align='center'>$percent3</div></td>
	<td><div align='center'>$row[teania]</div></td>
	<td><div align='center'>$row[hookworm]</div></td>
	<td><div align='center'>$row[ascar]</div></td>
	<td><div align='center'>$row[strong]</div></td>
	<td><div align='center'>$row[trichu]</div></td>
	<td><div align='center'>$row[entero]</div></td>
	<td><div align='center'>$row[gnatho]</div></td>
	<td><div align='center'>$row[orther]</div></td>
	<td><div align='center'>$row[proto]</div></td>
  </tr>
  ";
}
$txt .= "<tr>
  <td>&nbsp;&nbsp;</td>
  <td><div align='center'>&nbsp;รวม</td>
  <td>&nbsp;&nbsp;</td>
  <td><div align='center'>$sum_per</div></td>
  <td><div align='center'>$sum_count_stool1</div></td>
  <td><div align='center'>$sum_percent1</div></td>
  <td><div align='center'>$sum_count_ov1</div></td>
  <td><div align='center'>$sum_percent2</div></td>
  <td><div align='center'>$sum_ov</div></td>
  <td><div align='center'>$sum_percent3</div></td>
  <td><div align='center'>$sum_teania</div></td>
  <td><div align='center'>$sum_hookworm</div></td>
  <td><div align='center'>$sum_ascar</div></td>
  <td><div align='center'>$sum_strong</div></td>
  <td><div align='center'>$sum_trichu</div></td>
  <td><div align='center'>$sum_entero</div></td>
  <td><div align='center'>$sum_gnatho</div></td>
  <td><div align='center'>$sum_orther</div></td>
  <td><div align='center'>$sum_proto</div></td>
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
