<?php
session_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $titleweb; ?></title>
<link href="css/style.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="js/jquery-1.4.2.min.js"></script>
</head>

<body>
<?php 
if($_SESSION[username]){
include("includes/conndb.php"); 
include("includes/config.inc.php");
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
}elseif($village == "xxx"){
	$wvill = " AND right(h.villcode,2)='00'";	
}else{
	$wvill = " AND h.villcode='$village'";	
}
if($village == "00000000"){
	$mu = "ทุกหมู่บ้าน";
}else{
	$mu = getvillagename($village);	
}
$str = retDate($_GET[str]);
$sto = retDate($_GET[sto]);
$ds = $_GET[ds];
	if($ds == '00'){$ect = "";}else{$ect = " dc.group506code = '$ds' AND ";}	
$sql = "SELECT p.pid, p.idcard, CONCAT(t.titlename,p.fname,' ',p.lname) AS pname,getageyearnum(p.birth,vd.sickdatestart) as age, d.diseasecode, h.hno,h.villcode,co.occupaname, vd.sickdatestart,dc.group506name
							FROM
							house AS h
							Inner Join person AS p ON h.pcucode = p.pcucodeperson AND h.hcode = p.hcode
							Inner Join visit AS v ON p.pcucodeperson = v.pcucodeperson AND p.pid = v.pid
							Inner Join visitdiag506address AS vd ON v.pcucode = vd.pcucode AND v.visitno = vd.visitno
							Inner Join cdisease AS d ON vd.diagcode = d.diseasecode
							Inner Join cdisease506 AS dc ON d.code506 = dc.group506code
							left Join ctitle AS t ON p.prename = t.titlecode
							left join coccupa co on p.occupa = co.occupacode
							WHERE $ect vd.sickdatestart BETWEEN  '$str' AND '$sto' and dc.group506code in ('26','27','66') $wvill
							ORDER BY vd.sickdatestart";

$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>รายชื่อผู้ป่วยไข้เลือดออก <br>';
$txt .= "ข้อมูลระหว่างวันที่ $_GET[str] ถึง $_GET[sto]  $mu </b></p><br><b>$hosp</b><table width='99%' border='0' cellspacing='1' cellpadding='1' class='tbhl'>
  <tr>
    <th width='4%' scope='col'>ลำดับ</th>
	<th width='10%' scope='col'>เลขบัตรประชาชน</th>
    <th width='10%' scope='col'>ชื่อ - สกุล</th>
	<th width='5%' scope='col'>อายุ</th>
    <th width='6%' scope='col'>บ้านเลขที่</th>
    <th width='4%' scope='col'>หมู่ที่</th>
    <th width='15%' scope='col'>อาชีพ</th>
	<th width='9%' scope='col'>วันที่ป่วย</th>
	<th width='9%' scope='col'>รหัส ICD10</th>
	<th width='20%' scope='col'>โรค 506</th>
  </tr>";
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$title = getTitle($row[prename]);
	if($row[sickdatestart] == ""){$sick = "";}else{$sick = retDatets($row[sickdatestart]);}
++$i;
	if(($i%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$i</div></td>
	<td><div align='center'>$row[idcard]</div></td>
    <td>$row[pname]</td>
	<td><div align='center'>$row[age]</div></td>
    <td><div align='center'>$row[hno]</div></td>
    <td><div align='center'>$moo</div></td>
    <td>$row[occupaname]</td>
	<td><div align='center'>$sick</div></td>
	<td>$row[para]&nbsp;&nbsp;$row[diseasecode]</td>
	<td>$row[group506name]</td>
  </tr>";
}
$txt .= "</table><br>";  
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
