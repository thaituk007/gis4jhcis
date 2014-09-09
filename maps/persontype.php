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
$cuday = $y."-".$m."-".$d;
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
$txt = "<p align='center'><strong>บุคคลสำคัญ</strong></p>";
$txt .= "<div class='form-group'>
             				<label>ประเภท:</label>
                  			<div class='input-group'>
                   				<div class='input-group-addon'>
				   				<i class='fa fa-home'></i>
				   				</div><select name='ptype' id='ptype' class='form-control'  onchange='getData();'>
			<option value='00'>ทุกประเภท</option>";
$sql = "SELECT pt.persontypecode,pt.persontypename
				FROM house AS h
				Inner Join person AS p ON h.pcucode = p.pcucodeperson AND h.hcode = p.hcode
				Inner Join persontype AS ps ON p.pcucodeperson = ps.pcucodeperson AND p.pid = ps.pid
				Inner Join cpersontype AS pt ON ps.typecode = pt.persontypecode
				WHERE $ect ((p.dischargetype is null) or (p.dischargetype = '9')) AND
				SUBSTRING(h.villcode,7,2) <> '00'
				GROUP BY pt.persontypecode
				ORDER BY pt.persontypecode";
$result=mysql_query($sql,$link);
while($row=mysql_fetch_array($result)) {
	$txt .= "<option value='$row[persontypecode]'>$row[persontypename]</option>";
}	  
$txt .= "</select></div><!-- /.input group -->
                		</div><!-- /.form group -->";
$txt .= "<div class='form-group'>
             				<label>หมู่บ้าน:</label>
                  			<div class='input-group'>
                   				<div class='input-group-addon'>
				   				<i class='fa fa-home'></i>
				   				</div><select name='village' id='village' class='form-control'  onchange='getData();'>
			<option value='00000000'>ทุกหมู่บ้าน</option>";
$sql = "SELECT villcode,villno,villname FROM village WHERE villno <> '0' ORDER BY villcode";
$result=mysql_query($sql,$link);
while($row=mysql_fetch_array($result)) {
	$txt .= "<option value='$row[villcode]'>$row[villno] $row[villname]</option>";
}	  
$txt .= "</select></div><!-- /.input group -->
                		</div><!-- /.form group -->
									<table><tr><td><input type='button' class='btn btn-success' value='ok' onclick='getData();'></td><td><div id='csv'></div></td><td><div id='sum'></div></td><td><div id='chart'></div></td></tr></table><div id='dtl'></div><div id='slidebar'></div>";
	$txt .= "";
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
var map;
var infoWindow;
var markers=[];
var customIcons = {
	c0: {
	icon: '../img/c001.png',
	},
	c1: {
	icon: '../img/c001.png',
	},
	c2: {
	icon: '../img/c001.png',
	},
	c3: {
	icon: '../img/c001.png',
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
  getData();
}

function getData(){
			var html;
			var html2;
			var simg;
			deleteOverlays();
			var ptype = document.getElementById('ptype').value;
			var village = document.getElementById('village').value;
			var url = "../genxml/genxml_persontype.php?ptype="+ptype+"&village="+village;
			downloadUrl(url, function(data) {
				var xml = parseXml(data);
				var xmldata = xml.documentElement.getElementsByTagName("marker");
				infoWindow = new google.maps.InfoWindow;
				var sidebar = document.getElementById('slidebar');
				var bounds = new google.maps.LatLngBounds();
				sidebar.innerHTML = "";
			
				// Creating a loop
		  for (var i = 0; i < xmldata.length; i++) {
          var hono = xmldata[i].getAttribute("hono");
          var moo = xmldata[i].getAttribute("moo");
          var vill = xmldata[i].getAttribute("vill");
		  var pname = xmldata[i].getAttribute("pname");
		  var dsc = xmldata[i].getAttribute("dsc");
		  var telephoneperson = xmldata[i].getAttribute("telephoneperson");
		  var lat = xmldata[i].getAttribute("lat");
		  var lng = xmldata[i].getAttribute("lng");
		  var latlng = new google.maps.LatLng(lat, lng);
			if(lat != ''){simg = "success.png";}else{simg = "s_really.png";}
			html = pname+" โทร  "+telephoneperson+"<br><img src='../img/"+simg+"'> หมู่ "+moo+ " บ้านเลขที่ " + hono;
		   	html2 = pname+" โทร  "+telephoneperson+"<br>บ้านเลขที่ "+hono+" หมู่ " +vill + "<br>" + dsc;
					var icon = customIcons['c0'];
			if(lat != ""){					
					var marker = new google.maps.Marker({
						map : map,
						position: latlng,
						icon: icon.icon,
						title: pname+"("+hono+" ม."+moo+")"
					});
					bounds.extend(latlng);	
					bindInfoWindow(marker, map, infoWindow, html2);
					markers.push(marker);
				}
					var sidebarEntry = createSidebarEntry(marker,html,lat);
					sidebar.appendChild(sidebarEntry);					
				}
				var checkMarkers = markers.length;
				if(checkMarkers > 0){
					map.fitBounds(bounds);
				}
		});
		document.getElementById("csv").innerHTML = "<input type='button' value='ข้อมูล' class='btn btn-info' onclick='getXls();'>";
		document.getElementById("sum").innerHTML = "<input type='button' value='สรุป' class='btn btn-primary' onclick='getsum();'>";
		document.getElementById("chart").innerHTML = "<input type='button' value='chart' class='btn btn-danger' onclick='getchart();'>";
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
function getXls(){
	var ptype = document.getElementById('ptype').value;
	var village = document.getElementById('village').value;
	var url = "../xls/xls_persontype.php?ptype="+ptype+"&village="+village;
	window.open(url,'data','top=120,left=250,width=600,height=450');
}
function getchart(){
	var ptype = document.getElementById('ptype').value;
	var village = document.getElementById('village').value;
	var url = "../chart/chart_persontype.php?ptype="+ptype+"&village="+village;
	window.open(url,'data','top=120,left=250,width=800,height=500');
}
function getsum(){
	var ptype = document.getElementById('ptype').value;
	var village = document.getElementById('village').value;
	var url = "../sum/sum_persontype.php?ptype="+ptype+"&village="+village;
	window.open(url,'data','top=120,left=250,width=800,height=500');
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