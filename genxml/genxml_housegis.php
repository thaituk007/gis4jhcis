<?php 
include("../includes/conndb.php"); 
include("../includes/config.inc.php"); 
$villcode = $_GET[villcode];
if($villcode == '00000000'){
	$wt = "";
}else{
	$wt = " AND villcode='$villcode' ";
}
$sql = "SELECT
hp.*,
concat(ctitle.titlename,person.fname,'  ',person.lname) as pname
FROM
(SELECT house.pcucode,house.hcode,villcode,hno,pid,ygis,xgis,if(instr(hno,'/')>0,substring(hno,1,instr(hno,'/')-1),hno) as f 
FROM house 
WHERE  SUBSTRING(villcode,7,2) <> '00' $wt 
ORDER BY villcode,length(f),f,hno) as hp
left join person on person.pcucodeperson = hp.pcucode and person.pid = hp.pid
left join ctitle on person.prename = ctitle.titlecode";
$result = mysql_query($sql);
//header("Content-type: text/xml");
$xml = '<markers>';
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
  //$ad = iconv( 'TIS-620', 'UTF-8',$ad);
  $xml .= '<marker ';
  $xml .= 'hono="'.$row[hno].'" ';
  $xml .= 'moo="'.$moo.'" ';
  $xml .= 'vill="'.$vill.'" ';
  $xml .= 'hhouse="'.$row[pname].'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= '/>';
}

$xml .= '</markers>';
echo $xml;
?>

