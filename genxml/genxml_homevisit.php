<?php 
header("Content-type: text/xml");
include("../includes/conndb.php"); 
include("../includes/config.inc.php"); 
$villcode = $_GET[village];
if($villcode == "00000000"){
	$wvill = "";
}else{
	$wvill = " AND villcode='$villcode' ";	
}
$str = retdaterangstr($_GET[str]);
$sto = retdaterangsto($_GET[str]);
$strx = retDatets($str);
$stox = retDatets($sto);
//สร้างตารางชั่วคราวหลังคาเรือนทั้งหมด
$sql = "CREATE TEMPORARY TABLE tmpdata
		SELECT hcode,villcode,hno,pid,ygis,xgis,if(instr(hno,'/')>0,substring(hno,1,instr(hno,'/')-1),hno) as f,0 AS pa,0 AS pv 
		FROM house 
		WHERE  SUBSTRING(villcode,7,2) <> '00' $wvill
		ORDER BY villcode,length(f),f,hno";
if(mysql_query($sql)){
//ตารางคนในแต่ละบ้าน
$sql = "CREATE TEMPORARY TABLE tmpa
		SELECT h.hcode AS thcode ,COUNT(p.fname) AS tpa
		FROM
			house AS h
			Inner Join person AS p ON h.pcucode = p.pcucodeperson AND h.hcode = p.hcode
		WHERE ((p.dischargetype is null) or (p.dischargetype = '9')) AND 
			SUBSTRING(h.villcode,7,2) <> '00' 
		GROUP BY h.hcode";
mysql_query($sql);
//เยี่ยมบ้าน
$sql = "CREATE TEMPORARY TABLE tmpv
		SELECT
		h.hcode  AS thcode , COUNT(DISTINCT p.pid) AS tpv
		FROM
		house AS h
		Inner Join person AS p ON h.pcucode = p.pcucodeperson AND h.hcode = p.hcode
		Inner Join visit AS v ON p.pcucodeperson = v.pcucode AND p.pid = v.pid
		Inner Join visithomehealthindividual AS vh ON v.visitno = vh.visitno AND v.pcucode = vh.pcucode
		WHERE
		((p.dischargetype is null) or (p.dischargetype = '9')) AND 
		SUBSTRING(h.villcode,7,2) <> '00' AND
		v.visitdate BETWEEN  '$str' AND '$sto' and (v.flagservice <'04' OR v.flagservice is null OR length(trim(v.flagservice))=0 )
		GROUP BY h.hcode";
mysql_query($sql);
}
//รวมข้อมูล
$sql = "UPDATE tmpdata,tmpa,tmpv SET tmpdata.pa = tmpa.tpa,tmpdata.pv = tmpv.tpv
		WHERE tmpdata.hcode = tmpa.thcode AND
		tmpdata.hcode = tmpv.thcode";
mysql_query($sql);
$sql = "DROP TABLE tmpa";
mysql_query($sql);

$sql = "DROP TABLE tmpv";
mysql_query($sql);

$sql = "SELECT * FROM tmpdata";
$result = mysql_query($sql);
$xml = '<markers>';
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$sick = retDatets($row[sickdatestart]);
	$phname = getPersonName($row[pid]);
  $xml .= '<marker ';
  $xml .= 'hcode="'.$row[hcode].'" ';
  $xml .= 'hono="'.$row[hno].'" ';
  $xml .= 'moo="'.$moo.'" ';
  $xml .= 'vill="'.$vill.'" ';
  $xml .= 'pname="'.$phname.'" ';
  $xml .= 'pa="'.$row[pa].'" ';
  $xml .= 'pv="'.$row[pv].'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= '/>';
}
$xml .= '</markers>';
$sql = "DROP TABLE tmdata";
mysql_query($sql);
echo $xml;
?>

