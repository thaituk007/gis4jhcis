                    <!-- Sidebar user panel -->
                    <div class="user-panel">
                        <div class="pull-left image">
                            <img src="<?=$avatar?>" class="img-circle animated fadeInUp" alt="User Image" />
                        </div>
                        <div class="pull-left info">
                            <p>Hello</p><p></p>
                            <a href="#"><i class="fa fa-circle text-success"></i><?=$cuser?></a>
                        </div>
                    </div>
                    <!-- search form -->
                        <div class="input-group">
                            <input type="text" name="scmenu" id="scmenu" class="form-control" placeholder="Search..." onkeydown='getsxmenu();'>
                            <span class="input-group-btn">
                                <button type='button' name='seach' id='search-btn' onclick='getsxmenu();' class="btn btn-flat"><i class="fa fa-search"></i></button>
                            </span>
                        </div>
                    <!-- /.search form -->
                    <!-- sidebar menu: : style can be found in sidebar.less -->
                    <ul class="sidebar-menu">
                    	<div id="sxmenu"></div>
                        <li class="active">
                            <a href="../main/index.php">
                                <i class="fa fa-dashboard"></i> <span>Dashboard</span>
                            </a>
                        </li>
                        
                        <li class="treeview">
                            <a href="#">
                                <i class="fa fa-gear"></i>
                                <span>เครื่องมือ</span>
                                <i class="fa fa-angle-left pull-right"></i>
                            </a>
                            <ul class="treeview-menu">
                                <li><a href="../maps/setconfig.php"><i class="fa fa-angle-double-right"></i> config default</a></li>
                                <li><a href="../main/syncdata.php"><i class="fa fa-angle-double-right"></i> sync data</a></li>
                                <li><a href="../main/export_village.php"><i class="fa fa-angle-double-right"></i> ส่งออกพิกัดหมู่บ้าน</a></li>
                                <li><a href="../main/export.php"><i class="fa fa-angle-double-right"></i> ส่งออกพิกัดหลังคาเรือน</a></li>
                                <li><a href="../main/import.php"><i class="fa fa-angle-double-right"></i> import data</a></li>
                                <li><a href="../maps/movepoints.php"><i class="fa fa-angle-double-right"></i> move point</a></li>
                                <li><a href="../main/index.php?page=update_doctor"><i class="fa fa-angle-double-right"></i> กำหนดหมู่บ้านรับผิดชอบ</a></li>
                            </ul>
                        </li>
                        <li class="treeview">
                            <a href="#">
                                <i class="fa fa-edit "></i>
                                <span>บันทึก แก้ไข พิกัด</span>
                                <i class="fa fa-angle-left pull-right"></i>
                            </a>
                            <ul class="treeview-menu">
                                <li><a href="../maps/markvillage.php"><i class="fa fa-angle-double-right"></i> พิกัดหมู่บ้าน</a></li>
                                <li><a href="../maps/markhouse.php"><i class="fa fa-angle-double-right"></i> พิกัดหลังคาเรือน</a></li>
                                <li><a href="../maps/marktemple.php"><i class="fa fa-angle-double-right"></i> พิกัดศาสนสถาน</a></li>
                                <li><a href="../maps/markschool.php"><i class="fa fa-angle-double-right"></i> พิกัดโรงเรียน</a></li>
                                <li><a href="../maps/markwater.php"><i class="fa fa-angle-double-right"></i> พิกัดแหล่งน้ำสาธารณะ</a></li>
                                <li><a href="../maps/markfoodshop.php"><i class="fa fa-angle-double-right"></i> พิกัดร้านอาหาร</a></li>
                                <li><a href="../maps/markbusiness.php"><i class="fa fa-angle-double-right"></i> พิกัดสถานประกอบการ</a></li>
                            </ul>
                        </li>
                        <li class="treeview">
                            <a href="#">
                                <i class="fa fa-bar-chart-o"></i>
                                <span>ข้อมูลทั่วไป</span>
                                <i class="fa fa-angle-left pull-right"></i>
                            </a>
                            <ul class="treeview-menu">
                                <li><a href="../maps/villagegis.php"><i class="fa fa-angle-double-right"></i> หมู่บ้าน</a></li>
            					<li><a href="../maps/templegis.php"><i class="fa fa-angle-double-right"></i> ศาสนสถาน</a></li>
            					<li><a href="../maps/schoolgis.php"><i class="fa fa-angle-double-right"></i> โรงเรียน</a></li>
            					<li><a href="../chart/chart_pyramid.php"><i class="fa fa-angle-double-right"></i> ปิรามิด ปชก.</a></li>
            					<li><a href="../maps/search.php"><i class="fa fa-angle-double-right"></i> ค้นหาพิกัดจากชื่อ/CID</a></li>
            					<li><a href="../maps/person.php"><i class="fa fa-angle-double-right"></i> ปชก.ตามกลุ่มอายุ</a></li>
            					<li><a href="../maps/housegis.php"><i class="fa fa-angle-double-right"></i> หลังคาเรือนรายหมู่บ้าน</a></li>
            					<li><a href="../maps/persontype.php"><i class="fa fa-angle-double-right"></i> บุคคลสำคัญ</a></li>
            					<li><a href="../maps/buffers.php"><i class="fa fa-angle-double-right"></i> รัศมีจากหลังคาเรือน</a></li>
            					<li><a href="../maps/personvola.php"><i class="fa fa-angle-double-right"></i> บ้านที่ อสม.รับผิดชอบ</a></li>
                            </ul>
                        </li>
                        <li class="treeview">
                            <a href="#">
                                <i class="fa fa-thumbs-up"></i>
                                <span>ส่งเสริม</span>
                                <i class="fa fa-angle-left pull-right"></i>
                            </a>
                            <ul class="treeview-menu">
                                 <li class="treeview"><a href="#"><i class="fa fa-angle-double-right"></i> เยี่ยมบ้าน</a>
                                	<ul class="treeview-menu">
                                		<li><a href="../maps/home_visit_all.php"><i class="fa fa-angle-double-right"></i> เยี่ยมบ้านทุกกลุ่ม</a></li>
                						<li><a href="../maps/home_visit_old.php"><i class="fa fa-angle-double-right"></i> เยี่ยมบ้านผู้สูงอายุ</a></li>
                						<li><a href="../maps/home_visit_chronic.php"><i class="fa fa-angle-double-right"></i> เยี่ยมบ้านผู้ป่วยเรื้อรัง</span></a><li>
                						<li><a href="../maps/home_visit_disorder.php"><i class="fa fa-angle-double-right"></i> เยี่ยมบ้านผู้พิการ</a></li>
                                        <li><a href="../maps/home_visit_tb.php"><i class="fa fa-angle-double-right"></i> เยี่ยมบ้านผู้ป่วยวัณโรค</a></li>
            							<li><a href="../maps/mch.php"><i class="fa fa-angle-double-right"></i> เยี่ยมหญิงหลังคลอด</a></li>
                           			</ul>
                                </li>
                                 <li class="treeview"><a href="#"><i class="fa fa-angle-double-right"></i> อนามัยแม่และเด็ก</a>
                                	<ul class="treeview-menu">
                						<li><a href="../maps/anc_12_w.php"><i class="fa fa-angle-double-right"></i> หญิงคั้งครรภ์ 12 week</a></li>
                    					<li><a href="../maps/anc_4_q.php"><i class="fa fa-angle-double-right"></i> ฝากครรภ์ครบคุณภาพ</a></li>
                    					<li><a href="../maps/anc.php"><i class="fa fa-angle-double-right"></i> ทะเบียนหญิงตั้งครรภ์</a></li>
                    					<li><a href="../maps/anc_tooth.php"><i class="fa fa-angle-double-right"></i> หญิงตั้งครรภ์ตรวจฟัน</a></li>
										<li><a href="../maps/mch.php"><i class="fa fa-angle-double-right"></i> เยี่ยมหลังคลอด</a></li>
                           			</ul>
                                </li>
                                 <li class="treeview"><a href="#"><i class="fa fa-angle-double-right"></i> เด็ก 0 - 72 เดือน</a>
                                	<ul class="treeview-menu">
                                		<li><a href="../maps/nutri.php"><i class="fa fa-angle-double-right"></i> โภชนาการเด็ก</a></li>
										<li><a href="../maps/devolop.php"><i class="fa fa-angle-double-right"></i> พัฒนาการเด็ก</a></li>
                    					<li><a href="../maps/epi_tooth.php"><i class="fa fa-angle-double-right"></i> เด็ก9-24เดือนตรวจฟัน</a></li>
                    					<li><a href="../maps/epi_cover_all.php"><i class="fa fa-angle-double-right"></i> ความครอบคุลมวัคซีน</a></li>
                    					<li><a href="../maps/epi_appoint_miss.php"><i class="fa fa-angle-double-right"></i> เด็กขาดนัดวัคซีน</a></li>
                           			</ul>
                                </li>
                                 <li class="treeview"><a href="#"><i class="fa fa-angle-double-right"></i> ผู้สูงอายุ</a>
                                	<ul class="treeview-menu">
                                		<li><a href="../maps/old_3d.php"><i class="fa fa-angle-double-right"></i> ประเภทผู้สูงอายุ</a></li>
										<li><a href="../maps/old_health.php"><i class="fa fa-angle-double-right"></i> ผู้สูงอายุพึงประสงค์</a></li>
										<li><a href="../maps/home_visit_old.php"><i class="fa fa-angle-double-right"></i> เยี่ยมบ้านผู้สูงอายุ</a></li>
										<li><a href="../maps/old_nutri.php"><i class="fa fa-angle-double-right"></i> โภชนาการผู้สูงอายุ</a></li>
                           			</ul>
                                </li>
                                <li class="treeview"><a href="#"><i class="fa fa-angle-double-right"></i> วางแผนครอบครัว</a>
                                	<ul class="treeview-menu">
                                    	<li><a href="../maps/condom_fp.php"><i class="fa fa-angle-double-right"></i> จ่ายถุงยางอนามัย</a></li>
                                		<li><a href="../maps/female_mary_20.php"><i class="fa fa-angle-double-right"></i> หญิงอายุต่ำกว่า 20 ปี คุมดำเนิด</a></li>
										<li><a href="../maps/female_mary_44.php"><i class="fa fa-angle-double-right"></i> หญิงอายุ 20-44 ปี คุมดำเนิด</a></li>
                           			</ul>
                                </li>
                            </ul>
                        </li>
                        <li class="treeview">
                            <a href="#">
                                <i class="fa fa-laptop"></i>
                                <span>ป้องกัน</span>
                                <i class="fa fa-angle-left pull-right"></i>
                            </a>
                            <ul class="treeview-menu">
                                <li class="treeview"><a href="#"><i class="fa fa-angle-double-right"></i> ระบาดวิทยา</a>
                                	<ul class="treeview-menu">
                                    	<li><a href="../maps/epe0.php"><i class="fa fa-angle-double-right"></i> ผู้ที่ป่วยด้วยโรค 506</a></li>
                           			</ul>
                                </li>
                                 <li class="treeview"><a href="#"><i class="fa fa-angle-double-right"></i> โรคไม่ติดต่อ</a>
                                	<ul class="treeview-menu">
                                    	<li><a href="../maps/depress.php"><i class="fa fa-angle-double-right"></i> คัดกรองภาวะซึมเศร้า</a></li>
										<li><a href="../maps/chronic.php"><i class="fa fa-angle-double-right"></i> ทะเบียนผู้ป่วยโรคเรื้อรัง</a></li>
                                		<li class="treeview"><a href="#"><i class="fa fa-angle-double-right"></i> เบาหวาน/ความดัน</a>
                                			<ul class="treeview-menu">
                                				<li><a href="../maps/chronic_dmht.php"><i class="fa fa-angle-double-right"></i> ทะเบียนผู้ป่วย DM/HT</a></li>
                    							<li><a href="../maps/gis_ncd_screen.php"><i class="fa fa-angle-double-right"></i> รายงานการคัดกรอง</a></li>
                    							<li><a href="../maps/dm_lab.php"><i class="fa fa-angle-double-right"></i> Lab เบาหวาน/ความดัน</a></li>
                    							<li><a href="../maps/ncdrisk.php"><i class="fa fa-angle-double-right"></i> ประเมินลูกบอล 7 สี</a></li>
                           					</ul>
                               			</li>
                                        <li><a href="#"><i class="fa fa-angle-double-right"></i> มะเร็งเต้านม</a></li>
                                        <li><a href="../maps/papsmear.php"><i class="fa fa-angle-double-right"></i> มะเร็งปากมดลูก</a></li>
                						<li><a href="../maps/ncd_waist.php"><i class="fa fa-angle-double-right"></i> วัดรอบเอว</a></li>
                           			</ul>
                                </li>
                                 <li class="treeview"><a href="#"><i class="fa fa-angle-double-right"></i> วัคซีน</a>
                                	<ul class="treeview-menu">
                						<li><a href="../maps/epi_cover_all.php"><i class="fa fa-angle-double-right"></i> ความครอบคลุมวัคซีน</a></li>
                						<li><a href="../maps/appmiss.php"><i class="fa fa-angle-double-right"></i> เด็กที่ขาดนัดวัคซีน</a></li>
                           			</ul>
                                </li>
                                 <li class="treeview"><a href="#"><i class="fa fa-angle-double-right"></i> เอดส์</a>
                                	<ul class="treeview-menu">
                                		<li><a href="../maps/condom_aids.php"><i class="fa fa-angle-double-right"></i> จ่ายถุงยางอนามัย</a></li>
                           			</ul>
                                </li>
                                 <li class="treeview"><a href="#"><i class="fa fa-angle-double-right"></i> วัณโรค</a>
                                	<ul class="treeview-menu">
                                		<li><a href="../maps/home_visit_tb.php"><i class="fa fa-angle-double-right"></i> เยี่ยมบ้านวัณโรค</a></li>
                           			</ul>
                                </li>
                                <li class="treeview"><a href="#"><i class="fa fa-angle-double-right"></i> มะเร็งตับ</a>
                                	<ul class="treeview-menu">
                    					<li><a href="../maps/stool_ov.php"><i class="fa fa-angle-double-right"></i> รายงานการตรวจstool</a></li>
                    					<li><a href="../maps/home_stool.php"><i class="fa fa-angle-double-right"></i> รายงานผลแบบที่ 2</a></li>
                    					<li><a href="../maps/ultra.php"><i class="fa fa-angle-double-right"></i> รายงานผลอัลตร้าซาวด์</a></li>
                           			</ul>
                                </li>
                                <li class="treeview"><a href="#"><i class="fa fa-angle-double-right"></i> สารเคมีเกษตรกร</a>
                                	<ul class="treeview-menu">
                    					<li><a href="../maps/farmer.php"><i class="fa fa-angle-double-right"></i> รายงานการตรวจ</a></li>
                           			</ul>
                                </li>
                            </ul>
                        </li>
                        <li class="treeview">
                            <a href="#">
                                <i class="fa fa-plus-circle"></i> <span>รักษา</span>
                                <i class="fa fa-angle-left pull-right"></i>
                            </a>
                            <ul class="treeview-menu">
								<li class="treeview"><a href="#"><i class="fa fa-angle-double-right"></i> แผนไทย</a>
                                	<ul class="treeview-menu">
                						<li><a href="../maps/drug_panthai.php"><i class="fa fa-angle-double-right"></i> จ่ายยาสมุนไพร</a></li>
                						<li><a href="../maps/proced_panthai.php"><i class="fa fa-angle-double-right"></i> หัตถการแพทย์แผนไทย</a></li>
                           			</ul>
                                </li>
								<li><a href="../maps/visit.php"><i class="fa fa-angle-double-right"></i> รายงานการรับบริการ</a></li>
                				<li><a href="../main/service.php"><i class="fa fa-angle-double-right"></i> รายงานอื่นๆ</a></li>
                				<li><a href="../maps/appmiss.php"><i class="fa fa-angle-double-right"></i> รายงานการนัด</a></li>
                				<li><a href="../maps/lab_all.php"><i class="fa fa-angle-double-right"></i> ผลการตรวจLabต่างๆ</a></li>
                            </ul>
                        </li>
                        <li class="treeview">
                            <a href="#">
                                <i class="fa fa-shopping-cart"></i> <span>ฟื้นฟู</span>
                                <i class="fa fa-angle-left pull-right"></i>
                            </a>
                            <ul class="treeview-menu">
                                <li><a href="../maps/unable.php"><i class="fa fa-angle-double-right"></i> ทะเบียนผู้พิการ</a></li>
                            </ul>
                        </li>
                        <li class="treeview">
                            <a href="#">
                                <i class="fa fa-table"></i> <span>ตรวจสอบข้อมูล</span>
                                <i class="fa fa-angle-left pull-right"></i>
                            </a>
                            <ul class="treeview-menu">
            					<li><a href="../maps/gis_cid_else.php"><i class="fa fa-angle-double-right"></i> เลขบัตรประชาชนผิด</a></li>
                            </ul>
                        </li>
                    </ul>
                    
                    
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
function getsxmenu(){
		document.getElementById("sxmenu").innerHTML = "";
			var scmenu = document.getElementById("scmenu").value;
			tget = "search=menu&scmenu="+scmenu ;
			createXMLHttpRequest();
            xmlHttp.open("get", "../execute/execute.php?" + tget, true);
            xmlHttp.onreadystatechange = function () {
                if (xmlHttp.readyState == 4) {
                    if (xmlHttp.status == 200) {
                        document.getElementById("sxmenu").innerHTML = xmlHttp.responseText;
                    }
                }            
            };
            xmlHttp.send(null);
	}
</script>