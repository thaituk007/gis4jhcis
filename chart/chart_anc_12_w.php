<?php
set_time_limit(0);
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
$hosp=$row[chospital_hosname]; //ได้ตัวแปร $hosp 
$villcode = $_GET[village];
if($villcode == "00000000"){
	$wvill = "";
}else{
	$wvill = " AND village.villcode='$villcode' ";	
}
if($villcode == "00000000"){
	$mu = "ทุกหมู่บ้าน";
}else{
	$mu = getvillagename($villcode);	
}
$str = retdaterangstr($_GET[str]);
$sto = retdaterangsto($_GET[str]);
$strx = retDatets($str);
$stox = retDatets($sto);
//ตัวแปร array ที่ใ้ชสำหรับแสดงกราฟ
	$villname = array(); // ตัวแปรแกน x
	//ตัวแปรแกน y
	$per = array();
	$anc12 = array();
	//หมดตัวแปรแกน y

//sql สำหรับดึงข้อมูล จาก jhcis
$sql = "select
pcucode,
villcode,
villname,
sum(1) as per,
sum(case when chk = 1 then 1 else 0 end) as anc12
from
(SELECT 
village.pcucode, 
person.pid, 
person.idcard, 
concat(ctitle.titlename, person.fname , '  ' , person.lname) AS pname, 
village.villcode,
village.villname,
house.hno,
house.hcode,
house.xgis,
house.ygis,
person.birth,
ROUND(DATEDIFF(now(),person.birth)/365.25) AS age,
visitancpregnancy.edc,
visitanc.pregno,
visitancpregnancy.lmp,
min(visitanc.datecheck) as first_visit_date,
ROUND(DATEDIFF(min(visitanc.datecheck) ,visitancpregnancy.lmp) /7) AS agepreg,
case when ROUND(DATEDIFF(visitanc.datecheck ,visitancpregnancy.lmp) /7)  < 12 then 1 else 0 end as chk
FROM (((house INNER JOIN village ON (house.villcode = village.villcode) AND (house.pcucode = village.pcucode)) INNER JOIN person ON (house.hcode = person.hcode) AND (house.pcucode = person.pcucodeperson)) INNER JOIN visitancpregnancy ON (person.pid = visitancpregnancy.pid) AND (person.pcucodeperson = visitancpregnancy.pcucodeperson)) INNER JOIN visitanc ON (visitancpregnancy.pregno = visitanc.pregno) AND (visitancpregnancy.pid = visitanc.pid) AND (visitancpregnancy.pcucodeperson = visitanc.pcucodeperson) inner join ctitle on person.prename = ctitle.titlecode
WHERE (person.dischargetype Is Null Or person.dischargetype='9') and right(house.villcode,2) <> '00' $wvill
GROUP BY village.pcucode, person.pid
order by village.pcucode, village.villcode, person.fname) as tmp_anc
where first_visit_date Between '$str' And '$sto'
group by pcucode,villcode";
//จบ sql
$result = mysql_query($sql);
while($row=mysql_fetch_array($result)) {
//array_push คือการนำค่าที่ได้จาก sql ใส่เข้าไปตัวแปร array
	array_push($per,$row[per]);
	array_push($anc12,$row[anc12]);
	array_push($villname,$row[villname]);
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
                text: 'หญิงตั้งครรภ์ที่ฝากครรภ์ครั้งแรกก่อน 12 สัปดาห์<br><?php echo $mu ?>'
            },
            subtitle: {
                text: '<?= $hosp ?>'
            },
			
            xAxis: {
                categories: ['<?= implode("','", $villname); //นำตัวแปร array แกน x มาใส่ ในที่นี้คือ เดือน?>']
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
                        this.x +': '+ this.y +'ครั้ง';
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
                                name: 'หญิงตั้งครรภ์ทั้งหมด',
                                data: [<?= implode(',', $per) // ข้อมูล array แกน y ?>]
                            }, {
                                name: 'ฝากครรภ์ก่อน 12 สัปดาห์',
                                data: [<?= implode(',', $anc12) ?>]
							}]
        });
    });
        </script>


    </head> 
    <body> 
      <div id="container" style="min-width: 320px; height: 380px; margin: 0 auto"></div>
      <p div align="right" class="text-danger">ข้อมูลระหว่างวันที่ <?php echo $strx." ถึง ".$stox ?></p>
    </body>
</html>