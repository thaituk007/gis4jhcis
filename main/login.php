<?php
session_start();
include("../includes/conndb.php"); 
include("../includes/config.inc.php");
$sql = "SELECT `user`.avatar FROM `user`";
$results = mysql_query($sql);
if($results){
	}else{
		$sqltbl = "ALTER TABLE `user` ADD `avatar` VARCHAR(255) DEFAULT '../img/avatar/53db7dbda4f38.jpg'";
		$results = mysql_query($sqltbl);
	}
$sql = "SELECT menugis.* FROM menugis";
$results = mysql_query($sql);
if($results){
	}else{
		echo "<meta http-equiv='refresh' content='0;URL=../bigdump.php?start=1&fn=menugis.sql&foffset=0&totalqueries=0&delimiter=%3B'>";
	}
?>
<!DOCTYPE html>
<html class="bg-black">
    <head>
        <meta charset="UTF-8">
        <title><?php echo $titleweb; ?></title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
        <meta name="description" content="">
   		<meta name="author" content="">
    	<link rel="shortcut icon" href="ico/favicon.ico">
        <!-- bootstrap 3.0.2 -->
        <link href="../css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <!-- font Awesome -->
        <link href="../css/font-awesome.min.css" rel="stylesheet" type="text/css" />
        <!-- Theme style -->
        <link href="../css/AdminLTE.css" rel="stylesheet" type="text/css" />

        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
          <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
        <![endif]-->
    </head>
    <body class="bg-black">

        <div class="form-box" id="login-box">
            <div class="header">Please sign in</div>
            <!--<form class="form-signin" role="form" method='post' action='../main/login_check.php'>-->
                <div class="body bg-gray">
                  	<div id="div1"></div>
                    <div class="form-group">
                        <input type="username" class="form-control" placeholder="ชื่อผู้ใช้งาน" name='username' id='username' required autofocus>
                    </div>
                    <div class="form-group">
                        <input type="password" class="form-control" placeholder="รหัสผ่าน" name='user_password' id='user_password' required>
                    </div>          
                    <div class="form-group">
                    	<input name='chk' type='hidden' id='chk' value='login' />
                        <input type="checkbox" name="remember_me"/> Remember me
                    </div>                    
                </div>
                <div class="footer">                                                               
                    <button type="submit" class="btn bg-olive btn-block" id="btn1" name = "btn1">เข้าสู่ระบบ</button>  
                    <p><a href="#">I forgot my password</a></p>
                </div>
            <!--</form>-->

            <div class="margin text-center">
                <span>พัฒนาโดย <a href='http://www.kriwoot.com/gisjhcis' target='_blank'>อ.ไกรวุฒิ แก้วชาลุน</a> <br>ปรับแต่งเพิ่มเติมโดย <br><a href='http://facebook.com/gis4jhcis' target='_blank'>hanamichi  recca</a><br>แสดงผลได้ดี บนความละเอียด  1366 X 768<br></span>
                <br/>
                <a href="https://www.facebook.com/pages/GIS-for-JHCIS/564051720289449" target="_blank" type="button" class="btn bg-light-blue btn-circle"><i class="fa fa-facebook"></i></a>
                <a href="https://twitter.com/hanamichi_recca" target="_blank" type="button" class="btn bg-aqua btn-circle"><i class="fa fa-twitter"></i></a>
                <a href="https://plus.google.com/u/0/104852534143096655554/posts" target="_blank" type="button" class="btn bg-red btn-circle"><i class="fa fa-google-plus"></i></a>

            </div>
        </div>
 <!-- jQuery 2.0.2 -->
<script src="../js/jquery-2.0.2.min.js"></script>
<script>
$.fn.enterKey = function (fnc) {
    return this.each(function () {
        $(this).keypress(function (ev) {
            var keycode = (ev.keyCode ? ev.keyCode : ev.which);
            if (keycode == '13') {
                fnc.call(this, ev);
            }
        })
    })
}
$(document).ready(function(){
	$("#user_password").enterKey(function(){
		$("#div1").html("<div class='alert alert-info alert-dismissable'><center><img src='../img/loader3.gif' alt='Loading...'/></center></div>");
			$.post("../main/login_check.php", { 
			username: $("#username").val(), 
			user_password: $("#user_password").val(), 
			chk: $("#chk").val()}, 
				function(result){
					$("#div1").html(result);
				}
			);

		});
	$("#btn1").click(function(){
		$("#div1").html("<div class='alert alert-info alert-dismissable'><center><img src='../img/loader3.gif' alt='Loading...'/></center></div>");
			$.post("../main/login_check.php", { 
			username: $("#username").val(), 
			user_password: $("#user_password").val(), 
			chk: $("#chk").val()}, 
				function(result){
					$("#div1").html(result);
				}
			);

		});
	});

</script>
<?php

?>
  </body>
</html>
