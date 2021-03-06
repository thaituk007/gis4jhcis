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
				$txt = "<p align='center'><strong>ประเมินความเสี่ยง DM/HT</strong></p>";
				$txt .= "<div class='form-group'>
             				<label>หมู่บ้าน:</label>
                  			<div class='input-group'>
                   				<div class='input-group-addon'>
				   				<i class='fa fa-tasks'></i>
				   				</div>
				   				<select name='village' class='form-control' id='village'>
								<option value='00000000'>ทุกหมู่บ้าน</option>";
						$sql = "SELECT villcode,villno,villname FROM village WHERE villno <> '0' ORDER BY villcode";
						$result=mysql_query($sql,$link);
						while($row=mysql_fetch_array($result)) {
				$txt .= "<option value='$row[villcode]'>$row[villno] $row[villname]</option>";
							}	  
				$txt .= "</select>
							</div><!-- /.input group -->
                		</div><!-- /.form group -->";	
				$txt .= "<div class='form-group'>
             				<label>อายุ : (ปีขึ้นไป)</label>
                  				<div class='input-group'>
                   					<div class='input-group-addon'>
                     				<i class='fa fa-calendar'></i>
                      				</div>
                                <input name='strage' type='text' id='strage' value='30' class='form-control' size='5'>
                            	</div><!-- /.input group -->
                		</div><!-- /.form group -->";
					
				$txt .= "<center><table><tr><td><input type='button' value='ประมวลผล' class='btn btn-success' onclick='getData();'></td><td><a href='http://www.kriwoot.com/gisjhcis/flowball7.pdf' target='_blank'>รายละเอียด</a></td></tr><tr><td colspan='2' align='center'><div id='datapingpong'></div></td></tr></table></center><div id='slidebar'></div>";	 
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
        <script src="../js/AdminLTE/demo.js" type="text/javascript"></script>
        <!-- แผนที่ google -->
		<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=true"></script>

<script type="text/javascript">
$(function() { //Date range picker
                $('#datestart').daterangepicker({timePicker: true, timePickerIncrement: 30, format: 'DD/MM/YYYY'});
            });
			// end Date range picker
// เริ่ม ajax แสดงแผนที่
var map;
var infoWindow;
var markers=[];
var customIcons = {
	c0: {
	icon: '../img/l0.png',
	},
	c1: {
	icon: '../img/l1.png',
	},
	c2: {
	icon: '../img/l2.png',
	},
	c3: {
	icon: '../img/l3.png',
	},
	c4: {
	icon: '../img/l4.png',
	},
	c5: {
	icon: '../img/l5.png',
	},
	c6: {
	icon: '../img/l6.png',
	},
	c7: {
	icon: '../img/l7.png',
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
			document.getElementById('slidebar').innerHTML = "<br><center>กำลังประมวลผล...</center>";
			var hok = 0;
			var hno = 0;
			var html;
			var html2;
			var simg;
			
			deleteOverlays();
			var village = document.getElementById('village').value;
			var strage = document.getElementById('strage').value;
			var url = "../genxml/genxml_ncdrisk.php?strage="+strage+"&village="+village;
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
		  var bod = xmldata[i].getAttribute("bod");
		  var age = xmldata[i].getAttribute("age");
		  var fbs = xmldata[i].getAttribute("fbs");
		  var sys = xmldata[i].getAttribute("sys");
		  var dias = xmldata[i].getAttribute("dias");
		  var a1c = xmldata[i].getAttribute("a1c");
		  var type = xmldata[i].getAttribute("type");
		  var lat = xmldata[i].getAttribute("lat");
		  var lng = xmldata[i].getAttribute("lng");
		  var latlng = new google.maps.LatLng(lat, lng);
		  var exam="";
		  if(fbs != '0'){exam = "FBS "+fbs+" mg/dl ";}
		  if(sys != '0'){exam = exam+"BP "+sys+"/"+dias+" mm.Hg ";}
		  if(a1c != '0.00'){exam = exam+"HbA1c "+a1c+" ";}
			if(lat != ''){simg = "success.png";}else{simg = "s_really.png";}
			html = pname+" ("+age+" ปี)"+"<br><img src='../img/"+simg+"'> หมู่ "+moo+ " บ้านเลขที่ " + hono;
		   	html2 = pname+ "("+age+" ปี)<br>บ้านเลขที่ "+hono+" หมู่ " +vill +"<br>"+exam;
			if(type == '0'){
				var icon = customIcons['c0'];
			}else if(type == '1'){
				var icon = customIcons['c1'];
			}else if(type == '2'){
				var icon = customIcons['c2'];
			}else if(type == '3'){
				var icon = customIcons['c3'];
			}else if(type == '4'){
				var icon = customIcons['c4'];
			}else if(type == '5'){
				var icon = customIcons['c5'];
			}else if(type == '6'){
				var icon = customIcons['c6'];
			}else{
				var icon = customIcons['c7'];
			}
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
					var sidebarEntry = createSidebarEntry(marker,html,lat,type);
					sidebar.appendChild(sidebarEntry);					
				} 
				var checkMarkers = markers.length;
				if(checkMarkers > 0){
					map.fitBounds(bounds);
				}
		});
		document.getElementById("datapingpong").innerHTML = "<input type='button' class='btn btn-info' value='Download' onclick='getDownloadXls();'> <input type='button' class='btn btn-danger' value='แสดงข้อมูล' onclick='getXls();'>";
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

function createSidebarEntry(marker,name,lat,type) {
	var div = document.createElement('div');
	var html = ""+name+"";
		div.innerHTML = html;
		div.style.cursor = 'pointer';
		div.style.marginBottom = '1px';
		if(lat == ""){
				var bg = '#ffffff';
		}else{
			if(type == "0"){
				var bg = '#FFFFFF';
			}else if(type == "1"){
				var bg = '#FFFFFF';
			}else if(type == "2"){
				var bg = '#669900';
			}else if(type == "3"){
				var bg = '#66FF33';
			}else if(type == "4"){
				var bg = '#FFFF00';
			}else if(type == "5"){
				var bg = '#FF66CC';			
			}else if(type == "6"){
				var bg = '#FF0000';				
			}else{
				var bg = '#999999';
			}
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
			var village = document.getElementById('village').value;
			var strage = document.getElementById('strage').value;
			var url = "../xls/xls_ncdrisk.php?strage="+strage+"&village="+village;
	window.open(url,'data','top=120,left=20,width=800,height=500');
}
function getDownloadXls(){
			var village = document.getElementById('village').value;
			var strage = document.getElementById('strage').value;
			var url = "../download/download_xls_ncdrisk.php?strage="+strage+"&village="+village;
	window.open(url,'data','top=120,left=20,width=800,height=500');
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