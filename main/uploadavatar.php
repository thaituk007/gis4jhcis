<?php
session_start();
$userxx = $_SESSION[user_id];
include("../includes/conndb.php");
$valid_exts = array('jpeg', 'jpg', 'png', 'gif');
$max_file_size = 2048 * 1024; #200kb
$nw = $nh = 200; # image with # height

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if ( isset($_FILES['image']) ) {
		if (! $_FILES['image']['error'] && $_FILES['image']['size'] < $max_file_size) {
			$ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
			if (in_array($ext, $valid_exts)) {
					$path = '../img/avatar/' . uniqid() . '.' . $ext;
					$size = getimagesize($_FILES['image']['tmp_name']);
					$x = (int) $_POST['x'];
					$y = (int) $_POST['y'];
					$w = (int) $_POST['w'] ? $_POST['w'] : $size[0];
					$h = (int) $_POST['h'] ? $_POST['h'] : $size[1];

					$data = file_get_contents($_FILES['image']['tmp_name']);
					$vImg = imagecreatefromstring($data);
					$dstImg = imagecreatetruecolor($nw, $nh);
					imagecopyresampled($dstImg, $vImg, 0, 0, $x, $y, $nw, $nh, $w, $h);
					imagejpeg($dstImg, $path);
					imagedestroy($dstImg);
					echo "<img src='$path' />";
					$query="UPDATE `user` set `user`.avatar = '$path' where `user`.username = '$userxx'";
$result=mysql_query($query);
				} else {
					echo 'unknown problem!';
				} 
		} else {
			echo 'file is too small or large';
		}
	} else {
		echo 'file not set';
	}
} else {
	echo 'bad request!';
}

?>