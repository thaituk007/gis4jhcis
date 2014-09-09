<?php 
header("Content-type: text/xml");
include("../includes/conndb.php"); 
include("../includes/config.inc.php");
$datemask = $_GET[datemask]; 
$age = $_GET[age];
$ect0 = "'10000'";
if(strpos($age,",",0) > 0){
	$listage = explode(',',$age);
	foreach ($listage as $a){
		if(strpos($a,"-",0) > 0){
			list($str,$end) = split("-",$a,2);
			for($i = $str; $i <= $end; $i++){
				$ect0 .= ",'".$i."'";
			}
		}else{
			$ect0 .= ",'".$a."'";
		}
	}
}else{
		if(strpos($age,"-",0) > 0){
			list($str,$end) = split("-",$age,2);
			for($i = $str; $i <= $end; $i++){
				$ect0 .= ",'".$i."'";
			}
		}else{
			$ect0 .= ",'".$age."'";
		}
}
$sex = $_GET[sex];
if($sex == '0'){$ect1 = "";}else{$ect1 = " p.sex = '$sex' AND ";}
$village = $_GET[village];
if($village == "00000000"){
	$mu = "ทุกหมู่บ้าน";
}else{
	$mu = getvillagename($village);	
}
if($village == '00000000'){$ect2 = "";}else{$ect2 = " h.villcode = '$village' AND ";}
$live_type = $_GET[live_type];
if($live_type == '2'){$live_type_name = "ตามทะเบียนบ้าน(0,1,2)";}elseif($live_type == '1'){$live_type_name = "ที่อาศัยอยู่จริง (0,1,3)";}else{$live_type_name = "ทั้งหมดในเขตรับผิดชอบ(0,1,2,3)";}	
if($live_type == '2'){$live_type2 = "and p.typelive in ('0','1','2')";}elseif($live_type == '1'){$live_type2 = "and p.typelive in ('0','1','3')";}else{$live_type2 = "and p.typelive in ('0','1','2','3')";}
	$sql = "SELECT p.prename,CONCAT(p.fname,' ',p.lname) AS pname,h.hno,h.villcode,h.xgis,h.ygis,p.birth,
				FLOOR((TO_DAYS('$datemask')-TO_DAYS(p.birth))/365.25) AS age
				FROM house AS h
				Inner Join person AS p ON h.pcucode = p.pcucodeperson AND h.hcode = p.hcode
				WHERE $ect1 $ect2 ((p.dischargetype is null) or (p.dischargetype = '9')) AND
				SUBSTRING(h.villcode,7,2) <> '00' AND
				FLOOR((TO_DAYS('$datemask')-TO_DAYS(p.birth))/365.25) IN($ect0) $live_type2
				ORDER BY h.villcode asc ,h.hno*1 asc,FLOOR((TO_DAYS(NOW())-TO_DAYS(p.birth))/365.25) desc";
$result = mysql_query($sql);
$xml = '<markers>';
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$bod = retDatets($row[birth]);
	$title = getTitle($row[prename]);
  $xml .= '<marker ';
  $xml .= 'hono="'.$row[hno].'" ';
  $xml .= 'moo="'.$moo.'" ';
  $xml .= 'vill="'.$vill.'" ';
  $xml .= 'pname="'.$title.$row[pname].'" ';
  $xml .= 'bod="'.$bod.'" ';
  $xml .= 'ag="'.$row[age].'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= '/>';
}
$xml .= '</markers>';
echo $xml;
?>

