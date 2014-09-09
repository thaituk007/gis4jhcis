<?php 

include("../includes/conndb.php"); 
include("../includes/config.inc.php");
if($_GET[hcode]){
	$hcode = $_GET[hcode];
	$sql = "UPDATE villageschool SET xgis='',ygis='',dateupdate=now() WHERE concat(villageschool.villcode,villageschool.schoolno) = '$hcode' ";
	$result=mysql_query($sql,$link);
	if($result){
		echo "ok";
	}
}

if($_GET[villagecode]){
	$villcode = $_GET[villagecode];
	$schoolno = $_GET[schoolno];
	$lat = $_GET[lat];
	$lng = $_GET[lng];
	$sql = "UPDATE villageschool SET xgis='$lng',ygis='$lat',dateupdate=now() WHERE villcode = '$villcode' AND schoolno='$schoolno' ";
	$result=mysql_query($sql,$link);
	if($result){
		echo "ok";
	}	
}
?>
