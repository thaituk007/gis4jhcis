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
	$wvill = "h.villcode='$villcode' and ";	
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
if($live_type == '2'){$live_type2 = "and p.typelive in ('0','1','2')";}elseif($live_type == '1'){$live_type2 = "and p.typelive in ('0','1','3')";}else{$live_type2 = "and p.typelive in ('0','1','2','3')";}	
$sql = "
SELECT t1.mumoi,t1.villname,t1.villcode,
COUNT(t1.pid) AS child,
SUM(CASE when t2.weight>0 THEN 1 else 0 end ) AS childnutri,
ROUND(SUM(CASE when t2.weight>0 THEN 1 else 0 end )*100/COUNT(t1.pid),2) AS percchildweight ,
SUM(CASE when t2.tall>0 THEN 1 else 0 end ) AS hight,
ROUND(SUM(CASE when t2.tall>0 THEN 1 else 0 end )*100/COUNT(t1.pid),2) AS perchight ,
SUM(CASE when t2.weight>0 AND t2.tall>0 THEN 1 else 0 end ) AS weighthight,
ROUND(SUM(CASE when t2.weight>0 AND t2.tall>0 THEN 1 else 0 end )*100/COUNT(t1.pid),2) AS percweighthight ,

SUM(CASE when t2.bw_level=1 THEN 1 else 0 end) AS WL1, #น้ำหนัก/อายุ #น้อยกว่าเกณฑ์ ค่อนข้างน้อย ตามเกณฑ์ ค่อนข้างมาก มากเกินเกณฑ์
ROUND(SUM(CASE when t2.bw_level=1 THEN 1 else 0 end)*100/COUNT(t2.pid),2) AS percWL1, 
SUM(CASE when t2.bw_level=2 THEN 1 else 0 end) AS WL2,
ROUND(SUM(CASE when t2.bw_level=2 THEN 1 else 0 end)*100/COUNT(t2.pid),2) AS percWL2,
SUM(CASE when t2.bw_level=3 THEN 1 else 0 end) AS WL3,
ROUND(SUM(CASE when t2.bw_level=3 THEN 1 else 0 end)*100/COUNT(t2.pid),2) AS percWL3,
SUM(CASE when t2.bw_level=4 THEN 1 else 0 end) AS WL4,
ROUND(SUM(CASE when t2.bw_level=4 THEN 1 else 0 end)*100/COUNT(t2.pid),2) AS percWL4,
SUM(CASE when t2.bw_level=5 THEN 1 else 0 end) AS WL5,
ROUND(SUM(CASE when t2.bw_level=5 THEN 1 else 0 end)*100/COUNT(t2.pid),2) AS percWL5,

SUM(CASE when t2.heigth_level=1 THEN 1 else 0 end) AS HL1, #ส่วนสูง/อายุ # เตี้ย ค่อนข้างเตี้ย สูงตามเกณฑ์ ค่อนข้างสูง สูงกว่าเกณฑ์
ROUND(SUM(CASE when t2.heigth_level=1 THEN 1 else 0 end)*100/COUNT(t2.pid),2) AS percHL1,
SUM(CASE when t2.heigth_level=2 THEN 1 else 0 end) AS HL2,
ROUND(SUM(CASE when t2.heigth_level=2 THEN 1 else 0 end)*100/COUNT(t2.pid),2) AS percHL2,
SUM(CASE when t2.heigth_level=3 THEN 1 else 0 end) AS HL3,
ROUND(SUM(CASE when t2.heigth_level=3 THEN 1 else 0 end)*100/COUNT(t2.pid),2) AS percHL3,
SUM(CASE when t2.heigth_level=4 THEN 1 else 0 end) AS HL4,
ROUND(SUM(CASE when t2.heigth_level=4 THEN 1 else 0 end)*100/COUNT(t2.pid),2) AS percHL4,
SUM(CASE when t2.heigth_level=5 THEN 1 else 0 end) AS HL5,
ROUND(SUM(CASE when t2.heigth_level=5 THEN 1 else 0 end)*100/COUNT(t2.pid),2) AS percHL5,

SUM(CASE when t2.bmi_level=1 THEN 1 else 0 end) AS BMIL1, #น้ำหนัก/ส่วนสูง # ผอม ค่อนข้างผอม สมส่วน ท้วม เริ่มอ้วน อ้วน
ROUND(SUM(CASE when t2.bmi_level=1 THEN 1 else 0 end)*100/COUNT(t2.pid),2) AS percBMIL1,
SUM(CASE when t2.bmi_level=2 THEN 1 else 0 end) AS BMIL2,
ROUND(SUM(CASE when t2.bmi_level=2 THEN 1 else 0 end)*100/COUNT(t2.pid),2) AS percBMIL2,
SUM(CASE when t2.bmi_level=3 THEN 1 else 0 end) AS BMIL3,
ROUND(SUM(CASE when t2.bmi_level=3 THEN 1 else 0 end)*100/COUNT(t2.pid),2) AS percBMIL3,
SUM(CASE when t2.bmi_level=4 THEN 1 else 0 end) AS BMIL4,
ROUND(SUM(CASE when t2.bmi_level=4 THEN 1 else 0 end)*100/COUNT(t2.pid),2) AS percBMIL4,
SUM(CASE when t2.bmi_level=5 THEN 1 else 0 end) AS BMIL5,
ROUND(SUM(CASE when t2.bmi_level=5 THEN 1 else 0 end)*100/COUNT(t2.pid),2) AS percBMIL5,
SUM(CASE when t2.bmi_level=6 THEN 1 else 0 end) AS BMIL6,
ROUND(SUM(CASE when t2.bmi_level=6 THEN 1 else 0 end)*100/COUNT(t2.pid),2) AS percBMIL6,
SUM(CASE when t2.heigth_level in ('3','4','5') or t2.bmi_level=3 THEN 1 else 0 end) AS 'kpi13',
ROUND(SUM(CASE when t2.heigth_level in ('3','4','5') or t2.bmi_level=3 THEN 1 else 0 end)*100/COUNT(t2.pid),2) AS 'perckpi13'

FROM
(SELECT p.pid,h.pcucode,
p.birth,FLOOR((TO_DAYS('$sto')-TO_DAYS(p.birth))/30.44) as agemonth, 
RIGHT(h.villcode,2) AS mumoi,villname,h.villcode
FROM person p
LEFT JOIN house h ON h.hcode=p.hcode and h.pcucodeperson=p.pcucodeperson
LEFT JOIN village v ON v.villcode=h.villcode and v.pcucode=h.pcucode
WHERE $wvill p.BIRTH<'$sto' AND RIGHT(h.villcode,2)<>'00' $live_type2 AND CONCAT(p.pid,p.pcucodeperson) NOT IN (SELECT CONCAT(persondeath.pid,persondeath.pcucodeperson) FROM persondeath)
GROUP BY p.pcucodeperson,p.pid
HAVING agemonth<72
ORDER BY p.mumoi,hnomoi) t1

LEFT JOIN

(SELECT nu.pcucode,nu.pid,nu.sex,nu.visitdate ,nu.agemonth,nu.tall,nu.weight
,max(CASE when nu.tall BETWEEN hc.hmi and hc.hmx THEN hc.nul else null end) as 'heigth_level'
,MAX(case when nu.weight BETWEEN bmi_c.bwmi and bmi_c.bwmx THEN bmi_c.bwnul else null end) as 'bmi_level'
,MAX(case when nu.weight BETWEEN bc.bmin and bc.bmax THEN bc.bnul else null end) as 'bw_level'
from
(SELECT n.pcucode,v.pid,p.sex,p.birth,FLOOR((TO_DAYS(v.visitdate)-TO_DAYS(p.birth))/30.44) as agemonth,n.tall,n.weight,v.visitdate,CONCAT(getAgeMonth(p.birth,v.visitdate)
,case when p.sex=1 then 'm' Else 'f'end)as 'ms'
,CONCAT(CEILING(n.tall)
,case when p.sex=1 then 'm' Else 'f'end)as 'ts'

FROM visitnutrition as n
INNER JOIN visit as v on n.visitno=v.visitno
INNER JOIN person as p on v.pcucodeperson=p.pcucodeperson and v.pid=p.pid
WHERE v.visitdate BETWEEN '$str' and '$sto')as nu
INNER JOIN(SELECT cchart_bh.height_min as hmi ,cchart_bh.height_max as hmx,cchart_bh.nutrition_level as 'nul'
,concat(cchart_bh.age_month,case when cchart_bh.sex=1 then 'm' Else 'f'end)as 'ms' from cchart_bh) as hc on nu.ms=hc.ms
INNER JOIN (SELECT cchart_bmi.bw_min as bwmi ,cchart_bmi.bw_max as bwmx,cchart_bmi.nutrition_level as 'bwnul'
,concat(cchart_bmi.height,case when cchart_bmi.sex=1 then 'm' Else 'f'end)as 'bws' from cchart_bmi) as bmi_c on nu.ts=bmi_c.bws
INNER JOIN(SELECT cchart_bw.bw_min as bmin ,cchart_bw.bw_max as bmax,cchart_bw.nutrition_level as 'bnul'
,concat(cchart_bw.age_month,case when cchart_bw.sex=1 then 'm' Else 'f'end)as 'bs' from cchart_bw) as bc on nu.ms=bc.bs
where nu.agemonth < 72
GROUP BY nu.pcucode,nu.pid
ORDER BY nu.pid) t2
ON t2.pid=t1.pid and t2.pcucode=t1.pcucode
GROUP BY t1.mumoi";

$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>รายงานภาวะโภชนาการเด็กอายุ 0 - 72 เดือน</b><br>';
$txt .= "<b>$mu </b></p><br><br><b>$hosp</b><table width='99%' border='0' cellspacing='1' cellpadding='1' class='table table-striped table-hover table-bordered'>
  <tr>
    <th width='4%' rowspan='2' scope='col'><div align='center'>ลำดับ</div></th>
    <th width='10%' rowspan='2' scope='col'><div align='center'>หมู่บ้าน</div></th>
	<th width='4%' rowspan='2' scope='col'><div align='center'>หมู่ที่</div></th>
	<th width='4%' rowspan='2' scope='col'><div align='center'>เด็กทั้งหมด</div></th>
	<th width='4%' rowspan='2' scope='col'><div align='center'>ชั่งน้ำหนัก</div></th>
	<th width='4%' rowspan='2' scope='col'><div align='center'>ร้อยละ</div></th>
    <th width='4%' rowspan='2' scope='col'><div align='center'>วัดส่วนสูง</div></th>
	<th width='4%' rowspan='2' scope='col'><div align='center'>ร้อยละ</div></th>
	<th width='4%' rowspan='2' scope='col'><div align='center'>ชั่งน้ำหนักวัดส่วนสูง</div></th>
	<th width='4%' rowspan='2' scope='col'><div align='center'>ร้อยละ</div></th>
	<th width='40%' colspan='10' scope='col'><div align='center'>อายุ/น้ำหนัก</div></th>
	<th width='40%' colspan='10' scope='col'><div align='center'>อายุ/ส่วนสูง</div></th>
	<th width='48%' colspan='12' scope='col'><div align='center'>น้ำหนัก/ส่วนสูง</div></th>
	<th width='4%' rowspan='2' scope='col'><div align='center'>ส่วนสูงระดับดีรูปร่างสมส่วน</div></th>
	<th width='4%' rowspan='2' scope='col'><div align='center'>ร้อยละ</div></th>
  </tr>
  	<th width='4%' scope='col'><div align='center'>น้ำหนักต่ำมาก</div></th>
	<th width='4%' scope='col'><div align='center'>ร้อยละ</div></th>
    <th width='4%' scope='col'><div align='center'>น้ำหนักต่ำ</div></th>
	<th width='4%' scope='col'><div align='center'>ร้อยละ</div></th>
	<th width='4%' scope='col'><div align='center'>น้ำหนักปกติ</div></th>
    <th width='4%' scope='col'><div align='center'>ร้อยละ</div></th>
	<th width='4%' scope='col'><div align='center'>น้ำหนักสูง</div></th>
	<th width='4%' scope='col'><div align='center'>ร้อยละ</div></th>
	<th width='4%' scope='col'><div align='center'>น้ำหนักสูงมาก</div></th>
	<th width='4%' scope='col'><div align='center'>ร้อยละ</div></th>
	<th width='4%' scope='col'><div align='center'>เตี้ย</div></th>
    <th width='4%' scope='col'><div align='center'>ร้อยละ</div></th>
	<th width='4%' scope='col'><div align='center'>ค่อนข้างเตี้ย</div></th>
	<th width='4%' scope='col'><div align='center'>ร้อยละ</div></th>
    <th width='4%' scope='col'><div align='center'>ปกติ</div></th>
	<th width='4%' scope='col'><div align='center'>ร้อยละ</div></th>
	<th width='4%' scope='col'><div align='center'>ค่อนข้างสูง</div></th>
	<th width='4%' scope='col'><div align='center'>ร้อยละ</div></th>
	<th width='4%' scope='col'><div align='center'>สูงเกินเกณฑ์</div></th>
	<th width='4%' scope='col'><div align='center'>ร้อยละ</div></th>
    <th width='4%' scope='col'><div align='center'>ผอม</div></th>
	<th width='4%' scope='col'><div align='center'>ร้อยละ</div></th>
	<th width='4%' scope='col'><div align='center'>ค่อนข้างผอม</div></th>
    <th width='4%' scope='col'><div align='center'>ร้อยละ</div></th>
	<th width='4%' scope='col'><div align='center'>สมส่วน</div></th>
	<th width='4%' scope='col'><div align='center'>ร้อยละ</div></th>
	<th width='4%' scope='col'><div align='center'>ค่อนข้างอ้วน</div></th>
	<th width='4%' scope='col'><div align='center'>ร้อยละ</div></th>	
	<th width='4%' scope='col'><div align='center'>อ้วน</div></th>
    <th width='4%' scope='col'><div align='center'>ร้อยละ</div></th>
	<th width='4%' scope='col'><div align='center'>อ้วนมาก</div></th>
	<th width='4%' scope='col'><div align='center'>ร้อยละ</div></th>
  </tr>
";
while($row=mysql_fetch_array($result)) {
	
$sum_child = $sum_child+$row[child];
$sum_childnutri = $sum_childnutri+$row[childnutri];
$sum_hight = $sum_hight+$row[hight];
$sum_weighthight = $sum_weighthight+$row[weighthight];
if($sum_child == "0" or $sum_child == ""){
	$sum_percchildweight = number_format(0, 2, '.', '');
	$sum_perchight = number_format(0, 2, '.', '');
	$sum_percweighthight = number_format(0, 2, '.', '');
}else{
	$sum_percchildweight = number_format(($sum_childnutri)/($sum_child)*100, 2, '.', '');
	$sum_perchight = number_format(($sum_hight)/($sum_child)*100, 2, '.', '');
	$sum_percweighthight = number_format(($sum_weighthight)/($sum_child)*100, 2, '.', '');
}
$sum_WL1 = $sum_WL1+$row[WL1];
$sum_WL2 = $sum_WL2+$row[WL2];
$sum_WL3 = $sum_WL3+$row[WL3];
$sum_WL4 = $sum_WL4+$row[WL4];
$sum_WL5 = $sum_WL5+$row[WL5];
if($sum_childnutri == "0" or $sum_childnutri == ""){
	$sum_percWL1 = number_format(0, 2, '.', '');
	$sum_percWL2 = number_format(0, 2, '.', '');
	$sum_percWL3 = number_format(0, 2, '.', '');
	$sum_percWL4 = number_format(0, 2, '.', '');
	$sum_percWL5 = number_format(0, 2, '.', '');
}else{
	$sum_percWL1 = number_format(($sum_WL1)/($sum_childnutri)*100, 2, '.', '');
	$sum_percWL2 = number_format(($sum_WL2)/($sum_childnutri)*100, 2, '.', '');
	$sum_percWL3 = number_format(($sum_WL3)/($sum_childnutri)*100, 2, '.', '');
	$sum_percWL4 = number_format(($sum_WL4)/($sum_childnutri)*100, 2, '.', '');
	$sum_percWL5 = number_format(($sum_WL5)/($sum_childnutri)*100, 2, '.', '');
}

$sum_HL1 = $sum_HL1+$row[HL1];
$sum_HL2 = $sum_HL2+$row[HL2];
$sum_HL3 = $sum_HL3+$row[HL3];
$sum_HL4 = $sum_HL4+$row[HL4];
$sum_HL5 = $sum_HL5+$row[HL5];
if($sum_hight == "0" or $sum_hight == ""){
	$sum_percHL1 = number_format(0, 2, '.', '');
	$sum_percHL2 = number_format(0, 2, '.', '');
	$sum_percHL3 = number_format(0, 2, '.', '');
	$sum_percHL4 = number_format(0, 2, '.', '');
	$sum_percHL5 = number_format(0, 2, '.', '');
}else{
	$sum_percHL1 = number_format(($sum_HL1)/($sum_hight)*100, 2, '.', '');
	$sum_percHL2 = number_format(($sum_HL2)/($sum_hight)*100, 2, '.', '');
	$sum_percHL3 = number_format(($sum_HL3)/($sum_hight)*100, 2, '.', '');
	$sum_percHL4 = number_format(($sum_HL4)/($sum_hight)*100, 2, '.', '');
	$sum_percHL5 = number_format(($sum_HL5)/($sum_hight)*100, 2, '.', '');
}

$sum_BMIL1 = $sum_BMIL1+$row[BMIL1];
$sum_BMIL2 = $sum_BMIL2+$row[BMIL2];
$sum_BMIL3 = $sum_BMIL3+$row[BMIL3];
$sum_BMIL4 = $sum_BMIL4+$row[BMIL4];
$sum_BMIL5 = $sum_BMIL5+$row[BMIL5];
$sum_BMIL6 = $sum_BMIL6+$row[BMIL6];
$sum_kpi13 = $sum_kpi13+$row[kpi13];
if($sum_weighthight == "0" or $sum_weighthight == ""){
	$sum_percBMIL1 = number_format(0, 2, '.', '');
	$sum_percBMIL2 = number_format(0, 2, '.', '');
	$sum_percBMIL3 = number_format(0, 2, '.', '');
	$sum_percBMIL4 = number_format(0, 2, '.', '');
	$sum_percBMIL5 = number_format(0, 2, '.', '');
	$sum_percBMIL6 = number_format(0, 2, '.', '');
	$sum_perckpi13 = number_format(0, 2, '.', '');
}else{
	$sum_percBMIL1 = number_format(($sum_BMIL1)/($sum_weighthight)*100, 2, '.', '');
	$sum_percBMIL2 = number_format(($sum_BMIL2)/($sum_weighthight)*100, 2, '.', '');
	$sum_percBMIL3 = number_format(($sum_BMIL3)/($sum_weighthight)*100, 2, '.', '');
	$sum_percBMIL4 = number_format(($sum_BMIL4)/($sum_weighthight)*100, 2, '.', '');
	$sum_percBMIL5 = number_format(($sum_BMIL5)/($sum_weighthight)*100, 2, '.', '');
	$sum_percBMIL6 = number_format(($sum_BMIL6)/($sum_weighthight)*100, 2, '.', '');
	$sum_perckpi13 = number_format(($sum_kpi13)/($sum_weighthight)*100, 2, '.', '');
}
++$i;
	if(($i%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$i</div></td>
    <td>$row[villname]</td>
    <td><div align='center'>&nbsp;$row[mumoi]</div></td>
	<td><div align='center'>$row[child]</div></td>
	<td><div align='center'>$row[childnutri]</div></td>
	<td><div align='center'>$row[percchildweight]</div></td>
	<td><div align='center'>$row[hight]</div></td>
	<td><div align='center'>$row[perchight]</div></td>
	<td><div align='center'>$row[weighthight]</div></td>
	<td><div align='center'>$row[percweighthight]</div></td>
	<td><div align='center'>$row[WL1]</div></td>
	<td><div align='center'>$row[percWL1]</div></td>
	<td><div align='center'>$row[WL2]</div></td>
	<td><div align='center'>$row[percWL2]</div></td>
	<td><div align='center'>$row[WL3]</div></td>
	<td><div align='center'>$row[percWL3]</div></td>
	<td><div align='center'>$row[WL4]</div></td>
	<td><div align='center'>$row[percWL4]</div></td>
	<td><div align='center'>$row[WL5]</div></td>
	<td><div align='center'>$row[percWL5]</div></td>
	<td><div align='center'>$row[HL1]</div></td>
	<td><div align='center'>$row[percHL1]</div></td>
	<td><div align='center'>$row[HL2]</div></td>
	<td><div align='center'>$row[percHL2]</div></td>
	<td><div align='center'>$row[HL3]</div></td>
	<td><div align='center'>$row[percHL3]</div></td>
	<td><div align='center'>$row[HL4]</div></td>
	<td><div align='center'>$row[percHL4]</div></td>
	<td><div align='center'>$row[HL5]</div></td>
	<td><div align='center'>$row[percHL5]</div></td>
	<td><div align='center'>$row[BMIL1]</div></td>
	<td><div align='center'>$row[percBMIL1]</div></td>
	<td><div align='center'>$row[BMIL2]</div></td>
	<td><div align='center'>$row[percBMIL2]</div></td>
	<td><div align='center'>$row[BMIL3]</div></td>
	<td><div align='center'>$row[percBMIL3]</div></td>
	<td><div align='center'>$row[BMIL4]</div></td>
	<td><div align='center'>$row[percBMIL4]</div></td>
	<td><div align='center'>$row[BMIL5]</div></td>
	<td><div align='center'>$row[percBMIL5]</div></td>
	<td><div align='center'>$row[BMIL6]</div></td>
	<td><div align='center'>$row[percBMIL6]</div></td>
	<td><div align='center'>$row[kpi13]</div></td>
	<td><div align='center'>$row[perckpi13]</div></td>
  </tr>
  ";
}
$txt .= "<tr>
  	<td>&nbsp;&nbsp;</td>
  	<td><div align='center'>&nbsp;รวม</td>
  	<td>&nbsp;&nbsp;</td>
	<td><div align='center'>$sum_child</div></td>
	<td><div align='center'>$sum_childnutri</div></td>
	<td><div align='center'>$sum_percchildweight</div></td>
	<td><div align='center'>$sum_hight</div></td>
	<td><div align='center'>$sum_perchight</div></td>
	<td><div align='center'>$sum_weighthight</div></td>
	<td><div align='center'>$sum_percweighthight</div></td>
	<td><div align='center'>$sum_WL1</div></td>
	<td><div align='center'>$sum_percWL1</div></td>
	<td><div align='center'>$sum_WL2</div></td>
	<td><div align='center'>$sum_percWL2</div></td>
	<td><div align='center'>$sum_WL3</div></td>
	<td><div align='center'>$sum_percWL3</div></td>
	<td><div align='center'>$sum_WL4</div></td>
	<td><div align='center'>$sum_percWL4</div></td>
	<td><div align='center'>$sum_WL5</div></td>
	<td><div align='center'>$sum_percWL5</div></td>
	<td><div align='center'>$sum_HL1</div></td>
	<td><div align='center'>$sum_percHL1</div></td>
	<td><div align='center'>$sum_HL2</div></td>
	<td><div align='center'>$sum_percHL2</div></td>
	<td><div align='center'>$sum_HL3</div></td>
	<td><div align='center'>$sum_percHL3</div></td>
	<td><div align='center'>$sum_HL4</div></td>
	<td><div align='center'>$sum_percHL4</div></td>
	<td><div align='center'>$sum_HL5</div></td>
	<td><div align='center'>$sum_percHL5</div></td>
	<td><div align='center'>$sum_BMIL1</div></td>
	<td><div align='center'>$sum_percBMIL1</div></td>
	<td><div align='center'>$sum_BMIL2</div></td>
	<td><div align='center'>$sum_percBMIL2</div></td>
	<td><div align='center'>$sum_BMIL3</div></td>
	<td><div align='center'>$sum_percBMIL3</div></td>
	<td><div align='center'>$sum_BMIL4</div></td>
	<td><div align='center'>$sum_percBMIL4</div></td>
	<td><div align='center'>$sum_BMIL5</div></td>
	<td><div align='center'>$sum_percBMIL5</div></td>
	<td><div align='center'>$sum_BMIL6</div></td>
	<td><div align='center'>$sum_percBMIL6</div></td>
	<td><div align='center'>$sum_kpi13</div></td>
	<td><div align='center'>$sum_perckpi13</div></td>
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
