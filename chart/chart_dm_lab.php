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
$sql = "select villcode,
villname,
sum(case when CH99 is not null then 1 else 0 end)/count(distinct pid)*100 as percent
 from
(SELECT
person.pcucodeperson,
person.pid,
person.fname,
concat(ctitle.titlename,person.fname ,'  ' ,person.lname) AS pname,
person.birth,
ROUND(DATEDIFF(now(),person.birth)/365.25) AS age,
village.villcode,
village.villname,
house.hno,
house.hcode,
house.xgis,
house.ygis,
group_concat(cdisease.codechronic) as codex,
group_concat(cdiseasechronic.groupname) as chronicx
FROM
personchronic
inner join person on person.pcucodeperson = personchronic.pcucodeperson AND person.pid = personchronic.pid
inner join cdisease on personchronic.chroniccode = cdisease.diseasecode
left join cdiseasechronic on cdiseasechronic.groupcode = cdisease.codechronic
inner join ctitle on person.prename = ctitle.titlecode
inner join house on person.hcode = house.hcode and person.pcucodeperson = house.pcucode
inner join village ON house.villcode = village.villcode and house.pcucode = village.pcucode
where ((person.dischargetype is null) or (person.dischargetype = '9')) AND SUBSTRING(house.villcode,7,2) <> '00' $wvill
group by person.pcucodeperson, person.pid
having codex like '%10%'
) as tmp_per
left join
(select
person.pid as pid1,
person.pcucodeperson as pcucodeperson1,
(select l1.datecheck from visitlabchcyhembmsse l1  where person.pid = l1.pid  and person.pcucodeperson=l1.pcucodeperson  and l1.labcode='CH99'  and (l1.datecheck  IS NOT NULL OR  left(l1.datecheck,4) != '0000') group by l1.pid,l1.pcucodeperson) as CH99,
(select l1.datecheck from visitlabchcyhembmsse l1  where person.pid = l1.pid  and person.pcucodeperson=l1.pcucodeperson  and l1.labcode='CH25'  and (l1.datecheck  IS NOT NULL OR  left(l1.datecheck,4) != '0000') group by l1.pid,l1.pcucodeperson) as CH25,
(select l1.datecheck from visitlabchcyhembmsse l1  where person.pid = l1.pid  and person.pcucodeperson=l1.pcucodeperson  and l1.labcode='CH07'  and (l1.datecheck  IS NOT NULL OR  left(l1.datecheck,4) != '0000') group by l1.pid,l1.pcucodeperson) as CH07,
(select l1.datecheck from visitlabchcyhembmsse l1  where person.pid = l1.pid  and person.pcucodeperson=l1.pcucodeperson  and l1.labcode='CH14'  and (l1.datecheck  IS NOT NULL OR  left(l1.datecheck,4) != '0000') group by l1.pid,l1.pcucodeperson) as CH14,
(select l1.datecheck from visitlabchcyhembmsse l1  where person.pid = l1.pid  and person.pcucodeperson=l1.pcucodeperson  and l1.labcode='CH17'  and (l1.datecheck  IS NOT NULL OR  left(l1.datecheck,4) != '0000') group by l1.pid,l1.pcucodeperson) as CH17,
(select l1.datecheck from visitlabchcyhembmsse l1  where person.pid = l1.pid  and person.pcucodeperson=l1.pcucodeperson  and l1.labcode='CH04'  and (l1.datecheck  IS NOT NULL OR  left(l1.datecheck,4) != '0000') group by l1.pid,l1.pcucodeperson) as CH04,
(select l1.datecheck from visitlabchcyhembmsse l1  where person.pid = l1.pid  and person.pcucodeperson=l1.pcucodeperson  and l1.labcode='CH09'  and (l1.datecheck  IS NOT NULL OR  left(l1.datecheck,4) != '0000') group by l1.pid,l1.pcucodeperson) as CH09,
(select l1.datecheck from visitlabchcyhembmsse l1  where person.pid = l1.pid  and person.pcucodeperson=l1.pcucodeperson  and l1.labcode='Cha1'  and (l1.datecheck  IS NOT NULL OR  left(l1.datecheck,4) != '0000') group by l1.pid,l1.pcucodeperson) as Cha1,
(select l1.datecheck from visitlabchcyhembmsse l1  where person.pid = l1.pid  and person.pcucodeperson=l1.pcucodeperson  and l1.labcode='Chc1'  and (l1.datecheck  IS NOT NULL OR  left(l1.datecheck,4) != '0000') group by l1.pid,l1.pcucodeperson) as Chc1
FROM
personchronic
inner join person on person.pcucodeperson = personchronic.pcucodeperson AND person.pid = personchronic.pid
inner join cdisease on personchronic.chroniccode = cdisease.diseasecode
inner join cdiseasechronic on cdiseasechronic.groupcode = cdisease.codechronic
inner Join visitlabchcyhembmsse ON person.pcucodeperson = visitlabchcyhembmsse.pcucodeperson AND visitlabchcyhembmsse.pid = person.pid
inner join clabchcyhembmsse ON visitlabchcyhembmsse.labcode = clabchcyhembmsse.labcode
inner join ctitle on person.prename = ctitle.titlecode
inner join house on person.hcode = house.hcode and person.pcucodeperson = house.pcucode
inner join village ON house.villcode = village.villcode and house.pcucode = village.pcucode
where  visitlabchcyhembmsse.datecheck between '$str' and '$sto'
group by person.pid,person.pcucodeperson) as tmp_lab
on tmp_per.pid = tmp_lab.pid1 and tmp_per.pcucodeperson = tmp_lab.pcucodeperson1
group by villcode
order by villcode";
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
                text: '<?php echo "ร้อยละผู้ป่วยเบาหวานที่ได้รับการตรวจ Lab HbA1C ".$mu." ".$live_type_name."" ?>'
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