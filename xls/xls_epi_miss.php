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
$age = $_GET[age];
$ect0 = "''";
if(strpos($age,",",0) > 0){
	$listage = explode(',',$age);
	foreach ($listage as $a){
		if(strpos($a,"-",0) > 0){
			list($str,$end) = split("-",$a,2);
			for($i = $str; $i <= $end; $i++){
				$ect0 .= ",'".$i."'";
			}
		}else{
			$ect0 .= ",'".$a."'";
		}
	}
}else{
		if(strpos($age,"-",0) > 0){
			list($str,$end) = split("-",$age,2);
			for($i = $str; $i <= $end; $i++){
				$ect0 .= ",'".$i."'";
			}
		}else{
			$ect0 .= ",'".$age."'";
		}
}
$vaccine = $_GET[vaccine];
if($vaccine == ''){$ect1 = "";}else{$ect1 = " visitepi.vaccinecode = '$vaccine' ";}
$village = $_GET[village];
if($village == '00000000'){$ect2 = "";}else{$ect2 = " villcode = '$village' AND ";}	
$sql = "select
pcu,
pid,
pname,
birth,
age,
hno,
villcode,
villname,
vaccinecode,
drugname,
dateepi,
xgis,
ygis
FROM
(SELECT
person.pcucodeperson as pcu,
person.pid as pid,
concat(ctitle.titlename,person.fname,' ',person.lname) AS pname,
person.fname,
person.birth as birth,
round(DATEDIFF(now(),person.birth) /30) AS age,
house.hno as hno,
house.villcode as villcode,
CONVERT(village.villname using utf8) AS villname,
house.xgis,
house.ygis
FROM
house
Inner Join person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
Inner Join village ON village.pcucode = house.pcucode AND village.villcode = house.villcode
Inner Join ctitle ON person.prename = ctitle.titlecode
where right(house.villcode,2) <> '00' and ((person.dischargetype is null) or (person.dischargetype = '9')) and  round(DATEDIFF(now(),person.birth) /30)  IN($ect0)
order by age) as per_epi
left Join (SELECT
person.pcucodeperson as pcu1,
person.pid as pid1,
visitepi.vaccinecode,
cdrug.drugname,
visitepi.dateepi
FROM
house
Inner Join person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
Inner Join village ON village.pcucode = house.pcucode AND village.villcode = house.villcode
Inner Join visitepi ON person.pcucodeperson = visitepi.pcucodeperson AND person.pid = visitepi.pid
Inner Join ctitle ON person.prename = ctitle.titlecode
Inner Join cdrug ON visitepi.vaccinecode = cdrug.drugcode
where right(house.villcode,2) <> '00' and ((person.dischargetype is null) or (person.dischargetype = '9')) and  round(DATEDIFF(now(),person.birth) /30)  IN($ect0) and $ect1
) as visit_epi ON per_epi.pcu = visit_epi.pcu1 AND per_epi.pid = visit_epi.pid1
where $ect2 pid1 is null
order by right(villcode,2),fname";

$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>กลุ่มเด็กอายุที่ไม่ได้รับวัคซีนตามที่ระบุ</b></p>';
$txt .= "<table width='95%' border='0' cellspacing='1' cellpadding='1' class='tbhl'>
  <tr>
    <th width='8%' scope='col'>ลำดับ</th>
    <th width='22%' scope='col'>ชื่อ - สกุล</th>
	<th width='15%' scope='col'>ว/ด/ป เกิด</th>
	<th width='8%' scope='col'>อายุ(เดือน)</th>
    <th width='8%' scope='col'>บ้านเลขที่</th>
    <th width='8%' scope='col'>หมู่ที่</th>
    <th width='30%' scope='col'>ไม่ได้รับวัคซีน</th>
  </tr>";
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$bod = retDatets($row[birth]);
	$title = getTitle($row[prename]);
++$x;
	if(($x%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$x</div></td>
    <td>$title$row[pname]</td>
    <td>&nbsp;$bod</td>
    <td>&nbsp;$row[age]</td>
	<td>&nbsp;$row[hno]</td>
	<td>&nbsp;$moo</td>
    <td>&nbsp;$vaccine</td>
  </tr>";
}
$txt .= "</table>";  
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
