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
	$wvill = "h.villcode='$villcode' and ";	
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
$live_type = $_GET[live_type];
if($live_type == '2'){$live_type_name = "ตามทะเบียนบ้าน(0,1,2)";}elseif($live_type == '1'){$live_type_name = "ที่อาศัยอยู่จริง (0,1,3)";}else{$live_type_name = "ทั้งหมดในเขตรับผิดชอบ(0,1,2,3)";}	
if($live_type == '2'){$live_type2 = "and p.typelive in ('0','1','2')";}elseif($live_type == '1'){$live_type2 = "and p.typelive in ('0','1','3')";}else{$live_type2 = "and p.typelive in ('0','1','2','3')";}
	
//ตัวแปร array ที่ใ้ชสำหรับแสดงกราฟ
	$villname = array(); // ตัวแปรแกน x
	//ตัวแปรแกน y
	$percentc = array();
	//หมดตัวแปรแกน y

//sql สำหรับดึงข้อมูล จาก jhcis
$sql = "SELECT t1.mumoi,t1.villname,
SUM(CASE when t2.weight>0 THEN 1 else 0 end )/COUNT(t1.pid)*100 AS percent
FROM
(SELECT p.pid,h.pcucode,
p.birth,FLOOR((TO_DAYS('$sto')-TO_DAYS(p.birth))/30.44) as agemonth,
RIGHT(h.villcode,2) AS mumoi,villname
FROM person p
LEFT JOIN house h ON h.hcode=p.hcode and h.pcucodeperson=p.pcucodeperson
LEFT JOIN village v ON v.villcode=h.villcode and v.pcucode=h.pcucode
WHERE $wvill p.BIRTH<'$sto' AND p.typelive IN (1,3) AND RIGHT(h.villcode,2)<>'00' $live_type2 AND CONCAT(p.pid,p.pcucodeperson) NOT IN (SELECT CONCAT(persondeath.pid,persondeath.pcucodeperson) FROM persondeath)
GROUP BY p.pcucodeperson,p.pid
HAVING agemonth<72
ORDER BY p.mumoi,hnomoi) t1

LEFT JOIN

(SELECT nu.pcucode,nu.pid,nu.sex,nu.visitdate ,nu.agemonth,nu.tall,nu.weight
,max(CASE when nu.tall BETWEEN hc.hmi and hc.hmx THEN hc.nul else null end) as 'heigth_level'
,MAX(case when nu.weight BETWEEN bmi_c.bwmi and bmi_c.bwmx THEN bmi_c.bwnul else null end) as 'bmi_level'
,MAX(case when nu.weight BETWEEN bc.bmin and bc.bmax THEN bc.bnul else null end) as 'bw_level'
from
(SELECT n.pcucode,v.pid,p.sex,p.birth,FLOOR((TO_DAYS(v.visitdate)-TO_DAYS(p.birth))/30.44) as agemonth,n.tall,n.weight,v.visitdate,CONCAT(getAgeMonth(p.birth,v.visitdate)
,case when p.sex=1 then 'm' Else 'f'end)as 'ms'
,CONCAT(CEILING(n.tall)
,case when p.sex=1 then 'm' Else 'f'end)as 'ts'

FROM visitnutrition as n
INNER JOIN visit as v on n.visitno=v.visitno
INNER JOIN person as p on v.pcucodeperson=p.pcucodeperson and v.pid=p.pid
WHERE v.visitdate BETWEEN '$str' and '$sto')as nu
INNER JOIN(SELECT cchart_bh.height_min as hmi ,cchart_bh.height_max as hmx,cchart_bh.nutrition_level as 'nul'
,concat(cchart_bh.age_month,case when cchart_bh.sex=1 then 'm' Else 'f'end)as 'ms' from cchart_bh) as hc on nu.ms=hc.ms
INNER JOIN (SELECT cchart_bmi.bw_min as bwmi ,cchart_bmi.bw_max as bwmx,cchart_bmi.nutrition_level as 'bwnul'
,concat(cchart_bmi.height,case when cchart_bmi.sex=1 then 'm' Else 'f'end)as 'bws' from cchart_bmi) as bmi_c on nu.ts=bmi_c.bws
INNER JOIN(SELECT cchart_bw.bw_min as bmin ,cchart_bw.bw_max as bmax,cchart_bw.nutrition_level as 'bnul'
,concat(cchart_bw.age_month,case when cchart_bw.sex=1 then 'm' Else 'f'end)as 'bs' from cchart_bw) as bc on nu.ms=bc.bs
where nu.agemonth < 72
GROUP BY nu.pcucode,nu.pid
ORDER BY nu.pid) t2
ON t2.pid=t1.pid and t2.pcucode=t1.pcucode
GROUP BY t1.mumoi";
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
                text: '<?php echo "รายงานภาวะโภชนาการเด็กอายุ 0 - 72 เดือน  ".$mu."" ?>'
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