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
        		<?php
				$txt = "<p align='center'><strong>ค้นหาตำแหน่งบ้าน</strong></p>";
				$txt .= "<center><input type='button' value='จากชื่อ-สกุล' class='btn btn-info' onclick='frmName();'/> <input type='button' value='เลขประชาชน' class='btn btn-info' onclick='frmCid();'/><br /></center><div id='frm'></div>";	
			echo $txt;
?>
<div id='slidebar'> </div>
                	</div> <!-- //pleft-->
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
        <script src="../js/jquery-2.0.2.min.js"></script>
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
        <!-- <script src="../js/AdminLTE/demo.js" type="text/javascript"></script>-->
        <!-- แผนที่ google -->
		<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=true"></script>

<script type="text/javascript">
$(function() { //Date range picker
                $('#datestart').daterangepicker({timePicker: true, timePickerIncrement: 30, format: 'DD/MM/YYYY'});
            });
			// end Date range picker
// เริ่ม ajax แสดงแผนที่
var map;
var infoWindow = new google.maps.InfoWindow;
var markerhs;
var markers=[];
var myLatlng;
var customIcons = {
	c0: {
	icon: '../img/c002.png',
	},
	c1: {
	icon: '../img/c002.png',
	},
	c2: {
	icon: '../img/c002.png',
	},
	c3: {
	icon: '../img/c002.png',
	}
};
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
	myLatlng = new google.maps.LatLng(<?php echo $hospitoalat.",".$hospitoalng;?>);
	markerhs = new google.maps.Marker({
      position: myLatlng,
      title:"<?php echo $hospitalname;?>"
  	});
markerhs.setMap(map);
bindInfoWindow(markerhs, map, infoWindow, "<?php echo $hospitalname;?>");
}

function getData(id){
			var html;
			var html2;
			var simg;
			deleteOverlays();
			var tget = "";
			if(id == 1){
				var fname = document.getElementById('fname').value;
				var lname = document.getElementById('lname').value;
				tget = "fname="+fname+"&lname="+lname;
				if(fname == '' || lname == ''){
					alert("ระบุข้อมูลค้นหาให้ครบ");
					return false;
					}
			}else if(id == 2){
				var cid = document.getElementById('cid').value;
				tget = "cid="+cid;
				if(cid == ''){
					alert("ระบุข้อมูลค้นหาให้ครบ");
					return false;
					}				
			}else{}
			var url = "../genxml/genxml_search.php?"+tget;
			downloadUrl(url, function(data) {
				var xml = parseXml(data);
				var xmldata = xml.documentElement.getElementsByTagName("marker");
				var sidebar = document.getElementById('slidebar');
				var bounds = new google.maps.LatLngBounds();
				sidebar.innerHTML = "";
				// Creating a loop
		  for (var i = 0; i < xmldata.length; i++) {
		  if(i == 0){
			  gend = xmldata[i].getAttribute("lat")+","+xmldata[i].getAttribute("lng");
		  }
          var hono = xmldata[i].getAttribute("hono");
          var moo = xmldata[i].getAttribute("moo");
          var vill = xmldata[i].getAttribute("vill");
		  var pname = xmldata[i].getAttribute("pname");
		  var cid = xmldata[i].getAttribute("cid");
		  var lat = xmldata[i].getAttribute("lat");
		  var lng = xmldata[i].getAttribute("lng");
		  var latlng = new google.maps.LatLng(lat, lng);
			if(lat != ''){simg = "success.png";}else{simg = "s_really.png";}
			html2 = pname+"(" + hono+" หมู่ "+moo+")<br>CID "+cid;
		   	html = "<img src='../img/"+simg+"'>"+pname+"<br>CID "+cid+"("+hono+" หมู่ " +vill+")";
					var icon = customIcons['c0'];
			if(lat != ""){					
					var marker = new google.maps.Marker({
						map : map,
						position: latlng,
						icon: icon.icon,
						title: hono+" หมู่ "+moo
					});
					bounds.extend(latlng);	
					bindInfoWindow(marker, map, infoWindow, html2);
					markers.push(marker);
				}
					var sidebarEntry = createSidebarEntry(marker,html,lat);
					sidebar.appendChild(sidebarEntry);
				
				} 
				bounds.extend(myLatlng);			
				var checkMarkers = markers.length;
				if(checkMarkers > 0){
					map.fitBounds(bounds);
					calcRoute();
				}else{
					document.getElementById('slidebar').innerHTML = "<b>ไม่พบข้อมูล</b>";	
				}
		});
}

function downloadUrl(url, callback) {
var request = window.ActiveXObject ?
new ActiveXObject('Microsoft.XMLHTTP') :
new XMLHttpRequest;
request.onreadystatechange = function() {
if (request.readyState == 4) {
callback(request.responseText, request.status);
}
};
request.open('GET', url, true);
request.send(null);
}
function parseXml(str) {
	if (window.ActiveXObject) {
		var doc = new ActiveXObject('Microsoft.XMLDOM');
		doc.loadXML(str);
		return doc;
	} else if (window.DOMParser) {
		return (new DOMParser).parseFromString(str, 'text/xml');
	}
}

function bindInfoWindow(marker, map, infoWindow, html) {
	google.maps.event.addListener(marker, 'click', function() {
		infoWindow.setContent(html);
		infoWindow.open(map, marker);
	});
}
function createSidebarEntry(marker,name,lat) {
	var div = document.createElement('div');
	var html = ""+name+"";
		div.innerHTML = html;
		div.style.cursor = 'pointer';
		div.style.marginBottom = '1px';
		if(lat == ""){
			var bg = '#ffffff';
		}else{
			var bg = '#cfd8f3';
		}
	div.style.backgroundColor = bg;
	if(lat != ""){
	google.maps.event.addDomListener(div, 'click', function() {
		google.maps.event.trigger(marker, 'click');
	});
	}
	google.maps.event.addDomListener(div, 'mouseover', function() {
		div.style.backgroundColor = '#e8ecf9';
	});
	google.maps.event.addDomListener(div, 'mouseout', function() {
		div.style.backgroundColor = bg;
	});
	return div;
}
function deleteOverlays() {
	if (markers) {
		for (i in markers) {
		markers[i].setMap(null);
	}
		markers.length = 0;
	}
}

google.maps.event.addDomListener(window, 'load', initialize); 

function frmName(){
	var txt = " ชื่อ <input type='text' name='fname' id='fname' class='form-control'> สกุล <input type='text' name='lname' id='lname' class='form-control'>"+"<br><input type='button' value='ค้นหา' class='btn btn-success' onclick='getData(1);'>";
	document.getElementById('frm').innerHTML = txt;
}
function frmCid(){
	var txt = "เลขประชาชน <input type='text' name='cid' id='cid' class='form-control'><br><input type='button' class='btn btn-success'  value='ค้นหา' onclick='getData(2);'>";
	document.getElementById('frm').innerHTML = txt;	
}

</script>

   <?php
}
else{
		header("Location: ../main/login.php");//หากไม่ได้ login ให้ไปหน้านี้
		}
		?>
    </body>
</html>