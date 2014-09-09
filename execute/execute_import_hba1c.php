<?php
set_time_limit(0);
include("includes/config.inc.php");
include("includes/conndb.php");
$cuphosp = '10994';
$sqlpcucode = "SELECT chospital.hoscode, chospital.hosname FROM `chospital` chospital INNER JOIN `office` office ON chospital.`hoscode` = office.`offid` where chospital.hoscode <> '0000x'";
$resultpcucode = mysql_query($sqlpcucode,$link);
$rowpcucode=mysql_fetch_array($resultpcucode);
$hospcode =$rowpcucode[hoscode];

$sqlmaxv = "select max(v.visitno) as mvisit from visit v";
$resultmaxv = mysql_query($sqlmaxv,$link);
$rowmaxv=mysql_fetch_array($resultmaxv);
$maxvisit =$rowmaxv[mvisit];

include("includes/conndbprovis.php");
$str = retDate($_GET[str]);
$sto = retDate($_GET[sto]);	
$strx = retDatet19($_GET[str]);
$stox = retDatet19($_GET[sto]);	
$sqlx = "SELECT
person.pcucode as pcucodepcu,
labfu.pid,
labfu.seq,
concat(left(labfu.date_serv,4),'-',substr(labfu.date_serv,5,2),'-',right(labfu.date_serv,2)) as visitdate,
case when labfu.labtest = '05' then 'CH99'
	   when labfu.labtest = '06' then 'CH25'
	   when labfu.labtest = '07' then 'CH07'
	   when labfu.labtest = '08' then 'CH14'
	   when labfu.labtest = '09' then 'CH17'
	   when labfu.labtest = '10' then 'CH04'
	   when labfu.labtest = '11' then 'CH09'
	   when labfu.labtest = '12' then 'CHa1'
	   when labfu.labtest = '13' then 'CHc1' ELSE NULL end as labcode,
'1' as seqonday,
'$hospcode' as pcucode,
case when labfu.labresult >= 7 then 'สูง' else 'ปกติ' end as labresulttext,
labfu.labresult,
'2' as flag18fileexpo,
'30' as sell,
concat(left(labfu.d_update,4),'-',substr(labfu.d_update,5,2),'-',substr(labfu.d_update,7,2),' ',substr(labfu.d_update,9,2),':',substr(labfu.d_update,11,2),':',substr(labfu.d_update,13,2)) as dateupdate,
labfu.cid,
if(LENGTH(diag.diagcode) > 3,CONCAT(left(diag.diagcode,3),'.',substr(diag.diagcode,4,LENGTH(diag.diagcode)-3)),diag.diagcode) as diagcodej,
diag.diagtype
FROM
diag
INNER JOIN labfu ON diag.pcucode = labfu.pcucode AND diag.pid = labfu.pid AND diag.seq = labfu.seq
inner join person on person.cid = labfu.cid
where labfu.pcucode in ('$cuphosp') and labfu.labtest between '05' and '13' and labfu.date_serv between '$strx' and '$stox'
and person.pcucode = '$hospcode' and diag.diagtype = '1'
GROUP BY labfu.pcucode,labfu.seq";	
		$result=mysql_query($sqlx,$connprovis);
			if($result){while($row=mysql_fetch_array($result)) {
				++$i;
		include("includes/conndb.php");
		/*INSERT INTO visit 
( pcucode, visitno, visitdate, pcucodeperson, pid, timeservice, timestart, timeend, rightcode, rightno, hosmain, hossub, incup, symptoms, receivepatient, refer, username, flagservice, flag18fileexpo, dateupdate, servicetype ) */
				$sqlup = "INSERT INTO visit 
( pcucode, visitno, visitdate, pcucodeperson, pid, timeservice, timestart, timeend, rightcode, rightno, hosmain, hossub, incup, symptoms, receivepatient, refer, username, flagservice, flag18fileexpo, dateupdate, servicetype )
SELECT
house.pcucode,
($maxvisit)+($i)  as visitno,
'$row[visitdate]' as visitdate,
person.pcucodeperson,
person.pid,
'1' as timeservice,
now() as timestart,
now() as timeend,
person.rightcode,
person.rightno,
person.hosmain,
person.hossub,
'1' as incup,
'ตรวจ Lab โรคเรื้อรัง' as symptoms,
'00' as receivepatient,
'00' as refer,
house.usernamedoc as username,
'03' as flagservice,
'2' as flag18fileexpo,
'$row[dateupdate]' as dateupdate,
'1' as servicetype
FROM 
house
Inner Join person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode
where person.idcard = '$row[cid]'";
				$resultup=mysql_query($sqlup,$link);

				$sqlup2 = "INSERT INTO visitdiag 
( visitdiag.pcucode, visitdiag.visitno, visitdiag.diagcode, visitdiag.conti, visitdiag.clinic, visitdiag.dxtype, visitdiag.flag18fileexpo, visitdiag.dateupdate )
SELECT
tmp.*
from
(SELECT
visit.pcucode,
visit.visitno as visitno,
'$row[diagcodej]' as diagcode,
'0' as conti,
null as clinic,
'01' as dxtype,
'2' as flag18fileexpo,
visit.dateupdate as dateupdate
FROM
person
inner join visit on person.pcucodeperson = visit.pcucodeperson and visit.pid = person.pid
where visit.visitdate = '$row[visitdate]' and person.idcard = '$row[cid]' and visit.symptoms like 'ตรวจ Lab โรคเรื้อรัง' ) as tmp
inner join cdisease on tmp.diagcode = cdisease.diseasecode";
				$resultup2=mysql_query($sqlup2,$link);
				if($resultup){$total++;}
			}
			}else{echo "ไม่สามารถอัพเดทข้อมูลได้ กรุณาลองใหม่อีกครั้งภายหลัง";}
			


include("includes/conndbprovis.php");

$sqlxx = "SELECT
person.pcucode as pcucodepcu,
labfu.pid,
labfu.seq,
concat(left(labfu.date_serv,4),'-',substr(labfu.date_serv,5,2),'-',right(labfu.date_serv,2)) as visitdate,
case when labfu.labtest = '05' then 'CH99'
	   when labfu.labtest = '06' then 'CH25'
	   when labfu.labtest = '07' then 'CH07'
	   when labfu.labtest = '08' then 'CH14'
	   when labfu.labtest = '09' then 'CH17'
	   when labfu.labtest = '10' then 'CH04'
	   when labfu.labtest = '11' then 'CH09'
	   when labfu.labtest = '12' then 'CHa1'
	   when labfu.labtest = '13' then 'CHc1' ELSE NULL end as labcode,
'1' as seqonday,
'$hospcode' as pcucode,
case when labfu.labtest = '05' and labfu.labresult >= 7 then 'สูง' when labfu.labtest = '05' and labfu.labresult < 7 then 'ปกติ' else '' end as labresulttext,
labfu.labresult,
'2' as flag18fileexpo,
concat(left(labfu.d_update,4),'-',substr(labfu.d_update,5,2),'-',substr(labfu.d_update,7,2),' ',substr(labfu.d_update,9,2),':',substr(labfu.d_update,11,2),':',substr(labfu.d_update,13,2)) as dateupdate,
labfu.cid
FROM
labfu
inner join person on person.cid = labfu.cid
where labfu.pcucode in ('$cuphosp') and labfu.labtest between '05' and '13' and labfu.date_serv between '$strx' and '$stox'
and person.pcucode = '$hospcode'";	
		$resultx=mysql_query($sqlxx,$connprovis);
			if($resultx){while($rowx=mysql_fetch_array($resultx)) {
				++$ix;
		include("includes/conndb.php");
		/*INSERT INTO visit 
( pcucode, visitno, visitdate, pcucodeperson, pid, timeservice, timestart, timeend, rightcode, rightno, hosmain, hossub, incup, symptoms, receivepatient, refer, username, flagservice, flag18fileexpo, dateupdate, servicetype ) */
				$sqlupx = "INSERT INTO visitlabchcyhembmsse
(visitlabchcyhembmsse.pcucodeperson,
visitlabchcyhembmsse.pid,
visitlabchcyhembmsse.datecheck,
visitlabchcyhembmsse.labcode,
visitlabchcyhembmsse.seqonday,
visitlabchcyhembmsse.pcucode,
visitlabchcyhembmsse.visitno,
visitlabchcyhembmsse.labresulttext,
visitlabchcyhembmsse.labresultdigit,
visitlabchcyhembmsse.hosservice,
visitlabchcyhembmsse.flag18fileexpo,
visitlabchcyhembmsse.sell,
visitlabchcyhembmsse.dateupdate)
SELECT
labfu.*
FROM
(SELECT
person.pcucodeperson,
person.pid,
'$rowx[visitdate]',
'$rowx[labcode]',
'$rowx[seqonday]',
visit.pcucode,
visit.visitno,
'$rowx[labresulttext]',
'$rowx[labresult]',
null as cuphosp,
'$rowx[flag18fileexpo]',
'100' as sell,
visit.dateupdate
FROM
person
inner join visit on person.pcucodeperson = visit.pcucodeperson and visit.pid = person.pid
where visit.visitdate = '$rowx[visitdate]' and person.idcard = '$rowx[cid]' and visit.symptoms like 'ตรวจ Lab โรคเรื้อรัง') as labfu
LEFT JOIN visitlabchcyhembmsse labc on labfu.pcucodeperson = labc.pcucodeperson and labc.pid = labfu.pid and labc.labcode = '$rowx[labcode]' and labc.datecheck = '$rowx[visitdate]'
where labc.pid is null

";
				$resultupx=mysql_query($sqlupx,$link);
				if($resultupx){$totalx++;}
				
			}
			echo "<center><h2>นำเข้าข้อมูล Lab จำนวน $totalx Record</h2></center>";
			}else{echo "ไม่สามารถอัพเดทข้อมูลได้ กรุณาลองใหม่อีกครั้งภายหลัง";}
?>