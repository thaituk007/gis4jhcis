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
	$wvill = "AND right(house.villcode,2) <> '00'";
}elseif($villcode == "11111111"){
	$wvill = "AND right(house.villcode,2) = '00'";
}else{
	$wvill = " AND house.villcode='$villcode' ";	
}
if($villcode == "00000000"){
	$mu = "ทุกหมู่บ้าน";
}elseif($villcode == "11111111"){
	$mu = "นอกเขต";
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
	$cm1 = array();
	$cm2 = array();
	$cm3 = array();
	//หมดตัวแปรแกน y

//sql สำหรับดึงข้อมูล จาก jhcis
$sql = "select
village.villcode,
village.villname,
sum(case when mothername is not null then 1 else 0 end) as per_all,
sum(case when m1 is not null then 1 else 0 end) as cm1,
sum(case when m2 is not null then 1 else 0 end) as cm2,
sum(case when m3 is not null then 1 else 0 end) as cm3
from
village
inner join
(SELECT CONVERT(concat(ifnull(tm.titlename,ifnull(person.prename,'ไม่ระบุ') ),person.fname,' ',person.lname) using utf8) as mothername,
house.villcode,
person.birth,
house.hno,
house.hcode,
house.xgis,
house.ygis
,MAX(v.pregno) pregno,v.pid,v.pcucodeperson
,DATE_FORMAT(current_date,'%Y-%m-%d') - DATE_FORMAT(person.birth,'%Y-%m-%d') as age
,if(house.hno != '' ,CONVERT(concat(house.hno,' ม.',villno) USING utf8), CONVERT(concat('- ม.',villno) USING utf8) ) as address
,CONVERT(concat(ifnull(tc.titlename,ifnull(person.prename,'ไม่ระบุ') ),pchild.fname,' ',pchild.lname) using utf8) childname
,house.pcucode
,count(distinct v.pid,v.pcucodeperson,v.pregno,v.datecare) as cdc
,case when ( v.datecare between MIN(v.datecare) and MAX(v.datecare)  and (count(distinct v.pid,v.pcucodeperson,v.pregno,v.datecare)  >=1 ) ) then MIN(v.datecare) else null end m1
,case when ( v.datecare between MIN(v.datecare) and MAX(v.datecare)  and (count(distinct v.pid,v.pcucodeperson,v.pregno,v.datecare)  >=2 ))  then v.datecare  else null end m2
,case when ( v.datecare between MIN(v.datecare) and MAX(v.datecare)  and (count(distinct v.pid,v.pcucodeperson,v.pregno,v.datecare)  >=3 ))  then MAX(v.datecare)else null end m3
,curdate() as cdate
FROM  visitancmothercare v
        left join visitancdeliverchild vchild on v.pid = vchild.pid and v.pcucodeperson = vchild.pcucodeperson
	left join visitancdeliver on v.pid  = visitancdeliver.pid  and v.pcucodeperson= visitancdeliver.pcucodeperson
	left join person   on v.pid = person.pid and v.pcucodeperson = person.pcucodeperson
	left join ctitle tm on person.prename = tm.titlecode
	left join person pchild  on pchild.pid = vchild.pidchild and pchild.pcucodeperson = vchild.pcucodechild
	left join ctitle tc on pchild.prename = tc.titlecode
	left join house on person.hcode = house.hcode and person.pcucodeperson = house.pcucode
	left join village on house.villcode = village.villcode and house.pcucode = village.pcucode

WHERE visitancdeliver.datedeliver between '$str' and '$sto' $wvill
GROUP BY  v.pcucode,v.pid,v.pcucodeperson,v.pregno
order by house.villcode,person.fname) as tmp
on tmp.villcode = village.villcode
group by village.villcode";
//จบ sql
$result = mysql_query($sql);
while($row=mysql_fetch_array($result)) {

//array_push คือการนำค่าที่ได้จาก sql ใส่เข้าไปตัวแปร array
	array_push($cm1,$row[cm1]);
	array_push($cm2,$row[cm2]);
	array_push($cm3,$row[cm3]);
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
                text: '<?php echo "รายงานการเยี่ยมหลังคลอด " ?>'
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
                                name: 'ครั้งที่ 1',
                                data: [<?= implode(',', $cm1) // ข้อมูล array แกน y ?>]},{
								name: 'ครั้งที่ 2',
                                data: [<?= implode(',', $cm2) // ข้อมูล array แกน y ?>]},{
								name: 'ครั้งที่ 3',
                                data: [<?= implode(',', $cm3) // ข้อมูล array แกน y ?>]
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