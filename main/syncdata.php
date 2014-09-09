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
                	<br /><p align="center"><strong>การ Sync ข้อมูลกับ GIS Center</strong></p>
<ui>
<li> Database Server ของจังหวัดต้องเปิดให้ Sync ถึงใช้งานได้</li>
<li> หาก Sync ข้อมูลไม่ได้ ใช้การ Export และ Import โดยการ Upload และ Download ผ่านเว็บไซต์แทน</li>
<li> ตารางข้อมูล tbhouse ใน Database Server ต้องเป็นรูปแบบที่กำหนดเท่านั้น</li>
<li> รูปแบบตาราง tbhouse </li>
</ui>
<br><hr><br>CREATE TABLE IF `tbhouse` (
 <br> `id` int(11) NOT NULL auto_increment,
 <br> `mooban` varchar(8),
 <br> `house_no` varchar(100),
 <br> `lat` float(10,6),
<br>  `lng` float(10,6),
 <br> `catgis` int(2),
<br>  PRIMARY KEY  (`id`),
<br>  KEY `hono` (`mooban`,`house_no`)
<br> ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
<hr>
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
                        <li class="active">syndata</li>
                    </ol>
                </section>

                <!-- Main content -->
                <section class="content">
                <div class="row">
                        <div class="col-md-12">
                            <div class="box box-primary">
                                <div class="box-header">
                                    <i class="fa fa-edit"></i>
                                    <h3 class="box-title">Sync ข้อมูลกับ GIS Center</h3>
                                </div>
                                <div class="box-body pad table-responsive">
            <?php
if($_POST[village]){
		$hosserver = $_POST[ipserver];
		$userserver = $_POST[userserver];
		$pwdserver = $_POST[passwordserver];
		$dbserver = $_POST[databaseserver];
		$linkserver = mysql_connect($hosserver,$userserver,$pwdserver) or die ("Could not connect to MySQL Server");
		//mysql_query("SET NAMES UTF8",$linkserver);
		mysql_select_db($dbserver,$linkserver) or die ("Could not select $db database");	
		$village = $_POST[village];

	if($_POST[synctype] == '1'){
		$sql = "SELECT house_no,lat,lng FROM tbhouse WHERE mooban = '$village'";	
		$result=mysql_query($sql,$linkserver);
			while($row=mysql_fetch_array($result)) {
				$sqlup = "UPDATE house SET ygis='$row[lat]',xgis='$row[lng]' WHERE villcode = '$village' AND hno = '$row[house_no]' ";
				$resultup=mysql_query($sqlup,$link);
				if($resultup){$total++;}
			}	  
			$txt = "<br /><br /><br /><center><strong>ปรับปรุงข้อมูลจาก SERVER มา JHCIS  $total หลังคาเรือน</strong><br /><br />
					<INPUT TYPE='button' VALUE=' Sync หมู่ต่อไป ' onClick='history.go(-1);'>
					<a href='syncdata.php'><input type='button' value='ตกลง'></a></center>";
	}
	if($_POST[synctype] == '2'){
		$sql = "SELECT ygis,xgis,hno FROM house WHERE villcode='$village' AND ygis IS NOT NULL";
		$result=mysql_query($sql,$link);
			while($row=mysql_fetch_array($result)) {
				$sqltbhouse = "SELECT COUNT(*) AS n FROM tbhouse WHERE mooban = '$village' AND house_no = '$row[hno]'";
				$resulttbhouse=mysql_query($sqltbhouse,$linkserver);
				$rstbhouse = mysql_fetch_array($resulttbhouse);
				if($rstbhouse[n] > 0){
					$sqlup = "UPDATE tbhouse SET lat='$row[ygis]',lng='$row[xgis]' WHERE mooban = '$village' AND house_no = '$row[hno]' ";
					$resultup=mysql_query($sqlup,$linkserver);
					if($resultup){$total++;}
				}else{
					$sqlins = "INSERT INTO tbhouse SET mooban='$village',house_no='$row[hno]',lat='$row[ygis]',lng='$row[xgis]',catgis=1";	
					$resultins=mysql_query($sqlins,$linkserver);
					if($resultins){$total++;}
				}
			}
			$txt = "<br /><center><strong>ปรับปรุงข้อมูลจาก JHCIS ไป SERVER  $total หลังคาเรือน</strong><br /><br />
					<INPUT TYPE='button' VALUE=' Sync หมู่ต่อไป ' onClick='history.go(-1);'>
					<a href='syncdata.php'><input type='button' value='ตกลง'></a></center>";			
	}
	
}else{


$txt = "<form name='form1' method='post' action=''>
  <h4 class='text text-primary' align='center'>Config Server</h4>
  <table width='100%'  class='table table-hover'>
    <tr>
      <td width='47%'><div align='right'>IP Server</div></td>
      <td width='53%'><input type='text' name='ipserver' id='ipserver'  class='form-control'></td>
    </tr>
    <tr>
      <td><div align='right'>Username</div></td>
      <td><input name='userserver' type='text' id='userserver' size='15'  class='form-control'></td>
    </tr>
    <tr>
      <td><div align='right'>Password</div></td>
      <td><input name='passwordserver' type='text' id='passwordserver' size='15'  class='form-control'></td>
    </tr>
    <tr>
      <td><div align='right'>Database</div></td>
      <td><input name='databaseserver' type='text' id='databaseserver' size='15'  class='form-control'></td>
    </tr>
  </table>
  <table width='100%' class='table table-hover'>
    <tr>
      <td width='47%'><div align='right'>หมู่บ้าน</div></td>
      <td width='53%'><select name='village' id='village'  class='form-control'>";
$sql = "SELECT villcode,villno,villname FROM village WHERE villno <> '0' ORDER BY villcode";
$result=mysql_query($sql,$link);
while($row=mysql_fetch_array($result)) {
	$txt .= "<option value='$row[villcode]'>$row[villno] $row[villname]</option>";
}	  
$txt .= "</select></td>
    </tr>
    <tr>
      <td><div align='right'>รูปแบบ</div></td>
      <td><select name='synctype' id='synctype'  class='form-control'>
        <option value='1'>Server &gt; JHCIS</option>
        <option value='2'>JHCIS &gt; Server</option>
      </select></td>
    </tr>
    <tr>
      <td><div align='right'></div></td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td><div align='right'></div></td>
      <td><input type='submit' name='button' id='button' class='btn btn-success' value='ตกลง'></td>
    </tr>
  </table>
</form>";
}
echo $txt;
?>
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
   <?php
}
else{
		header("Location: ../main/login.php");
		}
		?>
    </body>
    
</html>