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
	$wvill = " AND h.villcode='$villcode' ";	
}
if($villcode == "00000000"){
	$mu = "ทุกหมู่บ้าน";
}else{
	$mu = getvillagename($villcode);	
}
$str = retDate($_GET[str]);
$sto = retDate($_GET[sto]);
$live_type = $_GET[live_type];
if($live_type == '2'){$live_type_name = "ตามทะเบียนบ้าน(0,1,2)";}elseif($live_type == '1'){$live_type_name = "ที่อาศัยอยู่จริง (0,1,3)";}else{$live_type_name = "ทั้งหมดในเขตรับผิดชอบ(0,1,2,3)";}	
if($live_type == '2'){$live_type2 = " and p.typelive in ('0','1','2')";}elseif($live_type == '1'){$live_type2 = "and p.typelive in ('0','1','3')";}else{$live_type2 = "and p.typelive in ('0','1','2','3')";}
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
anc5q.pcucodeperson,
anc5q.villcode,
COUNT(DISTINCT IF(anc5q.anc1 is not null and anc5q.anc2 is not null and anc5q.anc3 is not null and anc5q.anc4 is not null and anc5q.anc5 is not null, CONCAT(anc5q.pcucodeperson,'-',anc5q.pid), NULL))/COUNT(DISTINCT CONCAT(anc5q.pcucodeperson,'-',anc5q.pid))*100 as percent
FROM
(select
*,
case when anc1 is not null and anc2 is not null and anc3 is not null and anc4 is not null and anc5 is not null then 1 else 0 end as chk
from
(
SELECT
ancperson.*,
(select DATE_FORMAT(MAX(v1.datecheck),'%Y-%m-%d') from visitanc v1 inner JOIN visitancpregnancy p1 on p1.pcucodeperson = v1.pcucodeperson and v1.pid = p1.pid and p1.pregno = v1.pregno where v1.pid = ancperson.pid and v1.pcucodeperson = ancperson.pcucodeperson and DATEDIFF(v1.datecheck,p1.lmp) <= 90 and v1.pregno = ancperson.pregno group by ancperson.pid,ancperson.pcucodeperson,ancperson.pregno) as anc1,
(select DATE_FORMAT(MAX(v2.datecheck),'%Y-%m-%d') from visitanc v2 inner JOIN visitancpregnancy p2 on p2.pcucodeperson = v2.pcucodeperson and v2.pid = p2.pid and p2.pregno = v2.pregno where v2.pid = ancperson.pid and v2.pcucodeperson = ancperson.pcucodeperson and DATEDIFF(v2.datecheck,p2.lmp) between 112 AND 146 and v2.pregno = ancperson.pregno group by ancperson.pid,ancperson.pcucodeperson,ancperson.pregno) as anc2,
(select DATE_FORMAT(MAX(v3.datecheck),'%Y-%m-%d') from visitanc v3 inner JOIN visitancpregnancy p3 on p3.pcucodeperson = v3.pcucodeperson and v3.pid = p3.pid and p3.pregno = v3.pregno where v3.pid = ancperson.pid and v3.pcucodeperson = ancperson.pcucodeperson and DATEDIFF(v3.datecheck,p3.lmp) between 168 AND 202 and v3.pregno = ancperson.pregno group by ancperson.pid,ancperson.pcucodeperson,ancperson.pregno) as anc3,
(select DATE_FORMAT(MAX(v4.datecheck),'%Y-%m-%d') from visitanc v4 inner JOIN visitancpregnancy p4 on p4.pcucodeperson = v4.pcucodeperson and v4.pid = p4.pid and p4.pregno = v4.pregno where v4.pid = ancperson.pid and v4.pcucodeperson = ancperson.pcucodeperson and DATEDIFF(v4.datecheck,p4.lmp) between 210 AND 244 and v4.pregno = ancperson.pregno group by ancperson.pid,ancperson.pcucodeperson,ancperson.pregno) as anc4,
(select DATE_FORMAT(MAX(v5.datecheck),'%Y-%m-%d') from visitanc v5 inner JOIN visitancpregnancy p5 on p5.pcucodeperson = v5.pcucodeperson and v5.pid = p5.pid and p5.pregno = v5.pregno where v5.pid = ancperson.pid and v5.pcucodeperson = ancperson.pcucodeperson and DATEDIFF(v5.datecheck,p5.lmp) between 252 AND 286 and v5.pregno = ancperson.pregno group by ancperson.pid,ancperson.pcucodeperson,ancperson.pregno) as anc5
FROM
(SELECT
a.pcucodeperson,
a.pid,
p.fname,
concat(c.titlename, p.fname , '  ' , p.lname) AS pname,
p.birth,
FLOOR(datediff('$str',p.birth)/365.25) as age,
h.hno,
h.villcode,
h.xgis,
h.ygis,
a.pregno,
ap.lmp,
DATEDIFF('$sto',ap.lmp) as page
FROM
house as h
INNER JOIN person as p on p.pcucodeperson = h.pcucodeperson and p.hcode = h.hcode
LEFT JOIN ctitle as c on c.titlecode = p.prename
INNER JOIN visitancpregnancy as ap on ap.pcucodeperson = p.pcucodeperson and ap.pid = p.pid 
INNER JOIN visitanc as a on ap.pcucodeperson = a.pcucodeperson and ap.pid = a.pid and ap.pregno = a.pregno
where DATEDIFF('$sto',ap.lmp) BETWEEN 286 and DATEDIFF('$sto','$str')+286 $live_type2 AND ((p.dischargetype is null) or (p.dischargetype = '9')) $wvill
GROUP BY a.pcucodeperson, a.pid, a.pregno) as ancperson) as tmp_anc
order by villcode, fname) as anc5q
GROUP BY anc5q.pcucodeperson,anc5q.villcode
order by anc5q.pcucodeperson,anc5q.villcode";
//จบ sql
$result = mysql_query($sql);
while($row=mysql_fetch_array($result)) {
			$villnamerow = villnamechart($row[villcode]);
			$percent1 = number_format($row[percent], 2, '.', '');
//array_push คือการนำค่าที่ได้จาก sql ใส่เข้าไปตัวแปร array
	array_push($percentc,$percent1);
	array_push($villname,$villnamerow);
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
                text: '<?php echo "ร้อยละการคัดกรองภาวะซึมเศร้า อายุ ".$age." ปี".$mu." ".$live_type_name."" ?>'
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