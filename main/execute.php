<?php
session_start();
include("../includes/conndb.php"); 
include("../includes/config.inc.php");
	$op = $_GET['action'];
		$op();
function updateuserinfo(){ //ปรับปรุงข้อมูลผู้ใช้ 
$password = $_GET[password];
$prename = $_GET[prename];
$fname = $_GET[fname];
$lname = $_GET[lname];
$level = $_GET[level];
$idcard = $_GET[idcard];
$usernamesession = $_SESSION[user_id];
	$sqlup = "UPDATE `user` set `user`.`password` = '$password',`user`.prename = '$prename',`user`.fname = '$fname',`user`.lname = '$lname',`user`.officerposition = '$level',`user`.idcard = '$idcard' WHERE `user`.username = '$usernamesession'";
	$results = mysql_query($sqlup);
	if($results){
		echo "<div class='text text-success'>เปลี่ยนรหัสผ่านเรียบร้อย</div>";
	}else{
		echo "<div class='text text-danger'>ไม่สามารถเปลี่ยนรหัสผ่านได้</div>";
	}
}
?>