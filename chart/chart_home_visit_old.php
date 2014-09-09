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
sum(case when count_visit > 0 then 1 else 0 end)/count(distinct pid)*100 as percent
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
inner join house AS h on h.pcucode = village.pcucode and h.villcode = village.villcode
Inner Join person AS p ON h.pcucode = p.pcucodeperson AND h.hcode = p.hcode
Inner Join cstatus ON p.marystatus = cstatus.statuscode
left Join ctitle ON p.prename = ctitle.titlecode
WHERE ((p.dischargetype is null) or (p.dischargetype = '9')) AND
				SUBSTRING(h.villcode,7,2) <> '00' AND
				FLOOR((TO_DAYS(NOW())-TO_DAYS(p.birth))/365.25) > 59 $wvill ORDER BY h.villcode, p.fname
) as per
left join 
(SELECT
person.pcucodeperson as pcucodeperson1,
person.pid as pid1,
count(distinct visit.visitno) as count_visit,
visit.symptoms,
visit.vitalcheck,
visitdiag.diagcode,
max(visit.visitdate) as visitdate,
visithomehealthindividual.homehealthtype,
chomehealthtype.homehealthmeaning,
visithomehealthindividual.patientsign,
visithomehealthindividual.homehealthdetail,
visithomehealthindividual.homehealthresult,
visithomehealthindividual.homehealthplan,
visithomehealthindividual.dateappoint,
visithomehealthindividual.`user`,
concat(ctitle.titlename,`user`.fname,`user`.lname) as userh
from
village
INNER JOIN house ON village.pcucode = house.pcucode AND village.villcode = house.villcode
INNER JOIN person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
INNER JOIN visit ON person.pcucodeperson = visit.pcucodeperson AND person.pid = visit.pid
inner join visithomehealthindividual ON visit.pcucode = visithomehealthindividual.pcucode AND visit.visitno = visithomehealthindividual.visitno
INNER JOIN visitdiag ON visit.pcucode = visitdiag.pcucode AND visit.visitno = visitdiag.visitno
inner join `user` on `user`.username = visithomehealthindividual.`user`
left join ctitle on ctitle.titlecode = `user`.prename
inner join chomehealthtype on chomehealthtype.homehealthcode = visithomehealthindividual.homehealthtype
where visit.visitdate between '$str' and '$sto' and ((person.dischargetype is null) or (person.dischargetype = '9')) AND
				SUBSTRING(house.villcode,7,2) <> '00' AND 
				FLOOR((TO_DAYS(NOW())-TO_DAYS(person.birth))/365.25) > 59 and (visit.flagservice <'04' OR visit.flagservice is null OR length(trim(visit.flagservice))=0 )
group by visit.pid) as fp
on per.pcucodeperson = fp.pcucodeperson1 and per.pid = fp.pid1
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
                text: '<?php echo "ร้อยละผู้สูงอายุที่ได้รับการเยี่ยมบ้าน" ?>'
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