<?php
include("../includes/config.local.php");
$project = "GIS for JHCIS";
$usergis = $_SESSION[username]; 
$cuser = getusername($_SESSION[username]);
$cfuser = getuserfname($_SESSION[username]);
$clevel = $_SESSION[level];
$cposition = $_SESSION[position];
$titleweb = "GIS for JHCIS v3.0.0"; //
if($_SESSION[username]){
$headweb2 = $cuser;
$lmenu = "menutop".$clevel.".php";
}else{$lmenu = "menutop2.php";
$headweb2 = "บุคคลทั่วไป";
}
$sqloff =  "SELECT chospital.hosname,chospital.hoscode 
				FROM office
				Inner Join chospital ON office.offid = chospital.hoscode
				WHERE chospital.hoscode <>  '0000x'";
$resoff = mysql_query($sqloff);
$rowoff = mysql_fetch_array($resoff);
$hospitalname = $rowoff[hosname];
$hospitalcode = $rowoff[hoscode];
$offname = "".$rowoff[hosname]	."(".$rowoff[hoscode]	.")";
$version = "version v3.0.0 beta";			
$headweb = "<table width='100%' border='0' cellspacing='0' cellpadding='0'><tr><td><img src='images/logo.jpg'></td><td></td><td><div align='right'><strong>$offname</strong><br>$version</div></td></tr></table>";


$todays = date("Y-m-d");
$dtimenow = date("Y-m-d H:i:s");

$ThaiMonth = array( "มกราคม","กุมภาพันธ์","มีนาคม","เมษายน","พฤษภาคม","มิถุนายน","กรกฎาคม","สิงหาคม","กันยายน","ตุลาคม","พฤศจิกายน","ธันวาคม");
$ThaiSubMonth = array("ม.ค.","ก.พ.","มี.ค.","เม.ย.","พ.ค.","มิ.ย.","ก.ค.","ส.ค.","ก.ย.","ต.ค.","พ.ย.","ธ.ค.");
/*ฟังก์ชั่นตัดสตริงแปลงวันที่เป็นไทยแบบสั้น ตัวอย่างรูปแบบสตริงนำเข้า 2001-07-16 23:53:11*/
function SortThaiDate($txt)
				 {
				      global $ThaiSubMonth;
							$Year = substr( substr( $txt,0 ,4)+543, -2);
							$Month = substr( $txt, 4, 2);
							$DayNo = substr( $txt, 6, 2);
							// $Month = $Month - 1;
						 return $DayNo."/".$Month."/".$Year;									 
				 }					
/*ฟังก์ชั่นตัดสตริงแปลงวันที่เป็นไทยแบบยาว ตัวอย่างรูปแบบสตริงนำเข้า 2001-07-16 23:53:11*/
function LongThaiDate($txt)
				 {
				      global $ThaiMonth;
							$Year = substr( $txt,0 ,4)+543;
							$Month = substr( $txt,5, 2);
							$DayNo = substr( $txt, 8, 2);
							$Month = $Month - 1;
						 return $DayNo."  ".$ThaiMonth[$Month]."  ".$Year;						 
				 }					
//หาวัสุดท้ายของเดือน
function lastday($mon){
list($y, $m) = explode("/", $mon);
$m = $m+1; if($m==13){ $y=$y+1; $m=1; }
$newdate = mktime(12, 0, 0, $m, 1, $y);
$newdate = strtotime("-1 day", $newdate);
$newdate = date("Y-m-d", $newdate);
return($newdate);
}
/*ฟังก์ชั่นสร้าง Dropdown รายการเดือนไทย ส่งค่า 1-12*/
function getThaiMonth()
				 {
				 global $ThaiMonth;
				 for ($i=0;$i<=11;$i++)
				      {
							     $a = $i +1;
								       echo "\t<option value=\"$a\">$ThaiMonth[$i]</option>\n";
							}
				 }				 
function retDate($add){ //แปลงค่าวันที่ จาก 01/12/2552 เป็น 2009-12-01
		$strd = substr($add,0,2);
		$strm = substr($add,3,2);
		$stryT = substr($add,6,4);
		$stry = $stryT - 543;
		$str = $stry."-".$strm."-".$strd;
		return $str;
}
function retDatets($add){ //แปลงค่าวันที่ จาก 2009-12-01  เป็น  01/12/2552  
		$strd = substr($add,8,2);
		$strm = substr($add,5,2);
		$stryT = substr($add,0,4);
		$stry = $stryT + 543;
		$str = $strd."/".$strm."/".$stry;
		return $str;
}
function retDatetsyyyy($add){ //แปลงค่าวันที่ จาก 2009-12-01  เป็น  01/12/2552  
		$strd = substr($add,8,2);
		$strm = substr($add,5,2);
		$stryT = substr($add,0,4);
		$stry = $stryT;
		$str = $strd."/".$strm."/".$stry;
		return $str;
}
function retDatetsxxxx($add){ //แปลงค่าวันที่ จาก 2009-12-01  เป็น  01/12/2009 
		$strd = substr($add,8,2);
		$strm = substr($add,5,2);
		$stryT = substr($add,0,4);
		$str = $strd."/".$strm."/".$stryT;
		return $str;
}
function retdaterangstr($add){ //แปลงค่าวันที่ จาก 01/07/2014 - 31/07/2014  เป็น  2014-07-01  
		$strd = substr($add,0,2);
		$strm = substr($add,3,2);
		$stryT = substr($add,6,4);
		$str = $stryT."-".$strm."-".$strd;
		return $str;
}
function retdaterangsto($add){ //แปลงค่าวันที่ จาก 01/07/2014 - 31/07/2014  เป็น   2014-07-31  
		$strd = substr($add,13,2);
		$strm = substr($add,16,2);
		$stryT = substr($add,19,4);
		$str = $stryT."-".$strm."-".$strd;
		return $str;
}
function redatexx($add){ //แปลงค่าวันที่ จาก 2009-12-01  เป็น  01/12/2552  
		$strd = substr($add,8,2);
		$strm = substr($add,5,2);
		$stryT = substr($add,0,4);
		$stry = $stryT + 543;
		$stryto = substr($stry,2,2);
		$str = $strd."/".$strm."/".$stryto;
		return $str;
}

function retDatet18($add){ //แปลงค่าวันที่ จาก 20091201  เป็น  01/12/2552  
	if($add != ''){
		$strd = substr($add,6,2);
		$strm = substr($add,4,2);
		$stryT = substr($add,0,4);
		$stry = $stryT + 543;
		$str = $strd."/".$strm."/".$stry;
		return $str;
	}
}
function retDatet19($add){ //แปลงค่าวันที่ จาก 01/12/2552  เป็น  20091201
	if($add != ''){
		$strd = substr($add,0,2);
		$strm = substr($add,3,2);
		$stryT = substr($add,6,4);
		$stry = $stryT - 543;
		$str = $stry."".$strm."".$strd;
		return $str;
	}
}
function retDatet20($add){ //แปลงค่าวันที่ จาก 20091201  เป็น  2009-12-01
	if($add != ''){
		$strd = substr($add,6,2);
		$strm = substr($add,4,2);
		$stryT = substr($add,0,4);
		$stry = $stryT;
		$stry = substr($stry,-2);
		$str = $stryT."-".$strm."-".$strd;
		return $str;
	}
}							 
/*ฟังก์ชั่นคืนค่าเตรียมก่อนบันทึกกลับ*/
function get_reDate($gyear ,$gmonth ,$gday)
				 {
				 						if (strlen($gmonth)==1) {
											$gmonth = "0".$gmonth;
										}

										if (strlen($gday)==1){
											 $gday = "0".$gday;
										}
										$reDate = $gyear."-".$gmonth."-".$gday;
										return $reDate;
				 }
				 
/*ฟังก์ชั่นสร้าง DropDown รายการวัน 1-31 ส่งค่า 1-31*/
function getDay1to31()
				 {
				      for($i=1;$i<=31;$i++)
							     {
											      echo "\t<option valu>$i</option>\n";
									 }
				 }

/*ฟังก์ชั่นสร้าง DropDown ปีปัจจุบันย้อนไป 100 ปี โดย Auto Update ไม่ต้องมานั่งเปลี่ยน*/				 
 function get2Year()
 					{
					     $today = getdate();
							 $year = $today[year];
							 for ($x=$year-20;$x<=$year;$x++)
							      {
										     $z=$x+543;
												     echo "\t<option value=\"$x\">$z</option>\n";			
										}
					}
				

function persontype($id){
		$sql ="SELECT cpersontype.persontypename as n FROM cpersontype where cpersontype.persontypecode = '$id'";
		$result=mysql_query($sql);
		$row=mysql_fetch_array($result);
		$ret = $row[n];
		return $ret;
}
function getPersonName($id){
		$sql ="SELECT CONCAT(t.titlename,p.fname,' ',p.lname) AS n 
		FROM person AS p
		Inner Join ctitle AS t ON p.prename = t.titlecode
		 WHERE pid='$id'";
		$result=mysql_query($sql);
		$row=mysql_fetch_array($result);
		$ret = $row[n];
		return $ret;
}	
function getusername($xx){
		$sql ="select concat(c.titlename,user.fname,'  ',user.lname) as n FROM `user` Inner Join ctitle c ON `user`.prename = c.titlecode WHERE `user`.markdelete IS NULL and `user`.username = '$xx'";
		$result=mysql_query($sql);
		$row=mysql_fetch_array($result);
		$ret = $row[n];
		return $ret;
}	
function getuserfname($xx){
		$sql ="select user.fname as n FROM `user` Inner Join ctitle c ON `user`.prename = c.titlecode WHERE `user`.markdelete IS NULL and `user`.username = '$xx'";
		$result=mysql_query($sql);
		$row=mysql_fetch_array($result);
		$ret = $row[n];
		return $ret;
}
function getosmname($osm){
		$sql ="SELECT CONCAT(t.titlename,p.fname,' ',p.lname) AS n 
		FROM person AS p
		Inner Join ctitle AS t ON p.prename = t.titlecode
		 WHERE pid='$osm'";
		$result=mysql_query($sql);
		$row=mysql_fetch_array($result);
		$ret = $row[n];
		return $ret;
}	
function getTitle($titlecode){
		$sql ="SELECT t.titlename AS n 
		FROM ctitle AS t
		 WHERE t.titlecode='$titlecode'";
		$result=mysql_query($sql);
		if($result){
		$row=mysql_fetch_array($result);
		$ret = $row[n];
		}else{
			$ret = $titlecode;	
		}
		return $ret;
}
function getMooVillage($vid){
		$sql ="SELECT CONCAT(villno,' บ้าน',villname) AS n FROM village WHERE villcode='$vid'";
		$result=mysql_query($sql);
		$row=mysql_fetch_array($result);
		$ret = $row[n];
		return $ret;
}
function getvillagename($villname){
		$sql ="SELECT CONCAT('หมู่ที่ ',villno,' บ้าน',villname) AS n FROM village where village.villcode = '$villname'";
		$result=mysql_query($sql);
		$row=mysql_fetch_array($result);
		$ret = $row[n];
		return $ret;
}
function villnamechart($villname){
		$sql ="SELECT CONCAT(villname) AS n FROM village where village.villcode = '$villname'";
		$result=mysql_query($sql);
		$row=mysql_fetch_array($result);
		$ret = $row[n];
		return $ret;
}
function gethospname($pcucode){
		$sql ="SELECT concat(chospital.hoscode,'  ',chospital.hosname) as n  FROM office Inner Join chospital ON office.offid = chospital.hoscode WHERE chospital.hoscode <>  '0000x' and chospital.hoscode = '$pcucode'";
		$result=mysql_query($sql);
		$row=mysql_fetch_array($result);
		$ret = $row[n];
		return $ret;
}	
function getuserposition($username){
		$sql ="SELECT `user`.officerposition as n FROM `user` where `user`.username = '$username'";
		$result=mysql_query($sql);
		$row=mysql_fetch_array($result);
		$ret = $row[n];
		return $ret;
}
function getavatar($username){
		$sql ="SELECT `user`.avatar as n FROM `user` where `user`.username = '$username'";
		$result=mysql_query($sql);
		$row=mysql_fetch_array($result);
		$ret = $row[n];
		return $ret;
}	
function getservice($service){
		$sql ="SELECT cflagservice.servicedesc as n FROM cflagservice where cflagservice.servicecode = '$service'";
		$result=mysql_query($sql);
		$row=mysql_fetch_array($result);
		$ret = $row[n];
		return $ret;
}	
function getLatHouse($v,$h){
		$sql ="SELECT ygis FROM house WHERE villcode='$v' AND hno='$h'";
		$result=mysql_query($sql);
		if(mysql_num_rows($result) > 0){
			$row=mysql_fetch_array($result);
			$ret =$row[ygis];	
		}
		return $ret;
}
function getLngHouse($v,$h){
		$sql ="SELECT xgis FROM house WHERE villcode='$v' AND hno='$h'";
		$result=mysql_query($sql);
		if(mysql_num_rows($result) > 0){
			$row=mysql_fetch_array($result);
			$ret =$row[xgis];	
		}
		return $ret;
}
function gethospserv($pcucode){
		$sql ="SELECT concat(chospital.hoscode,'  ',chospital.hosname) as n  FROM chospital WHERE chospital.hoscode <>  '0000x' and chospital.hoscode = '$pcucode'";
		$result=mysql_query($sql);
		$row=mysql_fetch_array($result);
		$ret = $row[n];
		return $ret;
}
/*
function getVillLatLng($v){
				$sqla ="SELECT lat,lon FROM village WHERE mooban='$v'";
				$resa = mysql_query($sqla);
				if($rowa = mysql_fetch_array($resa)){
					$n = $rowa[lat].",".$rowa[lon];
					return $n;
				}
}	
function getVillLat($v){
				$sqla ="SELECT lat FROM village WHERE mooban='$v'";
				$resa = mysql_query($sqla);
				if($rowa = mysql_fetch_array($resa)){
					$n = $rowa[lat];
					return $n;
				}
}	
function getVillLng($v){
				$sqla ="SELECT lon FROM village WHERE mooban='$v'";
				$resa = mysql_query($sqla);
				if($rowa = mysql_fetch_array($resa)){
					$n = $rowa[lon];
					return $n;
				}
}
function getLatLng($i,$c){
	if($i == 1){
				$sqla ="SELECT lat as lat ,lon as lng FROM amphur WHERE amphurcode='$c'";
				$resa = mysql_query($sqla,$link);
				$rowa = mysql_fetch_array($resa);
				$n = $rowa[lat].",".$rowa[lng];
				return $n;
	}else if($i == 2){
				$sqla ="SELECT lat as lat ,lon as lng FROM tambon WHERE tambon='$c'";
				$resa = mysql_query($sqla);
				$rowa = mysql_fetch_array($resa);
				$n = $rowa[lat].",".$rowa[lng];
				return $n;
	}else if($i == 3){
				$sqla ="SELECT lat as lat ,lon as lng FROM village WHERE mooban='$c'";
				$resa = mysql_query($sqla);
				$rowa = mysql_fetch_array($resa);
				$n = $rowa[lat].",".$rowa[lng];
				return $n;
	}else if($i == 4){
				$sqla ="SELECT lat as lat ,lng as lng FROM hospitals WHERE off_id='$c'";
				$resa = mysql_query($sqla);
				$rowa = mysql_fetch_array($resa);
				$n = $rowa[lat].",".$rowa[lng];
				return $n;
	}
}
function getHsFromVill($v){
				$sqla ="SELECT hs FROM village WHERE mooban='$v'";
				$resa = mysql_query($sqla);
				if($rowa = mysql_fetch_array($resa)){
					$n = $rowa[hs];
					return $n;
				}
}	

function getLatLngHouse($v,$h){
		$sql ="SELECT lat,lng FROM tbhouse WHERE mooban='$v' AND house_no='$h'";
		$result=mysql_query($sql);
		if(mysql_num_rows($result) > 0){
			$row=mysql_fetch_array($result);
			$ret =$row[0].",".$row[1];	
		}
		return $ret;
}
*/

?>
