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
$str = retdaterangstr($_GET[str]);
$sto = retdaterangsto($_GET[str]);
$strx = retDatets($str);
$stox = retDatets($sto);
$villcode = $_GET[village];
if($villcode == "00000000"){
	$wvill = "";
}else{
	$wvill = " AND house.villcode='$villcode' ";	
}
$chk_stool = $_GET[chk_stool];
if($chk_stool == "1"){
	$chksto = "";
}elseif($chk_stool == "2"){
	$chksto = "and cancer.result is not null";
}elseif($chk_stool == "3"){
	$chksto = "and cancer.result in ('1','2','5','6','9')";		
}else{
	$chksto = "and cancer.result is null";		
}
$live_type = $_GET[live_type];
if($live_type == '2'){$live_type2 = "and person.typelive in ('0','1','2')";}elseif($live_type == '1'){$live_type2 = "and person.typelive in ('0','1','3')";}else{$live_type2 = "and person.typelive in ('0','1','2','3')";}
if($live_type == '2'){$live_type_name = "ตามทะเบียนบ้าน(0,1,2)";}elseif($live_type == '1'){$live_type_name = "ที่อาศัยอยู่จริง (0,1,3)";}else{$live_type_name = "ทั้งหมดในเขตรับผิดชอบ(0,1,2,3)";}	
$getage = $_GET[getage];
if($getage == "1"){
	$gage = "AND getAgeYearNum(person.birth,'$str') between 30 and 60";
}elseif($getage == "2"){
	$gage = "AND getAgeYearNum(person.birth,'$str') < 30";
}elseif($getage == "3"){
	$gage = "AND getAgeYearNum(person.birth,'$str') > 60";
}else{
	$gage = "";
}
if($getage == "1"){
	$gagename = "อายุ 30 - 60 ปี";
}elseif($getage == "2"){
	$gagename = "อายุต่ำกว่า 30 ปี";
}elseif($getage == "3"){
	$gagename = "อายุ 60 ปี ขึ้นไป";
}else{
	$gagename = "ทั้งหมด";
}
    //Generate the chart element
    $strXML = "<chart caption='ร้อยละประชาชนอายุ 30-60 ที่ได้รับการตรวจคัดกรองมะเร็งปากมดลูก ระหว่าง&nbsp;".$str."&nbsp;ถึง&nbsp;".$str." ".$live_type_name." ".$mu."' subCaption='".$hosp."' pieSliceDepth='30' showBorder='1' formatNumberScale='0' numberSuffix='' animation=' " . $animateChart . "'>";

    // Fetch all factory records
//    $strQuery = "select * from Factory_Master";	
 //   $result = mysql_query($strQuery) or die(mysql_error());
 
	$sql = "select
house.villcode,
village.villname,
sum(case when getageyearnum(person.birth,'$str') between 30 and 60 and cancer.pid is not null then 1 else 0 end)/sum(case when getageyearnum(person.birth,'$str') between 30 and 60 then 1 else 0 end)*100 as percent
from person 
inner join house on person.hcode = house.hcode and person.pcucodeperson = house.pcucode
inner join village on house.villcode = village.villcode and village.villno <>'0'
left join ctitle on person.prename = ctitle.titlecode
left join (select visit.visitno,visit.pid,visitlabcancer.datecheck as datecheck,visitlabcancer.typecancer, visitlabcancer.result,
visitlabcancer.hosservice,
visitlabcancer.hoslab
from visit inner join visitlabcancer on visit.visitno = visitlabcancer.visitno and visit.pcucode = visitlabcancer.pcucode
where visitlabcancer.typecancer in ('2','3') and visitlabcancer.datecheck between '$str' and '$sto'
group by visitlabcancer.pid)cancer on person.pid = cancer.pid 
where ((person.dischargetype is null) or (person.dischargetype = '9')) and right(house.villcode,2) <> '00' and person.sex = '2' $live_type2
group by house.villcode
order by house.villcode";
$result = mysql_query($sql); 
    //Iterate through each factory
  if ($result) {
        while($ors2 = mysql_fetch_array($result)) {
			$percent1 = number_format($ors2[percent], 2, '.', '');
            $strXML .= "<set label='" . $ors2['villname'] . "' value='" . $percent1 . "' />";
        }
    }
    mysql_close($link);

    //Finally, close <chart> element
    $strXML .= "</chart>";

    //Create the chart - Pie 3D Chart with data from strXML
if($ect2 == ''){
	echo renderChart("FusionCharts/Column2D.swf", "", $strXML, "FactorySum", 750, 450, false, false);
}else{
	echo renderChart("FusionCharts/Pie2D.swf", "", $strXML, "FactorySum", 750, 450, false, false);
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
