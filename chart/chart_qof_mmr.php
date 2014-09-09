<?php
session_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $titleweb; ?></title>
<link href="css/style.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="js/jquery-1.4.2.min.js"></script>
<SCRIPT LANGUAGE="Javascript" SRC="FusionCharts/FusionCharts.js"></SCRIPT>
<style type="text/css">
	<!--
	body {
		font-family: Arial, Helvetica, sans-serif;
		font-size: 12px;
	}
	.text{
		font-family: Arial, Helvetica, sans-serif;
		font-size: 12px;
	}
	-->
	</style>
</head>

<body>
<?php 
if($_SESSION[username]){
include("includes/conndb.php"); 
include("includes/config.inc.php"); 
include("includes/FusionCharts.php");
    //In this example, we show how to connect FusionCharts to a database.
    //For the sake of ease, we've used an MySQL databases containing two
    //tables.

    // Connect to the DB

    //We also keep a flag to specify whether we've to animate the chart or not.
    //If the user is viewing the detailed chart and comes back to this page, he shouldn't
    //see the animation again.
    $animateChart = $_GET['animate'];
    //Set default value of 1
    if ($animateChart=="")
        $animateChart = "1";

    //$strXML will be used to store the entire XML document generated
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
	$wvill = "";
}else{
	$wvill = " AND house.villcode='$villcode' ";	
}
if($villcode == "00000000"){
	$mu = "ทุกหมู่บ้าน";
}else{
	$mu = getvillagename($villcode);	
}
$chk_old = $_GET[chk_old];
if($chk_old == "8"){
	$chksto = "where vepi.pid is not null";
}elseif($chk_old == "1"){
	$chksto = "where vepi.pid is null";
}else{
	$chksto = "";	
}
$str = retDate($_GET[str]);
$sto = retDate($_GET[sto]);
$live_type = $_GET[live_type];
if($live_type == '2'){$live_type_name = "ตามทะเบียนบ้าน(0,1,2)";}elseif($live_type == '1'){$live_type_name = "ที่อาศัยอยู่จริง (0,1,3)";}else{$live_type_name = "ทั้งหมดในเขตรับผิดชอบ(0,1,2,3)";}	
if($live_type == '2'){$live_type2 = "and person.typelive in ('0','1','2')";}elseif($live_type == '1'){$live_type2 = "and person.typelive in ('0','1','3')";}else{$live_type2 = "and person.typelive in ('0','1','2','3')";}
    //Generate the chart element
    $strXML = "<chart caption='ร้อยละเด็กอายุ 1 ปี ได้รับวัคซีน MMR' subCaption='".$hosp."' pieSliceDepth='30' showBorder='1' formatNumberScale='0' numberSuffix='' animation=' " . $animateChart . "'>";

    // Fetch all factory records
//    $strQuery = "select * from Factory_Master";	
 //   $result = mysql_query($strQuery) or die(mysql_error());
 
	$sql = "SELECT
pepi.pcucodeperson,
pepi.villcode,
COUNT(DISTINCT IF(vepi.files18epi = '061', pepi.pid, NULL))/count(DISTINCT pepi.pid)*100 AS percent
FROM
(SELECT
person.pcucodeperson,
person.pid,
person.idcard,
concat(ifnull(titlename,'..') ,fname,' ',lname) as pname,
person.birth,
getagemonth(person.birth,now()) as age,
house.hno,
house.villcode,
house.xgis,
house.ygis
FROM
house
INNER JOIN person on person.pcucodeperson = house.pcucode and person.hcode = house.hcode
LEFT JOIN ctitle on ctitle.titlecode = person.prename
where person.birth BETWEEN '2012-04-01' and '2013-03-31' and person.typelive in ('0','1','3','2') and ((person.dischargetype is null) or (person.dischargetype = '9'))) as pepi
left JOIN
(SELECT
visitepi.pcucodeperson,
visitepi.pid,
visitepi.dateepi,
visitepi.vaccinecode,
visitepi.hosservice,
cdrug.files18epi
FROM
visitepi
INNER JOIN cdrug on cdrug.drugcode = visitepi.vaccinecode
where cdrug.files18epi = '061') as vepi
on pepi.pcucodeperson = vepi.pcucodeperson and pepi.pid = vepi.pid
GROUP BY pepi.pcucodeperson,pepi.villcode";
$result = mysql_query($sql); 
    //Iterate through each factory
  if ($result) {
        while($ors2 = mysql_fetch_array($result)) {
			$villname = getvillagename($ors2[villcode]);
			$percent1x = number_format($ors2['percent'], 2, '.', '');
            $strXML .= "<set label='" . $villname. "' value='" . $percent1x . "' />";
        }
    }
    mysql_close($link);

    //Finally, close <chart> element
    $strXML .= "</chart>";

    //Create the chart - Pie 3D Chart with data from strXML
	echo renderChart("FusionCharts/Column2D.swf", "", $strXML, "FactorySum", 750, 450, false, false);
?>
<?php
echo "<center><b>เด็กเกิดระหว่างวันที่ ".$_GET[str]."-".$_GET[sto]." ".$live_type_name." ".$mu."</b></center>";
}
else{
		header("Location: login.php");
		}
		?>
        
</body>
</html>
