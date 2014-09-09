<?php 
header("Content-type: text/xml");
include("../includes/conndb.php"); 
include("../includes/config.inc.php");
$op = $_GET['app_type'];
if($op === 'pregtest'){
	pregtest();
}else if($op === 'epi'){
    epi();
}else if($op === 'anc'){
    anc();
}else if($op === 'fp'){
    fp();
}


function pregtest(){ //function นัด pregtest 
$village = $_GET[village];
if($village == "00000000"){
	$wvill = "";
}else{
	$wvill = " AND house.villcode='$village' ";	
}
if($village == "00000000"){
	$mu = "ทุกหมู่บ้าน";
}else{
	$mu = getvillagename($village);	
}
$str = retdaterangstr($_GET[str]);
$sto = retdaterangsto($_GET[str]);
$strx = retDatets($str);
$stox = retDatets($sto);
$sql = "SELECT
person.pcucodeperson,
person.pid,
person.idcard,
person.fname,
concat(ctitle.titlename, person.fname , '  ' , person.lname) AS pname,
person.birth,
ROUND(DATEDIFF(now(),person.birth)/365.25) AS age,
house.villcode,
house.hno,
house.hcode,
house.xgis,
house.ygis,
visit.visitdate,
visitfp.pregtest,
visitfp.pregtestresult,
visit.username
FROM
house
INNER JOIN person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
LEFT JOIN ctitle on ctitle.titlecode = person.prename
INNER JOIN visit ON person.pcucodeperson = visit.pcucodeperson AND person.pid = visit.pid
INNER JOIN visitfp ON visit.pcucodeperson = visitfp.pcucodeperson AND visit.pid = visitfp.pid AND visit.visitdate = visitfp.datefp
where visitfp.pregtest = '17' and visit.visitdate between '$str' and '$sto' $wvill
order by visit.visitdate,person.fname";
$result = mysql_query($sql);
$xml = '<markers>';
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$userv = getusername($row[username]);
	if($row[pregtestresult] == "0"){$pregtestname = "ไม่ตั้งครรภ์";}elseif($row[pregtestresult] == "1"){$pregtestname = "ตั้งครรภ์";}elseif($row[pregtestresult] == "3"){$pregtestname = "แปลผลไม่ได้";}else{$pregtestname = "";}
	if($row[visitdate] == ""){$appsick = "";}else{$appsick = retDatets($row[visitdate]);}	
  $xml .= '<marker ';
  $xml .= 'pid="'.$row[pid].'" ';
  $xml .= 'pname="'.$row[pname].'" ';
  $xml .= 'age="'.$row[age].'" ';
  $xml .= 'hno="'.$row[hno].'" ';
  $xml .= 'moo="'.$moo.'" ';
  $xml .= 'sick="'.$appsick.'" ';
  $xml .= 'labresult="'.$pregtestname.'" ';
  $xml .= 'userv="'.$userv.'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= '/>';
}
$xml .= '</markers>';
echo $xml;
}
?>

