<?php 
header("Content-type: text/xml");
include("../includes/conndb.php"); 
include("../includes/config.inc.php"); 
$opid = $_GET[pid];
$ohid = $_GET[hid];
$villcode = $_GET[villcode];
	$sql = "SELECT h.hid,h.villcode,h.hno,h.pid,h.pidvola,h.ygis,h.xgis,if(instr(h.hno,'/')>0,substring(h.hno,1,instr(h.hno,'/')-1),h.hno) as f,concat(ctitle.titlename,person.fname,'  ',person.lname) as pname
FROM 
house as h
LEFT JOIN person on h.pcucode = person.pcucodeperson and h.pid = person.pid
LEFT JOIN ctitle on person.prename = ctitle.titlecode WHERE  villcode='$villcode' ORDER BY villcode,length(f),f,hno";
$result = mysql_query($sql);
$xml = '<markers>';
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	if($opid == $row[pidvola]){
		if($ohid == $row[hid]){$type = 'c2';}else{$type = 'c0';}
	}else{
		$type = 'c1';
	}
  $xml .= '<marker ';
  $xml .= 'hono="'.$row[hno].'" ';
  $xml .= 'moo="'.$moo.'" ';
  $xml .= 'vill="'.$vill.'" ';
  $xml .= 'hhouse="'.$row[pname].'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= 'type="'.$type.'" ';
  $xml .= '/>';
}
$xml .= '</markers>';
echo $xml;
?>

