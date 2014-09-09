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
$village = $_GET[village];
if($village == "00000000"){
	$wvill = "";
}elseif($village == "xxx"){
	$wvill = " AND right(h.villcode,2)='00'";	
}else{
	$wvill = " AND h.villcode='$village'";	
}
if($village == "00000000"){
	$mu = "ทุกหมู่บ้าน";
}else{
	$mu = getvillagename($village);	
}
$str = retDate($_GET[str]);
$sto = retDate($_GET[sto]);
$ds = $_GET[ds];
	if($ds == '00'){$ect = "";}else{$ect = " dc.group506code = '$ds' AND ";}
    //Generate the chart element
    $strXML = "<chart caption='จำนวนผู้ป่วยไข้เลือดออก ข้อมูลระหว่างวันที่".$_GET[str]." ถึงวันที่".$_GET[sto]."' subCaption='".$hosp."' pieSliceDepth='30' showBorder='1' formatNumberScale='0' numberSuffix='คน' animation=' " . $animateChart . "'>";

    // Fetch all factory records
//    $strQuery = "select * from Factory_Master";	
 //   $result = mysql_query($strQuery) or die(mysql_error());
 
	$sql = "SELECT
group506name,
count(pid) as countx
from
(SELECT p.pcucodeperson, p.pid, p.idcard, CONCAT(t.titlename,p.fname,' ',p.lname) AS pname,getageyearnum(p.birth,vd.sickdatestart) as age, d.diseasecode, h.hno,h.villcode,co.occupaname, vd.sickdatestart,dc.group506name
							FROM
							house AS h
							Inner Join person AS p ON h.pcucode = p.pcucodeperson AND h.hcode = p.hcode
							Inner Join visit AS v ON p.pcucodeperson = v.pcucodeperson AND p.pid = v.pid
							Inner Join visitdiag506address AS vd ON v.pcucode = vd.pcucode AND v.visitno = vd.visitno
							Inner Join cdisease AS d ON vd.diagcode = d.diseasecode
							Inner Join cdisease506 AS dc ON d.code506 = dc.group506code
							left Join ctitle AS t ON p.prename = t.titlecode
							left join coccupa co on p.occupa = co.occupacode
							WHERE $ect vd.sickdatestart BETWEEN  '$str' AND '$sto' and dc.group506code in ('26','27','66') $wvill
							ORDER BY h.villcode,h.hno) as tmp
group by group506name
order by countx desc";
$result = mysql_query($sql); 
    //Iterate through each factory
  if ($result) {
        while($ors2 = mysql_fetch_array($result)) {
            $strXML .= "<set label='" . $ors2['group506name'] . "' value='" . $ors2['countx'] . "' />";
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
