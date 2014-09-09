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
$sql = "select *,
case when CH99 is not null then 1 else 0 end as chk
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
order by villcode, fname";

$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>รายชื่อผู้ป่วยเบาหวานที่ได้รับการตรวจ Lab ต่างๆ</b><br>';
$txt .= "<b> $mu </b></p><br><b>$hosp</b><table width='99%' border='0' cellspacing='1' cellpadding='1' class='table table-striped table-hover table-bordered'>
  <tr>
    <th width='4%' scope='col'><div align='center'>ลำดับ</div></th>
    <th width='13%' scope='col'><div align='center'>ชื่อ - สกุล</div></th>
	<th width='5%' scope='col'><div align='center'>อายุ</div></th>
    <th width='5%' scope='col'><div align='center'>บ้านเลขที่</div></th>
    <th width='4%' scope='col'><div align='center'>หมู่ที่</div></th>
    <th width='12%' scope='col'><div align='center'>โรคประจำตัว</div></th>
	<th width='7%' scope='col'><div align='center'>HbA1c</div></th>
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
	if($row[CH99] == ''){$ch99 = '';}else{$ch99 = retDatets($row[CH99]);}
	if($row[CH25] == ''){$ch25 = '';}else{$ch25 = retDatets($row[CH25]);}
	if($row[CH07] == ''){$ch07 = '';}else{$ch07 = retDatets($row[CH07]);}
	if($row[CH14] == ''){$ch14 = '';}else{$ch14 = retDatets($row[CH14]);}
	if($row[CH17] == ''){$ch17 = '';}else{$ch17 = retDatets($row[CH17]);}
	if($row[CH04] == ''){$ch04 = '';}else{$ch04 = retDatets($row[CH04]);}
	if($row[CH09] == ''){$ch09 = '';}else{$ch09 = retDatets($row[CH09]);}
	if($row[Cha1] == ''){$cha1 = '';}else{$cha1 = retDatets($row[Cha1]);}
	if($row[Chc1] == ''){$chc1 = '';}else{$chc1 = retDatets($row[Chc1]);}
++$i;
	if(($i%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$i</div></td>
    <td>$title$row[pname]</td>
	<td>&nbsp;$row[age]</td>
    <td>&nbsp;$row[hno]</td>
    <td>&nbsp;$moo</td>
	<td>&nbsp;$row[chronicx]</td>
    <td><div align='center'>$ch99</div></td>
	<td><div align='center'>$ch25</div></td>
	<td><div align='center'>$ch07</div></td>
	<td><div align='center'>$ch14</div></td>
	<td><div align='center'>$ch17</div></td>
	<td><div align='center'>$ch04</div></td>
	<td><div align='center'>$ch09</div></td>
	<td><div align='center'>$cha1</div></td>
	<td><div align='center'>$chc1</div></td>
  </tr>";
}
$txt .= "</table><p div align='right' class='text-danger'>ข้อมูลระหว่างวันที่  $strx ถึง $stox </p></div><br>";  
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
