<?php
session_start();
set_time_limit(0);
if($_SESSION[username]){
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
include("includes/conndb.php"); 
include("includes/config.inc.php");
include("includes/FusionCharts.php"); 
?>
<?php
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

    //$strXML will be used to store the entire XML document generated
    //Generate the chart element
    $strXML = "<chart caption='แผนภูมิแสดงร้อยละประชากรกลุ่มเป้าหมายที่ได้รับการฉีดวัคซีน dT   ระหว่างวันที่  ".$_GET[str]." ถึงวันที่ ".$_GET[str]."' subCaption='".$hosp."' pieSliceDepth='30' showBorder='1' formatNumberScale='0' numberSuffix='' animation=' " . $animateChart . "'>";

    // Fetch all factory records
//    $strQuery = "select * from Factory_Master";	
 //   $result = mysql_query($strQuery) or die(mysql_error());
$villcode = $_GET[village];
if($villcode == "00000000"){
	$wvill = "";
}else{
	$wvill = " AND house.villcode='$villcode' ";	
}
$str = retDate($_GET[str]);
$sto = retDate($_GET[sto]);
$ovyear = substr($sto,0,4);
$sql = "SELECT
village.villcode,
village.villname,
(select count(distinct p.pid) from house h inner join person p on p.pcucodeperson = h.pcucode and p.hcode = h.hcode where h.villcode = house.villcode and ((p.dischargetype is null) or (p.dischargetype = '9')) and (p.typelive = '1' or p.typelive = '0' or p.typelive = '3')) as peru,
(select count(distinct p.pid) from house h inner join person p on p.pcucodeperson = h.pcucode and p.hcode = h.hcode INNER JOIN visitepi ve ON p.pcucodeperson = ve.pcucodeperson AND p.pid = ve.pid where h.villcode = house.villcode and ((p.dischargetype is null) or (p.dischargetype = '9')) and (p.typelive = '1' or p.typelive = '0' or p.typelive = '3') and ve.vaccinecode in ('dT1','dTs1') and ve.dateepi between '$str' and '$sto') as perepi
FROM
village
INNER JOIN house ON village.pcucode = house.pcucode AND village.villcode = house.villcode
where village.villcode is not null $wvill
group by village.villcode";

$result = mysql_query($sql);

    //Iterate through each factory
  if ($result) {
        while($ors2 = mysql_fetch_array($result)) {
			if($ors2[peru] == "0"){
			$percen = "0";
			}else{
			$percen = ($ors2[perepi])/($ors2[peru])*100;	
			}
			$percent1 = number_format($percen, 2, '.', '');
            $strXML .= "<set label='" . $ors2['villname'] . "' value='" . $percent1 . "' />";
        }
    }
    mysql_close($link);

    //Finally, close <chart> element
    $strXML .= "</chart>";

    //Create the chart - Pie 3D Chart with data from strXML
    echo renderChart("FusionCharts/Column2D.swf", "", $strXML, "FactorySum", 750, 450, false, false);
?>
<?php
}
else{
		header("Location: login.php");
		}
		?>
</body>
</html>
