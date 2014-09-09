<?php 
header("Content-type: text/xml");
include("../includes/conndb.php"); 
include("../includes/config.inc.php"); 
$villcode = $_GET[village];
if($villcode == "00000000"){
	$wvill = "";
}else{
	$wvill = " AND h.villcode='$villcode' ";	
}
$sugar = $_GET[sugar];
$str = retdaterangstr($_GET[str]);
$sto = retdaterangsto($_GET[str]);
$strx = retDatets($str);
$stox = retDatets($sto);
//สร้างตารางชั่วคราว จาก visitsugarblood
$sql = "CREATE TEMPORARY TABLE tmpdata
		SELECT v.pid,vs.sugarnumdigit AS sugar,v.visitdate
		FROM visitlabsugarblood AS vs
		Inner Join visit AS v ON v.pcucode = vs.pcucode AND v.visitno = vs.visitno
		WHERE
		v.visitdate BETWEEN  '$str' AND '$sto' and (v.flagservice <'04' OR v.flagservice is null OR length(trim(v.flagservice))=0 )";
if(mysql_query($sql)){
//เพิ่มเข้าไปจากตาราง ncd_person_ncd_screen
$sql = "INSERT INTO tmpdata (pid,sugar,visitdate)
		SELECT n.pid,n.bsl,n.screen_date
		FROM ncd_person_ncd_screen AS n
		WHERE n.screen_date BETWEEN  '$str' AND '$sto'";
mysql_query($sql);
}

$sql = "SELECT
		p.prename,CONCAT(p.fname,' ',p.lname) AS pname,h.hno,h.villcode,h.xgis,h.ygis,d.sugar,MAX(d.visitdate) AS vsd
		FROM
		house AS h
		Inner Join person AS p ON h.pcucode = p.pcucodeperson AND h.hcode = p.hcode
		Inner Join tmpdata AS d ON p.pid = d.pid
		WHERE
		SUBSTRING(h.villcode,7,2) <>  '00' AND
		((p.dischargetype is null) or (p.dischargetype = '9')) AND
		d.sugar > $sugar $wvill
		GROUP BY p.pid
		ORDER BY h.villcode,h.hno";
$result = mysql_query($sql);
$xml = '<markers>';
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$vsd = retDatets($row[vsd]);
	$title = getTitle($row[prename]);
	$pname = $title.$row[pname];
  $xml .= '<marker ';
  $xml .= 'hono="'.$row[hno].'" ';
  $xml .= 'moo="'.$moo.'" ';
  $xml .= 'vill="'.$vill.'" ';
  $xml .= 'pname="'.$pname.'" ';
  $xml .= 'sugar="'.$row[sugar].'" ';
  $xml .= 'vsd="'.$vsd.'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= '/>';
}
$xml .= '</markers>';
$sql = "DROP TABLE tmdata";
mysql_query($sql);
echo $xml;
?>

