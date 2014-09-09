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
	if($chronic == '00'){
		$ect = "sum(case when chronicc like '%01%' or chronicc like '%10%' then 1 else 0 end) as dmorht";
	}elseif($chronic == '01'){
		$ect = "sum(case when chronicc like '%01%' and chronicc like '%10%' then 1 else 0 end) as dmorht";
	}elseif($chronic == '02'){
		$ect = "sum(case when chronicc not like '%01%' and chronicc like '%10%' then 1 else 0 end) as dmorht";
	}elseif($chronic == '03'){
		$ect = "sum(case when chronicc like '%01%' and chronicc not like '%10%' then 1 else 0 end) as dmorht";
	}elseif($chronic == '04'){
		$ect = "sum(case when chronicc like '%10%' then 1 else 0 end) as dmorht";
	}elseif($chronic == '05'){
		$ect = "sum(case when chronicc like '%01%' then 1 else 0 end) as dmorht";
	}else{}
	if($chronic == '00'){
		$ect_name = "เบาหวานหรือความดัน";
	}elseif($chronic == '01'){
		$ect_name = "เบาหวานและความดัน";
	}elseif($chronic == '02'){
		$ect_name = "เบาหวานอย่างเดียว";
	}elseif($chronic == '03'){
		$ect_name = "ความดันโลหิตสูงอย่างเดียว";
	}elseif($chronic == '04'){
		$ect_name = "เบาหวาน";
	}elseif($chronic == '05'){
		$ect_name = "ความดัน";
	}else{}		
	
//ตัวแปร array ที่ใ้ชสำหรับแสดงกราฟ
	$villname = array(); // ตัวแปรแกน x
	//ตัวแปรแกน y
	$dmorht = array();
	//หมดตัวแปรแกน y

//sql สำหรับดึงข้อมูล จาก jhcis
$sql = "select
villcode,
villname,
$ect
from
(select villname,concat(ifnull(titlename,'..') ,fname,' ',lname) as pname, FLOOR((TO_DAYS(NOW())-TO_DAYS(person.birth))/365.25) as age, house.hno,house.villcode,house.xgis,house.ygis,person.idcard,person.pcucodeperson,person.pid,pc.datefirstdiag,pc.datedxfirst,pc.datedischart,
CASE
when pc.typedischart='01' then 'หาย'
when pc.typedischart='02' then 'ตาย'
when pc.typedischart='03' then 'ยังรักษาอยู่ฯ'
when pc.typedischart='04' then 'ไม่ทราบ(ไม่มีข้อมูล)'
when pc.typedischart='05' then 'รอการจำหน่าย/เฝ้าระวัง'
when pc.typedischart='06' then 'ยังรักษาอยู่ฯ'
when pc.typedischart='07' then 'ครบการรักษาฯ'
when pc.typedischart='08' then 'โรคอยู่ในภาวะสงบฯ'
when pc.typedischart='09' then 'ปฏิเสธการรักษาฯ'
when pc.typedischart='10' then 'ออกจากพื้นที่'
else null end AS typedischart,pc.cup
,group_concat(dc.groupcode) as chronicc
,group_concat(dc.groupname) as chronicx
FROM personchronic pc
left join person ON pc.pid = person.pid and pc.pcucodeperson = person.pcucodeperson
left join house ON person.hcode = house.hcode and person.pcucodeperson = house.pcucode
left join village ON house.villcode = village.villcode
left join ctitle ON person.prename = ctitle.titlecode
left Join cdisease d ON pc.chroniccode = d.diseasecode
left Join cdiseasechronic dc ON d.codechronic = dc.groupcode
where SUBSTRING(house.villcode,7,2) <> '00' AND pc.typedischart NOT IN  ('01', '02','07','10') and person.pid NOT IN (SELECT persondeath.pid FROM persondeath WHERE persondeath.pcucodeperson= person.pcucodeperson and (persondeath.deaddate IS NULL OR persondeath.deaddate<=now())) $wvill
group by pc.pcucodeperson, pc.pid
ORDER BY village.villcode,person.fname) as tmp
group by villcode";
//จบ sql
$result = mysql_query($sql);
while($row=mysql_fetch_array($result)) {
//array_push คือการนำค่าที่ได้จาก sql ใส่เข้าไปตัวแปร array
	array_push($dmorht,$row[dmorht]);
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
                text: '<?php echo "จำนวนผู้ป่วยโรค".$ect_name."  ".$mu."" ?>'
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
                                name: 'คน',
                                data: [<?= implode(',', $dmorht) // ข้อมูล array แกน y ?>]
                            }]
        });
    });
        </script>


    </head> 
    <body> 
      <div id="container"></div>
    </body>
</html>
