<?php
header("Content-Type: application/vnd.ms-excel");
header('Content-Disposition: attachment; filename="ncdrisk.xls"');#ชื่อไฟล์

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $titleweb; ?></title>
<link href="css/style.css" rel="stylesheet" type="text/css" />
</head>

<body>

<?php 
include("../includes/conndb.php"); 
include("../includes/config.inc.php"); 

$strage = $_GET[strage];
$village = $_GET[village];
if($village == '00000000'){$ect2 = "";}else{$ect2 = " h.villcode = '$village' AND ";}
$sql = "CREATE TEMPORARY TABLE tmp_ncd_risk
SELECT
CONCAT(c.titlename,p.fname,'  ',p.lname) AS pname,
p.birth,
FLOOR((TO_DAYS(NOW())-TO_DAYS(p.birth))/365.25) AS age,
h.hno,
v.villno,
v.villcode,
p.pid,pidvola,h.xgis,h.ygis,if(instr(hno,'/')>0,substring(hno,1,instr(hno,'/')-1),hno) as f,h.usernamedoc,0 AS fbs,0 AS sys,0 AS dias,00.00 AS a1c,0 AS ball,'          ' AS dserv,'    ' AS ds
FROM
house AS h
Inner Join person AS p ON h.pcucode = p.pcucodeperson AND h.hcode = p.hcode
Inner Join village AS v ON h.pcucode = v.pcucode AND h.villcode = v.villcode
Inner Join ctitle AS c ON p.prename = c.titlecode
WHERE
$ect2
v.villno <>  '0' AND
p.dischargetype =  '9' AND
FLOOR((TO_DAYS(NOW())-TO_DAYS(p.birth))/365.25) >= $strage AND
p.typelive IN  ('1', '3')
ORDER BY
h.villcode,length(f),f,h.hno,age";
mysql_query($sql);


$sql = "CREATE TEMPORARY TABLE tmp_ncd_ball7 
SELECT p.pcucodeperson AS pcu,p.pid AS pid
FROM
house AS h
Inner Join person AS p ON h.pcucode = p.pcucodeperson AND h.hcode = p.hcode
Inner Join personchronic AS pc ON p.pcucodeperson = pc.pcucodeperson AND p.pid = pc.pid
Inner Join cdisease AS d ON pc.chroniccode = d.diseasecode
Inner Join cdiseasechronic AS dc ON d.codechronic = dc.groupcode
WHERE
p.dischargetype =  '9' AND
p.typelive IN ('1', '3') AND
SUBSTRING(h.villcode,7,2) <>  '00' AND
pc.typedischart NOT IN  ('01', '02', '07') AND
dc.groupcode IN  ('03', '07', '09', '13')
GROUP BY dc.groupcode,p.pid";
mysql_query($sql);

$sql = "UPDATE tmp_ncd_risk AS r, personchronic AS p SET r.ds='DM'
WHERE r.pid = p.pid AND
LEFT(p.chroniccode,1) IN('E')";
mysql_query($sql);

$sql = "UPDATE tmp_ncd_risk AS r, personchronic AS p SET r.ds='DMHT'
WHERE r.pid = p.pid AND
LEFT(p.chroniccode,1) IN('I') AND
r.ds = 'DM'";
mysql_query($sql);

$sql = "UPDATE tmp_ncd_risk AS r, personchronic AS p SET r.ds='HT'
WHERE r.pid = p.pid AND
LEFT(p.chroniccode,1) IN('I') AND
r.ds = '    '";
mysql_query($sql);

$sql = "UPDATE tmp_ncd_risk, tmp_ncd_ball7 SET ball=7
WHERE tmp_ncd_risk.pid = tmp_ncd_ball7.pid";
mysql_query($sql);

$sql = "UPDATE tmp_ncd_risk AS r, personchronic AS p SET ball=6
WHERE r.pid = p.pid AND
LEFT(p.chroniccode,1) IN('I','E') AND
r.ball = 0;";
mysql_query($sql);

$sql = "UPDATE tmp_ncd_risk AS r, ncd_person_ncd_screen AS n SET r.fbs=n.bsl
WHERE r.pid = n.pid AND
n.bsl IS NOT NULL";
mysql_query($sql);

$sql = "UPDATE tmp_ncd_risk AS r, ncd_person_ncd_screen AS n SET r.sys=n.hbp_s2,r.dias=n.hbp_d2
WHERE r.pid = n.pid AND
n.hbp_s2 IS NOT NULL";
mysql_query($sql);

$sql = "UPDATE tmp_ncd_risk AS r, ncd_person_ncd_screen AS n SET r.sys=n.hbp_s1,r.dias=n.hbp_d1
WHERE r.pid = n.pid AND
r.sys = 0 AND
n.hbp_s1 IS NOT NULL";
mysql_query($sql);

$sql = "UPDATE tmp_ncd_risk AS r, visitlabchcyhembmsse AS l SET r.a1c=l.labresultdigit
WHERE r.pid = l.pid AND
l.labcode = 'CH99' AND
r.ball = 6";
mysql_query($sql);

$sql = "CREATE TEMPORARY TABLE tmp_sugarblood
SELECT
visit.pid,
max(visit.visitdate) as dateserv
FROM
visitlabsugarblood
Inner Join visit ON visit.visitno = visitlabsugarblood.visitno
where
TO_DAYS(NOW())-TO_DAYS(visit.visitdate) < 356
GROUP BY visit.pid
ORDER BY pid,visitdate asc";
mysql_query($sql);

$sql = "CREATE TEMPORARY TABLE tmp_ncd_sugarblood
SELECT
visit.pid,
visit.visitdate,
visitlabsugarblood.sugarnumdigit
FROM
visitlabsugarblood
Inner Join visit ON visit.visitno = visitlabsugarblood.visitno 
Inner Join tmp_sugarblood ON visit.pid = tmp_sugarblood.pid AND visit.visitdate = tmp_sugarblood.dateserv
ORDER BY pid,visitdate asc";
mysql_query($sql);

$sql = "UPDATE tmp_ncd_risk AS r, tmp_ncd_sugarblood AS l SET r.fbs=l.sugarnumdigit
WHERE r.pid = l.pid AND
r.ball = 6";
mysql_query($sql);

$sql = "CREATE TEMPORARY TABLE tmp_ncd_bp0
SELECT
visit.pid,
max(visit.visitdate) as dateserv
from visit
Inner Join tmp_ncd_risk ON tmp_ncd_risk.pid = visit.pid 
where 
TO_DAYS(NOW())-TO_DAYS(visit.visitdate) < 356 AND
length(pressure) = 5
GROUP BY
visit.pid";
mysql_query($sql);

$sql = "CREATE TEMPORARY TABLE tmp_ncd_bp
select 
visit.pid,
left(pressure,3) as sys,
right(pressure,2) as dias
from visit
Inner Join tmp_ncd_bp0 ON tmp_ncd_bp0.pid = visit.pid AND tmp_ncd_bp0.dateserv=visit.visitdate";
mysql_query($sql);

$sql = "UPDATE tmp_ncd_risk AS r, tmp_ncd_bp AS l SET r.sys=l.sys,r.dias=l.dias
WHERE r.pid = l.pid AND
r.ball = 6";
mysql_query($sql);

$sql = "UPDATE tmp_ncd_risk set ball=3
where
a1c < 7 AND
sys < 140 AND
dias < 90 AND
fbs < 126 AND
ball = 6";
mysql_query($sql);

$sql = "UPDATE tmp_ncd_risk set ball=4
where
a1c < 7  AND
ball = 6 AND
((sys BETWEEN 140 and 159) OR
(dias BETWEEN 90 and 99) OR
(fbs BETWEEN 126 and 154))";
mysql_query($sql);

$sql = "UPDATE tmp_ncd_risk set ball=5
where
ball = 6 AND
((a1c BETWEEN 7 and 7.9)  OR
(sys BETWEEN 140 and 159) OR
(dias BETWEEN 90 and 99) OR
(fbs BETWEEN 126 and 154))";
mysql_query($sql);

$sql = "UPDATE tmp_ncd_risk set ball=3
where
ball = 0 AND
((sys > 120) OR
(dias > 80) OR
(fbs >= 100))";
mysql_query($sql);

$sql = "UPDATE tmp_ncd_risk set ball=2
where
ball = 0 AND
((sys BETWEEN 120 and 139) OR
(dias BETWEEN 80 and 89)) AND
fbs < 100";
mysql_query($sql);

$sql = "UPDATE tmp_ncd_risk set ball=1
where
ball = 0 AND
sys < 120 AND
dias <80  AND
fbs < 100";
mysql_query($sql);

$sql = "UPDATE tmp_ncd_risk set ball=0
where
sys = 0 AND
fbs = 0";
mysql_query($sql);


$sql = "select * from tmp_ncd_risk order by ball DESC, villno ASC";
$result = mysql_query($sql);
$num = mysql_num_rows($result);
$txt = "<p align='center'>จัดกลุ่มสื่อสารต่อการดูแลรักษา โรคเบาหวาน โรคความดันโลหิตสูง (จราจรชีวิต 7 สี)</p>
<p align='center'>ข้อมูล ณ วันที่ ".LongThaiDate($todays)."</p>
<table width='99%' border='1' align='center' >
  <tr>
    <th width='6%' scope='col'>ลำดับ</th>
    <th width='21%' scope='col'>ชื่อ - สกุล</th>
    <th width='7%' scope='col'>อายุ</th>
    <th width='11%' scope='col'>บ้านเลขที่</th>
    <th width='9%' scope='col'>หมู่ที่</th>
    <th width='13%' scope='col'>DM or HT</th>
    <th width='16%' scope='col'>จัดกลุ่ม</th>
    <th width='17%' scope='col'>นสค.</th>
  </tr>";
while($r=mysql_fetch_array($result)) {
	++$i;
	if($r[ball] == 7){$rb = 'ป่วยรุนแรงโรคแทรกซ้อน';}
	else if($r[ball] == 6){$rb = 'ป่วยรุนแรง';}
	else if($r[ball] == 5){$rb = 'ป่วยปานกลาง';}
	else if($r[ball] == 4){$rb = 'ป่วยอ่อน';}
	else if($r[ball] == 3){$rb = 'เสี่ยงสูง';}
	else if($r[ball] == 2){$rb = 'ดูแลตัวเองได้';}
	else $rb = 'ปกติ';
$txt .= "<tr>
    <td><div align='center'>$i</div></td>
    <td>&nbsp;$r[pname]</td>
    <td><div align='center'>$r[age]</div></td>
    <td><div align='center'>&nbsp;$r[hno]</div></td>
    <td><div align='center'>&nbsp;$r[villno]</div></td>
    <td><div align='center'>$r[ds]</div></td>
    <td><div align='center'>$rb</div></td>
    <td>&nbsp;$r[usernamedoc]</td>
  </tr>";

}
$txt .= "</table>";

$sql = "drop table tmp_ncd_ball7,tmp_ncd_risk,tmp_sugarblood,tmp_ncd_sugarblood,tmp_ncd_bp0,tmp_ncd_bp";
mysql_query($sql);

echo $txt;
?>
</body>
</html>