<?php 
header("Content-type: text/xml");
include("../includes/conndb.php"); 
include("../includes/config.inc.php"); 
function redatepick($d){
	$y = substr($d,6,4)-543;
	$m = substr($d,3,2);
	$dn = substr($d,0,2);
	$rt = $y."/".$m."/".$dn;
	return $rt;
}
$str = $_GET[str];
	$sql = "SELECT  
person.idcard,
concat(ctitle.titlename,person.fname,' ',person.lname) AS pname,
person.birth,
round(DATEDIFF(now(),person.birth) /30) AS age,
house.hno,
house.villcode,
CONVERT(village.villname using utf8) as villname,
house.xgis,
house.ygis,
cdrug.drugcode, 
CONVERT(cdrug.drugname USING utf8) drugname , 
v.dateappoint,visitepi.dateepi
FROM   person
	LEFt JOIN ctitle ON person.prename = ctitle.titlecode
        LEFT JOIN visit  ON person.pid = visit.pid and person.pcucodeperson = visit.pcucodeperson
 	LEFT JOIN visitepi ON visit.visitno= visitepi.visitno and visit.pcucode = visitepi.pcucode 
 	LEFT JOIN visitepiappoint v ON visit.visitno = v.visitno  and visit.pcucode =v.pcucodeperson 
        LEFT JOIN cdrug ON (v.vaccinecode = cdrug.drugcode)                                     				  
        LEFT JOIN house ON person.hcode = house.hcode and person.pcucodeperson = house.pcucode
      	LEFT JOIN village ON house.villcode = village.villcode and person.pcucodeperson = village.pcucode
        LEFT JOIN chospital ON v.pcucode = chospital.hoscode
	LEFT JOIN cprovince ON cprovince.provcode =chospital.provcode
        LEFT JOIN cdistrict ON cdistrict.provcode = chospital.provcode and cdistrict.distcode = chospital.distcode
	 left join csubdistrict tb on tb.provcode = left(village.villcode,2) and tb.distcode = substring(village.villcode,3,2) and tb.subdistcode = substring(village.villcode,5,2)

WHERE  v.dateappoint = '$str' 
	and (birth IS NOT NULL OR birth NOT LIKE '0000%')
       and not exists (select CONCAT(visitepiappoint.visitno,visitepiappoint.pcucode) from visitepiappoint where visitepi.visitno =visitepiappoint.visitno and visitepi.pcucode =visitepiappoint.pcucode and  visitepi.dateepi = visitepiappoint.dateappoint group by visitepi.dateepi)
       and (visit.flagservice <'04' OR visit.flagservice is null OR length(trim(visit.flagservice))=0 )
GROUP BY v.pid,v.pcucodeperson
ORDER BY visit.pcucode,village.villcode,v.vaccinecode";
$result = mysql_query($sql);
$xml = '<markers>';
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$birth = retDatets($row[birth]);
  $xml .= '<marker ';
  $xml .= 'hono="'.$row[hno].'" ';
  $xml .= 'moo="'.$moo.'" ';
  $xml .= 'vill="'.$vill.'" ';
  $xml .= 'pname="'.$row[pname].'" ';
  $xml .= 'birth="'.$birth.'" ';
  $xml .= 'age="'.$row[age].'" ';
  $xml .= 'drugcode="'.$row[drugcode].'" ';
  $xml .= 'drugname="'.$row[drugname].'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= '/>';
}
$xml .= '</markers>';
echo $xml;
?>

