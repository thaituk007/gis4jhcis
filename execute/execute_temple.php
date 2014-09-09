<?php 

include("../includes/conndb.php"); 
include("../includes/config.inc.php");
if($_GET[hcode]){
	$hcode = $_GET[hcode];
	$sql = "UPDATE villagetemple SET xgis='',ygis='' WHERE concat(villagetemple.villcode,villagetemple.templeno) = '$hcode' ";
	$result=mysql_query($sql,$link);
	if($result){
		echo "ok";
	}
}

if($_GET[villagecode]){
	$villcode = $_GET[villagecode];
	$templeno = $_GET[templeno];
	$lat = $_GET[lat];
	$lng = $_GET[lng];
	$sql = "UPDATE villagetemple SET xgis='$lng',ygis='$lat' WHERE villcode = '$villcode' AND templeno='$templeno' ";
	$result=mysql_query($sql,$link);
	if($result){
		echo "ok";
	}	
}
?>
