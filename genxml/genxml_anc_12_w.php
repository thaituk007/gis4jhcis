<?php 
header("Content-type: text/xml");
include("../includes/conndb.php"); 
include("../includes/config.inc.php"); 
$villcode = $_GET[village];
if($villcode == "00000000"){
	$wvill = "";
}else{
	$wvill = " AND house.villcode='$villcode' ";	
}
$str = retdaterangstr($_GET[str]);
$sto = retdaterangsto($_GET[str]);
$strx = retDatets($str);
$stox = retDatets($sto);
$sql = "SELECT 
village.pcucode, 
person.pid, 
person.idcard, 
concat(ctitle.titlename, person.fname , '  ' , person.lname) AS pname, 
village.villcode,
village.villname,
house.hno,
house.hcode,
house.xgis,
house.ygis,
person.birth,
ROUND(DATEDIFF(now(),person.birth)/365.25) AS age,
visitancpregnancy.edc,
visitanc.pregno,
visitancpregnancy.lmp,
min(visitanc.datecheck) as first_visit_date,
ROUND(DATEDIFF(min(visitanc.datecheck) ,visitancpregnancy.lmp) /7) AS agepreg,
case when ROUND(DATEDIFF(visitanc.datecheck ,visitancpregnancy.lmp) /7) < 12 then 1 else 0 end as chk
FROM (((house INNER JOIN village ON (house.villcode = village.villcode) AND (house.pcucode = village.pcucode)) INNER JOIN person ON (house.hcode = person.hcode) AND (house.pcucode = person.pcucodeperson)) INNER JOIN visitancpregnancy ON (person.pid = visitancpregnancy.pid) AND (person.pcucodeperson = visitancpregnancy.pcucodeperson)) INNER JOIN visitanc ON (visitancpregnancy.pregno = visitanc.pregno) AND (visitancpregnancy.pid = visitanc.pid) AND (visitancpregnancy.pcucodeperson = visitanc.pcucodeperson) inner join ctitle on person.prename = ctitle.titlecode
WHERE visitanc.datecheck Between '$str' And '$sto' AND (person.dischargetype Is Null Or person.dischargetype='9') and right(house.villcode,2) <> '00' $wvill
GROUP BY village.pcucode, person.pid
order by village.pcucode, village.villcode, person.fname";
$result = mysql_query($sql);
$xml = '<markers>';
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$lmp = retDatets($row[lmp]);
	$edc = retDatets($row[edc]);
	$birth = retDatets($row[birth]);
	$first_visit_date = retDatets($row[first_visit_date]);
  $xml .= '<marker ';
  $xml .= 'hcode="'.$row[hcode].'" ';
  $xml .= 'hono="'.$row[hno].'" ';
  $xml .= 'moo="'.$moo.'" ';
  $xml .= 'vill="'.$vill.'" ';
  $xml .= 'pname="'.$row[pname].'" ';
  $xml .= 'birth="'.$birth.'" ';
  $xml .= 'age="'.$row[age].'" ';
  $xml .= 'lmp="'.$lmp.'" ';
  $xml .= 'edc="'.$edc.'" ';
  $xml .= 'first_visit_date="'.$first_visit_date.'" ';
  $xml .= 'pregno="'.$row[pregno].'" ';
  $xml .= 'agepreg="'.$row[agepreg].'" ';
  $xml .= 'chk="'.$row[chk].'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= '/>';
}
$xml .= '</markers>';
echo $xml;
?>

