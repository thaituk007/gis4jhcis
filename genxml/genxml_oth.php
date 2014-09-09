<?php 
header("Content-type: text/xml");
include("../includes/conndb.php"); 
include("../includes/config.inc.php"); 
$xml = '<markers>';
	$txtfile = fopen("oth.csv","r");
	while ($h = fgets($txtfile,1024)){
		$h = iconv( 'TIS-620', 'UTF-8',$h);
		++$i;
		if($i == 1){
		}else{
			list($villcode,$hono,$title,$pname,$info1,$info2) = split(',',$h);
			$moo = substr($villcode,6,2);
			$vill = getMooVillage($villcode);
			$lat = getLatHouse($villcode,$hono);
			$lng = getLngHouse($villcode,$hono);
			  $xml .= '<marker ';
			  $xml .= 'hono="'.$hono.'" ';
			  $xml .= 'moo="'.$moo.'" ';
			  $xml .= 'vill="'.$vill.'" ';
			  $xml .= 'pname="'.$pname.'" ';
			  $xml .= 'info1="'.$info1.'" ';
			  $xml .= 'info2="'.$info2.'" ';
			  $xml .= 'lat="'.$lat.'" ';
			  $xml .= 'lng="'.$lng.'" ';
			  $xml .= '/>';
		}
	}
	fclose($txtfile);
$xml .= '</markers>';
echo $xml;
?>

