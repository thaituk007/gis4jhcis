<?php 
header("Content-type: text/xml");
include("../includes/conndb.php"); 
include("../includes/config.inc.php"); 
$artype=$_GET[artype];
if($artype == '1'){
$radius=$_GET[rd]/1000*0.6214;	
$center_lat = $_GET["lat"];
$center_lng = $_GET["lng"];
$sql = "SELECT villcode,hno,pid,ygis,xgis,if(instr(hno,'/')>0,substring(hno,1,instr(hno,'/')-1),hno) as f
			FROM house 
			WHERE  SUBSTRING(villcode,7,2) <> '00' AND 
					ygis <> '' AND 
					ygis IS NOT NULL AND
					( 3959 * acos( cos( radians('$center_lat') ) * cos( radians( ygis ) ) * cos( radians( xgis ) - radians('$center_lng') ) + sin( radians('$center_lat') ) * sin( radians( ygis ) ) ) ) < '$radius'
			ORDER BY villcode,length(f),f,hno";
}else{
$latn = $_GET[latn];
$lnge = $_GET[lnge];
$lats = $_GET[lats];
$lngw = $_GET[lngw];	
$sql = "SELECT villcode,hno,pid,ygis,xgis,if(instr(hno,'/')>0,substring(hno,1,instr(hno,'/')-1),hno) as f
			FROM house 
			WHERE  SUBSTRING(villcode,7,2) <> '00' AND 
					ygis <> '' AND 
					ygis IS NOT NULL AND
					(ygis BETWEEN '$lats' AND '$latn') AND
					(xgis BETWEEN '$lngw' AND '$lnge')
			ORDER BY villcode,length(f),f,hno";

}
$result = mysql_query($sql);
$xml = '<markers>';
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$hhouse = getPersonName($row[pid]);
  //$ad = iconv( 'TIS-620', 'UTF-8',$ad);
  $xml .= '<marker ';
  $xml .= 'hono="'.$row[hno].'" ';
  $xml .= 'moo="'.$moo.'" ';
  $xml .= 'vill="'.$vill.'" ';
  $xml .= 'hhouse="'.$hhouse.'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= '/>';
}

$xml .= '</markers>';
echo $xml;
?>

