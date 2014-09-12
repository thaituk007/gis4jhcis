<?php 

include("../includes/conndb.php"); 
include("../includes/config.inc.php");
if($_GET[hcode]){
	$hcode = $_GET[hcode];
	$sql = "UPDATE villagewater SET xgis='',ygis='',dateupdate=now() WHERE concat(villagewater.villcode,villagewater.waterno) = '$hcode' ";
	$result=mysql_query($sql,$link);
	if($result){
		echo "ok";
	}
}

if($_GET[villagecode]){
	$villcode = $_GET[villagecode];
	$fsno = $_GET[fsno];
	$lat = $_GET[lat];
	$lng = $_GET[lng];
	$sql = "UPDATE villagewater SET xgis='$lng',ygis='$lat',dateupdate=now() WHERE villagewater.villcode = '$villcode' AND villagewater.waterno='$fsno' ";
	$result=mysql_query($sql,$link);
	if($result){
		echo "ok";
	}	
}
?>
