<?php
session_start();
include("../includes/conndb.php"); 
include("../includes/config.inc.php");
if($_POST[chk] == 'login'){
	$mem_username = mysql_real_escape_string($_POST['username']);
	$mem_password = mysql_real_escape_string($_POST['user_password']);	
				$sql = "SELECT `user`.pcucode,`user`.username, concat(ctitle.titlename,`user`.fname,'  ',`user`.lname) as fullname,`user`.grouplevel, `user`.officerposition FROM `user` left Join ctitle ON `user`.prename = ctitle.titlecode WHERE `user`.markdelete is null and `user`.pcucode != '0000x' and username='$mem_username' AND password='$mem_password'";			
				$results = mysql_query($sql,$link);
				$numrow = mysql_num_rows($results);
				if($numrow > 0){
				$row = mysql_fetch_array($results);
							$_SESSION['pcucode']=$row['pcucode'];
							$_SESSION['user_id']=$row['username'];
							$_SESSION['username']=$row['username'];
							$_SESSION['fullname']=$row['fullname'];
							$_SESSION['level']=$row['grouplevel'];
							$_SESSION['position']=$row['officerposition'];
							$title = $row[title];
							$_SESSION['namesur']=$title.$row['fname']." ".$row['lname'];
							echo "<div class='alert alert-success alert-dismissable'><div align=\"center\"><strong>Login เข้าสู่ระบบเรียบร้อย</strong></div></div>";
							echo "<meta http-equiv='refresh' content='1;URL=../main/index.php'>";

					}else{
						echo "<div class='alert alert-danger alert-dismissable'><div align=\"center\"><strong>Username หรือ Password ไม่ถูกต้อง<br>โปรด Login ใหม่อีกครั้ง</strong></div></div>";
					}
}else{
}?>