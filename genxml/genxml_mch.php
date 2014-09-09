<?php 
header("Content-type: text/xml");
include("../includes/conndb.php"); 
include("../includes/config.inc.php"); 
$villcode = $_GET[village];
if($villcode == "00000000"){
	$wvill = "AND right(house.villcode,2) <> '00'";
}elseif($villcode == "11111111"){
	$wvill = "AND right(house.villcode,2) = '00'";
}else{
	$wvill = " AND house.villcode='$villcode' ";	
}
$str = retdaterangstr($_GET[str]);
$sto = retdaterangsto($_GET[str]);
$strx = retDatets($str);
$stox = retDatets($sto);
$sql = "SELECT CONVERT(concat(ifnull(tm.titlename,ifnull(person.prename,'ไม่ระบุ') ),person.fname,' ',person.lname) using utf8) as mothername,
house.villcode,
person.birth,
house.hno,
house.hcode,
house.xgis,
house.ygis
,MAX(v.pregno) pregno,v.pid,v.pcucodeperson
,DATE_FORMAT(current_date,'%Y-%m-%d') - DATE_FORMAT(person.birth,'%Y-%m-%d') as age
,if(house.hno != '' ,CONVERT(concat(house.hno,' ม.',villno) USING utf8), CONVERT(concat('- ม.',villno) USING utf8) ) as address
,CONVERT(concat(ifnull(tc.titlename,ifnull(person.prename,'ไม่ระบุ') ),pchild.fname,' ',pchild.lname) using utf8) childname
,house.pcucode
,count(distinct v.pid,v.pcucodeperson,v.pregno,v.datecare) as cdc
,case when ( v.datecare between MIN(v.datecare) and MAX(v.datecare)  and (count(distinct v.pid,v.pcucodeperson,v.pregno,v.datecare)  >=1 ) ) then MIN(v.datecare) else null end m1
,case when ( v.datecare between MIN(v.datecare) and MAX(v.datecare)  and (count(distinct v.pid,v.pcucodeperson,v.pregno,v.datecare)  >=2 ))  then v.datecare  else null end m2
,case when ( v.datecare between MIN(v.datecare) and MAX(v.datecare)  and (count(distinct v.pid,v.pcucodeperson,v.pregno,v.datecare)  >=3 ))  then MAX(v.datecare)else null end m3
,curdate() as cdate
FROM  visitancmothercare v
        left join visitancdeliverchild vchild on v.pid = vchild.pid and v.pcucodeperson = vchild.pcucodeperson
	left join visitancdeliver on v.pid  = visitancdeliver.pid  and v.pcucodeperson= visitancdeliver.pcucodeperson
	left join person   on v.pid = person.pid and v.pcucodeperson = person.pcucodeperson
	left join ctitle tm on person.prename = tm.titlecode
	left join person pchild  on pchild.pid = vchild.pidchild and pchild.pcucodeperson = vchild.pcucodechild
	left join ctitle tc on pchild.prename = tc.titlecode
	left join house on person.hcode = house.hcode and person.pcucodeperson = house.pcucode
	left join village on house.villcode = village.villcode and house.pcucode = village.pcucode

WHERE visitancdeliver.datedeliver between '$str' and '$sto' $wvill
GROUP BY  v.pcucode,v.pid,v.pcucodeperson,v.pregno
order by house.villcode,person.fname";
$result = mysql_query($sql);
$xml = '<markers>';
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$birth = retDatets($row[birth]);
	if($row[m1] == ""){$m1 = '--/--/----';}else{$m1 = retDatets($row[m1]);}
	if($row[m2] == ""){$m2 = '--/--/----';}else{$m2 = retDatets($row[m2]);}
	if($row[m3] == ""){$m3 = '--/--/----';}else{$m3 = retDatets($row[m3]);}
  $xml .= '<marker ';
  $xml .= 'hcode="'.$row[hcode].'" ';
  $xml .= 'hono="'.$row[hno].'" ';
  $xml .= 'moo="'.$moo.'" ';
  $xml .= 'vill="'.$vill.'" ';
  $xml .= 'mothername="'.$row[mothername].'" ';
  $xml .= 'birth="'.$birth.'" ';
  $xml .= 'age="'.$row[age].'" ';
  $xml .= 'childname="'.$row[childname].'" ';
  $xml .= 'chk_m1="'.$row[m1].'" ';
  $xml .= 'chk_m2="'.$row[m2].'" ';
  $xml .= 'chk_m3="'.$row[m3].'" ';
  $xml .= 'm1="'.$m1.'" ';
  $xml .= 'm2="'.$m2.'" ';
  $xml .= 'm3="'.$m3.'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= '/>';
}
$xml .= '</markers>';
echo $xml;
?>