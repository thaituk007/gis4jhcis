<?php
session_start();
set_time_limit(0);
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
        <!-- iCheck for checkboxes and radio inputs -->
        <link href="../css/iCheck/all.css" rel="stylesheet" type="text/css" />
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
				$txt = "<p align='center'><strong>ปิรามิดประชากร</strong></p>";
				$txt .= "                                    <!-- Date dd/mm/yyyy -->
                                    <div class='form-group'>
                                        <label>Date masks:</label>
                                        <div class='input-group'>
                                            <div class='input-group-addon'>
                                                <i class='fa fa-calendar'></i>
                                            </div>
                                            <select name='year' id='year' class='form-control'>
			<option value='2015'>2558</option>
			<option value='2014'>2557</option>
			<option value='2013'>2556</option>
			<option value='2012'>2555</option>
			<option value='2011'>2554</option>
			<option value='2010'>2553</option>
			<option value='2009'>2552</option>
			<option value='2008'>2551</option>
			<option value='2007'>2550</option>
			<option value='2006'>2549</option>
			<option value='2005'>2548</option>
			<option value='2004'>2547</option>
			<option value='2003'>2546</option>
			<option value='2002'>2545</option>
			<option value='2001'>2544</option>
			<option value='2000'>2543</option>
			<option value='1999'>2542</option>
			<option value='1998'>2541</option>
			<option value='1997'>2540</option>
			<option value='1996'>2539</option>
			<option value='1995'>2538</option>
			<option value='1994'>2537</option>
			<option value='1993'>2536</option>
			<option value='1992'>2535</option></select>
                                        </div><!-- /.input group -->
                                    </div><!-- /.form group -->";
				$txt .= "<div class='form-group'>
             				<label>เลือกหมู่บ้าน:</label>
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
							</div><input type='button' id='btn1' name='btn1' value='ประมวลผล' class='btn btn-success'><!-- /.input group -->
                		</div><!-- /.form group -->";	
			echo $txt;
?>
</form>
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
                    <div id="container"></div>
                </section><!-- right col -->   
            </aside><!-- /.right-side -->
		</div><!-- ./wrapper -->
        
<!-- เริ่มใช้งาน js -->
		<!-- functions สำหรับแสดงแผนที่ -->
		<script type="text/javascript" src="../js/functions.js"></script>
        <script src="../js/AdminLTE/app.js" type="text/javascript"></script>
        <script src="../js/jquery-2.0.2.min.js"></script>
        <script src="../js/bootstrap.min.js" type="text/javascript"></script>
        <script type="text/javascript" src="../js/highcharts.js"></script>
        <script type="text/javascript" src="../js/exporting.js"></script>
<script>
$(document).ready(function() {
	
	$("#btn1").click(function(){ //อ้างอิงจาก button id = btn1
			year= $("#year").val();
			village= $("#village").val(); // ตัวแรกวันที่สุดท้าย เพื่อส่งค่าไป
		$.ajax({  // get ค่า วันที่ ไปที่ไฟล์ test2.php
            type: "GET",
            url: "../genxml/genxml_chart_pyramid.php",
			data: "year="+year+"&village="+village,
            dataType: "xml",
			beforeSend: function() {
				$("#container").html("<center><img src=\"../img/loader.gif\" alt=\"Loading...\"/></center>");
			},
			success: function(xml) { // รับค่ามาเป็น xml
				// Split the lines
				var $xml = $(xml);
				
				// push categories
				$xml.find('categories item').each(function(i, category) { 
					options.xAxis.categories.push($(category).text());
				});
				
				// push series
				$xml.find('series').each(function(i, series) {
					var seriesOptions = {
						name: $(series).find('name').text(),
						data: []
					};
					
					// push data points
					$(series).find('data point').each(function(i, point) {
						seriesOptions.data.push(
							parseFloat($(point).text())
						);
					});
					
					// add it to the options
					options.series.push(seriesOptions);
				});
				var chart = new Highcharts.Chart(options);
				},
				 cache: false
			});
			//จบ get ajax
			
			
			//เริ่ม chart
			var options = {
						chart: {
                            renderTo: 'container',
                            type: 'bar'
                        },
						
                        title: {
                            text: 'ปิรามิดประชากร'
                        },
                        subtitle: {
                            text: ''
                        },
                        xAxis: {
							categories: []
						},
                        yAxis: {
                            title: {
                                text: null
                            },
                            labels: {
                                formatter: function() {
                                    //return (Math.abs(this.value));
                                    return Highcharts.numberFormat(Math.abs(this.value), 0,',');
                                    
                                }
                            },
                        },
                        plotOptions: {
                            series: {
                                stacking: 'normal',
                                cursor: 'pointer',
								pointWidth: 12,
                                point: {
                                    events: {
                                        click: function() {
                                            //alert ('Category: '+ this.category +', value: '+ this.y);
                                            window.location = '#' + this.category;
                                        }
                                    }
                                }
                            }

                        },
                        tooltip: {
                            formatter: function() {
                                return '<b>' + this.series.name + ',' + this.point.category + '</b><br/>' +
                                        'จำนวน ' + Highcharts.numberFormat(Math.abs(this.point.y), 0,',') + ' คน';
                            }
                        },
                        series: []
			};
		});
	});
        </script>

   <?php
}
else{
		header("Location: ../main/login.php");//หากไม่ได้ login ให้ไปหน้านี้
		}
		?>
    </body>
</html>