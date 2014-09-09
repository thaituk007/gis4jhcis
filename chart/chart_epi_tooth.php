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
$villcode = $_GET[village];
if($villcode == "00000000"){
	$wvill = "";
}else{
	$wvill = " AND house.villcode='$villcode' ";	
}
if($villcode == "00000000"){
	$mu = "ทุกหมู่บ้าน";
}else{
	$mu = getvillagename($villcode);		
}
$str = $_GET[str];
$strx = retDatets($str);
	
//ตัวแปร array ที่ใ้ชสำหรับแสดงกราฟ
	$villname = array(); // ตัวแปรแกน x
	//ตัวแปรแกน y
	$percentc = array();
	//หมดตัวแปรแกน y

//sql สำหรับดึงข้อมูล จาก jhcis
$sql = "select
pcucode,
villcode,
villname,
sum(case when toothmilk is not null then 1 else 0 end)/count(distinct pid)*100 as percent
from
(SELECT
village.pcucode, 
person.pid, 
person.idcard,
person.fname, 
concat(ctitle.titlename, person.fname , '  ' , person.lname) AS pname, 
person.birth,
village.villcode,
village.villname,
house.hno,
house.hcode,
house.xgis,
house.ygis,
ROUND(DATEDIFF('$str',person.birth)/30) AS age,
max(visitdentalcheck.toothmilk) as toothmilk,
max(visitdentalcheck.toothmilkcorrupt) as toothmilkcorrupt,
max(visitdentalcheck.toothpermanent) as toothpermanent,
max(visitdentalcheck.toothpermanentcorrupt) as toothpermanentcorrupt,
max(visitdentalcheck.tartar) as tartar,
max(visitdentalcheck.gumstatus) as gumstatus
from
village 
INNER JOIN house ON village.villcode = house.villcode AND village.pcucode = house.pcucode 
INNER JOIN person ON house.hcode = person.hcode AND house.pcucode = person.pcucodeperson
 INNER JOIN visit ON person.pid = visit.pid AND person.pcucodeperson = visit.pcucodeperson
INNER JOIN visitepi ON visit.pid = visitepi.pid AND visit.visitno = visitepi.visitno AND visit.pcucode = visitepi.pcucode
INNER JOIN ctitle ON person.prename = ctitle.titlecode
left JOIN visitdentalcheck ON visit.pcucode = visitdentalcheck.pcucode AND visit.visitno = visitdentalcheck.visitno
where  (person.dischargetype Is Null Or person.dischargetype='9') and right(house.villcode,2) <> '00' and ROUND(DATEDIFF('$str',person.birth)/30)  Between 9 And 24
GROUP BY person.pid) as tmp_epi
group by pcucode, villcode";
//จบ sql
$result = mysql_query($sql);
while($row=mysql_fetch_array($result)) {
			$percent1 = number_format($row[percent], 2, '.', '');
//array_push คือการนำค่าที่ได้จาก sql ใส่เข้าไปตัวแปร array
	array_push($percentc,$percent1);
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
                text: '<?php echo "ร้อยละเด็กอายุ 9 - 24 เดือนที่มารับวัคซีนได้รับการตรวจฟัน" ?>'
            },
            subtitle: {
                text: '<?= $hosp ?>'
            },
			
            xAxis: {
                categories: ['<?= implode("','", $villname); //นำตัวแปร array แกน x มาใส่ ในที่นี้คือ เดือน?>']
            },
            yAxis: {
                title: {
                    text: '(ร้อยละ)'
                }
            },
            tooltip: {
                enabled: true,
                formatter: function() {
                    return '<b>'+ this.series.name +'</b><br/>'+
                        this.x +': '+ this.y +'ร้อยละ';
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
                                name: 'ร้อยละ',
                                data: [<?= implode(',', $percentc) // ข้อมูล array แกน y ?>]
                            }]
        });
    });
        </script>


    </head> 
    <body> 
      <div id="container"></div>
      <p div align="right" class="text-danger">ข้อมูล ณ วันที่ <?php echo $strx ?></p>
    </body>
</html>