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
$hosp=$row[chospital_hosname];
$villcode = $_GET[village];
if($villcode == "00000000"){
	$wvill = "";
}else{
	$wvill = "and h.villcode='$villcode' ";	
}
if($villcode == "00000000"){
	$mu = "ทุกหมู่บ้าน";
}else{
	$mu = substr($_GET[village],6,2);
}
$str = retdaterangstr($_GET[str]);
$sto = retdaterangsto($_GET[str]);
$strx = retDatets($str);
$stox = retDatets($sto);	
	
//ตัวแปร array ที่ใ้ชสำหรับแสดงกราฟ
	$villname = array(); // ตัวแปรแกน x
	//ตัวแปรแกน y
	$percentc = array();
	//หมดตัวแปรแกน y

//sql สำหรับดึงข้อมูล จาก jhcis
$sql = "select
pcucodeperson,
villcode,
villname,
count(distinct pid1)/count(distinct pid)*100 as percent
from
(select
person.pcucodeperson,
person.pid,
person.fname,
CONCAT(ctitle.titlename,person.fname,' ',person.lname) AS pname,
person.birth,
getageyearnum(person.birth,now()) as age,
house.hcode,
house.hno,
right(house.villcode,2) as moo,
house.villcode,
village.villname,
house.xgis,
house.ygis,
group_concat(cpersonincomplete.incompletename) as incom
FROM
village
Inner Join house ON village.pcucode = house.pcucode AND village.villcode = house.villcode
Inner Join person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
left Join ctitle ON person.prename = ctitle.titlecode
Inner Join personunable ON person.pcucodeperson = personunable.pcucodeperson AND person.pid = personunable.pid
Inner Join personunable1type ON personunable.pcucodeperson = personunable1type.pcucodeperson AND personunable.pid = personunable1type.pid
Inner Join cpersonincomplete ON personunable1type.typecode = cpersonincomplete.incompletecode
where ((person.dischargetype is null) or (person.dischargetype = '9')) AND SUBSTRING(house.villcode,7,2) <> '00' $wvill
group by person.pcucodeperson,person.pid
order by house.villcode,person.fname) as per_chronic 
left join
(SELECT
visit.pcucodeperson as pcucodeperson1,
visit.pid as pid1,
visit.visitno,
visit.visitdate,
chomehealthtype.homehealthmeaning,
visithomehealthindividual.patientsign,
visithomehealthindividual.homehealthdetail,
visithomehealthindividual.homehealthresult,
visithomehealthindividual.homehealthplan,
visithomehealthindividual.dateappoint,
concat(ctitle.titlename,`user`.fname,`user`.lname) as userh,
visithomehealthindividual.`user`
FROM
visit
Inner Join visithomehealthindividual ON visit.pcucode = visithomehealthindividual.pcucode AND visit.visitno = visithomehealthindividual.visitno
Inner Join chomehealthtype ON visithomehealthindividual.homehealthtype = chomehealthtype.homehealthcode
INNER JOIN `user` ON visit.pcucodeperson = `user`.pcucode AND visithomehealthindividual.`user` = `user`.username
left JOIN ctitle ON `user`.prename = ctitle.titlecode
where visit.visitdate between '$str' and '$sto' and (visit.flagservice <'04' OR visit.flagservice is null OR length(trim(visit.flagservice))=0)) as per_homevisit
on per_chronic.pcucodeperson = per_homevisit.pcucodeperson1 and per_chronic.pid = per_homevisit.pid1
group by villcode";
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
                text: '<?php echo "ร้อยละผู้พิการที่ได้รับการเยี่ยมบ้าน" ?>'
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
      <p div align="right" class="text-danger">ข้อมูลระหว่างวันที่ <?php echo $strx." ถึง ".$stox ?></p>
    </body>
</html>