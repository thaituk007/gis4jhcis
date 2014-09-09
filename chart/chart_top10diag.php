<?php
session_start();
if($_SESSION[username]){
include("includes/conndb.php"); 
include("includes/config.inc.php"); 
include("includes/FusionCharts.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $titleweb; ?></title>
<link href="css/style.css" rel="stylesheet" type="text/css" />
<link type="text/css" href="css/ui-lightness/jquery-ui-1.8.1.custom.css" rel="stylesheet" />
<script type="text/javascript" src="js/jquery-1.4.2.min.js"></script>
<SCRIPT LANGUAGE="Javascript" SRC="FusionCharts/FusionCharts.js"></SCRIPT>
<script type="text/javascript" src="js/jquery-ui-1.8.1.offset.datepicker.min.js"></script>
<script language=Javascript>
$(function(){
		  $("#datepicker-th1").datepicker({ dateFormat: 'dd/mm/yy', yearOffset: 543, defaultDate: '<?php echo $daysdatepick; ?>'});
		  $("#datepicker-th2").datepicker({ dateFormat: 'dd/mm/yy', yearOffset: 543, defaultDate: '<?php echo $daysdatepick; ?>'});
		});
  $(document).ready(function(){
	$("#btn1").click(function(){
    $("#div1").html("<center><br><br><br><br><br><img src=\"images/loader.gif\" alt=\"Loading...\"/></center>"); 
    	//Enable loading image after submin form    
    	var formdata = $(this).serialize();
    	$.get("chart_top10diag.php", {
			str: $("#datepicker-th1").val(),
			sto: $("#datepicker-th2").val()}, 
				function(data){
					$("#div1").html(data);
				}
			);
    return false;
	});
	
	});
</script>
</head>

<body>
<?php 
    $animateChart = $_GET['animate'];
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
    $strXML = "<chart caption='10 อันดับโรค ข้อมูลระหว่างวันที่ ".$_GET[str]." - ".$_GET[sto]."' subCaption='".$hosp."' pieSliceDepth='30' showBorder='1' formatNumberScale='0' numberSuffix='ต่อพันประชากร' animation=' " . $animateChart . "'>";
$str = retDate($_GET[str]);
$sto = retDate($_GET[sto]);
	$sql = "SELECT
cdisease.diseasename,
count(distinct visit.visitno)/(select count(p.pid) from person p where p.typelive <> '4')*1000 as cdiagcode
FROM
visit
INNER JOIN visitdiag ON visit.pcucode = visitdiag.pcucode AND visit.visitno = visitdiag.visitno
INNER JOIN cdisease ON visitdiag.diagcode = cdisease.diseasecode
where visit.visitdate between '$str' and '$sto' and (visit.flagservice <'04' OR visit.flagservice is null OR length(trim(visit.flagservice))=0 ) and visitdiag.diagcode not like 'Z%' and visitdiag.conti <> '1'
group by visitdiag.diagcode
order by count(distinct visit.visitno) DESC
limit 10";
$result = mysql_query($sql);
  if ($result) {
        while($ors2 = mysql_fetch_array($result)) {
            $strXML .= "<set label='" . $ors2['diseasename'] . "' value='" . $ors2['cdiagcode'] . "' />";
        }
    }
    mysql_close($link);
    $strXML .= "</chart>";
	echo renderChart("FusionCharts/Bar2D.swf", "", $strXML, "FactorySum", 750, 450, false, false);
?>
<?php
}
else{
		header("Location: login.php");
		}
		?>
        
</body>
</html>