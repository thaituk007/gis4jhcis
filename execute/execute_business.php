<?php 

include("../includes/conndb.php"); 
include("../includes/config.inc.php");
if($_GET[hcode]){
	$hcode = $_GET[hcode];
	$sql = "UPDATE villagebusiness SET xgis='',ygis='',dateupdate=now() WHERE concat(villagebusiness.villcode,villagebusiness.businessno) = '$hcode' ";
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
	$sql = "UPDATE villagebusiness SET xgis='$lng',ygis='$lat',dateupdate=now() WHERE villagebusiness.villcode = '$villcode' AND villagebusiness.businessno='$fsno' ";
	$result=mysql_query($sql,$link);
	if($result){
		echo "ok";
	}	
}
?>
