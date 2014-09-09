<?php
set_time_limit(0);
session_start();
//We've included ../Includes/FusionCharts.php and ../Includes/DBConn.php, which contains
//functions to help us easily embed the charts and connect to a database.
include("includes/FusionCharts.php");
include("includes/conndb.php");
include("includes/config.inc.php");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $titleweb; ?></title>
<link href="css/style.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="js/functions.js"></script>
<script type="text/javascript" src="js/jquery-1.4.2.min.js"></script>
<SCRIPT LANGUAGE="Javascript" SRC="FusionCharts/FusionCharts.js"></SCRIPT>
</head>
<body>
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
$str = $_GET[year]."-07-01";
$sto = $_GET[year]+543;

                        // SQL query for category labels
                        $strQueryCategories = "SELECT
case when getageyearnum(person.birth,'$str') between 0 and 4 then 'อายุ 0 - 4 ปี' 
     when getageyearnum(person.birth,'$str') between 5 and 9 then 'อายุ 5 - 9 ปี'
     when getageyearnum(person.birth,'$str') between 10 and 14 then 'อายุ 10 - 14 ปี' 
     when getageyearnum(person.birth,'$str') between 15 and 19 then 'อายุ 15 - 19 ปี' 
     when getageyearnum(person.birth,'$str') between 20 and 24 then 'อายุ 20 - 24 ปี' 
     when getageyearnum(person.birth,'$str') between 25 and 29 then 'อายุ 25 - 29 ปี' 
     when getageyearnum(person.birth,'$str') between 30 and 34 then 'อายุ 30 - 34 ปี' 
     when getageyearnum(person.birth,'$str') between 35 and 39 then 'อายุ 35 - 39 ปี' 
     when getageyearnum(person.birth,'$str') between 40 and 44 then 'อายุ 40 - 44 ปี' 
     when getageyearnum(person.birth,'$str') between 45 and 49 then 'อายุ 45 - 49 ปี' 
     when getageyearnum(person.birth,'$str') between 50 and 54 then 'อายุ 50 - 54 ปี' 
     when getageyearnum(person.birth,'$str') between 55 and 59 then 'อายุ 55 - 59 ปี' 
     when getageyearnum(person.birth,'$str') between 60 and 64 then 'อายุ 60 - 64 ปี' 
     when getageyearnum(person.birth,'$str') between 65 and 69 then 'อายุ 65 - 69 ปี' 
     when getageyearnum(person.birth,'$str') between 70 and 74 then 'อายุ 70 - 74 ปี' 
     when getageyearnum(person.birth,'$str') between 75 and 79 then 'อายุ 75 - 79 ปี' 
     when getageyearnum(person.birth,'$str') between 80 and 120 then 'อายุ 80 ปีขึ้นไป'  else null end as detail
FROM
house
Inner Join person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
left join persondeath on person.pcucodeperson = persondeath.pcucodeperson and person.pid = persondeath.pid
WHERE (((person.dischargetype is null) or (person.dischargetype = '9')) or DATE_FORMAT(persondeath.deaddate,'%Y') <= DATE_FORMAT('$str','%Y')) and person.birth <= '$str' and SUBSTRING(house.villcode,7,2) <> '00'
group by detail
order by SUBSTRING(detail,6,2)*1 desc";

                        // Query database
                        $resultCategories = mysql_query($strQueryCategories) or die(mysql_error());


                        // SQL query for factory output data
                        $strQueryData =  "SELECT
case when getageyearnum(person.birth,'$str') between 0 and 4 then 'อายุ 0 - 4 ปี' 
     when getageyearnum(person.birth,'$str') between 5 and 9 then 'อายุ 5 - 9 ปี'
     when getageyearnum(person.birth,'$str') between 10 and 14 then 'อายุ 10 - 14 ปี' 
     when getageyearnum(person.birth,'$str') between 15 and 19 then 'อายุ 15 - 19 ปี' 
     when getageyearnum(person.birth,'$str') between 20 and 24 then 'อายุ 20 - 24 ปี' 
     when getageyearnum(person.birth,'$str') between 25 and 29 then 'อายุ 25 - 29 ปี' 
     when getageyearnum(person.birth,'$str') between 30 and 34 then 'อายุ 30 - 34 ปี' 
     when getageyearnum(person.birth,'$str') between 35 and 39 then 'อายุ 35 - 39 ปี' 
     when getageyearnum(person.birth,'$str') between 40 and 44 then 'อายุ 40 - 44 ปี' 
     when getageyearnum(person.birth,'$str') between 45 and 49 then 'อายุ 45 - 49 ปี' 
     when getageyearnum(person.birth,'$str') between 50 and 54 then 'อายุ 50 - 54 ปี' 
     when getageyearnum(person.birth,'$str') between 55 and 59 then 'อายุ 55 - 59 ปี' 
     when getageyearnum(person.birth,'$str') between 60 and 64 then 'อายุ 60 - 64 ปี' 
     when getageyearnum(person.birth,'$str') between 65 and 69 then 'อายุ 65 - 69 ปี' 
     when getageyearnum(person.birth,'$str') between 70 and 74 then 'อายุ 70 - 74 ปี' 
     when getageyearnum(person.birth,'$str') between 75 and 79 then 'อายุ 75 - 79 ปี' 
     when getageyearnum(person.birth,'$str') between 80 and 120 then 'อายุ 80 ปีขึ้นไป'  else null end as detail,
'ชาย' as sex,
sum(case when person.sex = '1' then 1 else 0 end)/(select count(distinct p.pid) from house h
Inner Join person p ON h.pcucode = p.pcucodeperson AND h.hcode = p.hcode
left join persondeath pd on p.pcucodeperson = pd.pcucodeperson and p.pid = pd.pid
WHERE (((p.dischargetype is null) or (p.dischargetype = '9')) or DATE_FORMAT(pd.deaddate,'%Y') <= DATE_FORMAT('$str','%Y')) and DATE_FORMAT(p.birth,'%Y') <= DATE_FORMAT('$str','%Y') and SUBSTRING(h.villcode,7,2) <> '00')*-100 as gender
FROM
house
Inner Join person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
left join persondeath on person.pcucodeperson = persondeath.pcucodeperson and person.pid = persondeath.pid
WHERE (((person.dischargetype is null) or (person.dischargetype = '9')) or DATE_FORMAT(persondeath.deaddate,'%Y') <= DATE_FORMAT('$str','%Y')) and DATE_FORMAT(person.birth,'%Y') <= DATE_FORMAT('$str','%Y') and SUBSTRING(house.villcode,7,2) <> '00'
group by detail
UNION
SELECT
case when getageyearnum(person.birth,'$str') between 0 and 4 then 'อายุ 0 - 4 ปี' 
     when getageyearnum(person.birth,'$str') between 5 and 9 then 'อายุ 5 - 9 ปี'
     when getageyearnum(person.birth,'$str') between 10 and 14 then 'อายุ 10 - 14 ปี' 
     when getageyearnum(person.birth,'$str') between 15 and 19 then 'อายุ 15 - 19 ปี' 
     when getageyearnum(person.birth,'$str') between 20 and 24 then 'อายุ 20 - 24 ปี' 
     when getageyearnum(person.birth,'$str') between 25 and 29 then 'อายุ 25 - 29 ปี' 
     when getageyearnum(person.birth,'$str') between 30 and 34 then 'อายุ 30 - 34 ปี' 
     when getageyearnum(person.birth,'$str') between 35 and 39 then 'อายุ 35 - 39 ปี' 
     when getageyearnum(person.birth,'$str') between 40 and 44 then 'อายุ 40 - 44 ปี' 
     when getageyearnum(person.birth,'$str') between 45 and 49 then 'อายุ 45 - 49 ปี' 
     when getageyearnum(person.birth,'$str') between 50 and 54 then 'อายุ 50 - 54 ปี' 
     when getageyearnum(person.birth,'$str') between 55 and 59 then 'อายุ 55 - 59 ปี' 
     when getageyearnum(person.birth,'$str') between 60 and 64 then 'อายุ 60 - 64 ปี' 
     when getageyearnum(person.birth,'$str') between 65 and 69 then 'อายุ 65 - 69 ปี' 
     when getageyearnum(person.birth,'$str') between 70 and 74 then 'อายุ 70 - 74 ปี' 
     when getageyearnum(person.birth,'$str') between 75 and 79 then 'อายุ 75 - 79 ปี' 
     when getageyearnum(person.birth,'$str') between 80 and 120 then 'อายุ 80 ปีขึ้นไป'  else null end as detail,
'หญิง' as sex,
sum(case when person.sex = '2' then 1 else 0 end)/(select count(distinct p.pid) from house h
Inner Join person p ON h.pcucode = p.pcucodeperson AND h.hcode = p.hcode
left join persondeath pd on p.pcucodeperson = pd.pcucodeperson and p.pid = pd.pid
WHERE (((p.dischargetype is null) or (p.dischargetype = '9')) or DATE_FORMAT(pd.deaddate,'%Y') <= DATE_FORMAT('$str','%Y')) and DATE_FORMAT(p.birth,'%Y') <= DATE_FORMAT('$str','%Y')  and SUBSTRING(h.villcode,7,2) <> '00')*100 as gender
FROM
house
Inner Join person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
left join persondeath on person.pcucodeperson = persondeath.pcucodeperson and person.pid = persondeath.pid
WHERE (((person.dischargetype is null) or (person.dischargetype = '9')) or DATE_FORMAT(persondeath.deaddate,'%Y') <= DATE_FORMAT('$str','%Y')) and DATE_FORMAT(person.birth,'%Y') <= DATE_FORMAT('$str','%Y') and SUBSTRING(house.villcode,7,2) <> '00'
group by detail
order by sex, SUBSTRING(detail,6,2)*1 desc";
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
                        $strXML = "<chart legendPostion='' caption='ปิรามิดประชากร ปี พ.ศ.".$sto."' subCaption='".$hosp."' xAxisName='' yAxisName='ร้อยละ' showValues='1' formatNumberScale='0' rotateValues='1' animation=' " . $animateChart . "'>";

                        // Build category XML
                        $strXML .= buildCategories ($resultCategories, "detail");

                        // Build datasets XML
                        $strXML .= buildDatasets ( $resultData, "gender", "sex");

                        //Finally, close <chart> element
                        $strXML .= "</chart>";


                        //Create the chart - Pie 3D Chart with data from strXML
                        echo renderChart("FusionCharts/StackedBar2D.swf", "", $strXML, "FactorySum", 750, 450, false, false);


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
</body>
</html>