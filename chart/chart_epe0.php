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
$village = $_GET[village];
if($village == "00000000"){
	$wvill = "";
}elseif($village == "xxx"){
	$wvill = " AND right(h.villcode,2)='00'";	
}else{
	$wvill = " AND h.villcode='$village'";	
}
if($village == "00000000"){
	$mu = "ทุกหมู่บ้าน";
}else{
	$mu = getvillagename($village);	
}
$str = retdaterangstr($_GET[str]);
$sto = retdaterangsto($_GET[str]);
$strx = retDatets($str);
$stox = retDatets($sto);
$ds = $_GET[ds];
	if($ds == '00'){$ect = "";}else{$ect = " dc.group506code = '$ds' AND ";}
	
//ตัวแปร array ที่ใ้ชสำหรับแสดงกราฟ
	$group506name = array(); // ตัวแปรแกน x
	//ตัวแปรแกน y
	$countx = array();
	//หมดตัวแปรแกน y

//sql สำหรับดึงข้อมูล จาก jhcis
$sql = "SELECT
group506name,
count(pid) as countx
from
(SELECT p.pcucodeperson, p.pid, p.idcard, CONCAT(t.titlename,p.fname,' ',p.lname) AS pname,getageyearnum(p.birth,vd.sickdatestart) as age, d.diseasecode, h.hno,h.villcode,co.occupaname, vd.sickdatestart,dc.group506name
							FROM
							house AS h
							Inner Join person AS p ON h.pcucode = p.pcucodeperson AND h.hcode = p.hcode
							Inner Join visit AS v ON p.pcucodeperson = v.pcucodeperson AND p.pid = v.pid
							Inner Join visitdiag506address AS vd ON v.pcucode = vd.pcucode AND v.visitno = vd.visitno
							Inner Join cdisease AS d ON vd.diagcode = d.diseasecode
							Inner Join cdisease506 AS dc ON d.code506 = dc.group506code
							left Join ctitle AS t ON p.prename = t.titlecode
							left join coccupa co on p.occupa = co.occupacode
							WHERE $ect vd.sickdatestart BETWEEN  '$str' AND '$sto' $wvill
							ORDER BY h.villcode,h.hno) as tmp
group by group506name
order by countx desc";
//จบ sql
$result = mysql_query($sql);
while($row=mysql_fetch_array($result)) {
//array_push คือการนำค่าที่ได้จาก sql ใส่เข้าไปตัวแปร array
	array_push($countx,$row[countx]);
	array_push($group506name,$row[group506name]);
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
                text: '<?php echo "จำนวนผู้ป่วยด้วยโรคที่ต้องเฝ้าระวังทางระบาดวิทยา" ?>'
            },
            subtitle: {
                text: '<?= $hosp ?>'
            },
			
            xAxis: {
                categories: ['<?= implode("','", $group506name); //นำตัวแปร array แกน x มาใส่ ในที่นี้คือ เดือน?>']
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
                        this.x +': '+ this.y +'ครั้ง';
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
                                data: [<?= implode(',', $countx) // ข้อมูล array แกน y ?>]
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