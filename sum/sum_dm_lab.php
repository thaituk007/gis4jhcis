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
$str = retdaterangstr($_GET[str]);
$sto = retdaterangsto($_GET[str]);
$strx = retDatets($str);
$stox = retDatets($sto);	
$sql = "select villcode,
villname,
count(distinct pid) as sum,
sum(case when CH99 is not null then 1 else 0 end) as c1,
sum(case when CH25 is not null then 1 else 0 end) as c2,
sum(case when CH07 is not null then 1 else 0 end) as c3,
sum(case when CH14 is not null then 1 else 0 end) as c4,
sum(case when CH17 is not null then 1 else 0 end) as c5,
sum(case when CH04 is not null then 1 else 0 end) as c6,
sum(case when CH09 is not null then 1 else 0 end) as c7,
sum(case when Cha1 is not null then 1 else 0 end) as c8,
sum(case when Chc1 is not null then 1 else 0 end) as c9
 from
(SELECT
person.pcucodeperson,
person.pid,
person.fname,
concat(ctitle.titlename,person.fname ,'  ' ,person.lname) AS pname,
person.birth,
ROUND(DATEDIFF(now(),person.birth)/365.25) AS age,
village.villcode,
village.villname,
house.hno,
house.hcode,
house.xgis,
house.ygis,
group_concat(cdisease.codechronic) as codex,
group_concat(cdiseasechronic.groupname) as chronicx
FROM
personchronic
inner join person on person.pcucodeperson = personchronic.pcucodeperson AND person.pid = personchronic.pid
inner join cdisease on personchronic.chroniccode = cdisease.diseasecode
left join cdiseasechronic on cdiseasechronic.groupcode = cdisease.codechronic
inner join ctitle on person.prename = ctitle.titlecode
inner join house on person.hcode = house.hcode and person.pcucodeperson = house.pcucode
inner join village ON house.villcode = village.villcode and house.pcucode = village.pcucode
where ((person.dischargetype is null) or (person.dischargetype = '9')) AND SUBSTRING(house.villcode,7,2) <> '00' $wvill
group by person.pcucodeperson, person.pid
having codex like '%10%'
) as tmp_per
left join
(select
person.pid as pid1,
person.pcucodeperson as pcucodeperson1,
(select l1.datecheck from visitlabchcyhembmsse l1  where person.pid = l1.pid  and person.pcucodeperson=l1.pcucodeperson  and l1.labcode='CH99'  and (l1.datecheck  IS NOT NULL OR  left(l1.datecheck,4) != '0000') group by l1.pid,l1.pcucodeperson) as CH99,
(select l1.datecheck from visitlabchcyhembmsse l1  where person.pid = l1.pid  and person.pcucodeperson=l1.pcucodeperson  and l1.labcode='CH25'  and (l1.datecheck  IS NOT NULL OR  left(l1.datecheck,4) != '0000') group by l1.pid,l1.pcucodeperson) as CH25,
(select l1.datecheck from visitlabchcyhembmsse l1  where person.pid = l1.pid  and person.pcucodeperson=l1.pcucodeperson  and l1.labcode='CH07'  and (l1.datecheck  IS NOT NULL OR  left(l1.datecheck,4) != '0000') group by l1.pid,l1.pcucodeperson) as CH07,
(select l1.datecheck from visitlabchcyhembmsse l1  where person.pid = l1.pid  and person.pcucodeperson=l1.pcucodeperson  and l1.labcode='CH14'  and (l1.datecheck  IS NOT NULL OR  left(l1.datecheck,4) != '0000') group by l1.pid,l1.pcucodeperson) as CH14,
(select l1.datecheck from visitlabchcyhembmsse l1  where person.pid = l1.pid  and person.pcucodeperson=l1.pcucodeperson  and l1.labcode='CH17'  and (l1.datecheck  IS NOT NULL OR  left(l1.datecheck,4) != '0000') group by l1.pid,l1.pcucodeperson) as CH17,
(select l1.datecheck from visitlabchcyhembmsse l1  where person.pid = l1.pid  and person.pcucodeperson=l1.pcucodeperson  and l1.labcode='CH04'  and (l1.datecheck  IS NOT NULL OR  left(l1.datecheck,4) != '0000') group by l1.pid,l1.pcucodeperson) as CH04,
(select l1.datecheck from visitlabchcyhembmsse l1  where person.pid = l1.pid  and person.pcucodeperson=l1.pcucodeperson  and l1.labcode='CH09'  and (l1.datecheck  IS NOT NULL OR  left(l1.datecheck,4) != '0000') group by l1.pid,l1.pcucodeperson) as CH09,
(select l1.datecheck from visitlabchcyhembmsse l1  where person.pid = l1.pid  and person.pcucodeperson=l1.pcucodeperson  and l1.labcode='Cha1'  and (l1.datecheck  IS NOT NULL OR  left(l1.datecheck,4) != '0000') group by l1.pid,l1.pcucodeperson) as Cha1,
(select l1.datecheck from visitlabchcyhembmsse l1  where person.pid = l1.pid  and person.pcucodeperson=l1.pcucodeperson  and l1.labcode='Chc1'  and (l1.datecheck  IS NOT NULL OR  left(l1.datecheck,4) != '0000') group by l1.pid,l1.pcucodeperson) as Chc1
FROM
personchronic
inner join person on person.pcucodeperson = personchronic.pcucodeperson AND person.pid = personchronic.pid
inner join cdisease on personchronic.chroniccode = cdisease.diseasecode
inner join cdiseasechronic on cdiseasechronic.groupcode = cdisease.codechronic
inner Join visitlabchcyhembmsse ON person.pcucodeperson = visitlabchcyhembmsse.pcucodeperson AND visitlabchcyhembmsse.pid = person.pid
inner join clabchcyhembmsse ON visitlabchcyhembmsse.labcode = clabchcyhembmsse.labcode
inner join ctitle on person.prename = ctitle.titlecode
inner join house on person.hcode = house.hcode and person.pcucodeperson = house.pcucode
inner join village ON house.villcode = village.villcode and house.pcucode = village.pcucode
where  visitlabchcyhembmsse.datecheck between '$str' and '$sto'
group by person.pid,person.pcucodeperson) as tmp_lab
on tmp_per.pid = tmp_lab.pid1 and tmp_per.pcucodeperson = tmp_lab.pcucodeperson1
group by villcode
order by villcode";

$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>จำนวนผู้ป่วยเบาหวานที่ได้รับการตรวจ Lab ต่างๆ</b><br>';
$txt .= "<b> $mu </b></p><br><b>$hosp</b><table width='99%' border='0' cellspacing='1' cellpadding='1' class='table table-striped table-hover table-bordered'>
  <tr>
    <th width='5%' scope='col'><div align='center'>ลำดับ</div></th>
    <th width='12%' scope='col'><div align='center'>หมู่บ้าน</div></th>
	<th width='8%' scope='col'><div align='center'>หมู่ที่</div></th>
    <th width='8%' scope='col'><div align='center'>จำนวนผู้ป่วยเบาหวาน</div></th>
    <th width='7%' scope='col'><div align='center'>HbA1c</div></th>
	<th width='7%' scope='col'><div align='center'>ร้อยละ</div></th>
	<th width='7%' scope='col'><div align='center'>Triglyceride</div></th>
	<th width='7%' scope='col'><div align='center'>Total Cholesterol</div></th>
	<th width='7%' scope='col'><div align='center'>HDL Cholesterol</div></th>
	<th width='7%' scope='col'><div align='center'>LDL Cholesterol</div></th>
	<th width='7%' scope='col'><div align='center'>BUN</div></th>
	<th width='7%' scope='col'><div align='center'>Creatinine</div></th>
	<th width='7%' scope='col'><div align='center'>Urine Albumin</div></th>
	<th width='7%' scope='col'><div align='center'>Urine Creatinine</div></th>
  </tr>";
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
if($row[sum] == "0"){
	$percen = "0";
}else{
	$percen = ($row[c1])/($row[sum])*100;	
}
	$percent1 = number_format($percen, 2, '.', '');
	$sum_sum = $sum_sum+$row[sum];
	$sum_c1 = $sum_c1+$row[c1];
	$sum_c2 = $sum_c2+$row[c2];
	$sum_c3 = $sum_c3+$row[c3];
	$sum_c4 = $sum_c4+$row[c4];
	$sum_c5 = $sum_c5+$row[c5];
	$sum_c6 = $sum_c6+$row[c6];
	$sum_c7 = $sum_c7+$row[c7];
	$sum_c8 = $sum_c8+$row[c8];
	$sum_c9 = $sum_c9+$row[c9];
if($sum_sum == "0"){
	$sum_percen = "0";
}else{
	$sum_percen = $sum_c1/$sum_sum*100;	
}
	$sum_percent1 = number_format($sum_percen, 2, '.', '');
++$i;
	if(($i%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$i</div></td>
    <td>$row[villname]</td>
    <td><div align='center'>&nbsp;$moo</div></td>
    <td><div align='center'>$row[sum]</div></td>
	<td><div align='center'>$row[c1]</div></td>
	<td><div align='center'>$percent1</div></td>
	<td><div align='center'>$row[c2]</div></td>
	<td><div align='center'>$row[c3]</div></td>
	<td><div align='center'>$row[c4]</div></td>
	<td><div align='center'>$row[c5]</div></td>
	<td><div align='center'>$row[c6]</div></td>
	<td><div align='center'>$row[c7]</div></td>
	<td><div align='center'>$row[c8]</div></td>
	<td><div align='center'>$row[c9]</div></td>
  </tr>
  ";
}
$txt .= "<tr>
  <td>&nbsp;&nbsp;</td>
  <td><div align='center'>&nbsp;รวม</td>
  <td>&nbsp;&nbsp;</td>
  <td><div align='center'>$sum_sum</div></td>
  <td><div align='center'>$sum_c1</div></td>
  <td><div align='center'>$sum_percent1</div></td>
  <td><div align='center'>$sum_c2</div></td>
  <td><div align='center'>$sum_c3</div></td>
  <td><div align='center'>$sum_c4</div></td>
  <td><div align='center'>$sum_c5</div></td>
  <td><div align='center'>$sum_c6</div></td>
  <td><div align='center'>$sum_c7</div></td>
  <td><div align='center'>$sum_c8</div></td>
  <td><div align='center'>$sum_c9</div></td>
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
