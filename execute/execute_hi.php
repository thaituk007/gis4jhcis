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

if($_GET[chk] == 'vola'){
	$villcode = $_GET[villcode];
	$sql = "SELECT h.hid,p.pid,p.prename,CONCAT(p.fname,' ',p.lname) AS pname,h.hno,h.villcode,p.telephoneperson
					FROM
					house AS h
					Inner Join person AS p ON h.pcucode = p.pcucodeperson AND h.hcode = p.hcode
					Inner Join persontype AS pt ON p.pcucodeperson = pt.pcucodeperson AND p.pid = pt.pid
					WHERE
					pt.typecode =  '09' AND
					h.villcode =  '$villcode'";

$txt .= "<table width='100%' border='0' cellspacing='0' cellpadding='1' class='tbhl'>";
$result = mysql_query($sql);
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$title = getTitle($row[prename]);

	$txt .= "<tr><td>$title".$row[pname]."<br>".$row[hno]." ม.".$moo." ".$row[telephoneperson]."</td>";
	$txt .= "<td><input type='button' value='บ้าน...' onclick=getData(".$row[pid].",".$row[hid].");></td></tr>";
}
$txt .= "</table>";		
echo $txt;			
}
?>
