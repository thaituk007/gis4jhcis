<?php 
session_start();
include("../includes/conndb.php"); 
include("../includes/config.inc.php"); 
$ccenter = $_GET[ccenter];
$ctype = strtoupper($_GET[ctype]);
$c_zoom = $_GET[c_zoom];
$hslat = $_GET[hslat];
$hslng = $_GET[hslng];

$fx = fopen("../includes/config.local.php", "r");
while($ln = fgets($fx, 1024)){
	if(strpos($ln, "dfmapcenter")){
		$ck = "\$dfmapcenter = \"$ccenter\";\r\n";
	}else if(strpos($ln, "dfmapzoom")){
		$ck = "\$dfmapzoom = \"$c_zoom\";\r\n";
	}else if(strpos($ln, "dfmapview")){
		$ck = "\$dfmapview = \"$ctype\";\r\n";
	}else if(strpos($ln, "hospitoalat")){
		$ck = "\$hospitoalat = \"$hslat\";\r\n";
	}else if(strpos($ln, "hospitoalng")){
		$ck = "\$hospitoalng = \"$hslng\";\r\n";		
	}else{
		$ck = $ln;	
	}
	$txt3 .= $ck;
}
fclose($fx);

$fx = fopen("../includes/config.local.php", "w+");
fputs($fx, $txt3);
if(fclose($fx)){
	echo "<center><p class='text text-success'>บันทึกข้อมูลแล้ว </p></center>";
}
?>

