<?php 
header("Content-type: text/xml");
include("../includes/conndb.php"); 
include("../includes/config.inc.php"); 
$villcode = $_GET[village];
if($villcode == "00000000"){
	$wvill = "";
}else{
	$wvill = " AND h.villcode='$villcode' ";	
}
$str = retdaterangstr($_GET[str]);
$sto = retdaterangsto($_GET[str]);
$strx = retDatets($str);
$stox = retDatets($sto);
$live_type = $_GET[live_type];
if($live_type == '2'){$live_type_name = "ตามทะเบียนบ้าน(0,1,2)";}elseif($live_type == '1'){$live_type_name = "ที่อาศัยอยู่จริง (0,1,3)";}else{$live_type_name = "ทั้งหมดในเขตรับผิดชอบ(0,1,2,3)";}	
if($live_type == '2'){$live_type2 = " and p.typelive in ('0','1','2')";}elseif($live_type == '1'){$live_type2 = "and p.typelive in ('0','1','3')";}else{$live_type2 = "and p.typelive in ('0','1','2','3')";}
$sql = "select
*,
case when anc1 is not null and anc2 is not null and anc3 is not null and anc4 is not null and anc5 is not null then 1 else 0 end as chk
from
(
SELECT
ancperson.*,
(select DATE_FORMAT(MAX(v1.datecheck),'%Y-%m-%d') from visitanc v1 inner JOIN visitancpregnancy p1 on p1.pcucodeperson = v1.pcucodeperson and v1.pid = p1.pid and p1.pregno = v1.pregno where v1.pid = ancperson.pid and v1.pcucodeperson = ancperson.pcucodeperson and DATEDIFF(v1.datecheck,p1.lmp) <= 90 and v1.pregno = ancperson.pregno group by ancperson.pid,ancperson.pcucodeperson,ancperson.pregno) as anc1,
(select DATE_FORMAT(MAX(v2.datecheck),'%Y-%m-%d') from visitanc v2 inner JOIN visitancpregnancy p2 on p2.pcucodeperson = v2.pcucodeperson and v2.pid = p2.pid and p2.pregno = v2.pregno where v2.pid = ancperson.pid and v2.pcucodeperson = ancperson.pcucodeperson and DATEDIFF(v2.datecheck,p2.lmp) between 112 AND 146 and v2.pregno = ancperson.pregno group by ancperson.pid,ancperson.pcucodeperson,ancperson.pregno) as anc2,
(select DATE_FORMAT(MAX(v3.datecheck),'%Y-%m-%d') from visitanc v3 inner JOIN visitancpregnancy p3 on p3.pcucodeperson = v3.pcucodeperson and v3.pid = p3.pid and p3.pregno = v3.pregno where v3.pid = ancperson.pid and v3.pcucodeperson = ancperson.pcucodeperson and DATEDIFF(v3.datecheck,p3.lmp) between 168 AND 202 and v3.pregno = ancperson.pregno group by ancperson.pid,ancperson.pcucodeperson,ancperson.pregno) as anc3,
(select DATE_FORMAT(MAX(v4.datecheck),'%Y-%m-%d') from visitanc v4 inner JOIN visitancpregnancy p4 on p4.pcucodeperson = v4.pcucodeperson and v4.pid = p4.pid and p4.pregno = v4.pregno where v4.pid = ancperson.pid and v4.pcucodeperson = ancperson.pcucodeperson and DATEDIFF(v4.datecheck,p4.lmp) between 210 AND 244 and v4.pregno = ancperson.pregno group by ancperson.pid,ancperson.pcucodeperson,ancperson.pregno) as anc4,
(select DATE_FORMAT(MAX(v5.datecheck),'%Y-%m-%d') from visitanc v5 inner JOIN visitancpregnancy p5 on p5.pcucodeperson = v5.pcucodeperson and v5.pid = p5.pid and p5.pregno = v5.pregno where v5.pid = ancperson.pid and v5.pcucodeperson = ancperson.pcucodeperson and DATEDIFF(v5.datecheck,p5.lmp) between 252 AND 286 and v5.pregno = ancperson.pregno group by ancperson.pid,ancperson.pcucodeperson,ancperson.pregno) as anc5
FROM
(SELECT
a.pcucodeperson,
a.pid,
p.fname,
concat(c.titlename, p.fname , '  ' , p.lname) AS pname,
p.birth,
FLOOR(datediff('$str',p.birth)/365.25) as age,
h.hno,
h.villcode,
h.xgis,
h.ygis,
a.pregno,
ap.lmp,
DATEDIFF('$sto',ap.lmp) as page
FROM
house as h
INNER JOIN person as p on p.pcucodeperson = h.pcucodeperson and p.hcode = h.hcode
LEFT JOIN ctitle as c on c.titlecode = p.prename
INNER JOIN visitancpregnancy as ap on ap.pcucodeperson = p.pcucodeperson and ap.pid = p.pid 
INNER JOIN visitanc as a on ap.pcucodeperson = a.pcucodeperson and ap.pid = a.pid and ap.pregno = a.pregno
where DATEDIFF('$sto',ap.lmp) BETWEEN 286 and DATEDIFF('$sto','$str')+286 $live_type2 AND ((p.dischargetype is null) or (p.dischargetype = '9')) $wvill
GROUP BY a.pcucodeperson, a.pid, a.pregno) as ancperson) as tmp_anc
order by villcode, fname";
$result = mysql_query($sql);
$xml = '<markers>';
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	if($row[anc1] == ""){$anc1 = '---';}else{$anc1 = retDatets($row[anc1]);}
	if($row[anc2] == ""){$anc2 = '---';}else{$anc2 = retDatets($row[anc2]);}
	if($row[anc3] == ""){$anc3 = '---';}else{$anc3 = retDatets($row[anc3]);}
	if($row[anc4] == ""){$anc4 = '---';}else{$anc4 = retDatets($row[anc4]);}
	if($row[chk] == "1"){$anc_chk = 'ฝากครรภ์ครบ4ครั้งคุณภาพ';}else{$anc_chk = 'ฝากครรภ์ยังไม่ครบ4ครั้งคุณภาพ';}
	$birth = retDatets($row[birth]);
  $xml .= '<marker ';
  $xml .= 'hono="'.$row[hno].'" ';
  $xml .= 'moo="'.$moo.'" ';
  $xml .= 'vill="'.$vill.'" ';
  $xml .= 'pname="'.$row[pname].'" ';
  $xml .= 'birth="'.$birth.'" ';
  $xml .= 'age="'.$row[age].'" ';
  $xml .= 'anc1="'.$anc1.'" ';
  $xml .= 'anc2="'.$anc2.'" ';
  $xml .= 'anc3="'.$anc3.'" ';
  $xml .= 'anc4="'.$anc4.'" ';
  $xml .= 'anc5="'.$anc5.'" ';
  $xml .= 'anc_chk="'.$anc_chk.'" ';
  $xml .= 'pregno="'.$row[pregno].'" ';
  $xml .= 'chk="'.$row[chk].'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= '/>';
}
$xml .= '</markers>';
echo $xml;
?>