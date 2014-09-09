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
	$wvill = " AND house.villcode='$villcode' ";	
}
$chk_ultra = $_GET[chk_ultra];
if($chk_ultra == "2"){
	$chksto = "and tmp.vitalcheck is not null";
}elseif($chk_ultra == "3"){
	$chksto = "and tmp.vitalcheck not like 'ปกติ' and tmp.vitalcheck is not null";
}elseif($chk_ultra == "4"){
	$chksto = "and tmp.vitalcheck is null";		
}else{
	$chksto = "";	
}
$str = retdaterangstr($_GET[str]);
$sto = retdaterangsto($_GET[str]);
$strx = retDatets($str);
$stox = retDatets($sto);
$ovyear = substr($sto,0,4);
	
//ตัวแปร array ที่ใ้ชสำหรับแสดงกราฟ
	$villname = array(); // ตัวแปรแกน x
	//ตัวแปรแกน y
	$percentc = array();
	//หมดตัวแปรแกน y

//sql สำหรับดึงข้อมูล จาก jhcis
$sql = "SELECT
tmp2.pcucode,
tmp2.villcode,
tmp2.villname,
if(count(distinct tmp2.pid) = 0,0,count(distinct tmp2.ultrapid)/count(distinct tmp2.pid)*100) as percent
FROM
(SELECT
person.pcucodeperson,
person.pid,
person.idcard,
CONVERT(concat(ifnull(ctitle.titlename,ifnull(person.prename,'ไม่ระบุ') ),person.fname,' ' ,person.lname) using utf8) as pname,
ctitle.titlename,
person.fname,
person.lname,
person.birth,
getageyearnum(person.birth,visit.visitdate) AS age,
house.hno,
house.villcode,
village.villname,
house.xgis,
house.ygis,
house.usernamedoc,
visit.pcucode,
visit.visitno,
visit.visitdate,
visit.symptoms,
visit.vitalcheck,
group_concat(visitdiag.diagcode) as xx,
tmp.pid as ultrapid,
tmp.visitno as ultravisitno,
tmp.visitdate as ultravisitdate,
tmp.symptoms as ultrasys,
tmp.vitalcheck as ultravital,
tmp.diagcode as ultradiag
FROM
village
INNER JOIN house ON village.pcucode = house.pcucode AND village.villcode = house.villcode
INNER JOIN person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
INNER JOIN visit ON person.pcucodeperson = visit.pcucodeperson AND person.pid = visit.pid
INNER JOIN visitdiag ON visit.pcucode = visitdiag.pcucode AND visit.visitno = visitdiag.visitno
LEFT JOIN ctitle ON person.prename = ctitle.titlecode
left JOIN
(SELECT
v.pcucode,
v.visitno,
v.visitdate,
v.pcucodeperson,
v.pid,
v.symptoms,
v.vitalcheck,
vd.diagcode
FROM
visit v
INNER JOIN visitdiag vd ON v.pcucode = vd.pcucode AND v.visitno = vd.visitno
where v.visitdate between '$str' and '$sto' and v.symptoms like '%ตรวจอัลตร้าซาว%' and vd.diagcode like 'Z12.8') as tmp
on tmp.pcucodeperson = visit.pcucodeperson and tmp.pid = visit.pid
where right(house.villcode,2) <> '00' and getageyearnum(person.birth,visit.visitdate) > 39 and visit.visitdate between '$str' and '$sto' $wvill
group by visit.pcucode, visit.visitno
having xx like '%Z11.6%' and xx like '%B66%') as tmp2
group by tmp2.pcucode, tmp2.villname
order by tmp2.pcucode, tmp2.villcode";
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
                text: '<?php echo "แผนภูมิแสดงจำนวนประชาชนอายุ 40 ปีขึ้นไปที่ตรวจพบพยาธิใบไม้ตับได้รับการตรวจอัลตร้าซาวด์" ?>'
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
