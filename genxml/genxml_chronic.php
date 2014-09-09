<?php 
include("../includes/conndb.php"); 
include("../includes/config.inc.php"); 
function getListChronic($pcucode,$pid){
		$sql ="SELECT dc.groupname
					FROM
					personchronic AS pc
					Inner Join cdisease AS d ON pc.chroniccode = d.diseasecode
					Inner Join cdiseasechronic AS dc ON d.codechronic = dc.groupcode
					WHERE
					pc.pcucodeperson =  '$pcucode' AND
					pc.pid =  '$pid'";
		$result=mysql_query($sql);
		while($row=mysql_fetch_array($result)) {
			if($i > 0){$com = ",";}else{$com = "";}
			$ret .= $com.$row[groupname];
			$i++;
		}
		return $ret;
}
	$chronic = $_GET[chronic];
	$village = $_GET[village];
	if($village == "00000000"){
		$wvill = "";
	}else{
		$wvill = "AND h.villcode='$village'";	
	}
	if($chronic == '00'){$ect = "";}else{$ect = "AND dc.groupcode = '$chronic'";}
	$sql = "SELECT
				p.prename,CONCAT(p.fname,' ',p.lname) AS pname, FLOOR((TO_DAYS(NOW())-TO_DAYS(p.birth))/365.25) as age, h.hno,h.villcode,h.xgis,h.ygis,p.idcard,p.pcucodeperson,p.pid
				FROM
				house AS h
				Inner Join person AS p ON h.pcucode = p.pcucodeperson AND h.hcode = p.hcode
				Inner Join personchronic AS pc ON p.pcucodeperson = pc.pcucodeperson AND p.pid = pc.pid
				Inner Join cdisease AS d ON pc.chroniccode = d.diseasecode
				Inner Join cdiseasechronic AS dc ON d.codechronic = dc.groupcode
				WHERE ((p.dischargetype is null) or (p.dischargetype = '9')) $ect $wvill
				AND SUBSTRING(h.villcode,7,2) <> '00' AND
				pc.typedischart NOT IN  ('01', '02','07','10')
				GROUP BY
				p.pcucodeperson,
				p.pid";

$result = mysql_query($sql);
//header("Content-type: text/xml");
$xml = '<markers>';
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$dsc = getListChronic($row[pcucodeperson],$row[pid]);
	$title = getTitle($row[prename]);
  $xml .= '<marker ';
  $xml .= 'hono="'.$row[hno].'" ';
  $xml .= 'moo="'.$moo.'" ';
  $xml .= 'vill="'.$vill.'" ';
  $xml .= 'pname="'.$title.$row[pname].'" ';
  $xml .= 'dsc="'.$dsc.'" ';
  $xml .= 'age="'.$row[age].'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= '/>';
}
$xml .= '</markers>';
echo $xml;
?>

