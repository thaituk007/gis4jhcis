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
$txt = "<p align='center'><strong>รัศมีจากหลังคาเรือน</strong></p>";
$txt .= "<div class='form-group'>
             				<label>ชนิดพื้นที่ :</label>
                  			<div class='input-group'>
                   				<div class='input-group-addon'>
				   				<i class='fa fa-bullseye'></i>
				   				</div><select name='artype' id='artype' class='form-control' onchange='getCenterHouse();'>
    <option value='1' selected>วงกลม</option>
    <option value='2'>สี่เหลี่ยม</option>
  </select></div><!-- /.input group -->
                		</div><!-- /.form group -->";
$txt .= "<div class='form-group'>
             				<label>รัศมี :</label>
                  			<div class='input-group'>
                   				<div class='input-group-addon'>
				   				<i class='fa fa-dot-circle-o'></i>
				   				</div><input type='text' name='rd' id='rd' size='5' class='form-control' value='50' onBlur='getCenterHouse();'/>
    <option value='1' selected>วงกลม</option>
    <option value='2'>สี่เหลี่ยม</option>
  </select></div><!-- /.input group -->
                		</div><!-- /.form group -->";
$txt .= "<div class='form-group'>
             				<label>บ้านเลขที่ : </label>
                  			<div class='input-group'>
                   				<div class='input-group-addon'>
				   				<div id='ret_data'></div>
				   				</div><input type='text' name='hono' id='hono' size='5' class='form-control' onBlur='getCenterHouse();'>
    <option value='1' selected>วงกลม</option>
    <option value='2'>สี่เหลี่ยม</option>
  </select></div><!-- /.input group -->
                		</div><!-- /.form group -->";
$txt .= "<div class='form-group'>
             				<label>หมู่บ้าน:</label>
                  			<div class='input-group'>
                   				<div class='input-group-addon'>
				   				<i class='fa fa-home'></i>
				   				</div><select name='village' id='village' class='form-control'  onchange='getCenterHouse();'>
			";
$sql = "SELECT villcode,villno,villname FROM village WHERE villno <> '0' ORDER BY villcode";
$result=mysql_query($sql,$link);
while($row=mysql_fetch_array($result)) {
	$txt .= "<option value='$row[villcode]'>$row[villno] $row[villname]</option>";
}	  
$txt .= "</select></div><!-- /.input group -->
                		</div><!-- /.form group -->
									<table><tr><td><input type='button' class='btn btn-success' value='ตกลง' onclick='getData();'></td><td><div id='csv'></div></td><td><div id='sum'></div></td><td><div id='chart'></div></td></tr></table><div id='dtl'></div><div id='slidebar'></div>";
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
var markercenter;
var markertr;
var markerbl;
var checkSave = 0;
var clatlng;
var circle;
var rectangle;
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
 
 }
 
  function getData(){
			var html;
			var html2;
			var simg;
			deleteOverlays();
			var artype = document.getElementById('artype').value;
			var rd = document.getElementById('rd').value;
			var hono = document.getElementById('hono').value;
			var villcode = document.getElementById('village').value;
			var clat = clatlng.lat();
			var clng = clatlng.lng();
			if(artype == '1'){
				var url = "../genxml/genxml_buffers.php?rd="+rd+"&artype="+artype+"&lat="+clat+"&lng="+clng;
			}else{
				var latn = markertr.getPosition().lat();
				var lnge = markertr.getPosition().lng();
				var lats = markerbl.getPosition().lat();
				var lngw = markerbl.getPosition().lng();				
				var url = "../genxml/genxml_buffers.php?latn="+latn+"&lats="+lats+"&lnge="+lnge+"&artype="+artype+"&lngw="+lngw;
			}
			downloadUrl(url, function(data) {
				var xml = parseXml(data);
				var xmldata = xml.documentElement.getElementsByTagName("marker");
				infoWindow = new google.maps.InfoWindow;
				var sidebar = document.getElementById('slidebar');
				sidebar.innerHTML = "";
				var bounds = new google.maps.LatLngBounds();
				// Creating a loop
		  for (var i = 0; i < xmldata.length; i++) {
		  var hono = xmldata[i].getAttribute("hono");
          var moo = xmldata[i].getAttribute("moo");
          var vill = xmldata[i].getAttribute("vill");
		  var hhouse = xmldata[i].getAttribute("hhouse");
		  var lat = xmldata[i].getAttribute("lat");
		  var lng = xmldata[i].getAttribute("lng");
		  var latlng = new google.maps.LatLng(lat, lng);
			if(lat != ''){simg = "success.png";}else{simg = "s_really.png";}
			html = "<img src='../img/"+simg+"'> บ้านเลขที่ " + hono+"<br>"+hhouse;
		   	html2 = "บ้านเลขที่ "+hono+" หมู่ " +vill + "<br>" + hhouse;
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
					map.fitBounds(bounds);
		});	
		document.getElementById("csv").innerHTML = "<input type='button' value='ข้อมูล' class= 'btn btn-success' onclick='getXls();'>";
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
		document.getElementById('slidebar').innerHTML = "";
	}
}
function deleteBorder(){
		if(circle){
		circle.setMap(null);
	}
	if(rectangle){
		rectangle.setMap(null);
		markertr.setMap(null);
		markerbl.setMap(null);		
	}
}
function deleteMarkercenter() {
	if (markercenter) {
		markercenter.setMap(null);
	}
	deleteBorder();
	deleteOverlays()
}


google.maps.event.addDomListener(window, 'load', initialize);   


function getCenterHouse(){
	deleteMarkercenter();
	var artype = document.getElementById('artype').value;
	var rd = document.getElementById('rd').value;
	var hno = document.getElementById('hono').value;
	var villagecode = document.getElementById('village').value;
    var url = "../execute/get_centerbuffers.php?villagecode="+villagecode+"&hno="+hno; 
	var chkdata;
	  createXMLHttpRequest();
            xmlHttp.open("get", url, true);
			xmlHttp.onreadystatechange = function () {
                if (xmlHttp.readyState == 4) {
                    if (xmlHttp.status == 200) {
						var chkdata = xmlHttp.responseText;
						if(chkdata == 0){
							document.getElementById("ret_data").innerHTML = "<img src='../img/cross_circle.png'>";
						}else{
							document.getElementById("ret_data").innerHTML = "<img src='../img/success.png'>";
							var ret = chkdata.split(",")
							clatlng = new google.maps.LatLng(ret[0],ret[1]);
							markercenter = new google.maps.Marker({
								map: map,
								position: clatlng,
								title: "บ้านเลขที่ "+hno
							});
							map.setCenter(clatlng);
							if(artype == "2"){
								var nlat = 9;
								var nlng = 9.4;
								var ls = parseFloat(ret[0])-(rd*nlat/1000000);
								var ln = parseFloat(ret[0])+(rd*nlat/1000000);
								var le = parseFloat(ret[1])+(rd*nlng/1000000);
								var lw = parseFloat(ret[1])-(rd*nlng/1000000);
								var sw = new google.maps.LatLng(ls,lw);
								var ne = new google.maps.LatLng(ln,le);
								markertr = new google.maps.Marker({
								  map: map,
								  position: ne,
								  draggable: true,
								  icon:"../img/mm_20_gray.png",
								  title: 'เลื่อน!'
								});
								markerbl = new google.maps.Marker({
								  map: map,
								  position: sw,
								  draggable: true,
								  icon:"../img/mm_20_gray.png",
								  title: 'เลื่อน!'
								});
								google.maps.event.addListener(markertr, 'drag', redraw);
        						google.maps.event.addListener(markerbl, 'drag', redraw);
								rectangle = new google.maps.Rectangle({
									map: map,
								});

								redraw();
							}else{
								var k_radius = rd*1;
				 				circle = new google.maps.Circle({
			     				center: clatlng,
				  				map: map,
				  				radius: k_radius
								});
							}
						}
                    } 
                }            
            };
      xmlHttp.send(null);	
}

  function redraw() {
		var latLngBounds = new google.maps.LatLngBounds(
			markerbl.getPosition(),
			markertr.getPosition()
		);
		rectangle.setBounds(latLngBounds);
  }

function getXls(){
	var artype = document.getElementById('artype').value;
	var rd = document.getElementById('rd').value;
	var hono = document.getElementById('hono').value;
	var villcode = document.getElementById('village').value;
	var clat = clatlng.lat();
	var clng = clatlng.lng();	
			if(artype == '1'){
				var url = "../xls/xls_buffers.php?rd="+rd+"&artype="+artype+"&lat="+clat+"&lng="+clng;
			}else{
				var latn = markertr.getPosition().lat();
				var lnge = markertr.getPosition().lng();
				var lats = markerbl.getPosition().lat();
				var lngw = markerbl.getPosition().lng();				
				var url = "../xls/xls_buffers.php?latn="+latn+"&lats="+lats+"&lnge="+lnge+"&artype="+artype+"&lngw="+lngw;
			}
	window.open(url,'data','top=120,left=250,width=600,height=450');
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