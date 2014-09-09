<?php
include("includes/conndb.php");
include("includes/config.inc.php");
//ดึงชื่อสถานบริการ
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
$hosp=$row[chospital_hosname]; //ได้ตัวแปร $hosp 
$str = retDate($_GET[str]);
$sto = retDate($_GET[sto]);
//ตัวแปร array ที่ใ้ชสำหรับแสดงกราฟ
	$diseasename = array(); // ตัวแปรแกน x
	//ตัวแปรแกน y
	$cdiagcode = array();
	//หมดตัวแปรแกน y

//sql สำหรับดึงข้อมูล จาก jhcis
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
//จบ sql
$result = mysql_query($sql);
while($row=mysql_fetch_array($result)) {
//array_push คือการนำค่าที่ได้จาก sql ใส่เข้าไปตัวแปร array
	$cdisease = number_format($row[cdiagcode], 2, '.', '');
	array_push($cdiagcode,$cdisease);
	array_push($diseasename,$row[diseasename]);
}
?>
<!DOCTYPE HTML>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>Highcharts Example</title>
        <script src="js/jquery-1.9.1.min.js"></script>
        <script src="js/highcharts.js"></script>
        <script src="js/exporting.js"></script>    
        <script type="text/javascript">
$(function () {
        $('#container').highcharts({
            chart: {
                type: 'bar'
            },
            title: {
                text: ''
            },
            subtitle: {
                text: ''
            },
            xAxis: {
                categories: ['<?= implode("','", $diseasename); //นำตัวแปร array แกน x มาใส่ ในที่นี้คือ เดือน?>'],
                title: {
                    text: null
                }
            },
            yAxis: {
                min: 0,
                title: {
                    text: 'อัตราป่วย (ต่อ1000)',
                    align: 'high'
                },
                labels: {
                    overflow: 'justify'
                }
            },
            tooltip: {
                valueSuffix: '(ต่อ1000)'
            },
            plotOptions: {
                bar: {
                    dataLabels: {
                        enabled: true
                    }
                }
            },
            legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'top',
                x: -40,
                y: 100,
                floating: true,
                borderWidth: 1,
                backgroundColor: '#FFFFFF',
                shadow: true
            },
            credits: {
                enabled: false
            },
            series: [{
                                name: 'อัตราป่วย (ต่อ1000)',
                                data: [<?= implode(',', $cdiagcode) // ข้อมูล array แกน y ?>]
                            }]
        });
    });	
        </script>


</head> 
	<body>
      <div id="container" style="min-width: 320px; height: 380px; margin: 0 auto"></div>       
    </body>
</html>
