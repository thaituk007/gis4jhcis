<?php
session_start();
set_time_limit(0);
include("includes/conndb.php");
include("includes/config.inc.php");
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
$age = $_GET[age];
$ect0 = "'10000'";
if(strpos($age,",",0) > 0){
	$listage = explode(',',$age);
	foreach ($listage as $a){
		if(strpos($a,"-",0) > 0){
			list($str,$end) = split("-",$a,2);
			for($i = $str; $i <= $end; $i++){
				$ect0 .= ",'".$i."'";
			}
		}else{
			$ect0 .= ",'".$a."'";
		}
	}
}else{
		if(strpos($age,"-",0) > 0){
			list($str,$end) = split("-",$age,2);
			for($i = $str; $i <= $end; $i++){
				$ect0 .= ",'".$i."'";
			}
		}else{
			$ect0 .= ",'".$age."'";
		}
}
$sex = $_GET[sex];
if($sex == '0'){$ect1_name = "";}elseif($sex == '1'){$ect1_name = "เพศชาย";}else{$ect1_name = "เพศหญิง";}
if($sex == '0'){$ect1 = "";}else{$ect1 = " p.sex = '$sex' AND ";}
$village = $_GET[village];
if($village == "00000000"){
	$mu = "ทุกหมู่บ้าน";
}else{
	$mu = getvillagename($village);	
}
if($village == '00000000'){$ect2 = "";}else{$ect2 = " h.villcode = '$village' AND ";}
$live_type = $_GET[live_type];
if($live_type == '2'){$live_type_name = "ตามทะเบียนบ้าน(0,1,2)";}elseif($live_type == '1'){$live_type_name = "ที่อาศัยอยู่จริง (0,1,3)";}else{$live_type_name = "ทั้งหมดในเขตรับผิดชอบ(0,1,2,3)";}	
if($live_type == '2'){$live_type2 = "and p.typelive in ('0','1','2')";}elseif($live_type == '1'){$live_type2 = "and p.typelive in ('0','1','3')";}else{$live_type2 = "and p.typelive in ('0','1','2','3')";}
$countofpid = array();
$villname = array();
$sql = "SELECT count(distinct p.pid) as countofpid,h.villcode,v.villname
				FROM house AS h
				Inner Join person AS p ON h.pcucode = p.pcucodeperson AND h.hcode = p.hcode
				inner join village v on h.pcucode = v.pcucode and h.villcode = v.villcode
				WHERE $ect1 $ect2 ((p.dischargetype is null) or (p.dischargetype = '9')) AND
				SUBSTRING(h.villcode,7,2) <> '00' AND
				FLOOR((TO_DAYS(NOW())-TO_DAYS(p.birth))/365.25) IN($ect0) $live_type2
				group by h.villcode
				ORDER BY h.villcode";
$result = mysql_query($sql);
while($row=mysql_fetch_array($result)) {
	array_push($countofpid,$row[countofpid]);
	array_push($villname,$row[villname]);
}
?>
<!DOCTYPE html> 
<html>
    <head>

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1"> 
        
         <link rel="stylesheet" href="http://code.jquery.com/mobile/1.3.2/jquery.mobile-1.3.2.min.css" />
         <link rel="stylesheet" href="http://taitems.github.io/iOS-Inspired-jQuery-Mobile-Theme/ios_inspired/styles.css"/>
        <script src="js/jquery-1.9.1.min.js"></script>
        <script src="http://code.jquery.com/mobile/1.3.2/jquery.mobile-1.3.2.min.js"></script>
        
        <script src="js/highcharts.js"></script>
        <script src="js/exporting.js"></script>    
        <script>
            $(document).on('pageshow', '#index', function() {
                var chart;
                $(document).ready(function() {
                    chart = new Highcharts.Chart({
                        credits: {
                            enabled: false
                        },
                        chart: {
                            renderTo: 'container',
                            type: 'column',
                            marginRight: 130,
                            marginBottom: 25
                        },
                        title: {
                            text: '<?= "จำนวนประชาชน".$ect1_name." ที่อายุระหว่าง ".$age." ปี  ".$live_type_name." ".$mu."" ?>',
                            x: -20 //center
                        },
						subtitle: {
                            text: '<?= $hosp ?>'
                        },
                        xAxis: {
                            categories: ['โคก','โนนปอแดง','ทุ่งน้อย','นาโมง','กกต้อง','โนนอุดม','โนนมะค่า','ดงบัง','ต่างแคน','โคกสอง','โนนปอแดง','ดงบังน้อย','โคกกลาง','เพ็กทอง','ต่างแคน']
                        },
                        yAxis: {
                            title: {
                                text: 'จำนวนรับบริการ (ราย)'
                            },
                            plotLines: [{
                                    value: 0,
                                    width: 1,
                                    color: '#808080'
                                }]
                        },
                        tooltip: {
                            formatter: function() {
                                return '<b>' + this.series.name + '</b><br/>' +
                                        this.x + ': ' + this.y + ' ราย';
                            }
                        },
                        legend: {
                            layout: 'vertical',
                            align: 'right',
                            verticalAlign: 'top',
                            x: -10,
                            y: 100,
                            borderWidth: 0
                        },
                        series: [{
                                name: 'จำนวนประชากร',
                                data: [<?= implode(',', $countofpid) ?>]
                            }]
                    });
                });

            });

        </script>


    </head> 
    <body> 
             <div data-role="page" id="index">
            <div data-role="header" data-position="fixed">
		<h1>Header Buttons</h1>
		<a href="index.php" data-rel="back" data-theme="a">Back</a>
	</div><!-- /header --> 


            <div data-role="content">
                <div id="container" style="min-width: 320px; height: 380px; margin: 0 auto"></div>
            </div>

            
        </div>       
    </body>
</html>