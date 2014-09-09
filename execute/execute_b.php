<?php 

include("includes/conndb.php"); 
include("includes/config.inc.php"); 
if($_GET[hcode]){
	$hcode = $_GET[hcode];
	$sql = "UPDATE house SET xgis='',ygis='' WHERE hcode = '$hcode' ";
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
	$sql = "UPDATE house SET xgis='$lng',ygis='$lat' WHERE villcode = '$villcode' AND hno='$hno' ";
	$result=mysql_query($sql,$link);
	if($result){
		echo "ok";
	}	
}
?>
