<?php
$position = getuserposition($_SESSION[username]);
$avatar = getavatar($_SESSION[username]);
?>
<header class="header">
            <a href="../main/index.php" class="logo">
                <!-- Add the class icon to your logo image or logo icon to add the margining -->
                <div class='animated zoomIn'>GIS for JHCIS</div>
            </a>
            <!-- Header Navbar: style can be found in header.less -->
            <nav class="navbar navbar-static-top" role="navigation">
                <!-- Sidebar toggle button-->
                    <a href="#" class="navbar-btn sidebar-toggle" data-toggle="offcanvas" role="button">
                   		<span class="sr-only">Toggle navigation</span>
                    	<span class="icon-bar"></span>
                    	<span class="icon-bar"></span>
                    	<span class="icon-bar"></span>
                	</a>               
                <div class="navbar-right">
                    <ul class="nav navbar-nav">
                        <!-- Messages: style can be found in dropdown.less-->

                        <li class="dropdown messages-menu">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                <i class="fa fa-list-alt"></i>
                                <span class="label label-success">5</span>
                            </a>
                            <ul class="dropdown-menu">
                                <li class="header">เมนูใหม่</li>
                                <li>
                                    <!-- inner menu: contains the actual data -->
                                    <ul class="menu">
                                    <?php
								include "../includes/function.php";
								$txtm = '';
								$txtm .= "";
								$sqlm = "SELECT menugis.id, menugis.menugroup, menugis.menuname, menugis.menulink, menugis.detail,if(LENGTH(menugis.detail)>= 80,concat(left(menugis.detail,80),'...'),menugis.detail) as detailleft, menugis.mark,menugis.dateupdate FROM menugis ORDER BY menugis.dateupdate DESC LIMIT 5";
								$resultm=mysql_query($sqlm);
									while($rowm=mysql_fetch_array($resultm)) {
										$dateupdate = fb_thaidate(strtotime($rowm[dateupdate]));
										$txtm .="<li><!-- start message -->";
										$txtm .=" <a href='$rowm[menulink]'>";
										$txtm .="<div class='pull-left'>";
										$txtm .="<img src='../img/new_icon.gif' class='img-circle' alt=''/>";
										$txtm .="</div>";
										$txtm .="<h4>";
										$txtm .="$rowm[menuname]";
										$txtm .="";
										$txtm .="</h4>";
										$txtm .="<p>$rowm[detailleft]</p>";
										$txtm .="<p><small>$dateupdate <i class='fa fa-clock-o'></i></small></p></a>";
										$txtm .="</li><!-- end message -->";
									}
								echo $txtm;
								?>
                                    </ul>
                                </li>
                                <li class="footer"><a href="../main/index.php?page=boxmenu">View all Menu</a></li>
                            </ul>
                        </li>
                        <!-- Notifications: style can be found in dropdown.less -->
                        <li class="dropdown notifications-menu">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                <i class="fa fa-tasks"></i>
                                <span class="label label-warning">8</span>
                            </a>
                            <ul class="dropdown-menu">
                                <li class="header">เลือกกลุ่มเมนู</li>
                                <li>
                                    <!-- inner menu: contains the actual data -->
                                    <ul class="menu">
                                    	<li>
                                            <a href="../main/index.php?page=boxmenu&gmenu=1">
                                                <i class="fa fa-users warning"></i> เครื่องมือ
                                            </a>
                                        </li>

                                        <li>
                                            <a href="../main/index.php?page=boxmenu&gmenu=2">
                                                <i class="ion ion-ios7-cart success"></i> บันทึกพิกัด
                                            </a>
                                        </li>
                                        <li>
                                            <a href="../main/index.php?page=boxmenu&gmenu=3">
                                                <i class="ion ion-ios7-person danger"></i> ข้อมูลทั่วไป
                                            </a>
                                        </li>
                                        <li>
                                            <a href="../main/index.php?page=boxmenu&gmenu=4">
                                                <i class="ion ion-ios7-people info"></i> ส่งเสริม
                                            </a>
                                        </li>
                                        <li>
                                            <a href="../main/index.php?page=boxmenu&gmenu=5">
                                                <i class="fa fa-warning danger"></i> ป้องกัน ควบคุมโรค
                                            </a>
                                        </li>
                                        <li>
                                            <a href="../main/index.php?page=boxmenu&gmenu=6">
                                                <i class="fa fa-users warning"></i> รักษา
                                            </a>
                                        </li>

                                        <li>
                                            <a href="../main/index.php?page=boxmenu&gmenu=7">
                                                <i class="ion ion-ios7-cart success"></i> ฟื้นฟู
                                            </a>
                                        </li>
                                        <li>
                                            <a href="../main/index.php?page=boxmenu&gmenu=8">
                                                <i class="ion ion-ios7-person danger"></i> ตรวจสอบเครื่องมือ
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                                <li class="footer"><a href="../main/index.php?page=boxmenu">View all Menu</a></li>
                            </ul>
                        </li>
                        <!-- Tasks: style can be found in dropdown.less -->
                        <li class="dropdown tasks-menu">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                <i class="fa fa-bars"></i>
                                <span class="label label-danger">4</span>
                            </a>
                            <ul class="dropdown-menu">
                                <li class="header">เมนูแนะนำ</li>
                                <li>
                                    <!-- inner menu: contains the actual data -->
                                    <ul class="menu">
                                        <li><!-- Task item -->
                                            <a href="../maps/appmiss.php">
                                                <h3>
                                                    รายงานการนัด
                                                    <small class="pull-right">20%</small>
                                                </h3>
                                                <div class="progress xs">
                                                    <div class="progress-bar progress-bar-aqua" style="width: 20%" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
                                                        <span class="sr-only">20% Complete</span>
                                                    </div>
                                                </div>
                                            </a>
                                        </li><!-- end task item -->
                                        <li><!-- Task item -->
                                            <a href="../maps/epe0.php">
                                                <h3>
                                                    ข้อมูลระบาดวิทยา
                                                    <small class="pull-right">40%</small>
                                                </h3>
                                                <div class="progress xs">
                                                    <div class="progress-bar progress-bar-green" style="width: 40%" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
                                                        <span class="sr-only">40% Complete</span>
                                                    </div>
                                                </div>
                                            </a>
                                        </li><!-- end task item -->
                                        <li><!-- Task item -->
                                            <a href="../maps/buffers.php">
                                                <h3>
                                                    รัศมีจากหลังคาเรือน
                                                    <small class="pull-right">60%</small>
                                                </h3>
                                                <div class="progress xs">
                                                    <div class="progress-bar progress-bar-red" style="width: 60%" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
                                                        <span class="sr-only">60% Complete</span>
                                                    </div>
                                                </div>
                                            </a>
                                        </li><!-- end task item -->
                                        <li><!-- Task item -->
                                            <a href="../chart/chart_pyramid.php">
                                                <h3>
                                                    ปิรามิดประชากร
                                                    <small class="pull-right">80%</small>
                                                </h3>
                                                <div class="progress xs">
                                                    <div class="progress-bar progress-bar-yellow" style="width: 80%" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100">
                                                        <span class="sr-only">80% Complete</span>
                                                    </div>
                                                </div>
                                            </a>
                                        </li><!-- end task item -->
                                    </ul>
                                </li>
                                <li class="footer">
                                    <a href="../main/index.php?page=boxmenu">View all Menu</a>
                                </li>
                            </ul>
                        </li>
                        <!-- User Account: style can be found in dropdown.less -->
                        <li class="dropdown user user-menu">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                <i class="glyphicon glyphicon-user"></i>
                                <span><?php echo $cfuser?> <i class="caret"></i></span>
                            </a>
                            <ul class="dropdown-menu">
                                <!-- User image -->
                                <li class="user-header bg-light-blue">
                                    <img src="<?=$avatar?>" class="img-circle" alt="User Image" />
                                    <p>
                                        <?php echo $cuser?>
                                        <small><?php echo $position?></small>
                                    </p>
                                </li>
                                <!-- Menu Body -->
                                <li class="user-body">
                                    <div class="col-xs-12 text-center">
                                        <a href="#"><?php echo $hospitalname ?></a>
                                    </div>
                                </li>
                                <!-- Menu Footer-->
                                <li class="user-footer">
                                    <div class="pull-left">
                                        <a href="../main/index.php?page=profile" class="btn btn-default btn-flat">Profile</a>
                                    </div>
                                    <div class="pull-right">
                                        <a href="../main/logout.php" class="btn btn-default btn-flat">Sign out</a>
                                    </div>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>
        </header>
        
<script src="../ajax/ajaxmember.js"></script>

<!-- reset password -->
<!-- Modal -->
<div class="modal fade" id="myModal" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="myModalLabel"><span class="glyphicon glyphicon-cog"></span>  เปลี่ยนรหัสผ่าน</h4>
      </div>
      <div class="modal-body">
      <form class="form-horizontal" name="form1" id="form1" role="form" method='post' action='index.php?page=resetpass'>
      	<div class="form-group">
    		<label for="inputEmail3" class="col-sm-4 control-label">รหัสผ่านเก่า</label>
    		<div class="col-sm-8">
      			<input type="password" name="oldpassword" class="form-control" id="inputEmail3" placeholder="" required autofocus>
    		</div>
  		</div>
        <div class="form-group">
    		<label for="inputEmail3" class="col-sm-4 control-label">รหัสผ่านใหม่</label>
    		<div class="col-sm-8">
      			<input type="password" name="password" class="form-control" id="inputEmail3" placeholder="" required>
    		</div>
  		</div>
        <div class="form-group">
    		<label for="inputEmail3" class="col-sm-4 control-label">รหัสผ่านไม่อีกครั้ง</label>
    		<div class="col-sm-8">
      			<input type="password" name="repassword" class="form-control" id="inputEmail3" placeholder="" required onKeyUp="checkpasswd(form1.password.value, form1.repassword.value)">
    		</div>
  		</div>
        <div class="form-group">
    		<label for="inputEmail3" class="col-sm-4 control-label"></label>
    		<div class="col-sm-8">
      			<div id="msg11"></div>
    		</div>
  		</div>
      </div>
      <div class="modal-footer"> 
        <button type="submit" class="btn btn-success">เปลี่ยนรหัสผ่าน</button>
        <button type="button" class="btn btn-danger" data-dismiss="modal">ปิดหน้าต่าง</button>
      </div>
      </form>
    </div>
  </div>
</div>