<?php
$oldpassword = $_POST[oldpassword];
$password = $_POST[password];
$repassword = $_POST[repassword];
$sql = "SELECT * FROM user WHERE username = '$usergis'";
	$results = mysql_query($sql,$link);
	$row = mysql_fetch_array($results);
	$passwordsql = $row[password];
if($passwordsql == $oldpassword){
	$sqlup = "UPDATE user set user.password = '$password' where user.username = '$usergis'";
	$results = mysql_query($sqlup,$link);
	if($results){
		echo "เปลี่ยนรหัสผ่านเรียบร้อย";
		echo "<meta http-equiv='refresh' content='1;URL=index.php'>";
	}else{
		echo "ไม่สามารถเปลี่ยนรหัสผ่านได้";
		echo "<meta http-equiv='refresh' content='1;URL=index.php'>";}
}else{
	echo "รหัสผ่านเก่าไม่ถูกต้องกรุณาลองใหม่อีกครั้ง";
	echo "<meta http-equiv='refresh' content='1;URL=index.php'>";
	}
?>
