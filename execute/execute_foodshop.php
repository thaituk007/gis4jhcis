<?php 

include("../includes/conndb.php"); 
include("../includes/config.inc.php");
if($_GET[hcode]){
	$hcode = $_GET[hcode];
	$sql = "UPDATE villagefoodshop SET xgis='',ygis='',dateupdate=now() WHERE concat(villagefoodshop.villcode,villagefoodshop.foodshopno) = '$hcode' ";
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
	$sql = "UPDATE villagefoodshop SET xgis='$lng',ygis='$lat',dateupdate=now() WHERE villagefoodshop.villcode = '$villcode' AND villagefoodshop.foodshopno='$fsno' ";
	$result=mysql_query($sql,$link);
	if($result){
		echo "ok";
	}	
}
?>
