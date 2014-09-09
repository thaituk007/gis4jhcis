<?php
session_start();
if($_SESSION[username]){
include("../includes/conndb.php"); 
include("../includes/config.inc.php");

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title><?php echo $titleweb; ?></title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
        <!-- bootstrap 3.0.2 -->
        <link href="../css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <!-- font Awesome -->
        <link href="../css/font-awesome.min.css" rel="stylesheet" type="text/css" />
        <!-- Ionicons -->
        <link href="../css/ionicons.min.css" rel="stylesheet" type="text/css" />
        <!-- Morris chart -->
        <link href="../css/morris/morris.css" rel="stylesheet" type="text/css" />
        <!-- jvectormap -->
        <link href="../css/jvectormap/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />
        <!-- fullCalendar -->
        <link href="../css/fullcalendar/fullcalendar.css" rel="stylesheet" type="text/css" />
        <!-- Daterange picker -->
        <link href="../css/daterangepicker/daterangepicker-bs3.css" rel="stylesheet" type="text/css" />
        <!-- bootstrap wysihtml5 - text editor -->
        <link href="../css/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css" rel="stylesheet" type="text/css" />
        <!-- Theme style -->
        <link href="../css/AdminLTEindex.css" rel="stylesheet" type="text/css" />

        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
          <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
        <![endif]-->
    </head>
    <body class="skin-blue" onLoad="getData();">
        <!-- header logo: style can be found in header.less -->
        <?php include("../main/menu.php");?>
        <div class="wrapper row-offcanvas row-offcanvas-left">
            <!-- Left side column. contains the logo and sidebar -->
			<aside class="left-side sidebar-offcanvas">
				<!-- sidebar: style can be found in sidebar.less -->
				<section class="sidebar">
            		<?php include("../main/lmenu.php");?>
 				</section>
            </aside>
            <!-- Right side column. Contains the navbar and content of the page -->
            <aside class="right-side">
            <section class="content-header">
                    <h1>
                        <?php echo $offname?>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                        <li class="active">updatedoctor</li>
                    </ol>
                </section>

                <!-- Main content -->
                <section class="content">
                <div class="row">
                        <div class="col-md-12">
                            <div class="box box-primary">
                                <div class="box-header">
                                    <i class="fa fa-edit"></i>
                                    <h3 class="box-title">กำหนดหมู่บ้านรับผิดชอบ</h3>
                                </div>
                                <div class="box-body pad table-responsive">

<form id="frmdoc" name="frmdoc">
<?php
$txt .= "<div align = 'center'><br/><table><tr><td>เจ้าหน้าที่</td><td><select name='username' id='username' class='form-control'><option value=''>เลือก นสค.</option>";
	$sql = "select `user`.username, concat(c.titlename,user.fname,'  ',user.lname) as pname FROM `user` Inner Join ctitle c ON `user`.prename = c.titlecode WHERE `user`.markdelete IS NULL";
	$result=mysql_query($sql,$link);
	while($row=mysql_fetch_array($result)) {
		$txt .= "<option value='$row[username]'>$row[pname]</option>";
	}
	$txt .= "</select></td>";

$txt .= "<td>รับผิดชอบหมู่บ้าน :</td><td><select name='villcode' id='villcode' class='form-control'><option value=''>เลือกหมู่บ้าน</option>";
$sql = "SELECT villcode,villno,villname FROM village WHERE villno <> '0' ORDER BY villcode";
$result=mysql_query($sql,$link);
while($row=mysql_fetch_array($result)) {
	$txt .= "<option value='$row[villcode]'>$row[villno] $row[villname]</option>";
}	  
$txt .= "</select></td>
			<td></td><td><input type='button' class='btn btn-success' name='btn1' id='btn1' value='บันทึก' onclick='getData();'></td></tr></table></div>";
	$txt .= "<div id='update_doctor'></div>";
	echo $txt;
?>
</form>

<br>
<center><input type='button' class='btn btn-success' value='กำหนด นสค. ประชากรนอกเขต เป็นค่าว่าง( typearea = 4)' onclick='setnullnsk();'></center>
                                </div><!-- /.box -->
                            </div>
                        </div><!-- /.col -->
                    </div><!-- ./row -->
				</section>
            </aside><!-- /.right-side -->
        </div><!-- ./wrapper -->

        <!-- add new calendar event modal -->


        <!-- jQuery 2.0.2 -->
        <script src="../js/jquery-2.0.2.min.js"></script>
        <!-- jQuery UI 1.10.3 -->
        <script src="../js/jquery-ui-1.10.3.min.js" type="text/javascript"></script>
        <!-- Bootstrap -->
        <script src="../js/bootstrap.min.js" type="text/javascript"></script>
        <!-- Morris.js charts -->
        <script src="../js/raphael-min.js"></script>
        <script src="../js/plugins/morris/morris.min.js" type="text/javascript"></script>
        <!-- Sparkline -->
        <script src="../js/plugins/sparkline/jquery.sparkline.min.js" type="text/javascript"></script>
        <!-- jvectormap -->
        <script src="../js/plugins/jvectormap/jquery-jvectormap-1.2.2.min.js" type="text/javascript"></script>
        <script src="../js/plugins/jvectormap/jquery-jvectormap-world-mill-en.js" type="text/javascript"></script>
        <!-- fullCalendar -->
        <script src="../js/plugins/fullcalendar/fullcalendar.min.js" type="text/javascript"></script>
        <!-- jQuery Knob Chart -->
        <script src="../js/plugins/jqueryKnob/jquery.knob.js" type="text/javascript"></script>
        <!-- daterangepicker -->
        <script src="../js/plugins/daterangepicker/daterangepicker.js" type="text/javascript"></script>
        <!-- Bootstrap WYSIHTML5 -->
        <script src="../js/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js" type="text/javascript"></script>
        <!-- iCheck -->
        <script src="../js/plugins/iCheck/icheck.min.js" type="text/javascript"></script>

        <!-- AdminLTE App -->
        <script src="../js/AdminLTE/app.js" type="text/javascript"></script>
        
        <!-- AdminLTE dashboard demo (This is only for demo purposes) -->
        <script src="../js/AdminLTE/dashboard.js" type="text/javascript"></script>     
        
        <!-- AdminLTE for demo purposes -->
        <script src="../js/AdminLTE/demo.js" type="text/javascript"></script>
<script type="text/javascript">
var xmlHttp;

function createXMLHttpRequest() {
    if (window.ActiveXObject) {
    xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
	 } 
	else if (window.XMLHttpRequest) {
 	 xmlHttp = new XMLHttpRequest();
	 }
}
function getData(){
		document.getElementById("update_doctor").innerHTML = "<center><img src='../img/loader3.gif'/></center>";
			var username = document.getElementById('username').value;
			var villcode = document.getElementById('villcode').value;
			tget = "username="+username+"&villcode="+villcode;
			createXMLHttpRequest();
            xmlHttp.open("get", "../execute/execute_updatedoctor.php?" + tget, true);
            xmlHttp.onreadystatechange = function () {
                if (xmlHttp.readyState == 4) {
                    if (xmlHttp.status == 200) {
                        document.getElementById("update_doctor").innerHTML = xmlHttp.responseText;
                    }
                }            
            };
            xmlHttp.send(null);
	}
function setnullnsk(){
		document.getElementById("update_doctor").innerHTML = "<center><img src='../img/loader3.gif'/></center>";
			createXMLHttpRequest();
            xmlHttp.open("get", "../execute/execute_updatedoctor.php?chk=0", true);
            xmlHttp.onreadystatechange = function () {
                if (xmlHttp.readyState == 4) {
                    if (xmlHttp.status == 200) {
                        document.getElementById("update_doctor").innerHTML = xmlHttp.responseText;
                    }
                }            
            };
            xmlHttp.send(null);
	}
</script>
   <?php
}
else{
		header("Location: ../main/login.php");
		}
		?>
    </body>
    
</html>