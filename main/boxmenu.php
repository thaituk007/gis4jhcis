				<section class="content-header animated fadeInRight">
                    <h1>
                        <?php echo $offname?>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                        <li class="active">Dashboard</li>
                    </ol>
                </section>
								<?php
								//รับค่าและแปลงค่าตัวแปร
								$menugrop = $_GET[gmenu];
								if($menugrop == ""){
									$sqlbox = "";
								}else{
									$sqlbox = " where menugis.menugroup = '$menugrop'";
								}
								if($menugrop == 1){
									$gmenuname = "ตั้งค่าระบบ";
								}elseif($menugrop == 2){
									$gmenuname = "บันทึก แก้ไข พิกัด";
								}elseif($menugrop == 3){
									$gmenuname = "ข้อมูลทั่วไป";
								}elseif($menugrop == 4){
									$gmenuname = "ส่งเสริมสุขภาพ";
								}elseif($menugrop == 5){
									$gmenuname = "ป้องกัน ควบคุมโรค";
								}elseif($menugrop == 6){
									$gmenuname = "รักษา";
								}elseif($menugrop == 7){
									$gmenuname = "ฟื้นฟูสุภาพ";
								}else{
									$gmenuname = "";
								}
								?>
                <!-- Main content -->
                <section class="content">
					 <div class="row">
                        <div class="col-xs-12 connectedSortable">
							<div class="box">
                                <div class="box-header">
                                    <h3 class="box-title"><?php echo $gmenuname ?></h3>
                                </div>
                                <div class="box-body">
                                <?php
								$txt = '<div class="row">';
								$txt .= "";
								$sql = "SELECT menugis.id, menugis.menugroup, menugis.menuname, menugis.menulink, menugis.detail, menugis.mark FROM
menugis $sqlbox";
								$result=mysql_query($sql);
									while($row=mysql_fetch_array($result)) {
										$txt .="<div class='col-md-4'>";
										$txt .="<div class='box box-solid bg-$row[mark] animated zoomIn'>";
										$txt .="<div class='box-header'>";
										$txt .="<h3 class='box-title'>$row[menuname]</h3>";
										$txt .="</div>";
										$txt .="<div class='box-body'>";
										$txt .="แสดงรายงาน : <code><a href='$row[menulink]'>คลิกที่นี่</a></code>";
										$txt .="<p>";
										$txt .="$row[detail]";
										$txt .="</p>";
										$txt .="</div><!-- /.box-body -->";
										$txt .="</div><!-- /.box -->";
										$txt .="</div><!-- /.col -->";
									}
								$txt .= "</div>";
								echo $txt;
								?>
                               		
                                </div>
                            </div><!-- /.box -->
                         </div>
                      </div>
                  </section>