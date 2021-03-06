<?php
session_start();
set_time_limit(0);
if($_SESSION[username]){
include("../includes/conndb.php"); 
include("../includes/config.inc.php");
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="../ico/favicon.ico">

    <title><?php echo $titleweb; ?></title>

    <!-- Bootstrap core CSS -->
    <link href="../css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="../css/style.css" rel="stylesheet">

    <!-- Just for debugging purposes. Don't actually copy this line! -->
    <!--[if lt IE 9]><script src="../../assets/js/ie8-responsive-file-warning.js"></script><![endif]-->

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

    <script src="../js/jquery.1.11.0.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
</head>
<body>
<?php
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
$chk_epi = $_GET[chk_epi];
if($chk_epi == "9"){
	$chksto = "";
}elseif($chk_epi == "1"){
	$chksto = "where chk != 0";	
}else{
	$chksto = "where chk = 0";
}
$str = $_GET[str];
$strx = retDatets($str);
$live_type = $_GET[live_type];
if($live_type == '2'){$live_type_name = "ตามทะเบียนบ้าน(0,1,2)";}elseif($live_type == '1'){$live_type_name = "ที่อาศัยอยู่จริง (0,1,3)";}else{$live_type_name = "ทั้งหมดในเขตรับผิดชอบ(0,1,2,3)";}	
if($live_type == '2'){$live_type2 = "and person.typelive in ('0','1','2')";}elseif($live_type == '1'){$live_type2 = "and person.typelive in ('0','1','3')";}else{$live_type2 = "and person.typelive in ('0','1','2','3')";}
$sql = "select * from(select
*,
case when (age between 0 and 3) and bcg is not null and hbv1 is not null then 1
	when (age between 4 and 5) and bcg is not null and hbv1 is not null and (dtp1 is not null or dhb1 is not null) and opv1 is not null then 2
	when (age between 6 and 7) and bcg is not null and hbv1 is not null and (dtp1 is not null or dhb1 is not null) and opv1 is not null and (dtp2 is not null or dhb2 is not null) and opv2 is not null then 3
	when (age between 8 and 10) and bcg is not null and hbv1 is not null and (dtp1 is not null or dhb1 is not null) and opv1 is not null and (dtp2 is not null or dhb2 is not null) and opv2 is not null and (dtp3 is not null or dhb3 is not null) and opv3 is not null then 4
	when (age between 11 and 19) and bcg is not null and hbv1 is not null and (dtp1 is not null or dhb1 is not null) and opv1 is not null and (dtp2 is not null or dhb2 is not null) and opv2 is not null and (dtp3 is not null or dhb3 is not null) and opv3 is not null and (meas is not null or mmr is not null) then 5
	when (age between 20 and 21) and bcg is not null and hbv1 is not null and (dtp1 is not null or dhb1 is not null) and opv1 is not null and (dtp2 is not null or dhb2 is not null) and opv2 is not null and (dtp3 is not null or dhb3 is not null) and opv3 is not null and (meas is not null or mmr is not null) and je1 is not null and dtp4 is not null and opv4 is not null then 6
	when (age between 22 and 31) and bcg is not null and hbv1 is not null and (dtp1 is not null or dhb1 is not null) and opv1 is not null and (dtp2 is not null or dhb2 is not null) and opv2 is not null and (dtp3 is not null or dhb3 is not null) and opv3 is not null and (meas is not null or mmr is not null) and je1 is not null and dtp4 is not null and opv4 is not null and je2 is not null then 7
	when (age between 32 and 49) and bcg is not null and hbv1 is not null and (dtp1 is not null or dhb1 is not null) and opv1 is not null and (dtp2 is not null or dhb2 is not null) and opv2 is not null and (dtp3 is not null or dhb3 is not null) and opv3 is not null and (meas is not null or mmr is not null) and je1 is not null and dtp4 is not null and opv4 is not null and je2 is not null and je3 is not null then 8
	when (age between 50 and 71) and bcg is not null and hbv1 is not null and (dtp1 is not null or dhb1 is not null) and opv1 is not null and (dtp2 is not null or dhb2 is not null) and opv2 is not null and (dtp3 is not null or dhb3 is not null) and opv3 is not null and (meas is not null or mmr is not null) and je1 is not null and dtp4 is not null and opv4 is not null and je2 is not null and je3 is not null and dtp5 is not null and opv5 is not null then 9
else
0
end as chk
from
(SELECT 
v.pid
,v.pcucodeperson
,person.birth as bb
,DATE_FORMAT(person.birth,'%Y-%m-%d' ) as birth
,CONVERT(concat(ifnull(titlename,ifnull(prename,'ไม่ระบุ') ),' ',person.fname,'  ',person.lname) using utf8) as pname
, ROUND(DATEDIFF('$str',birth) /30) AS age
,village.villno
,person.father
,person.mother
,house.hno
,house.hcode
,house.xgis
,house.ygis
,village.villname
, village.villcode
,chospital.hosname
,cprovince.provname
,cdistrict.distname
,(select DATE_FORMAT(v1.dateepi,'%Y-%m-%d')  from visitepi v1  where v.pid = v1.pid  and v.pcucodeperson=v1.pcucodeperson  and v1.vaccinecode='DTP1'  and (v1.dateepi  IS NOT NULL OR  left(v1.dateepi,4) != '0000'  )   group by v1.pid    and v1.pcucodeperson) as dtp1
,(select DATE_FORMAT(v2.dateepi,'%Y-%m-%d')  from visitepi v2  where v.pid = v2.pid  and v.pcucodeperson=v2.pcucodeperson  and v2.vaccinecode='DTP2'  and (v2.dateepi  IS NOT NULL OR  left(v2.dateepi,4) != '0000'  )  group by v2.pid    and v2.pcucodeperson) as dtp2
,(select DATE_FORMAT(v3.dateepi,'%Y-%m-%d')  from visitepi v3  where v.pid = v3.pid  and v.pcucodeperson=v3.pcucodeperson  and v3.vaccinecode='DTP3'  and (v3.dateepi  IS NOT NULL OR  left(v3.dateepi,4) !=  '0000'  )  group by v3.pid    and v3.pcucodeperson) as dtp3
,(select DATE_FORMAT(v4.dateepi,'%Y-%m-%d')  from visitepi v4  where v.pid = v4.pid  and v.pcucodeperson=v4.pcucodeperson  and v4.vaccinecode='DTP4'  and (v4.dateepi  IS NOT NULL OR  left(v4.dateepi,4) !=  '0000'  )  group by v4.pid    and v4.pcucodeperson) as dtp4
,(select DATE_FORMAT(v5.dateepi,'%Y-%m-%d')  from visitepi v5  where v.pid = v5.pid  and v.pcucodeperson=v5.pcucodeperson  and v5.vaccinecode='DTP5'  and (v5.dateepi  IS NOT NULL OR  left(v5.dateepi,4) !=  '0000'  )  group by v5.pid    and v5.pcucodeperson) as dtp5
,(select DATE_FORMAT(v6.dateepi,'%Y-%m-%d')  from visitepi v6  where v.pid = v6.pid  and v.pcucodeperson=v6.pcucodeperson  and v6.vaccinecode='DHB1'  and (v6.dateepi  IS NOT NULL OR  left(v6.dateepi,4) !=  '0000'  )  group by v6.pid    and v6.pcucodeperson) as dhb1
,(select DATE_FORMAT(v7.dateepi,'%Y-%m-%d')  from visitepi v7  where v.pid = v7.pid  and v.pcucodeperson=v7.pcucodeperson  and v7.vaccinecode='DHB2'  and (v7.dateepi  IS NOT NULL OR  left(v7.dateepi,4) !=  '0000'  )  group by v7.pid    and v7.pcucodeperson) as dhb2
,(select DATE_FORMAT(v8.dateepi,'%Y-%m-%d')  from visitepi v8  where v.pid = v8.pid  and v.pcucodeperson=v8.pcucodeperson  and v8.vaccinecode='DHB3'  and (v8.dateepi  IS NOT NULL OR  left(v8.dateepi,4) !=  '0000'  )  group by v8.pid    and v8.pcucodeperson) as dhb3
,(select DATE_FORMAT(v9.dateepi,'%Y-%m-%d')  from visitepi v9  where v.pid = v9.pid  and v.pcucodeperson=v9.pcucodeperson  and v9.vaccinecode='HBV1'  and (v9.dateepi  IS NOT NULL OR  left(v9.dateepi,4) !=  '0000'  )   group by v9.pid   and v9.pcucodeperson) as hbv1
,(select DATE_FORMAT(v10.dateepi,'%Y-%m-%d') from visitepi v10 where v.pid = v10.pid and v.pcucodeperson=v10.pcucodeperson and v10.vaccinecode='HBV2' and (v10.dateepi IS NOT NULL OR  left(v10.dateepi,4) !=  '0000'  ) group by v10.pid and v10.pcucodeperson) as hbv2
,(select DATE_FORMAT(v11.dateepi,'%Y-%m-%d') from visitepi v11 where v.pid = v11.pid and v.pcucodeperson=v11.pcucodeperson and v11.vaccinecode='HBV3' and (v11.dateepi IS NOT NULL OR  left(v11.dateepi,4) !=  '0000'  ) group by v11.pid and v11.pcucodeperson) as hbv3
,(select DATE_FORMAT(v12.dateepi,'%Y-%m-%d') from visitepi v12 where v.pid = v12.pid and v.pcucodeperson=v12.pcucodeperson and v12.vaccinecode='JE1'  and (v12.dateepi IS NOT NULL OR  left(v12.dateepi,4) !=  '0000'  )  group by v12.pid and v12.pcucodeperson) as je1
,(select DATE_FORMAT(v13.dateepi,'%Y-%m-%d') from visitepi v13 where v.pid = v13.pid and v.pcucodeperson=v13.pcucodeperson and v13.vaccinecode='JE2'  and (v13.dateepi IS NOT NULL OR  left(v13.dateepi,4) !=  '0000'  )  group by v13.pid and v13.pcucodeperson) as je2
,(select DATE_FORMAT(v14.dateepi,'%Y-%m-%d') from visitepi v14 where v.pid = v14.pid and v.pcucodeperson=v14.pcucodeperson and v14.vaccinecode='JE3'  and (v14.dateepi IS NOT NULL OR  left(v14.dateepi,4) !=  '0000'  )  group by v14.pid and v14.pcucodeperson) as je3
,(select DATE_FORMAT(v15.dateepi,'%Y-%m-%d') from visitepi v15 where v.pid = v15.pid and v.pcucodeperson=v15.pcucodeperson and v15.vaccinecode='OPV1' and (v15.dateepi IS NOT NULL OR  left(v15.dateepi,4) !=  '0000'  ) group by v15.pid and v15.pcucodeperson) as opv1
,(select DATE_FORMAT(v16.dateepi,'%Y-%m-%d') from visitepi v16 where v.pid = v16.pid and v.pcucodeperson=v16.pcucodeperson and v16.vaccinecode='OPV2' and (v16.dateepi IS NOT NULL OR  left(v16.dateepi,4) !=  '0000'  ) group by v16.pid and v16.pcucodeperson) as opv2
,(select DATE_FORMAT(v17.dateepi,'%Y-%m-%d') from visitepi v17 where v.pid = v17.pid and v.pcucodeperson=v17.pcucodeperson and v17.vaccinecode='OPV3' and (v17.dateepi IS NOT NULL OR  left(v17.dateepi,4) !=  '0000'  ) group by v17.pid and v17.pcucodeperson) as opv3
,(select DATE_FORMAT(v18.dateepi,'%Y-%m-%d') from visitepi v18 where v.pid = v18.pid and v.pcucodeperson=v18.pcucodeperson and v18.vaccinecode='OPV4' and (v18.dateepi IS NOT NULL OR  left(v18.dateepi,4) !=  '0000'  ) group by v18.pid and v18.pcucodeperson) as opv4
,(select DATE_FORMAT(v19.dateepi,'%Y-%m-%d') from visitepi v19 where v.pid = v19.pid and v.pcucodeperson=v19.pcucodeperson and v19.vaccinecode='OPV5' and (v19.dateepi IS NOT NULL OR  left(v19.dateepi,4) !=  '0000'  )  group by v19.pid and v19.pcucodeperson) as opv5
,(select DATE_FORMAT(v20.dateepi,'%Y-%m-%d') from visitepi v20 where v.pid = v20.pid and v.pcucodeperson=v20.pcucodeperson and v20.vaccinecode='BCG'  and (v20.dateepi IS NOT NULL OR  left(v20.dateepi,4) !=  '0000'  )  group by v20.pid and v20.pcucodeperson) as bcg
,(select DATE_FORMAT(v21.dateepi,'%Y-%m-%d') from visitepi v21 where v.pid = v21.pid and v.pcucodeperson=v21.pcucodeperson and v21.vaccinecode='MEAS' and (v21.dateepi IS NOT NULL OR  left(v21.dateepi,4) !=  '0000'  )  group by v21.pid and v21.pcucodeperson) as meas
,(select DATE_FORMAT(v22.dateepi,'%Y-%m-%d') from visitepi v22 where v.pid = v22.pid and v.pcucodeperson=v22.pcucodeperson and v22.vaccinecode like'MMR%' and (v22.dateepi IS NOT NULL OR left(v22.dateepi,4) !=  '0000'  ) group by v22.pid and v22.pcucodeperson) as mmr

FROM  person
          left join visitepi v on person.pid = v.pid and person.pcucodeperson = v.pcucodeperson
   	  left join  visit  on (person.pid = visit.pid and person.pcucodeperson = visit.pcucodeperson)  
          left join ctitle t on person.prename = t.titlecode
	  left join house on person.hcode = house.hcode and person.pcucodeperson = house.pcucode
	  left join village ON house.villcode = village.villcode and house.pcucode = village.pcucode
          left join chospital ON (village.pcucode = chospital.hoscode)
	  left join cprovince ON (cprovince.provcode =chospital.provcode)
          left join cdistrict ON (cdistrict.provcode = chospital.provcode and cdistrict.distcode = chospital.distcode) 
	  left join csubdistrict sdt on (sdt.subdistcode = person.subdistcodemoi and sdt.distcode = person.distcodemoi and sdt.provcode = person.provcodemoi)
          left join cdistrict dt on (dt.provcode = person.provcodemoi and dt.distcode = person.distcodemoi)
   	  left join cprovince pp on (pp.provcode = person.provcodemoi)
WHERE ROUND(DATEDIFF('$str',birth) /30.44) < 72  $live_type2
	and (birth IS NOT NULL OR left(birth,4) != '0000')
        and substring(v.vaccinecode,0,2) <> 'TT' 
        and person.pid NOT IN (SELECT persondeath.pid FROM persondeath WHERE persondeath.pcucodeperson= person.pcucodeperson and ((person.dischargetype is null) or (person.dischargetype = '9'))) 
        and (visit.flagservice <'04' OR visit.flagservice is null OR length(trim(visit.flagservice))=0 ) 
	and SUBSTRING(house.villcode,7,2) <> '00' $wvill 
group by v.pid,v.pcucodeperson
ORDER BY visit.pcucode,village.villcode,person.birth DESC) as tmp_epi) as tmp_epi2
$chksto";

$result = mysql_query($sql);
$txt = '<p align=\'center\'><b>รายงานความครอบคลุมวันซีน ของเด็กอายุ 0 - 71 เดือน</b><br>';
$txt .= "<b>ข้อมูล ณ วันที่ $strx  $mu </b></p><br>$live_type_name<b>$hosp</b><table width='98%' border='0' cellspacing='1' cellpadding='1' class='table table-striped table-hover table-bordered'>
  <tr>
    <th width='2%' scope='col'><div align='center'>ลำดับ</div></th>
    <th width='8%' scope='col'><div align='center'>ชื่อ - สกุล</div></th>
	<th width='5%' scope='col'><div align='center'>ว/ด/ป เกิด</div></th>
	<th width='2%' scope='col'><div align='center'>อายุ<br>(เดือน)</div></th>
    <th width='4%' scope='col'><div align='center'>บ้านเลขที่</div></th>
    <th width='3%' scope='col'><div align='center'>หมู่ที่</div></th>
	<th width='5%' scope='col'><div align='center'>รับวัคซีน</div></th>
	<th width='3%' scope='col'><div align='center'>BCG</div></th>
	<th width='3%' scope='col'><div align='center'>HBV1</div></th>
	<th width='3%' scope='col'><div align='center'>DTP1</div></th>
	<th width='3%' scope='col'><div align='center'>HBV2</div></th>
	<th width='3%' scope='col'><div align='center'>DHB1</div></th>
	<th width='3%' scope='col'><div align='center'>OPV1</div></th>
	<th width='3%' scope='col'><div align='center'>DTP2</div></th>
	<th width='3%' scope='col'><div align='center'>DHB2</div></th>
	<th width='3%' scope='col'><div align='center'>OPV2</div></th>
	<th width='3%' scope='col'><div align='center'>DTP3</div></th>
	<th width='3%' scope='col'><div align='center'>HBV3</div></th>
	<th width='3%' scope='col'><div align='center'>DHB3</div></th>
	<th width='3%' scope='col'><div align='center'>OPV3</div></th>
	<th width='3%' scope='col'><div align='center'>MEAS</div></th>
	<th width='3%' scope='col'><div align='center'>MMR</div></th>
	<th width='3%' scope='col'><div align='center'>DTP4</div></th>
	<th width='3%' scope='col'><div align='center'>OPV4</div></th>
	<th width='3%' scope='col'><div align='center'>JE1</div></th>
	<th width='3%' scope='col'><div align='center'>JE2</div></th>
	<th width='3%' scope='col'><div align='center'>JE3</div></th>
	<th width='3%' scope='col'><div align='center'>DTP5</div></th>
	<th width='3%' scope='col'><div align='center'>OPV5</div></th>
  </tr>";
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	if($row[chk] == 0){$epi_chk = 'ไม่ครบ';}else{$epi_chk = 'ครบ';}
	$birth = redatexx($row[birth]);
	if($row[dtp1] == ""){$dtp1 = '';}else{$dtp1 = redatexx($row[dtp1]);}
	if($row[dtp2] == ""){$dtp2 = '';}else{$dtp2 = redatexx($row[dtp2]);}
	if($row[dtp3] == ""){$dtp3 = '';}else{$dtp3 = redatexx($row[dtp3]);}
	if($row[dtp4] == ""){$dtp4 = '';}else{$dtp4 = redatexx($row[dtp4]);}
	if($row[dtp5] == ""){$dtp5 = '';}else{$dtp5 = redatexx($row[dtp5]);}
	if($row[dhb1] == ""){$dhb1 = '';}else{$dhb1 = redatexx($row[dhb1]);}
	if($row[dhb2] == ""){$dhb2 = '';}else{$dhb2 = redatexx($row[dhb2]);}
	if($row[dhb3] == ""){$dhb3 = '';}else{$dhb3 = redatexx($row[dhb3]);}
	if($row[hbv1] == ""){$hbv1 = '';}else{$hbv1 = redatexx($row[hbv1]);}
	if($row[hbv2] == ""){$hbv2 = '';}else{$hbv2 = redatexx($row[hbv2]);}
	if($row[hbv3] == ""){$hbv3 = '';}else{$hbv3 = redatexx($row[hbv3]);}
	if($row[je1] == ""){$je1 = '';}else{$je1 = redatexx($row[je1]);}
	if($row[je2] == ""){$je2 = '';}else{$je2 = redatexx($row[je2]);}
	if($row[je3] == ""){$je3 = '';}else{$je3 = redatexx($row[je3]);}
	if($row[opv1] == ""){$opv1 = '';}else{$opv1 = redatexx($row[opv1]);}
	if($row[opv2] == ""){$opv2 = '';}else{$opv2 = redatexx($row[opv2]);}
	if($row[opv3] == ""){$opv3 = '';}else{$opv3 = redatexx($row[opv3]);}
	if($row[opv4] == ""){$opv4 = '';}else{$opv4 = redatexx($row[opv4]);}
	if($row[opv5] == ""){$opv5 = '';}else{$opv5 = redatexx($row[opv5]);}
	if($row[bcg] == ""){$bcg = '';}else{$bcg = redatexx($row[bcg]);}
	if($row[meas] == ""){$meas = '';}else{$meas = redatexx($row[meas]);}
	if($row[mmr] == ""){$mmr = '';}else{$mmr = redatexx($row[mmr]);}
++$i;
	if(($i%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$i</div></td>
    <td>$row[pname]</td>
	<td><div align='center'>$birth</div></td>
	<td><div align='center'>$row[age]</div></td>
    <td><div align='center'>$row[hno]</div></td>
    <td><div align='center'>$moo</div></td>
    <td><div align='center'>$epi_chk</div></td>
	<td><div align='center'>$bcg</div></td>
	<td><div align='center'>$hbv1</div></td>
	<td><div align='center'>$dtp1</div></td>
	<td><div align='center'>$hbv2</div></td>
	<td><div align='center'>$dhb1</div></td>
	<td><div align='center'>$opv1</div></td>
	<td><div align='center'>$dtp2</div></td>
	<td><div align='center'>$dhb2</div></td>
	<td><div align='center'>$opv2</div></td>
	<td><div align='center'>$dtp3</div></td>
	<td><div align='center'>$hbv3</div></td>
	<td><div align='center'>$dhb3</div></td>
	<td><div align='center'>$opv3</div></td>
	<td><div align='center'>$meas</div></td>
	<td><div align='center'>$mmr</div></td>
	<td><div align='center'>$dtp4</div></td>
	<td><div align='center'>$opv4</div></td>
	<td><div align='center'>$je1</div></td>
	<td><div align='center'>$je2</div></td>
	<td><div align='center'>$je3</div></td>
	<td><div align='center'>$dtp5</div></td>
	<td><div align='center'>$opv5</div></td>
  </tr>";
}
$txt .= "</table><br>";  
echo $txt;
?>
<?php
}
else{
		header("Location: login.php");
		}
		?>  
</body>
</html>
