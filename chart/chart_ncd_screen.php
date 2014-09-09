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
$village = $_GET[village];
if($village == "00000000"){
	$mu = "ทุกหมู่บ้าน";
}else{
	$mu = getvillagename($village);	
}
$getchronic = $_GET[getchronic];
$live_type = $_GET[live_type];
$getage = $_GET[getage];
if($village == '00000000'){$ect2 = "";}else{$ect2 = " house.villcode = '$village' AND ";}
if($getchronic == '9'){$gchronic = "";}else{$gchronic = "where pid not in (SELECT p.pid FROM house AS h Inner Join person AS p ON h.pcucode = p.pcucodeperson AND h.hcode = p.hcode Inner Join personchronic AS pc ON p.pcucodeperson = pc.pcucodeperson AND p.pid = pc.pid Inner Join cdisease AS d ON pc.chroniccode = d.diseasecode Inner Join cdiseasechronic AS dc ON d.codechronic = dc.groupcode WHERE ((p.dischargetype is null) or (p.dischargetype = '9')) AND SUBSTRING(h.villcode,7,2) <> '00' AND pc.typedischart NOT IN  ('01', '02','07','10') and dc.groupcode in ('01','10') GROUP BY p.pid)";}
if($live_type == '2'){$live_type2 = "and person.typelive in ('0','1','2')";}elseif($live_type == '1'){$live_type2 = "and person.typelive in ('0','1','3')";}else{$live_type2 = "";}
if($getage == '15'){$gage = "AND FLOOR((TO_DAYS(NOW())-TO_DAYS(person.birth))/365.25) > 14";}elseif($getage == '35'){$gage = "AND FLOOR((TO_DAYS(NOW())-TO_DAYS(person.birth))/365.25) > 34";}else{$gage = "AND FLOOR((TO_DAYS(NOW())-TO_DAYS(person.birth))/365.25) between '15 and '34'";}
if($getage == "15"){
	$gagename = "อายุ 15 ปี ขึ้นไป";
}elseif($getage == "35"){
	$gagename = "อายุ 35 ปี ขึ้นไป";
}else{
	$gagename = "อายุ 15 - 34 ปี";
}
if($live_type == '2'){$live_type_name = "ตามทะเบียนบ้าน(0,1,2)";}elseif($live_type == '1'){$live_type_name = "ที่อาศัยอยู่จริง (0,1,3)";}else{$live_type_name = "ทั้งหมดในเขตรับผิดชอบ(0,1,2,3)";}	
if($getchronic == '9'){$gchronic_name = "ทุกคนทั้งป่วยและไม่ป่วย";}else{$gchronic_name = "เฉพาะผู้ที่ยังไม่ป่วย";}	
	
//ตัวแปร array ที่ใ้ชสำหรับแสดงกราฟ
	$villname = array(); // ตัวแปรแกน x
	//ตัวแปรแกน y
	$percentc = array();
	//หมดตัวแปรแกน y

//sql สำหรับดึงข้อมูล จาก jhcis
$sql = "select
per.pcucodeperson,
per.villcode,
per.villname,
count(distinct pid1)/count(distinct pid)*100 as percent
from
(SELECT
person.pcucodeperson,
person.pid,
person.fname,
CONCAT(ctitle.titlename,person.fname,' ',person.lname) AS pname,
house.hno,
house.villcode,
village.villname,
house.xgis,
house.ygis,
person.birth,
FLOOR((TO_DAYS(NOW())-TO_DAYS(person.birth))/365.25) AS age
FROM
village
INNER JOIN house ON village.pcucode = house.pcucode AND village.villcode = house.villcode
Inner Join person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
Inner Join cstatus ON person.marystatus = cstatus.statuscode
Inner Join ctitle ON person.prename = ctitle.titlecode
WHERE $ect2 ((person.dischargetype is null) or (person.dischargetype = '9')) AND
				SUBSTRING(house.villcode,7,2) <> '00' $gage $live_type2 ORDER BY house.villcode,house.hno*1
) as per
left join 
(SELECT 
person.pcucodeperson as pcucodeperson1,
person.pid as pid1,
house.villcode, 
house.hno, house.xgis, house.ygis, person.idcard, CONCAT(ctitle.titlename,person.fname,' ',person.lname) AS pname, FLOOR((TO_DAYS(NOW())-TO_DAYS(person.birth))/365.25) as age, ncd_person_ncd_screen.screen_date, ncd_person_ncd_screen.bmi, ncd_person_ncd_screen.weight, ncd_person_ncd_screen.height, ncd_person_ncd_screen.waist, ncd_person_ncd_screen.hbp_s1,
ncd_person_ncd_screen.hbp_d1, ncd_person_ncd_screen.result_new_dm, ncd_person_ncd_screen.result_new_hbp, ncd_person_ncd_screen.result_new_waist, ncd_person_ncd_screen.result_new_obesity, if(ncd_person_ncd_screen.hbp_s2 is null ,if(ncd_person_ncd_screen.hbp_s1 between 120 and 139 or ncd_person_ncd_screen.hbp_d1 between 80 and  89, 'เสี่ยง',if(ncd_person_ncd_screen.hbp_s1 > 139 or ncd_person_ncd_screen.hbp_d1 > 89,'สูง','ปกติ')),if(ncd_person_ncd_screen.hbp_s2  between 120 and 139 or  ncd_person_ncd_screen.hbp_d2 between 80 and  89, 'เสี่ยง',if(ncd_person_ncd_screen.hbp_s2 > 139 or ncd_person_ncd_screen.hbp_d2 > 89,'สูง','ปกติ'))) as resultht,
if(ncd_person_ncd_screen.bstest = '3' or ncd_person_ncd_screen.bstest = '1',if(ncd_person_ncd_screen.bsl between 100 and 125,'เสี่ยง',if(ncd_person_ncd_screen.bsl > 125,'สูง','ปกติ')),if(ncd_person_ncd_screen.bsl between 140 and 199,'เสี่ยง',if(ncd_person_ncd_screen.bsl > 199,'สูง','ปกติ'))) as resultdm,
ncd_person_ncd_screen.bsl,
ncd_person_ncd_screen.hbp_s2, ncd_person_ncd_screen.hbp_d2, ncd_person_ncd_screen.bstest,
if(ncd_person_ncd_screen.waist is null,null,if( (person.sex='1' and ncd_person_ncd_screen.waist >89 ) or (person.sex='2' and ncd_person_ncd_screen.waist >79),'รอบเอวเกิน','รอบเอวปกติ')) as resultwaist
from
house
Inner Join person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
Inner Join village ON village.pcucode = house.pcucode AND village.villcode = house.villcode
inner join ncd_person_ncd_screen on person.pid = ncd_person_ncd_screen.pid AND person.pcucodeperson = ncd_person_ncd_screen.pcucode
Inner Join ctitle ON person.prename = ctitle.titlecode
where $ect2 ((person.dischargetype is null) or (person.dischargetype = '9'))  and SUBSTRING(house.villcode,7,2) <> '00' and ncd_person_ncd_screen.screen_date BETWEEN '$str' AND '$sto' $gage $live_type2
							ORDER BY
							house.villcode,house.hno*1
) as fp
on per.pcucodeperson = fp.pcucodeperson1 and per.pid = fp.pid1
$gchronic
group by pcucodeperson, villcode";
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
                text: '<?php echo "ประชาชน".$gagename."ที่ได้รับการคัดกรองโรคเบาหวานและความดันโลหิตสูง ".$mu ."ประชากร ".$live_type_name."   ".$gchronic_name."" ?>'
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