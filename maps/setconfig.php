<?php
session_start();
if($_SESSION[username]){
include("../includes/conndb.php"); 
include("../includes/config.inc.php");
$dm = date("d/m");
$d = date("d");
$m = date("m");
$yx = date("Y");
$y = date("Y");
$daysdatestr = "01/".$m."/".$y;
$daystart = $y."-".$m."-01";
$daylast = $yx."/".$m;
$daylast2 = lastday($daylast);
$dayend = retDatetsxxxx($daylast2);
$daysdatepick = $dm."/".$y;
$daledcy = date("Y");
$imputday = $daysdatestr." - ".$dayend;
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
        <!-- daterange picker -->
        <link href="../css/daterangepicker/daterangepicker-bs3.css" rel="stylesheet" type="text/css" />
        <!-- iCheck for checkboxes and radio inputs -->
        <link href="../css/iCheck/all.css" rel="stylesheet" type="text/css" />
        <!-- Bootstrap Color Picker -->
        <link href="../css/colorpicker/bootstrap-colorpicker.min.css" rel="stylesheet"/>
        <!-- Bootstrap time Picker -->
        <link href="../css/timepicker/bootstrap-timepicker.min.css" rel="stylesheet"/>
        <!-- Theme style -->
        <link href="../css/AdminLTE.css" rel="stylesheet" type="text/css" />

        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
          <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
        <![endif]-->
        <link href="../css/style.css" rel="stylesheet">
    </head>
    <body class="skin-blue" onLoad="adjustWindow()">
        <!-- เรียกใช้งาน menu -->
        <?php include("../main/menu.php");?>
        <!-- เริ่ม  wrapper-->
		<div class="wrapper row-offcanvas row-offcanvas-left">
            <!-- Left side column. contains the logo and sidebar -->
            <!-- L menu -->
			<aside class="left-side sidebar-offcanvas">
				<!-- sidebar: style can be found in sidebar.less -->
				<section class="sidebar">
                	<div id="pleft">
				<form role="form">
        		<?php
$txt = "<center><br><h4>กำหนดค่าเริ่มต้นแผนที่</strong></h4></center>";
$txt .= "<center>".$rowoff[hosname]."<br />"."(".$rowoff[hoscode]	.")"."</center>";
$txt .= "<table width='98%' border='0' align='center' cellpadding='1' cellspacing='1' class='table table-condensed'
  <tr>
    <td width='35%'><div align='right'>จุดกึ่งกลาง</div></td>
    <td width='65%'>ค่าเริ่มต้นแผนที่</td>
  </tr>
  <tr>
    <td><div align='right'>ละติจูด</div></td>
    <td><div id='c_lat'></div></td>
  </tr>
  <tr>
    <td><div align='right'>ลองดิจูด</div></td>
    <td><div id='c_lng'></div></td>
  </tr>
  <tr>
    <td><div align='right'>ชนิดแผนที่</div></td>
    <td><div id='c_type'></div></td>
  </tr>
  <tr>
    <td><div align='right'>ระยะย่อขยาย</div></td>
    <td><div id='c_zoom'></div></td>
  </tr>
  <tr>
    <td><div align='right'>ที่ตั้ง</div></td>
    <td>หน่วยบริการ</td>
  </tr>
  <tr>
    <td><div align='right'>ละติจูด</div></td>
    <td><div id='h_lat'></div></td>
  </tr>
  <tr>
    <td><div align='right'>ลองดิจูด</div></td>
    <td><div id='h_lng'></div></td>
  </tr>  
  <tr>
    <td><div align='right'></div></td>
    <td><input type='button' value='บันทึก' onclick='saveData();'></td>
  </tr>  
</table>";
$txt .= "<center><div id='d_data'></div></center>";
echo $txt;
?>
        		</form>
                	</div>
				</section>
			</aside>
            
            <!-- เริ่มแผนที่ -->
            <!-- Right side column. Contains the navbar and content of the page -->
            <aside class="right-side">
                <!-- Content Header (Page header) -->
                <!-- Main content -->
                <section class="content">
                    <!-- Small boxes (Stat box) -->
                    <div id ="pright"></div>
                </section><!-- right col -->   
            </aside><!-- /.right-side -->
		</div><!-- ./wrapper -->
        
<!-- เริ่มใช้งาน js -->
		<!-- functions สำหรับแสดงแผนที่ -->
		<script type="text/javascript" src="../js/functions.js"></script>
        <!-- jQuery 2.0.2 -->
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/2.0.2/jquery.min.js"></script>
        <!-- Bootstrap -->
        <script src="../js/bootstrap.min.js" type="text/javascript"></script>
        <!-- InputMask -->
        <script src="../js/plugins/input-mask/jquery.inputmask.js" type="text/javascript"></script>
        <script src="../js/plugins/input-mask/jquery.inputmask.date.extensions.js" type="text/javascript"></script>
        <script src="../js/plugins/input-mask/jquery.inputmask.extensions.js" type="text/javascript"></script>
        <!-- date-range-picker -->
        <script src="../js/plugins/daterangepicker/daterangepicker.js" type="text/javascript"></script>
        <!-- bootstrap color picker -->
        <script src="../js/plugins/colorpicker/bootstrap-colorpicker.min.js" type="text/javascript"></script>
        <!-- bootstrap time picker -->
        <script src="../js/plugins/timepicker/bootstrap-timepicker.min.js" type="text/javascript"></script>
        <!-- AdminLTE App -->
        <script src="../js/AdminLTE/app.js" type="text/javascript"></script>
        <!-- AdminLTE for demo purposes -->
        <script src="js/AdminLTE/demo.js" type="text/javascript"></script>
        <!-- แผนที่ google -->
		<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=true"></script>

<script type="text/javascript">
	var map;
	var curCenter;
	var curMapview;
	var curZoom;
	var curLat;
	var curLng;
	var hsLat;
	var hsLng;
	var markerhs;
	var infowindows;
  function initialize() {
	  
         map = new google.maps.Map(document.getElementById("pright"), {
        center: new google.maps.LatLng<?php if($_POST[ccenter]){echo $_POST[ccenter];}else{echo $dfmapcenter;}?>,
        zoom: <?php if($_POST[czoom]){echo $_POST[czoom];}else{echo $dfmapzoom;}?>,
        mapTypeId: google.maps.MapTypeId.<?php if($_POST[cmapview]){echo strtoupper($_POST[cmapview]);}else{echo $dfmapview;}?>,
		mapTypeControl: true,
    mapTypeControlOptions: {
        style: google.maps.MapTypeControlStyle.HORIZONTAL_BAR,
        position: google.maps.ControlPosition.TOP_RIGHT
    },
    navigationControl: true,
    navigationControlOptions: {
        style: google.maps.NavigationControlStyle.ZOOM_PAN,
        position: google.maps.ControlPosition.TOP_RIGHT
    },
    scaleControl: true,
    scaleControlOptions: {
        position: google.maps.ControlPosition.TOP_RIGHT
    }
    });

		var olat = <?php echo $hospitoalat; ?>;
		var olng = <?php echo $hospitoalng; ?>;
		if(olat != ""){
			var olatlng = new google.maps.LatLng(olat, olng);
			placeMarker(olatlng);
		}
	google.maps.event.addListener(map, 'bounds_changed', function() {
		getData();
	});
	google.maps.event.addListener(map, 'maptypeid_changed', function() {
		getData();
	});	
}

function getData(){
	curCenter = map.getCenter();
	curMapview = map.getMapTypeId();
	curZoom = map.getZoom();
	if(markerhs){hsCenter = markerhs.getPosition();}
	else{hsCenter = map.getCenter();}
	hsLat = hsCenter.lat().toPrecision(8);
	hsLng = hsCenter.lng().toPrecision(8);
	curLat = curCenter.lat().toPrecision(8);
	curLng = curCenter.lng().toPrecision(8);
	document.getElementById('c_lat').innerHTML = curLat;
	document.getElementById('c_lng').innerHTML = curLng;
	document.getElementById('h_lat').innerHTML = hsLat;
	document.getElementById('h_lng').innerHTML = hsLng;	
	document.getElementById('c_type').innerHTML = curMapview;
	document.getElementById('c_zoom').innerHTML = curZoom;

}
function saveData(){
	createXMLHttpRequest();
	  document.getElementById("d_data").innerHTML = "<img src='../img/ajax-loader.gif'/>";
	  var url = "gen_setconfig.php?ccenter="+curCenter+"&ctype="+curMapview+"&c_zoom="+curZoom+"&hslat="+hsLat+"&hslng="+hsLng;
            xmlHttp.open("get", url, true);
			xmlHttp.onreadystatechange = function () {
                if (xmlHttp.readyState == 4) {
                    if (xmlHttp.status == 200) {
						document.getElementById("d_data").innerHTML = xmlHttp.responseText;
						setInterval("document.getElementById('d_data').innerHTML =''",5000);
                    } 
                }            
            };
            xmlHttp.send(null);
}

function placeMarker(location) {
	var html = "ที่ตั้ง <?php echo $hospitalname;?><br>";
	 markerhs = new google.maps.Marker({
		position: location,
		draggable : true,
		map: map
	});
	google.maps.event.addListener(markerhs, "dragend", function() {
          getData();
    });
	infowindow = new google.maps.InfoWindow({
     		content: html
    });
	google.maps.event.addListener(markerhs, "click", function() {
          infowindow.open(map, markerhs);
		  getData();
     });
}	
	google.maps.event.addDomListener(window, 'load', initialize); 
</script>

   <?php
}
else{
		header("Location: ../main/login.php");//หากไม่ได้ login ให้ไปหน้านี้
		}
		?>
    </body>
</html>