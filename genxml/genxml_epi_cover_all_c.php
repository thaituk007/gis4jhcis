<?php 
header("Content-type: text/xml");
include("../includes/conndb.php"); 
include("../includes/config.inc.php"); 
set_time_limit(0);
$villcode = $_GET[village];
if($villcode == "00000000"){
	$wvill = "";
}else{
	$wvill = " AND house.villcode='$villcode' ";	
}
$chk_epi = $_GET[chk_epi];
if($chk_epi == "9"){
	$chksto = "";
}elseif($chk_epi == "1"){
	$chksto = "where chk != 0";	
}elseif($chk_epi == "0"){
	$chksto = "where chk = 0";
}else{}
$str = retDate($_GET[str]);
$sql = "CREATE TEMPORARY TABLE tmp_epi
		SELECT 
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
WHERE ROUND(DATEDIFF('$str',birth) /30) <= 60
	and (birth IS NOT NULL OR left(birth,4) != '0000')
        and substring(v.vaccinecode,0,2) <> 'TT' 
        and person.pid NOT IN (SELECT persondeath.pid FROM persondeath WHERE persondeath.pcucodeperson= person.pcucodeperson and ((person.dischargetype is null) or (person.dischargetype = '9'))) 
        and (visit.flagservice <'04' OR visit.flagservice is null OR length(trim(visit.flagservice))=0 ) 
	and SUBSTRING(house.villcode,7,2) <> '00' $wvill 
group by v.pid,v.pcucodeperson
ORDER BY visit.pcucode,village.villcode,person.birth DESC";
mysql_query($sql);
$sql = "CREATE TEMPORARY TABLE tmp_epi2
		select
*,
case when (age between 0 and 3) and bcg is not null and hbv1 is not null then 1
	when (age between 4 and 5) and bcg is not null and hbv1 is not null and (dtp1 is not null or dhb1 is not null) and opv1 is not null then 2
	when (age between 6 and 7) and bcg is not null and hbv1 is not null and (dtp1 is not null or dhb1 is not null) and opv1 is not null and (dtp2 is not null or dhb2 is not null) and opv2 is not null then 3
	when (age between 8 and 10) and bcg is not null and hbv1 is not null and (dtp1 is not null or dhb1 is not null) and opv1 is not null and (dtp2 is not null or dhb2 is not null) and opv2 is not null and (dtp3 is not null or dhb3 is not null) and opv3 is not null then 4
	when (age between 11 and 19) and bcg is not null and hbv1 is not null and (dtp1 is not null or dhb1 is not null) and opv1 is not null and (dtp2 is not null or dhb2 is not null) and opv2 is not null and (dtp3 is not null or dhb3 is not null) and opv3 is not null and (meas is not null or mmr is not null) then 5
	when (age between 20 and 21) and bcg is not null and hbv1 is not null and (dtp1 is not null or dhb1 is not null) and opv1 is not null and (dtp2 is not null or dhb2 is not null) and opv2 is not null and (dtp3 is not null or dhb3 is not null) and opv3 is not null and (meas is not null or mmr is not null) and je1 is not null and dtp4 is not null and opv4 is not null then 6
	when (age between 22 and 31) and bcg is not null and hbv1 is not null and (dtp1 is not null or dhb1 is not null) and opv1 is not null and (dtp2 is not null or dhb2 is not null) and opv2 is not null and (dtp3 is not null or dhb3 is not null) and opv3 is not null and (meas is not null or mmr is not null) and je1 is not null and dtp4 is not null and opv4 is not null and je2 is not null then 7
	when (age between 32 and 49) and bcg is not null and hbv1 is not null and (dtp1 is not null or dhb1 is not null) and opv1 is not null and (dtp2 is not null or dhb2 is not null) and opv2 is not null and (dtp3 is not null or dhb3 is not null) and opv3 is not null and (meas is not null or mmr is not null) and je1 is not null and dtp4 is not null and opv4 is not null and je2 is not null and je3 is not null then 8
	when (age between 50 and 60) and bcg is not null and hbv1 is not null and (dtp1 is not null or dhb1 is not null) and opv1 is not null and (dtp2 is not null or dhb2 is not null) and opv2 is not null and (dtp3 is not null or dhb3 is not null) and opv3 is not null and (meas is not null or mmr is not null) and je1 is not null and dtp4 is not null and opv4 is not null and je2 is not null and je3 is not null and dtp5 is not null and opv5 is not null then 9
else
0
end as chk
from tmp_epi";
mysql_query($sql);

$sql = "select * from tmp_epi2
$chksto";
$result = mysql_query($sql);
$xml = '<markers>';
while($row=mysql_fetch_array($result)) {
	$moo = substr($row[villcode],6,2);
	$vill = getMooVillage($row[villcode]);
	if($row[chk] == 0){$epi_chk = 'วัคซีนไม่ครบตามเกณฑ์';}else{$epi_chk = 'วัคซีนครบตามเกณฑ์';}
	$birth = retDatets($row[birth]);
  $xml .= '<marker ';
  $xml .= 'hcode="'.$row[hcode].'" ';
  $xml .= 'hono="'.$row[hno].'" ';
  $xml .= 'moo="'.$moo.'" ';
  $xml .= 'vill="'.$vill.'" ';
  $xml .= 'pname="'.$row[pname].'" ';
  $xml .= 'birth="'.$birth.'" ';
  $xml .= 'age="'.$row[age].'" ';
  $xml .= 'epi_chk="'.$epi_chk.'" ';
  $xml .= 'chk="'.$row[chk].'" ';
  $xml .= 'lat="'.$row[ygis].'" ';
  $xml .= 'lng="'.$row[xgis].'" ';
  $xml .= '/>';
}
$xml .= '</markers>';
echo $xml;
?>