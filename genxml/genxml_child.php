<?php  // ปรับใช้ Nutri อย่างเดียว
header("Content-type: text/xml");
include("../includes/conndb.php"); 
include("../includes/config.inc.php");  

$gdata = $_GET[gdata];
if($gdata == 'nutri'){
$village = $_GET[village];
if($village == '00000000'){$ect2 = "";}else{$ect2 = " h.villcode = '$village' AND ";}
	$sql = "SELECT p.pid,p.prename,CONCAT(p.fname,' ',p.lname) AS pname,h.hno,h.villcode,h.xgis,h.ygis,p.birth,
				FLOOR((TO_DAYS(NOW())-TO_DAYS(p.birth))/365.25) AS age,
				(TO_DAYS(NOW())-TO_DAYS(p.birth))/365.25 AS age2
				FROM house AS h
				Inner Join person AS p ON h.pcucode = p.pcucodeperson AND h.hcode = p.hcode
				WHERE $ect2 ((p.dischargetype is null) or (p.dischargetype = '9')) AND
				SUBSTRING(h.villcode,7,2) <> '00' AND
				(TO_DAYS(NOW())-TO_DAYS(p.birth))/365.25 <= 6
				ORDER BY h.villcode,h.hno";
$result = mysql_query($sql);
$xml = '<markers>';
while($row=mysql_fetch_array($result)) {
	$sqlnutri = "SELECT v.pid,n.weight,n.tall,n.nlevel,MAX(v.visitdate) AS vsd
				FROM
				visit AS v
				Inner Join visitnutrition AS n ON v.pcucode = n.pcucode AND v.visitno = n.visitno
				WHERE
				(TO_DAYS(NOW())-TO_DAYS(v.visitdate)) <=  183
				AND v.pid = '$row[pid]' and (v.flagservice <'04' OR v.flagservice is null OR length(trim(v.flagservice))=0 )
				GROUP BY v.pid";
	$resultnutri = mysql_query($sqlnutri);
	if(mysql_num_rows($resultnutri) >= 1){
		$rsnutri = mysql_fetch_array($resultnutri);
		$va = $rsnutri[weight];
		$vb = $rsnutri[tall];
		$vc = $rsnutri[nlevel];
		if($vc == 1){$vd1 = "น.น.ต่ำมาก";}
		else if($vc == 2){$vd1 = "น.น.ต่ำ";}
		else if($vc == 3){$vd1 = "น.น.ปกติ";}
		else if($vc == 4){$vd1 = "น.น.สูง";}
		else if($vc == 5){$vd1 = "น.น.สูงมาก";}
		else{$vd1="";}
		if($rsnutri[vsd] != ""){
			$vd2 = retDatets($rsnutri[vsd]);
		}else{
			$vd2 = "";
		}
		$vd = $vd1."(".$vd2.")";
	}else{
		$vc = '9';
		$vd = 'รอบ 6 ด. ไม่มีข้อมูล';
	}
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$bod = retDatets($row[birth]);
	$title = getTitle($row[prename]);
	$m = number_format((($row[age2]-$row[age])*12),0);
	$ageym = $row[age]."-".$m;
  $xml .= '<marker ';
  $xml .= 'hono="'.$row[hno].'" ';
  $xml .= 'moo="'.$moo.'" ';
  $xml .= 'vill="'.$vill.'" ';
  $xml .= 'pname="'.$title.$row[pname].'" ';
  $xml .= 'bod="'.$bod.'" ';
  $xml .= 'ag="'.$ageym.'" ';
  $xml .= 'va="'.$va.'" ';
  $xml .= 'vb="'.$vb.'" ';
  $xml .= 'vc="'.$vc.'" ';
  $xml .= 'vd="'.$vd.'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= '/>';
}
$xml .= '</markers>';
echo $xml;
}


?>

