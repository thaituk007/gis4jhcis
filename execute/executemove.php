<?php 

include("../includes/conndb.php"); 
include("../includes/config.inc.php"); 


if($_GET[villagecode]){
	$villcode = $_GET[villagecode];
	$hno = $_GET[hno];
	$lat = $_GET[lat];
	$lng = $_GET[lng];
$sql = "SELECT xgis,ygis FROM house WHERE villcode = '$villcode' AND hno='$hno' ";	
$result=mysql_query($sql,$link);
if($result){
$rs=mysql_fetch_array($result);
$x = $lng-$rs[xgis];
$y = $lat-$rs[ygis];

$sql = "SELECT hid,xgis,ygis FROM house WHERE villcode = '$villcode'";
$result = mysql_query($sql);
	while($row=mysql_fetch_array($result)) {
		if($row[xgis] != ''){
		$nx = $row[xgis]+$x;
		$ny = $row[ygis]+$y;
		$sql = "UPDATE house SET xgis='$nx',ygis='$ny' WHERE hid = $row[hid]";
		mysql_query($sql,$link);
		}
	}
}

}


?>
