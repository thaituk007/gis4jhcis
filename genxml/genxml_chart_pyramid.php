<?php 
header("Content-type: text/xml");
include("../includes/conndb.php"); 
include("../includes/config.inc.php"); 
function getvillagenamenomoo($villname){
		$sql ="SELECT village.villname AS n FROM village where village.villcode = '$villname'";
		$result=mysql_query($sql);
		$row=mysql_fetch_array($result);
		$ret = $row[n];
		return $ret;
}
$villcode = $_GET[village];
if($villcode == "00000000"){
	$wvill = "";
}else{
	$wvill = " and h.villcode = '$villcode'";	
}
if($villcode == "00000000"){
	$mu = "ทุกหมู่บ้าน";
}else{
	$mu = getvillagename($villcode);	
}
$str = $_GET[year]."-07-01";
$sto = $_GET[year]+543;
//ตัวแปร array ที่ใ้ชสำหรับแสดงกราฟ
	$detail = array(); // ตัวแปรแกน x
	
	//ตัวแปรแกน y
	$male = array();
	
	$female = array();
	//หมดตัวแปรแกน y

//sql สำหรับดึงข้อมูล จาก jhcis
$sql = "SELECT
case when FLOOR(datediff('$str',p.birth)/365.25) between 0 and 4 then 'อายุ 0 - 4 ปี' 
     when FLOOR(datediff('$str',p.birth)/365.25) between 5 and 9 then 'อายุ 5 - 9 ปี'
     when FLOOR(datediff('$str',p.birth)/365.25) between 10 and 14 then 'อายุ 10 - 14 ปี' 
     when FLOOR(datediff('$str',p.birth)/365.25) between 15 and 19 then 'อายุ 15 - 19 ปี' 
     when FLOOR(datediff('$str',p.birth)/365.25) between 20 and 24 then 'อายุ 20 - 24 ปี' 
     when FLOOR(datediff('$str',p.birth)/365.25) between 25 and 29 then 'อายุ 25 - 29 ปี' 
     when FLOOR(datediff('$str',p.birth)/365.25) between 30 and 34 then 'อายุ 30 - 34 ปี' 
     when FLOOR(datediff('$str',p.birth)/365.25) between 35 and 39 then 'อายุ 35 - 39 ปี' 
     when FLOOR(datediff('$str',p.birth)/365.25) between 40 and 44 then 'อายุ 40 - 44 ปี' 
     when FLOOR(datediff('$str',p.birth)/365.25) between 45 and 49 then 'อายุ 45 - 49 ปี' 
     when FLOOR(datediff('$str',p.birth)/365.25) between 50 and 54 then 'อายุ 50 - 54 ปี' 
     when FLOOR(datediff('$str',p.birth)/365.25) between 55 and 59 then 'อายุ 55 - 59 ปี' 
     when FLOOR(datediff('$str',p.birth)/365.25) between 60 and 64 then 'อายุ 60 - 64 ปี' 
     when FLOOR(datediff('$str',p.birth)/365.25) between 65 and 69 then 'อายุ 65 - 69 ปี' 
     when FLOOR(datediff('$str',p.birth)/365.25) between 70 and 74 then 'อายุ 70 - 74 ปี' 
     when FLOOR(datediff('$str',p.birth)/365.25) between 75 and 79 then 'อายุ 75 - 79 ปี' 
     when FLOOR(datediff('$str',p.birth)/365.25) between 80 and 120 then 'อายุ 80 ปีขึ้นไป'  else null end as detail,
count(DISTINCT if(p.sex = '1',concat(p.pcucodeperson,p.pid),null)) as male,
count(DISTINCT if(p.sex = '2',concat(p.pcucodeperson,p.pid),null))*-1 as female,
count(DISTINCT concat(p.pcucodeperson,p.pid)) as perall
FROM
chospital as chp
Inner Join house as h on h.pcucode = chp.hoscode
Inner Join person as p ON h.pcucode = p.pcucodeperson AND h.hcode = p.hcode
left join persondeath as pd on p.pcucodeperson = pd.pcucodeperson and p.pid = pd.pid
WHERE (((p.dischargetype is null) or (p.dischargetype = '9')) or DATE_FORMAT(pd.deaddate,'%Y') <= DATE_FORMAT('$str','%Y')) and DATE_FORMAT(p.birth,'%Y') <= DATE_FORMAT('$str','%Y') 
and p.typelive <> '4' and p.birth is not NULL and p.birth <= '$str' $wvill
group by detail
ORDER BY SUBSTRING(detail,6,2)*1 desc";
//จบ sql
$result = mysql_query($sql);
while($row=mysql_fetch_array($result)) {
//array_push คือการนำค่าที่ได้จาก sql ใส่เข้าไปตัวแปร array
	array_push($male,$row[male]);
	array_push($female,$row[female]);
	array_push($detail,$row[detail]);
}
$catigory = "<item>".implode("</item><item>", $detail)."</item>";
$male = "<point>".implode("</point><point>", $male)."</point>";
$female = "<point>".implode("</point><point>", $female)."</point>";

$xml = '<chart>';
$xml .= '<categories>';
$xml .= $catigory;
$xml .= '</categories>';

$xml .= '<series>';
$xml .= '<name>ชาย</name>';
$xml .= '<data>';
$xml .= $male;
$xml .= '</data>';
$xml .= '</series>';
$xml .= '<series>';
$xml .= '<name>หญิง</name>';
$xml .= '<data>';
$xml .= $female;
$xml .= '</data>';
$xml .= '</series>';
$xml .= '</chart>';
echo $xml;
?>