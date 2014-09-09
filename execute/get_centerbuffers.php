<?php 
include("../includes/conndb.php"); 
include("../includes/config.inc.php"); 
$hno = $_GET[hno];
$villagecode = $_GET[villagecode];
$sql ="SELECT ygis,xgis FROM house WHERE villcode='$villagecode' AND hno='$hno'";
	$result=mysql_query($sql);
	$row=mysql_fetch_array($result);
	if($row[ygis] != ''){
		$ret =$row[ygis].','.$row[xgis];	
	}else{
		$ret=0;	
	}
echo $ret;	

?>

