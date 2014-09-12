<?php
session_start();
set_time_limit(0);
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
	$wvill = " and h.villcode='$villcode' ";	
}
if($villcode == "00000000"){
	$mu = "ทุกหมู่บ้าน";
}else{
	$mu = substr($_GET[village],6,2);	
}
$str = retDate($_GET[str]);
$sto = retDate($_GET[sto]);
    //Generate the chart element
    $strXML = "<chart caption='รายงาน 21 เฟ้ม แฟ้ม EPI ปี 2556 ข้อมูลระหว่างวันที่".$_GET[str]." ถึงวันที่".$_GET[sto]."' subCaption='".$hosp."' pieSliceDepth='30' showBorder='1' formatNumberScale='0' numberSuffix='คน' animation=' " . $animateChart . "'>";

    // Fetch all factory records
//    $strQuery = "select * from Factory_Master";	
 //   $result = mysql_query($strQuery) or die(mysql_error());
 
	$sql = "select
tmp.pcucode,
tmp.villcode,
village.villname,
count(distinct pid) as pidva,
count(pid) as pidvac
FROM
(SELECT DISTINCT  trim(visitepi.pcucodeperson) AS pcucode, visitepi.pid, concat(ctitle.titlename,person.fname,'  ',person.lname) as pname, getAgeMonth(person.birth,CURDATE()) AS agemonth, visitepi.visitno AS seq, cdrug.files18epi AS vcctype,  cdrug.drugname, IF(visitepi.dateepi IS NULL OR TRIM(visitepi.dateepi)='' OR visitepi.dateepi LIKE '0000-00-00%','',DATE_FORMAT(visitepi.dateepi,'%Y%m%d')) AS date_serv,  visitepi.dateepi, IF(visitepi.hosservice IS NULL OR visitepi.hosservice='',trim(visitepi.pcucode),trim(visitepi.hosservice)) AS vccplace,  IF(visitepi.dateupdate IS NULL OR TRIM(visitepi.dateupdate)='' OR     visitepi.dateupdate LIKE '0000-00-00%',DATE_FORMAT(visitepi.dateepi,'%Y%m%d%H%i%s'),    DATE_FORMAT(visitepi.dateupdate,'%Y%m%d%H%i%s') ) AS d_update, visitepi.dateupdate, idcard as cid , house.hno, house.villcode, house.xgis, house.ygis
FROM 
visitepi  
join person on visitepi.pcucodeperson = person.pcucodeperson and visitepi.pid = person.pid
join house on person.pcucodeperson = house.pcucode and person.hcode = house.hcode
left join ctitle on person.prename = ctitle.titlecode  
LEFT JOIN cdrug ON (visitepi.vaccinecode=cdrug.drugcode AND cdrug.drugtype='05') 
WHERE visitepi.dateepi IS NOT NULL AND TRIM(visitepi.dateepi)<>''  AND TRIM(visitepi.pcucodeperson)<>''
AND (visitepi.dateepi >= '$str') AND (visitepi.dateepi BETWEEN '$str' AND '$sto')
ORDER BY visitepi.pcucodeperson ASC, visitepi.dateepi DESC, visitepi.visitno DESC) as tmp
inner join village on village.pcucode = tmp.pcucode and village.villcode = tmp.villcode
group by tmp.villcode";
$result = mysql_query($sql); 
    //Iterate through each factory
  if ($result) {
        while($ors2 = mysql_fetch_array($result)) {
            $strXML .= "<set label='" . $ors2['villname'] . "' value='" . $ors2['pidva'] . "' />";
        }
    }
    mysql_close($link);

    //Finally, close <chart> element
    $strXML .= "</chart>";

    //Create the chart - Pie 3D Chart with data from strXML
if($ect2 == ''){
	echo renderChart("FusionCharts/Column2D.swf", "", $strXML, "FactorySum", 750, 450, false, false);
}
?>
<?php
}
else{
		header("Location: login.php");
		}
		?>
        
</body>
</html>
