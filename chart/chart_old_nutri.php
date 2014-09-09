<?php
//We've included ../Includes/FusionCharts.php and ../Includes/DBConn.php, which contains
//functions to help us easily embed the charts and connect to a database.
include("includes/FusionCharts.php");
include("includes/conndb.php");
include("includes/config.inc.php");
set_time_limit(0);
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
$villcode = $_GET[village];
if($villcode == "00000000"){
	$wvill = "";
}else{
	$wvill = " AND village.villcode='$villcode' ";	
}
if($villcode == "00000000"){
	$mu = "ทุกหมู่บ้าน";
}else{
	$mu = substr($_GET[village],6,2);
}
$str = retDate($_GET[str]);
$sto = retDate($_GET[sto]);

                        // SQL query for factory output data
                        $strQueryData =  "select 
tmp_per.pcucodeperson,
villcode,
villname,
'ผู้สูงอายุทั้งหมด' as mark,
sum(case when tmp_per.pid is not null then 1 else 0 end) as per
from
(select
person.pcucodeperson,
person.pid,
person.fname, 
concat(ctitle.titlename, person.fname , '  ' , person.lname) AS pname,
person.birth,
ROUND(DATEDIFF('$str',person.birth)/365.25) AS age,
village.villcode,
village.villname,
house.hno,
house.hcode,
house.xgis,
house.ygis
FROM
village
Inner Join house ON village.pcucode = house.pcucode AND village.villcode = house.villcode
Inner Join person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
Inner Join ctitle ON person.prename = ctitle.titlecode
where  FLOOR((TO_DAYS('$str')-TO_DAYS(person.birth))/365.25) >59 and  ((person.dischargetype is null) or (person.dischargetype = '9'))  and SUBSTRING(house.villcode,7,2) <> '00' $wvill) as tmp_per
left join
(select
visit.pcucodeperson,
visit.pid,
max(visit.visitdate) as m_visit,
visit.visitno,
visit.weight,
visit.height,
visit.weight/pow(visit.height/100,2) as bmi,
case when visit.weight/pow(visit.height/100,2) < 18.9 then 'ผอม' when (visit.weight/pow(visit.height/100,2) ) between 18.9 and 22.9 then 'ปกติ' when (visit.weight/pow(visit.height/100,2) ) > 22.9 then 'อ้วน' else '' end as chk
FROM
village
Inner Join house ON village.pcucode = house.pcucode and village.villcode = house.villcode
Inner Join person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
Inner Join ctitle ON person.prename = ctitle.titlecode
Inner Join visit ON person.pcucodeperson = visit.pcucodeperson AND person.pid = visit.pid
Inner Join visitdiag ON visit.pcucode = visitdiag.pcucode AND visit.visitno = visitdiag.visitno
where FLOOR((TO_DAYS('$str')-TO_DAYS(person.birth))/365.25) >59 and  ((person.dischargetype is null) or (person.dischargetype = '9'))  and SUBSTRING(house.villcode,7,2) <> '00' and visit.weight is not null and visit.height is not null
and visit.visitdate between '$str' and '$sto' and (visit.flagservice <'04' OR visit.flagservice is null OR length(trim(visit.flagservice))=0 )
group by person.pid) as tmp_ncd
ON tmp_per.pcucodeperson = tmp_ncd.pcucodeperson AND tmp_per.pid = tmp_ncd.pid
group by tmp_per.pcucodeperson,villcode
union
select 
tmp_per.pcucodeperson,
villcode,
villname,
'ชั่งน้ำหนัก' as mark,
sum(case when chk is not null then 1 else 0 end) as per
from
(select
person.pcucodeperson,
person.pid,
person.fname, 
concat(ctitle.titlename, person.fname , '  ' , person.lname) AS pname,
person.birth,
ROUND(DATEDIFF('$str',person.birth)/365.25) AS age,
village.villcode,
village.villname,
house.hno,
house.hcode,
house.xgis,
house.ygis
FROM
village
Inner Join house ON village.pcucode = house.pcucode AND village.villcode = house.villcode
Inner Join person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
Inner Join ctitle ON person.prename = ctitle.titlecode
where  FLOOR((TO_DAYS('$str')-TO_DAYS(person.birth))/365.25) >59 and  ((person.dischargetype is null) or (person.dischargetype = '9'))  and SUBSTRING(house.villcode,7,2) <> '00' $wvill) as tmp_per
left join
(select
visit.pcucodeperson,
visit.pid,
max(visit.visitdate) as m_visit,
visit.visitno,
visit.weight,
visit.height,
visit.weight/pow(visit.height/100,2) as bmi,
case when visit.weight/pow(visit.height/100,2) < 18.9 then 'ผอม' when (visit.weight/pow(visit.height/100,2) ) between 18.9 and 22.9 then 'ปกติ' when (visit.weight/pow(visit.height/100,2) ) > 22.9 then 'อ้วน' else '' end as chk
FROM
village
Inner Join house ON village.pcucode = house.pcucode and village.villcode = house.villcode
Inner Join person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
Inner Join ctitle ON person.prename = ctitle.titlecode
Inner Join visit ON person.pcucodeperson = visit.pcucodeperson AND person.pid = visit.pid
Inner Join visitdiag ON visit.pcucode = visitdiag.pcucode AND visit.visitno = visitdiag.visitno
where FLOOR((TO_DAYS('$str')-TO_DAYS(person.birth))/365.25) >59 and  ((person.dischargetype is null) or (person.dischargetype = '9'))  and SUBSTRING(house.villcode,7,2) <> '00' and visit.weight is not null and visit.height is not null
and visit.visitdate between '$str' and '$sto' and (visit.flagservice <'04' OR visit.flagservice is null OR length(trim(visit.flagservice))=0 )
group by person.pid) as tmp_ncd
ON tmp_per.pcucodeperson = tmp_ncd.pcucodeperson AND tmp_per.pid = tmp_ncd.pid
group by tmp_per.pcucodeperson,villcode";
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
                        $strXML = "<chart legendPostion='' caption='รายงานการประเมินภาวะโภชนาการผู้สูงอายุ ข้อมูลระหว่างวันที่ ".$_GET[str]." ถึง ".$_GET[sto]." หมู่บ้าน ".$mu ."' subCaption='".$hosp."' xAxisName='' yAxisName='คน' showValues='1' formatNumberScale='0' rotateValues='1' animation=' " . $animateChart . "'>";

                        // Build category XML
                        $strXML .= buildCategories ($resultCategories, "villname");

                        // Build datasets XML
                        $strXML .= buildDatasets ( $resultData, "per", "mark");

                        //Finally, close <chart> element
                        $strXML .= "</chart>";


                        //Create the chart - Pie 3D Chart with data from strXML
                        echo renderChart("FusionCharts/MSColumn2D.swf", "", $strXML, "FactorySum", 900, 450, false, false);


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