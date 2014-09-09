<?php
//We've included ../Includes/FusionCharts.php and ../Includes/DBConn.php, which contains
//functions to help us easily embed the charts and connect to a database.
include("includes/FusionCharts.php");
include("includes/conndb.php");
include("includes/config.inc.php");
$dx = date("md");
$yx = date("Y");
$yy = date("Y")-1;
if($dx > "1001"){$daymidyear = $yx."-10-01";}else{$daymidyear = $yy."-10-01";}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<HTML>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

        <title><?php echo $titleweb; ?></title>
        <!--[if IE 6]>
        <script>
                <script type="text/javascript" src="../assets/ui/js/DD_belatedPNG_0.0.8a-min.js"></script>
          /* select the element name, css selector, background etc */
          DD_belatedPNG.fix('img');

          /* string argument can be any CSS selector */
        </script>
        <![endif]-->

        <style type="text/css">
            h2.headline {
                font: normal 110%/137.5% "Trebuchet MS", Arial, Helvetica, sans-serif;
                padding: 0;
                margin: 25px 0 25px 0;
                color: #7d7c8b;
                text-align: center;
            }
            p.small {
                font: normal 68.75%/150% Verdana, Geneva, sans-serif;
                color: #919191;
                padding: 0;
                margin: 0 auto;
                width: 664px;
                text-align: center;
            }
        </style>
        <?php
        //You need to include the following JS file, if you intend to embed the chart using JavaScript.
        //Embedding using JavaScripts avoids the "Click to Activate..." issue in Internet Explorer
        //When you make your own charts, make sure that the path to this JS file is correct. Else, you
        //would get JavaScript errors.
        ?>
        <SCRIPT LANGUAGE="Javascript" SRC="FusionCharts/FusionCharts.js"></SCRIPT>

    </head>
    <BODY>
                        <?php
                        //In this example, we show how to connect FusionCharts to a database.
                        //For the sake of ease, we've used an MySQL databases containing two
                        //tables.

                        // Connect to the DB
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


                        // SQL query for category labels
                        $strQueryCategories = "SELECT
village.villname as villname
FROM
village
WHERE
right(village.villcode,2) <>  '00' and village.villname is not null
order by village.villcode";

                        // Query database
                        $resultCategories = mysql_query($strQueryCategories) or die(mysql_error());
$village = $_GET[village];
if($village == "00000000"){
	$wvill = "";
}else{
	$wvill = " and h.villcode='$village' ";	
}
if($village == "00000000"){
	$mu = "ทุกหมู่บ้าน";
}else{
	$mu = getvillagename($village);	
}
$getage = $_GET[getage];
if($getage == "35"){
	$gage = "AND FLOOR((TO_DAYS('$daymidyear')-TO_DAYS(p.birth))/365.25) between 30 and 39";
}elseif($getage == "20"){
	$gage = "AND FLOOR((TO_DAYS('$daymidyear')-TO_DAYS(p.birth))/365.25) < 30";
}elseif($getage == "30"){
	$gage = "AND FLOOR((TO_DAYS('$daymidyear')-TO_DAYS(p.birth))/365.25) > 29";
}elseif($getage == "40"){
	$gage = "AND FLOOR((TO_DAYS('$daymidyear')-TO_DAYS(p.birth))/365.25) > 39";
}else{
	$gage = "";
}
if($getage == "35"){
	$gagename = "อายุ 30 - 39 ปี";
}elseif($getage == "20"){
	$gagename = "อายุต่ำกว่า 30 ปี";
}elseif($getage == "30"){
	$gagename = "อายุ 30 ปี ขึ้นไป";
}elseif($getage == "40"){
	$gagename = "อายุ 40 ปี ขึ้นไป";
}else{
	$gagename = "ทั้งหมด";
}
$str = retDate($_GET[str]);
$sto = retDate($_GET[sto]);
$live_type = $_GET[live_type];
if($live_type == '2'){$live_type2 = "p.typelive in ('0','1','2') and";}elseif($live_type == '1'){$live_type2 = "p.typelive in ('0','1','3') and";}else{$live_type2 = "p.typelive in ('0','1','2','3') and";}
if($live_type == '2'){$live_type_name = "ตามทะเบียนบ้าน(0,1,2)";}elseif($live_type == '1'){$live_type_name = "ที่อาศัยอยู่จริง (0,1,3)";}else{$live_type_name = "ทั้งหมดในเขตรับผิดชอบ(0,1,2,3)";}	
                        // SQL query for factory output data
                        $strQueryData =  "select
pcucodeperson,
villcode,
villname,
'$gagename' as mark,
count(distinct pid) as per
from
(SELECT
p.pcucodeperson,
p.pid,
p.fname,
CONCAT(ctitle.titlename,p.fname,' ',p.lname) AS pname,
h.hno,
h.villcode,
village.villname,
h.xgis,
h.ygis,
p.birth,
FLOOR((TO_DAYS('$daymidyear')-TO_DAYS(p.birth))/365.25) AS age
FROM
village
INNER JOIN house as h ON village.pcucode = h.pcucode AND village.villcode = h.villcode
Inner Join person AS p ON h.pcucode = p.pcucodeperson AND h.hcode = p.hcode
Inner Join cstatus ON p.marystatus = cstatus.statuscode
Inner Join ctitle ON p.prename = ctitle.titlecode
WHERE $live_type2 ((p.dischargetype is null) or (p.dischargetype = '9')) AND
				SUBSTRING(h.villcode,7,2) <> '00' $gage $wvill ORDER BY h.villcode,h.hno*1
) as per
left join 
(SELECT
person.pcucodeperson as pcucodeperson1,
person.pid as pid1,
visit.visitno as visitno1,
visit.symptoms,
visit.vitalcheck,
visitdiag.diagcode,
visit.visitdate as visitdate
FROM
village
INNER JOIN house ON village.pcucode = house.pcucode AND village.villcode = house.villcode
INNER JOIN person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
INNER JOIN visit ON person.pcucodeperson = visit.pcucodeperson AND person.pid = visit.pid
INNER JOIN visitdiag ON visit.pcucode = visitdiag.pcucode AND visit.visitno = visitdiag.visitno
where visit.visitdate between '$str' and '$sto' and ((person.dischargetype is null) or (person.dischargetype = '9')) AND
				SUBSTRING(house.villcode,7,2) <> '00' and visitdiag.diagcode = 'Z11.6' and (visit.flagservice <'04' OR visit.flagservice is null OR length(trim(visit.flagservice))=0 )
) as fp
on per.pcucodeperson = fp.pcucodeperson1 and per.pid = fp.pid1
left join
(SELECT
person.pcucodeperson as pcucodeperson2,
person.pid as pid2,
visit.visitno as visitno2,
visitdiag.diagcode as para,
cdisease.diseasenamethai as diseasenamethai,
cdisease.diseasename as diseasename
FROM
village
INNER JOIN house ON village.pcucode = house.pcucode AND village.villcode = house.villcode
INNER JOIN person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
INNER JOIN visit ON person.pcucodeperson = visit.pcucodeperson AND person.pid = visit.pid
INNER JOIN visitdiag ON visit.pcucode = visitdiag.pcucode AND visit.visitno = visitdiag.visitno
inner join cdisease on visitdiag.diagcode = cdisease.diseasecode
where visit.visitdate between '$str' and '$sto' and ((person.dischargetype is null) or (person.dischargetype = '9')) AND
				SUBSTRING(house.villcode,7,2) <> '00' and visitdiag.diagcode != 'Z11.6' and (visit.flagservice <'04' OR visit.flagservice is null OR length(trim(visit.flagservice))=0 ) 
) as para
on para.pcucodeperson2 = fp.pcucodeperson1 and para.pid2 = fp.pid1 and para.visitno2 = fp.visitno1
group by pcucodeperson, villcode
union
select
pcucodeperson,
villcode,
villname,
'ได้รับการตรวจ' as mark,
count(distinct pid1) as per
from
(SELECT
p.pcucodeperson,
p.pid,
p.fname,
CONCAT(ctitle.titlename,p.fname,' ',p.lname) AS pname,
h.hno,
h.villcode,
village.villname,
h.xgis,
h.ygis,
p.birth,
FLOOR((TO_DAYS(NOW())-TO_DAYS(p.birth))/365.25) AS age
FROM
village
INNER JOIN house as h ON village.pcucode = h.pcucode AND village.villcode = h.villcode
Inner Join person AS p ON h.pcucode = p.pcucodeperson AND h.hcode = p.hcode
Inner Join cstatus ON p.marystatus = cstatus.statuscode
Inner Join ctitle ON p.prename = ctitle.titlecode
WHERE $live_type2 ((p.dischargetype is null) or (p.dischargetype = '9')) AND
				SUBSTRING(h.villcode,7,2) <> '00' $gage $wvill ORDER BY h.villcode,h.hno*1
) as per
left join 
(SELECT
person.pcucodeperson as pcucodeperson1,
person.pid as pid1,
visit.visitno as visitno1,
visit.symptoms,
visit.vitalcheck,
visitdiag.diagcode,
visit.visitdate as visitdate
FROM
village
INNER JOIN house ON village.pcucode = house.pcucode AND village.villcode = house.villcode
INNER JOIN person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
INNER JOIN visit ON person.pcucodeperson = visit.pcucodeperson AND person.pid = visit.pid
INNER JOIN visitdiag ON visit.pcucode = visitdiag.pcucode AND visit.visitno = visitdiag.visitno
where visit.visitdate between '$str' and '$sto' and ((person.dischargetype is null) or (person.dischargetype = '9')) AND
				SUBSTRING(house.villcode,7,2) <> '00' and visit.vitalcheck like '%พบ%' and visitdiag.diagcode = 'Z11.6' and (visit.flagservice <'04' OR visit.flagservice is null OR length(trim(visit.flagservice))=0 )
) as fp
on per.pcucodeperson = fp.pcucodeperson1 and per.pid = fp.pid1
left join
(SELECT
person.pcucodeperson as pcucodeperson2,
person.pid as pid2,
visit.visitno as visitno2,
visitdiag.diagcode as para,
cdisease.diseasenamethai as diseasenamethai,
cdisease.diseasename as diseasename
FROM
village
INNER JOIN house ON village.pcucode = house.pcucode AND village.villcode = house.villcode
INNER JOIN person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
INNER JOIN visit ON person.pcucodeperson = visit.pcucodeperson AND person.pid = visit.pid
INNER JOIN visitdiag ON visit.pcucode = visitdiag.pcucode AND visit.visitno = visitdiag.visitno
inner join cdisease on visitdiag.diagcode = cdisease.diseasecode
where visit.visitdate between '$str' and '$sto' and ((person.dischargetype is null) or (person.dischargetype = '9')) AND
				SUBSTRING(house.villcode,7,2) <> '00' and visit.vitalcheck like 'พบ' and visitdiag.diagcode != 'Z11.6' and (visit.flagservice <'04' OR visit.flagservice is null OR length(trim(visit.flagservice))=0 ) 
) as para
on para.pcucodeperson2 = fp.pcucodeperson1 and para.pid2 = fp.pid1 and para.visitno2 = fp.visitno1
group by pcucodeperson, villcode
union
select
pcucodeperson,
villcode,
villname,
'ได้ตรวจพยาธิใบไม้ตับ' as mark,
sum(case when para between 'B66.0' and 'B66.3' then 1 else 0 end) as per
from
(SELECT
p.pcucodeperson,
p.pid,
p.fname,
CONCAT(ctitle.titlename,p.fname,' ',p.lname) AS pname,
h.hno,
h.villcode,
village.villname,
h.xgis,
h.ygis,
p.birth,
FLOOR((TO_DAYS('$daymidyear')-TO_DAYS(p.birth))/365.25) AS age
FROM
village
INNER JOIN house as h ON village.pcucode = h.pcucode AND village.villcode = h.villcode
Inner Join person AS p ON h.pcucode = p.pcucodeperson AND h.hcode = p.hcode
Inner Join cstatus ON p.marystatus = cstatus.statuscode
Inner Join ctitle ON p.prename = ctitle.titlecode
WHERE $live_type2 ((p.dischargetype is null) or (p.dischargetype = '9')) AND
				SUBSTRING(h.villcode,7,2) <> '00' $gage $wvill ORDER BY h.villcode,h.hno*1
) as per
left join 
(SELECT
person.pcucodeperson as pcucodeperson1,
person.pid as pid1,
visit.visitno as visitno1,
visit.symptoms,
visit.vitalcheck,
visitdiag.diagcode,
visit.visitdate as visitdate
FROM
village
INNER JOIN house ON village.pcucode = house.pcucode AND village.villcode = house.villcode
INNER JOIN person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
INNER JOIN visit ON person.pcucodeperson = visit.pcucodeperson AND person.pid = visit.pid
INNER JOIN visitdiag ON visit.pcucode = visitdiag.pcucode AND visit.visitno = visitdiag.visitno
where visit.visitdate between '$str' and '$sto' and ((person.dischargetype is null) or (person.dischargetype = '9')) AND
				SUBSTRING(house.villcode,7,2) <> '00' and visit.vitalcheck like '%พบ%' and visitdiag.diagcode = 'Z11.6' and (visit.flagservice <'04' OR visit.flagservice is null OR length(trim(visit.flagservice))=0 )
) as fp
on per.pcucodeperson = fp.pcucodeperson1 and per.pid = fp.pid1
left join
(SELECT
person.pcucodeperson as pcucodeperson2,
person.pid as pid2,
visit.visitno as visitno2,
visitdiag.diagcode as para,
cdisease.diseasenamethai as diseasenamethai,
cdisease.diseasename as diseasename
FROM
village
INNER JOIN house ON village.pcucode = house.pcucode AND village.villcode = house.villcode
INNER JOIN person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
INNER JOIN visit ON person.pcucodeperson = visit.pcucodeperson AND person.pid = visit.pid
INNER JOIN visitdiag ON visit.pcucode = visitdiag.pcucode AND visit.visitno = visitdiag.visitno
inner join cdisease on visitdiag.diagcode = cdisease.diseasecode
where visit.visitdate between '$str' and '$sto' and ((person.dischargetype is null) or (person.dischargetype = '9')) AND
				SUBSTRING(house.villcode,7,2) <> '00' and visit.vitalcheck like 'พบ' and visitdiag.diagcode != 'Z11.6' and (visit.flagservice <'04' OR visit.flagservice is null OR length(trim(visit.flagservice))=0 ) 
) as para
on para.pcucodeperson2 = fp.pcucodeperson1 and para.pid2 = fp.pid1 and para.visitno2 = fp.visitno1
group by pcucodeperson, villcode";
						// Query database
                        $resultData = mysql_query($strQueryData) or die(mysql_error());

                        //We also keep a flag to specify whether we've to animate the chart or not.
                        //If the user is viewing the detailed chart and comes back to this page, he shouldn't
                        //see the animation again.
                        $animateChart = @$_GET['animate'];
                        //Set default value of 1
                        if ($animateChart=="")
                            $animateChart = "1";

                        //$strXML will be used to store the entire XML document generated
                        //Generate the chart element
                        $strXML = "<chart legendPostion='' caption='ประชาชน".$gagename."ที่ได้รับการตรวจหาไข่พยาธิในอุจจาระ ข้อมูลระหว่างวันที่ ".$_GET[str]." ถึง ".$_GET[sto]."  ".$mu ."  ".$live_type_name."' subCaption='".$hosp."' xAxisName='' yAxisName='คน' showValues='1' formatNumberScale='0' rotateValues='1' animation=' " . $animateChart . "'>";

                        // Build category XML
                        $strXML .= buildCategories ($resultCategories, "villname");

                        // Build datasets XML
                        $strXML .= buildDatasets ( $resultData, "per", "mark");

                        //Finally, close <chart> element
                        $strXML .= "</chart>";


                        //Create the chart - Pie 3D Chart with data from strXML
                        echo renderChart("FusionCharts/MSColumn2D.swf", "", $strXML, "FactorySum", 750, 450, false, false);


                        // Free database resource
                        mysql_free_result($resultCategories);
                        mysql_free_result($resultData);
                        mysql_close($link);


                        /***********************************************************************************************
	 * Function to build XML for categories
	 * @param	$result 			Database resource
	 * @param 	$labelField 	Field name as String that contains value for chart category labels
	 *
	 *	@return categories XML node
                         */
                        function buildCategories ( $result, $labelField ) {
                            $strXML = "";
                            if ($result) {
                                $strXML = "<categories>";
                                while($ors = mysql_fetch_array($result)) {
                                    $strXML .= "<category label='" . $ors[$labelField]. "'/>";
                                }
                                $strXML .= "</categories>";
                            }
                            return $strXML;
                        }

                        /***********************************************************************************************
	 * Function to build XML for datesets that would contain chart data
	 * @param	$result 			Database resource. The data should come ordered by a control break
	 									field which would require to identify datasets and set its value to
										dataset's series name
	 * @param 	$valueField 	Field name as String that contains value for chart dataplots
	 * @param 	$controlBreak 	Field name as String that contains value for chart dataplots
	 *
	 *	@return 						Dataset XML node
                         */
                        function buildDatasets ($result, $valueField, $controlBreak ) {
                            $strXML = "";
                            if ($result) {

                                $controlBreakValue ="";

                                while( $ors = mysql_fetch_array($result) ) {

                                    if( $controlBreakValue != $ors[$controlBreak] ) {
                                        $controlBreakValue =  $ors[$controlBreak];
                                        $strXML .= ( $strXML =="" ? "" : "</dataset>") . ( "<dataset seriesName='" . $controlBreakValue . "'>" ) ;
                                    }
                                    $strXML .= "<set value='" . $ors[$valueField] . "'/>";

                                }
                                $strXML .= "</dataset>";
                            }
                            return $strXML;

                        }

                        ?>
    </BODY>
</HTML>