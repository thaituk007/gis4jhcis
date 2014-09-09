<?php 
include("../includes/conndb.php"); 
include("../includes/config.inc.php");
if($_GET[villcode]){
	$villcode = $_GET[villcode];
	$sql = "UPDATE village set village.latitude = null, village.longitude = null,dateupdate=now() WHERE village.villcode = '$villcode'";
	$result=mysql_query($sql,$link);
	if($result){
		echo "ok";
	}
}

if($_GET[villagecode]){
	$villcode = $_GET[villagecode];
	$hno = $_GET[hno];
	$lat = $_GET[lat];
	$lng = $_GET[lng];
	$sql = "UPDATE village set village.latitude = '$lat', village.longitude = '$lng',dateupdate=now() WHERE village.villcode = '$villcode'";
	$result=mysql_query($sql,$link);
	if($result){
		echo "ok";
	}	
}
?>
