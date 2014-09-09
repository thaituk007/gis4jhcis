<?php 
header("Content-type: text/xml");
include("../includes/conndb.php"); 
include("../includes/config.inc.php"); 
$dx = date("md");
$yx = date("Y");
$yy = date("Y")-1;
if($dx > "1001"){$daymidyear = $yx."-07-01";}else{$daymidyear = $yy."-07-01";}
$age = $_GET[age];
$ect0 = "'10000'";
if(strpos($age,",",0) > 0){
	$listage = explode(',',$age);
	foreach ($listage as $a){
		if(strpos($a,"-",0) > 0){
			list($str,$end) = split("-",$a,2);
			for($i = $str; $i <= $end; $i++){
				$ect0 .= ",'".$i."'";
			}
		}else{
			$ect0 .= ",'".$a."'";
		}
	}
}else{
		if(strpos($age,"-",0) > 0){
			list($str,$end) = split("-",$age,2);
			for($i = $str; $i <= $end; $i++){
				$ect0 .= ",'".$i."'";
			}
		}else{
			$ect0 .= ",'".$age."'";
		}
}
$villcode = $_GET[village];
if($villcode == "00000000"){
	$wvill = "";
}else{
	$wvill = " AND house.villcode='$villcode' ";	
}
$chk_old = $_GET[chk_old];
if($chk_old == "0"){
	$chksto = "";
}elseif($chk_old == "1"){
	$chksto = "and vsb.visitdate is not null";	
}else{
	$chksto = "and vsb.visitdate is null";
}
$live_type = $_GET[live_type];
if($live_type == '2'){$live_type_name = "ตามทะเบียนบ้าน(0,1,2)";}elseif($live_type == '1'){$live_type_name = "ที่อาศัยอยู่จริง (0,1,3)";}else{$live_type_name = "ทั้งหมดในเขตรับผิดชอบ(0,1,2,3)";}	
if($live_type == '2'){$live_type2 = "and person.typelive in ('0','1','2')";}elseif($live_type == '1'){$live_type2 = "and person.typelive in ('0','1','3')";}else{$live_type2 = "and person.typelive in ('0','1','2','3')";}
$str = retdaterangstr($_GET[str]);
$sto = retdaterangsto($_GET[str]);
$strx = retDatets($str);
$stox = retDatets($sto);
$strd = substr($str,8,2);
$strm = substr($str,5,2);
$stryT = substr($str,0,4);
$stryF = substr($str,0,4)-1;
$dx = $strm."".$strd;
if($dx > "1001"){$daymidyear = $stryT."-10-01";}else{$daymidyear = $stryF."-10-01";}	
$sql = "
SELECT
person.pcucodeperson,
person.pid,
person.fname, 
concat(ctitle.titlename, person.fname , '  ' , person.lname) AS pname,
person.birth,
getAgeYearNum(person.birth,'$daymidyear') AS age,
village.villcode,
village.villname,
house.hno,
house.hcode,
house.xgis,
house.ygis,
vsb.pid as vspid,
vsb.visitdate,
vsb.coderesult,
vsb.depressed,
vsb.fedup
FROM village
Inner Join house ON village.pcucode = house.pcucode AND village.villcode = house.villcode
INNER JOIN person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
INNER JOIN coccupa ON person.occupa = coccupa.occupacode
left Join ctitle ON person.prename = ctitle.titlecode
left join (SELECT
vs.pcucodeperson,
vs.pid,
vs.visitdate,
vslab.codescreen,
vslab.coderesult,
vslab.depressed,
vslab.fedup
FROM 
visit as vs 
INNER JOIN visitdiag as vsd ON vs.pcucode = vsd.pcucode AND vs.visitno = vsd.visitno
left join visitscreenspecialdisease vslab on vs.pcucode = vslab.pcucode and vslab.visitno = vs.visitno
where vs.visitdate between '$str' and '$sto' and vsd.diagcode = 'Z13.3' and vslab.codescreen like 'c01' 
group by vs.pcucodeperson,vs.pid) as vsb
on person.pcucodeperson = vsb.pcucodeperson and person.pid = vsb.pid
where getAgeYearNum(person.birth,'$daymidyear') IN($ect0) and ((person.dischargetype is null) or (person.dischargetype = '9'))  and SUBSTRING(house.villcode,7,2) <> '00' $live_type2 $chksto $wvill
order by person.pcucodeperson,village.villcode,person.fname";
$result = mysql_query($sql);
$xml = '<markers>';
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	if($row[visitdate] == ""){$old_chk = 'ไม่ได้รับการคัดกรอง';}else{$old_chk = 'ได้รับคัดกรอง';}
	$birth = retDatets($row[birth]);
	if($row[visitdate] == ""){$visitdate = '--/--/----';}else{$visitdate = retDatets($row[visitdate]);}
  $xml .= '<marker ';
  $xml .= 'hono="'.$row[hno].'" ';
  $xml .= 'moo="'.$moo.'" ';
  $xml .= 'vill="'.$vill.'" ';
  $xml .= 'pname="'.$row[pname].'" ';
  $xml .= 'birth="'.$birth.'" ';
  $xml .= 'age="'.$row[age].'" ';
  $xml .= 'vspid="'.$row[vspid].'" ';
  $xml .= 'old_chk="'.$old_chk.'" ';
  $xml .= 'visitdate="'.$visitdate.'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= '/>';
}
$xml .= '</markers>';
echo $xml;
?>