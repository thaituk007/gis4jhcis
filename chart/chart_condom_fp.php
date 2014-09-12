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
$sql = "SELECT
count(distinct tmp.pid) as countofpid, 
count(tmp.visitno) as countofvisitno,
sum(tmp.unit) as unitcondom,
village.villcode,
village.villname
from
village
left join
(SELECT person.idcard, person.pid
,CONVERT(concat(ifnull(c.titlename,ifnull(person.prename,'ไม่ระบุ') ),person.fname,' ' ,person.lname) using utf8) as pname 
,DATE_FORMAT(visitdate,'%Y-%m-%d') as visitdate
,office.offid
,v.pcucode
,v.visitno
,if(v.pcucode = office.offid,'หน่วยบริการ' ,'ที่อื่น ' ) as pcu
,drugname
,CONVERT(concat(c.titlename,u.fname,' ' ,u.lname) using utf8) as uname
,curdate() as cdate,
house.hno,
house.villcode,
house.xgis,
house.ygis,
visitfp.unit,
cdrugunitsell.unitsellname
FROM person left join ctitle on person.prename = ctitle.titlecode
	left join visit  v on person.pid = v.pid  and person.pcucodeperson = v.pcucodeperson
	left join visitfp on v.visitno = visitfp.visitno and v.pcucode = visitfp.pcucode
	left join cdrug on visitfp.fpcode = cdrug.drugcode
	left join cdrugunitsell on cdrug.unitsell = cdrugunitsell.unitsellcode
	left join user u on v.username = u.username
	left join ctitle c on c.titlecode = u.prename
	left join office on v.pcucode = office.offid
	Inner Join house ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
WHERE    cdrug.drugcode like'CONDOM%'   
 	and visitdate between '$str' and '$sto' and (v.flagservice <'04' OR v.flagservice is null OR length(trim(v.flagservice))=0 )
GROUP BY v.visitno,v.pcucodeperson
ORDER BY house.villcode,v.visitdate) as tmp
on tmp.pcucode = village.pcucode and tmp.villcode = village.villcode
where village.villname is not null
group by village.pcucode, village.villcode";
//จบ sql
$result = mysql_query($sql);
while($row=mysql_fetch_array($result)) {
//array_push คือการนำค่าที่ได้จาก sql ใส่เข้าไปตัวแปร array
	array_push($percentc,$row[unitcondom]);
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
                text: '<?php echo "แผนภูมิแสดงจำนวนการจ่ายถุงยางอนามัยในงานวางแผนครอบครัว" ?>'
            },
            subtitle: {
                text: '<?= $hosp ?>'
            },
			
            xAxis: {
                categories: ['<?= implode("','", $villname); //นำตัวแปร array แกน x มาใส่ ในที่นี้คือ เดือน?>']
            },
            yAxis: {
                title: {
                    text: '(ชิ้น)'
                }
            },
            tooltip: {
                enabled: true,
                formatter: function() {
                    return '<b>'+ this.series.name +'</b><br/>'+
                        this.x +': '+ this.y +'ชิ้น';
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
                                name: 'จ่ายถุงยางอนามัย',
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