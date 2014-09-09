<?php 
header("Content-type: text/xml");
include("../includes/conndb.php"); 
include("../includes/config.inc.php"); 
$nyear = date("Y");
$village = $_GET[village];
$str = retdaterangstr($_GET[str]);
$sto = retdaterangsto($_GET[str]);
$strx = retDatets($str);
$stox = retDatets($sto);
if($village == '00000000'){$wt = "";}else{$wt = " villcode = '$village' AND ";}

$sql = "SELECT hcode,villcode,hno,pid,ygis,xgis,if(instr(hno,'/')>0,substring(hno,1,instr(hno,'/')-1),hno) as f FROM house 
			WHERE  $wt SUBSTRING(villcode,7,2) <> '00'  ORDER BY villcode,length(f),f,hno";
$result = mysql_query($sql);
$xml = '<markers>';
while($row=mysql_fetch_array($result)) {
	$sqlv = "SELECT v.hcode,v.noofves,v.noofgenusculex,v.datesurvey
					FROM housegenusculex AS v 
					WHERE v.datesurvey BETWEEN  '$str' AND '$sto' AND
								v.hcode = $row[hcode]
					ORDER BY  v.datesurvey DESC LIMIT 1 ";
	$resultv = mysql_query($sqlv);
	$rs=mysql_fetch_array($resultv);
	if($rs[noofves] > 0){
			$nv = $rs[noofves];
			$ng = $rs[noofgenusculex];
			$bi = number_format(($rs[noofgenusculex]/$rs[noofves]*100),2);	
			if($bi < 1){
				$type = '1';
			}else if($bi < 10){
				$type = '2';
			}else if($bi <= 20){
				$type = '3';
			}else{
				$type = '4';
			}
	}else{$bi = "";$type="0";$nv="-";$ng="-";}	
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$hhouse = getPersonName($row[pid]);
  //$ad = iconv( 'TIS-620', 'UTF-8',$ad);
  $xml .= '<marker ';
  $xml .= 'hono="'.$row[hno].'" ';
  $xml .= 'moo="'.$moo.'" ';
  $xml .= 'vill="'.$vill.'" ';
  $xml .= 'hhouse="'.$hhouse.'" ';
  $xml .= 'bi="'.$bi.'" ';
  $xml .= 'type="'.$type.'" ';
  $xml .= 'nv="'.$nv.'" ';
  $xml .= 'ng="'.$ng.'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= '/>';
}
$xml .= '</markers>';
echo $xml;



?>

