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
	$wvill = " h.villcode='$villcode' and ";	
}
if($villcode == "00000000"){
	$mu = "ทุกหมู่บ้าน";
}else{
	$mu = getvillagename($villcode);	
}
$chk_old = $_GET[chk_old];
if($chk_old == "0"){
	$chksto = "";
}elseif($chk_old == "1"){
	$chksto = "having bwok <> 0";	
}elseif($chk_old == "2"){
	$chksto = "having bwok = 3";
}elseif($chk_old == "3"){
	$chksto = "having bwok = 1";
}else{
	$chksto = "having bwok = 0";
}
$str = retdaterangstr($_GET[str]);
$sto = retdaterangsto($_GET[str]);
$strx = retDatets($str);
$stox = retDatets($sto);
$live_type = $_GET[live_type];
if($live_type == '2'){$live_type_name = "ตามทะเบียนบ้าน(0,1,2)";}elseif($live_type == '1'){$live_type_name = "ที่อาศัยอยู่จริง (0,1,3)";}else{$live_type_name = "ทั้งหมดในเขตรับผิดชอบ(0,1,2,3)";}	
if($live_type == '2'){$live_type2 = "and p.typelive in ('0','1','2')";}elseif($live_type == '1'){$live_type2 = "and p.typelive in ('0','1','3')";}else{$live_type2 = "and p.typelive in ('0','1','2','3')";}
$sql = "SELECT 
t1.pcucode,
t1.pcucodeperson,
t1.pid,
t1.pname,
t1.birth,
t1.agemonth,
t1.hno, 
t1.mumoi,
t1.villname,
t1.villcode,
t2.visitdate ,
t2.tall,
t2.weight,
t2.bw_level,
t2.bmi_level,
t2.heigth_level,
CASE when t2.heigth_level in ('3','4','5') or t2.bmi_level=3 THEN 3 when t2.bmi_level is null then 0 else 1 end as bwok
FROM
(SELECT 
h.pcucode,
p.pcucodeperson,
p.pid,
concat(ctitle.titlename,p.fname,'  ',p.lname) as pname,
p.birth,
FLOOR((TO_DAYS('$sto')-TO_DAYS(p.birth))/30.44) as agemonth,
h.hno, 
RIGHT(h.villcode,2) AS mumoi,
villname,h.villcode
FROM person p
LEFT JOIN ctitle on ctitle.titlecode = p.prename
LEFT JOIN house h ON h.hcode=p.hcode and h.pcucodeperson=p.pcucodeperson
LEFT JOIN village v ON v.villcode=h.villcode and v.pcucode=h.pcucode
WHERE $wvill p.BIRTH<'$sto' AND RIGHT(h.villcode,2)<>'00' $live_type2 AND CONCAT(p.pid,p.pcucodeperson) NOT IN (SELECT CONCAT(persondeath.pid,persondeath.pcucodeperson) FROM persondeath)
GROUP BY p.pcucodeperson,p.pid
HAVING agemonth <72
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
$chksto
order by t1.villcode,t1.agemonth";

$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>รายงานภาวะโภชนาการเด็กอายุ 0 - 72 เดือน</b><br>';
$txt .= "<b>ข้อมูลระหว่างวันที่ $strx ถึง $stox $mu </b></p><br><br><b>$hosp</b><table width='98%' border='0' cellspacing='1' cellpadding='1' class='table table-striped table-hover table-bordered'>
  <tr>
    <th width='4%' scope='col'><div align='center'>ลำดับ</div></th>
    <th width='13%' scope='col'><div align='center'>ชื่อ - สกุล</div></th>
	<th width='8%' scope='col'><div align='center'>ว/ด/ป เกิด</div></th>
	<th width='5%' scope='col'><div align='center'>อายุ</div></th>
    <th width='7%' scope='col'><div align='center'>บ้านเลขที่</div></th>
    <th width='4%' scope='col'><div align='center'>หมู่ที่</div></th>
	<th width='4%' scope='col'><div align='center'>วันที่ตรวจ</div></th>
	<th width='6%' scope='col'><div align='center'>น้ำหนัก</div></th>
	<th width='6%' scope='col'><div align='center'>ส่วนสูง</div></th>
	<th width='6%' scope='col'><div align='center'>อายุ/น้ำหนัก</div></th>
	<th width='6%' scope='col'><div align='center'>อายุ/ส่วนสูง</div></th>
	<th width='6%' scope='col'><div align='center'>น้ำหนัก/ส่วนสูง</div></th>
  </tr>";
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$birth = retDatets($row[birth]);
if($row[visitdate] == ""){$visitdate = '--/--/----';}else{$visitdate = retDatets($row[visitdate]);}
if($row[bw_level] == "1"){$aw = "น้ำหนักต่ำมาก";}else if($row[bw_level] == "2"){$aw = "น้ำหนักต่ำ";}else if($row[bw_level] == "3"){$aw = "น้ำหนักปกติ";}else if($row[bw_level] == "4"){$aw = "น้ำหนักสูง";}else if($row[bw_level] == "5"){$aw = "น้ำหนักสูงมาก";}else{$aw = "---";}
if($row[heigth_level] == "1"){$ah = "เตี้ย";}else if($row[heigth_level] == "2"){$ah = "ค่อนข้างเตี้ย";}else if($row[heigth_level] == "3"){$ah = "ปกติ";}else if($row[heigth_level] == "4"){$ah = "ค่อนข้างสูง";}else if($row[heigth_level] == "5"){$ah = "สูงเกินเกณฑ์";}else{$ah = "---";}
if($row[bmi_level] == "1"){$wh = "ผอม";}else if($row[bmi_level] == "2"){$wh = "ค่อนข้างผอม";}else if($row[bmi_level] == "3"){$wh = "สมส่วน";}else if($row[bmi_level] == "4"){$wh = "ค่อนข้างอ้วน";}else if($row[bmi_level] == "5"){$wh = "อ้วน";}else if($row[bmi_level] == "6"){$wh = "อ้วนมาก";}else{$wh = "---";}
++$i;
	if(($i%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$i</div></td>
    <td>$row[pname]</td>
	<td><div align='center'>&nbsp;$birth</div></td>
	<td><div align='center'>&nbsp;$row[agemonth]</div></td>
    <td><div align='center'>&nbsp;$row[hno]</div></td>
    <td><div align='center'>&nbsp;$moo</div></td>
    <td><div align='center'>&nbsp;$visitdate</div></td>
	<td><div align='center'>&nbsp;$row[weight]</div></td>
	<td><div align='center'>&nbsp;$row[tall]</div></td>
    <td><div align='center'>&nbsp;$aw</div></td>
    <td><div align='center'>&nbsp;$ah</div></td>
	<td><div align='center'>&nbsp;$wh</div></td>
  </tr>";
}
$txt .= "</table><br>";  
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
