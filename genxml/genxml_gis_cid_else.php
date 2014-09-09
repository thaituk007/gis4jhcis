<?php 
include("../includes/conndb.php"); 
include("../includes/config.inc.php"); 
$villcode = $_GET[villcode];
if($villcode == '00000000'){
	$wt = " ";
}else{
	$wt = " AND villcode='$villcode' ";
}
$sql = "SELECT
person.idcard as cid,
concat(ctitle.titlename,
person.fname,'  ',
person.lname) as pname, 
person.birth,
house.hno,
house.villcode,
house.xgis,
house.ygis
FROM
person
Inner Join house ON person.pcucodeperson = house.pcucode AND person.hcode = house.hcode
inner join ctitle on person.prename = ctitle.titlecode
where 
Right(11-((Left(person.idcard,1)*13)+(Mid(person.idcard,2,1)*12)+(Mid(person.idcard,3,1)*11)+(Mid(person.idcard,4,1)*10)+(Mid(person.idcard,5,1)*9)+(Mid(person.idcard,6,1)*8)+(Mid(person.idcard,7,1)*7)+(Mid(person.idcard,8,1)*6)+(Mid(person.idcard,9,1)*5)+(Mid(person.idcard,10,1)*4)+(Mid(person.idcard,11,1)*3)+(Mid(person.idcard,12,1)*2)) Mod 11,1) <> Right(person.idcard,1) $wt
order by house.villcode";
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
  $xml .= 'cid="'.$row[cid].'" ';
  $xml .= 'pname="'.$row[pname].'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= '/>';
}

$xml .= '</markers>';
echo $xml;
?>

