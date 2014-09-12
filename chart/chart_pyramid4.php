<?php
set_time_limit(0);
include("includes/conndb.php");
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
$hosp=$row[chospital_hosname]; //ได้ตัวแปร $hosp
$str = $_GET[yearx]."-07-01";
$sto = $_GET[year]+543;
//ตัวแปร array ที่ใ้ชสำหรับแสดงกราฟ
	$detail = array(); // ตัวแปรแกน x
	//ตัวแปรแกน y
	$male = array();
	$female = array();
	//หมดตัวแปรแกน y

//sql สำหรับดึงข้อมูล จาก jhcis
$sql = "SELECT
case when getageyearnum(person.birth,'2013-07-01') between 0 and 4 then 'อายุ 0 - 4 ปี' 
     when getageyearnum(person.birth,'2013-07-01') between 5 and 9 then 'อายุ 5 - 9 ปี'
     when getageyearnum(person.birth,'2013-07-01') between 10 and 14 then 'อายุ 10 - 14 ปี' 
     when getageyearnum(person.birth,'2013-07-01') between 15 and 19 then 'อายุ 15 - 19 ปี' 
     when getageyearnum(person.birth,'2013-07-01') between 20 and 24 then 'อายุ 20 - 24 ปี' 
     when getageyearnum(person.birth,'2013-07-01') between 25 and 29 then 'อายุ 25 - 29 ปี' 
     when getageyearnum(person.birth,'2013-07-01') between 30 and 34 then 'อายุ 30 - 34 ปี' 
     when getageyearnum(person.birth,'2013-07-01') between 35 and 39 then 'อายุ 35 - 39 ปี' 
     when getageyearnum(person.birth,'2013-07-01') between 40 and 44 then 'อายุ 40 - 44 ปี' 
     when getageyearnum(person.birth,'2013-07-01') between 45 and 49 then 'อายุ 45 - 49 ปี' 
     when getageyearnum(person.birth,'2013-07-01') between 50 and 54 then 'อายุ 50 - 54 ปี' 
     when getageyearnum(person.birth,'2013-07-01') between 55 and 59 then 'อายุ 55 - 59 ปี' 
     when getageyearnum(person.birth,'2013-07-01') between 60 and 64 then 'อายุ 60 - 64 ปี' 
     when getageyearnum(person.birth,'2013-07-01') between 65 and 69 then 'อายุ 65 - 69 ปี' 
     when getageyearnum(person.birth,'2013-07-01') between 70 and 74 then 'อายุ 70 - 74 ปี' 
     when getageyearnum(person.birth,'2013-07-01') between 75 and 79 then 'อายุ 75 - 79 ปี' 
     when getageyearnum(person.birth,'2013-07-01') between 80 and 120 then 'อายุ 80 ปีขึ้นไป'  else null end as detail,
'ชาย' as sex,
sum(case when person.sex = '1' then 1 else 0 end)/(select count(distinct p.pid) from house h
Inner Join person p ON h.pcucode = p.pcucodeperson AND h.hcode = p.hcode
left join persondeath pd on p.pcucodeperson = pd.pcucodeperson and p.pid = pd.pid
WHERE (((p.dischargetype is null) or (p.dischargetype = '9')) or DATE_FORMAT(pd.deaddate,'%Y') <= DATE_FORMAT('2013-07-01','%Y')) and DATE_FORMAT(p.birth,'%Y') <= DATE_FORMAT('2013-07-01','%Y') and SUBSTRING(h.villcode,7,2) <> '00')*-100 as male,
sum(case when person.sex = '2' then 1 else 0 end)/(select count(distinct p.pid) from house h
Inner Join person p ON h.pcucode = p.pcucodeperson AND h.hcode = p.hcode
left join persondeath pd on p.pcucodeperson = pd.pcucodeperson and p.pid = pd.pid
WHERE (((p.dischargetype is null) or (p.dischargetype = '9')) or DATE_FORMAT(pd.deaddate,'%Y') <= DATE_FORMAT('2013-07-01','%Y')) and DATE_FORMAT(p.birth,'%Y') <= DATE_FORMAT('2013-07-01','%Y')  and SUBSTRING(h.villcode,7,2) <> '00')*100 as female
FROM
house
Inner Join person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
left join persondeath on person.pcucodeperson = persondeath.pcucodeperson and person.pid = persondeath.pid
WHERE (((person.dischargetype is null) or (person.dischargetype = '9')) or DATE_FORMAT(persondeath.deaddate,'%Y') <= DATE_FORMAT('2013-07-01','%Y')) and DATE_FORMAT(person.birth,'%Y') <= DATE_FORMAT('2013-07-01','%Y') and SUBSTRING(house.villcode,7,2) <> '00'
group by detail
order by sex, SUBSTRING(detail,6,2)*1";
//จบ sql
$result = mysql_query($sql);
while($row=mysql_fetch_array($result)) {
//array_push คือการนำค่าที่ได้จาก sql ใส่เข้าไปตัวแปร array
	array_push($male,$row[male]);
	array_push($female,$row[female]);
	array_push($detail,$row[detail]);
}
?>
<!DOCTYPE HTML>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>Highcharts Example</title>
        <script src="js/jquery-1.9.1.min.js"></script>
        <script src="js/highcharts.js"></script>
        <script src="js/exporting.js"></script>    
        <script>
$(function () {
    var chart,
        categories = ['<?= implode("','", $detail);?>'];
    $(document).ready(function() {
        $('#container').highcharts({
            chart: {
                type: 'bar'
            },
            title: {
                text: 'ปิรามิดประชากร'
            },
            subtitle: {
                text: '<?= $hosp ?>'
            },
            xAxis: [{
                                categories: categories,
                                reversed: false
                            }, {// mirror axis on right side
                                opposite: true,
                                reversed: false,
                                categories: categories,
                                linkedTo: 0
                            }],
                        yAxis: {
                            title: {
                                text: null
                            },
                            labels: {
                                formatter: function() {
                                    //return (Math.abs(this.value));
                                    return Highcharts.numberFormat(Math.abs(this.value), 0,',');
                                    
                                }
                            },
                            min: -6,
                            max: 6
                        },
                        plotOptions: {
                            series: {
                                stacking: 'normal',
                                cursor: 'pointer',
                                point: {
                                    events: {
                                        click: function() {
                                            //alert ('Category: '+ this.category +', value: '+ this.y);
                                            window.location = 'bar.php?grp=' + this.category;
                                        }
                                    }
                                }
                            }

                        },
                        tooltip: {
                            formatter: function() {
                                return '<b>' + this.series.name + ',' + this.point.category + '</b><br/>' +
                                        'จำนวน ' + Highcharts.numberFormat(Math.abs(this.point.y), 0,',') + ' %';
                            }
                        },
    
            series: [{
                                name: 'ชาย',
                                data: [<?= implode(',', $male) ?>]
                            }, {
                                name: 'หญิง',
                                data: [<?= implode(',', $female) ?>]
                            }]
        });
    });
    
});
        </script>


    </head> 
    <body> 
      <div id="container" style="min-width: 320px; height: 380px; margin: 0 auto"></div>       
    </body>
</html>
