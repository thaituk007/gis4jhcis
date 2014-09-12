<?php
function rudeword($input){
	$words = array("xxxx", "yyyy", "zzzz"); //ใส่คำหยาบที่นี่

	$replace = "<font color=red>***</font>";
	for($i=0; $i<count($words);$i++) {
		$input=str_replace(trim($words[$i]), $replace,$input);
	}
		return $input;	
}

function fb_thaidate($timestamp){
	
	$diff = time() - $timestamp;
	$periods = array("วินาที", "นาที", "ชั่วโมง");	
	$words="ที่แล้ว";
	
	if($diff<60){
		$i=0;
		$diff=($diff==1)?"":$diff;
		$text = "$diff $periods[$i]$words";	
		
	}elseif($diff<3600){
		$i=1;
		$diff=round($diff/60);
		$diff=($diff==3 || $diff==4)?"":$diff;
		$text = "$diff $periods[$i]$words";	
		
	}elseif($diff<86400){
		$i=2;
		$diff=round($diff/3600);
		$diff=($diff != 1)?$diff:"" . $diff ;		
		$text = "$diff $periods[$i]$words";	
		
	}elseif($diff<172800){
		$diff=round($diff/86400);
		$text = "$diff วันที่แล้ว เมื่อเวลา " .date("g:i a",$timestamp);			
							
	}else{

		$thMonth = array("มกราคม","กุมภาพันธ์","มีนาคม","เมษายน","พฤษภาคม","มิถุนายน","กรกฏาคม","สิงหาคม","กันยายน","ตุลาคม","พฤศจิกายน","ธันวาคม");
		$date = date("j", $timestamp);
		$month = $thMonth[date("m", $timestamp)-1];
		$y = date("Y", $timestamp)+543;
		$t1 = "$date  $month  $y";
		$t2 = "$date  $month  ";		

		if($timestamp<strtotime(date("Y-01-01 00:00:00"))){
			$text = "เมื่อวันที่ " . $t1. " เวลา " . date("G:i",$timestamp) . " น.";
		}else{					
			$text = "เมื่อวันที่ " . $t2 . " เวลา " . date("G:i",$timestamp) . " น.";	
		}
	}
	return $text;
}
?>