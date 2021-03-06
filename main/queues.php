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
    <body class="skin-blue">
        <!-- header logo: style can be found in header.less -->
        <?php include("../main/menu.php");?>
        <div class="wrapper row-offcanvas row-offcanvas-left">
            <!-- Left side column. contains the logo and sidebar -->
            <aside class="left-side sidebar-offcanvas">
                <!-- sidebar: style can be found in sidebar.less -->
                <section class="sidebar">
                <ul class="sidebar-menu">
                        <li>
                            <a href="#">
                                <i class="fa fa-dashboard"></i> <span>จัดรอคิว</span>
                            </a>
                        </li>
                        <li class="treeview">

            <?php
			$sql = "SELECT cflagservice.servicecode,
if(LENGTH(cflagservice.servicedesc) > 20,concat(left(cflagservice.servicedesc,20),'...'),cflagservice.servicedesc) as servicename FROM cflagservice where cflagservice.servicecode not in ('02','03','04','05','06','07','99','-H')";
						$result=mysql_query($sql,$link);
						while($row=mysql_fetch_array($result)) {
				$txt .= "<a href='#' onclick=getData('$row[servicecode]');><i class='fa fa-plus-circle'></i><span>$row[servicename]</span><i class='fa fa-angle-right pull-right'></i></a>";
							}	  
				$txt .= "";
				echo $txt;
			?>
            		</li>
                  </ul>
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
                        <li class="active">queues</li>
                    </ol>
             </section>
             <div id='div1'></div>
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
<script>
var xmlHttp;

function createXMLHttpRequest() {
    if (window.ActiveXObject) {
    xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
	 } 
	else if (window.XMLHttpRequest) {
 	 xmlHttp = new XMLHttpRequest();
	 }
}
function getData(data){
		document.getElementById("div1").innerHTML = "";
			tget = "data="+data;
			createXMLHttpRequest();
            xmlHttp.open("get", "queues_list.php?" + tget, true);
            xmlHttp.onreadystatechange = function () {
                if (xmlHttp.readyState == 4) {
                    if (xmlHttp.status == 200) {
                        document.getElementById("div1").innerHTML = xmlHttp.responseText;
                    }
                }            
            };
            xmlHttp.send(null);
	}
function getsound(flname,servicename){	
		var url = "../main/queues_sound.php?seq="+flname+"&ch="+servicename;
	window.open(url,'data','top=120,left=250,width=350,height=40');
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