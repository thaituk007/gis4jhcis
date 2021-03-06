<?php
session_start();
set_time_limit(0);
if($_SESSION[username]){
include("../includes/conndb.php"); 
include("../includes/config.inc.php");
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="../ico/favicon.ico">

    <title><?php echo $titleweb; ?></title>

    <!-- Bootstrap core CSS -->
    <link href="../css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="../css/style.css" rel="stylesheet">

    <!-- Just for debugging purposes. Don't actually copy this line! -->
    <!--[if lt IE 9]><script src="../../assets/js/ie8-responsive-file-warning.js"></script><![endif]-->

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

    <script src="../js/jquery.1.11.0.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
</head>
<body>
<?php
$sql = "SELECT
     concat('สถานบริการ(สถานีอนามัย/PCU): ',chospital.`hosname`,' หมู่ที่:',ifnull(chospital.`mu`,'...'),' ต.',
	ifnull(csubdistrict.`subdistname`,' ...'),' อ.',ifnull(cdistrict.`distname`,' ...'),' จ.',
	ifnull(cprovince.`provname`,'...')) AS chospital_hosname
FROM
     `chospital` chospital 
     INNER JOIN `office` office ON chospital.`hoscode` = office.`offid`
     left outer join `csubdistrict` csubdistrict ON chospital.`provcode` = csubdistrict.`provcode`
                                                        AND chospital.`distcode` = csubdistrict.`distcode`
                                                        AND chospital.`subdistcode` = csubdistrict.`subdistcode`
     left outer JOIN `cdistrict` cdistrict ON chospital.`provcode` = cdistrict.`provcode`
                                                  AND chospital.`distcode` = cdistrict.`distcode`
     INNER JOIN `cprovince` cprovince ON chospital.`provcode` = cprovince.`provcode`";

$result = mysql_query($sql);
$row=mysql_fetch_array($result);
$hosp=$row[chospital_hosname];
$villcode = $_GET[village];
if($villcode == "00000000"){
	$wvill = "AND right(house.villcode,2) <> '00'";
}elseif($villcode == "11111111"){
	$wvill = "AND right(house.villcode,2) = '00'";
}else{
	$wvill = " AND house.villcode='$villcode' ";	
}
if($villcode == "00000000"){
	$mu = "ทุกหมู่บ้าน";
}elseif($villcode == "11111111"){
	$mu = "นอกเขต";
}else{
	$mu = getvillagename($villcode);	
}
$str = retdaterangstr($_GET[str]);
$sto = retdaterangsto($_GET[str]);
$strx = retDatets($str);
$stox = retDatets($sto);
$sql = "SELECT CONVERT(concat(ifnull(tm.titlename,ifnull(person.prename,'ไม่ระบุ') ),person.fname,' ',person.lname) using utf8) as mothername,
house.villcode,
person.birth,
house.hno,
house.hcode,
house.xgis,
house.ygis
,MAX(v.pregno) pregno,v.pid,v.pcucodeperson
,DATE_FORMAT(current_date,'%Y-%m-%d') - DATE_FORMAT(person.birth,'%Y-%m-%d') as age
,if(house.hno != '' ,CONVERT(concat(house.hno,' ม.',villno) USING utf8), CONVERT(concat('- ม.',villno) USING utf8) ) as address
,CONVERT(concat(ifnull(tc.titlename,ifnull(person.prename,'ไม่ระบุ') ),pchild.fname,' ',pchild.lname) using utf8) childname
,house.pcucode
,count(distinct v.pid,v.pcucodeperson,v.pregno,v.datecare) as cdc
,case when ( v.datecare between MIN(v.datecare) and MAX(v.datecare)  and (count(distinct v.pid,v.pcucodeperson,v.pregno,v.datecare)  >=1 ) ) then MIN(v.datecare) else null end m1
,case when ( v.datecare between MIN(v.datecare) and MAX(v.datecare)  and (count(distinct v.pid,v.pcucodeperson,v.pregno,v.datecare)  >=2 ))  then v.datecare  else null end m2
,case when ( v.datecare between MIN(v.datecare) and MAX(v.datecare)  and (count(distinct v.pid,v.pcucodeperson,v.pregno,v.datecare)  >=3 ))  then MAX(v.datecare)else null end m3
,curdate() as cdate
FROM  visitancmothercare v
        left join visitancdeliverchild vchild on v.pid = vchild.pid and v.pcucodeperson = vchild.pcucodeperson
	left join visitancdeliver on v.pid  = visitancdeliver.pid  and v.pcucodeperson= visitancdeliver.pcucodeperson
	left join person   on v.pid = person.pid and v.pcucodeperson = person.pcucodeperson
	left join ctitle tm on person.prename = tm.titlecode
	left join person pchild  on pchild.pid = vchild.pidchild and pchild.pcucodeperson = vchild.pcucodechild
	left join ctitle tc on pchild.prename = tc.titlecode
	left join house on person.hcode = house.hcode and person.pcucodeperson = house.pcucode
	left join village on house.villcode = village.villcode and house.pcucode = village.pcucode

WHERE visitancdeliver.datedeliver between '$str' and '$sto' $wvill
GROUP BY  v.pcucode,v.pid,v.pcucodeperson,v.pregno
order by house.villcode,person.fname";

$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>รายงานการเยี่ยมหลังคลอด</b><br>';
$txt .= "<b>ข้อมูลระหว่างวันที่ $strx ถึง $stox หมู่ที่ $mu </b></p><br><b>$hosp</b><table width='98%' border='0' cellspacing='1' cellpadding='1' class='table table-striped table-hover table-bordered'>
  <tr>
    <th width='4%' scope='col'><div align='center'>ลำดับ</div></th>
    <th width='13%' scope='col'><div align='center'>ชื่อ - สกุล มารดา</div></th>
	<th width='8%' scope='col'><div align='center'>ว/ด/ป เกิด</div></th>
	<th width='5%' scope='col'><div align='center'>อายุ</div></th>
    <th width='7%' scope='col'><div align='center'>บ้านเลขที่</div></th>
    <th width='4%' scope='col'><div align='center'>หมู่ที่</div></th>
	<th width='4%' scope='col'><div align='center'>ครรภ์ที่</div></th>
	<th width='13%' scope='col'><div align='center'>ชื่อ - สกลุเด็ก</div></th>
	<th width='8%' scope='col'><div align='center'>เยี่ยมครั้งที่ 1</div></th>
	<th width='8%' scope='col'><div align='center'>เยี่ยมครั้งที่ 2</div></th>
	<th width='8%' scope='col'><div align='center'>เยี่ยมครั้งที่ 3</div></th>
  </tr>";
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	$birth = retDatets($row[birth]);
	if($row[m1] == ""){$m1 = '__/__/____';}else{$m1 = retDatets($row[m1]);}
	if($row[m2] == ""){$m2 = '__/__/____';}else{$m2 = retDatets($row[m2]);}
	if($row[m3] == ""){$m3 = '__/__/____';}else{$m3 = retDatets($row[m3]);}
++$i;
	if(($i%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$i</div></td>
    <td>$row[mothername]</td>
	<td><div align='center'>$birth</div></td>
	<td><div align='center'>$row[age]</div></td>
    <td><div align='center'>$row[hno]</div></td>
    <td><div align='center'>$moo</div></td>
    <td><div align='center'>$row[pregno]</div></td>
	<td>$row[childname]</td>
	<td><div align='center'>$m1</div></td>
	<td><div align='center'>$m2</div></td>
	<td><div align='center'>$m3</div></td>
  </tr>";
}
$txt .= "</table><br>";  
echo $txt;
?>
<?php
}
else{
		header("Location: ../main/login.php");
		}
		?>
        
</body>
</html>
