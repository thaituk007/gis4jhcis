<?php
include("../includes/conndb.php");
include("../includes/config.inc.php");
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
$hosp=$row[chospital_hosname];
$chronic = $_GET[chronic];
	$village = $_GET[village];
	if($village == "00000000"){
		$wvill = "";
	}else{
		$wvill = "AND house.villcode='$village'";	
	}
	if($village == "00000000"){
	$mu = "ทุกหมู่บ้าน";
}else{
	$mu = getvillagename($village);	
}
	if($chronic == '00'){$ect = "";}else{$ect = "AND dc.groupcode = '$chronic'";}
	
//ตัวแปร array ที่ใ้ชสำหรับแสดงกราฟ
	$groupname = array(); // ตัวแปรแกน x
	//ตัวแปรแกน y
	$per = array();
	//หมดตัวแปรแกน y

//sql สำหรับดึงข้อมูล จาก jhcis
$sql = "SELECT
dc.groupcode,
dc.groupname,
count(pc.pid) as per
FROM personchronic pc
left join person ON pc.pid = person.pid and pc.pcucodeperson = person.pcucodeperson
left join house ON person.hcode = house.hcode and person.pcucodeperson = house.pcucode
left join village ON house.villcode = village.villcode
left join ctitle ON person.prename = ctitle.titlecode
left Join cdisease d ON pc.chroniccode = d.diseasecode
left Join cdiseasechronic dc ON d.codechronic = dc.groupcode
where SUBSTRING(house.villcode,7,2) <> '00' AND pc.typedischart NOT IN  ('01', '02','07','10') and person.pid NOT IN (SELECT persondeath.pid FROM persondeath WHERE persondeath.pcucodeperson= person.pcucodeperson and (persondeath.deaddate IS NULL OR persondeath.deaddate<=now())) $wvill
group by dc.groupname
ORDER BY village.villcode";
//จบ sql
$result = mysql_query($sql);
while($row=mysql_fetch_array($result)) {
//array_push คือการนำค่าที่ได้จาก sql ใส่เข้าไปตัวแปร array
	array_push($per,$row[per]);
	array_push($groupname,$row[groupname]);
}
?>
<!DOCTYPE HTML>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title><?php echo $titleweb; ?></title>
        <link href="../css/bootstrap.min.css" rel="stylesheet">
        <script src="../js/bootstrap.min.js"></script>
        <script src="../js/jquery.1.11.0.min.js"></script>
        <script src="../js/highcharts.js"></script>
        <script src="../js/exporting.js"></script>    
        <script>
$(function () {
        $('#container').highcharts({
            chart: {
                type: 'column'
            },
            title: {
                text: '<?php echo "จำนวนผู้ป่วยโรคเรื้อรังจำแนกรายโรค ".$mu."" ?>'
            },
            subtitle: {
                text: '<?= $hosp ?>'
            },
			
            xAxis: {
                categories: ['<?= implode("','", $groupname); //นำตัวแปร array แกน x มาใส่ ในที่นี้คือ เดือน?>']
            },
            yAxis: {
                title: {
                    text: '(คน)'
                }
            },
            tooltip: {
                enabled: true,
                formatter: function() {
                    return '<b>'+ this.series.name +'</b><br/>'+
                        this.x +': '+ this.y +'คน';
                }
            },
			legend: {
                            layout: 'vertical ',
                            align: 'button',
                            verticalAlign: 'top',
                            x: -10,
                            y: 100,
                            borderWidth: 0
            },
            plotOptions: {
                column: {
                    dataLabels: {
                        enabled: true
                    },
                    enableMouseTracking: true,
					
                }
            },
			series: [{
                                name: 'คน',
                                data: [<?= implode(',', $per) // ข้อมูล array แกน y ?>]
                            }]
        });
    });
        </script>


    </head> 
    <body> 
      <div id="container"></div>
    </body>
</html>