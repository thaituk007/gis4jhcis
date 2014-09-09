<?php
set_time_limit(0);
include("../includes/conndb.php"); 
include("../includes/config.inc.php");
if(move_uploaded_file($_FILES["file"]["tmp_name"],"import.txt")){
		echo "Upload OK.";
	}
if($_GET[chk]=="import"){
	$txtfile = fopen("../main/import.txt","r");
	while ($h = fgets($txtfile,1024)){
		//$h = iconv( 'TIS-620', 'UTF-8',$h);
		++$i;
		if($i == 1){
		}else{
			list($pcucode,$hcode,$villcode,$house_no,$lat,$lng,$dateupdate) = split(',',$h);
			$sql = "UPDATE house SET ygis='$lat',xgis='$lng' WHERE pcucode = '$pcucode' AND hcode = '$hcode' ";
				$resultup=mysql_query($sql,$link);
				if($resultup){$total++;}
		}
	}
	fclose($txtfile);
	echo "<br><br><br><p align='center'><strong>ปรับปรุงข้อมูลพิกัด $total หลังคาเรือน</strong></p>";
}
?>
