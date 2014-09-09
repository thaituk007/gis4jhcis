<?PHP
$user = $_SESSION[user_id];
	$sql = "SELECT * FROM `user` where `user`.username = '".$user."'";
	$result = mysql_query($sql) or die(mysql_error());
	$row = mysql_fetch_array($result);
	$mtitle = $row['prename'];
	$mpassword = $row['password'];
	$mtitlename = getTitle($row['prename']);
	$mfname = $row['fname'];
	$mlname = $row['lname'];
	$mname = $row['username'];
	$mavatar = getavatar($row['username']);
	$musername = getusername($row['username']);
	$midcard = $row['idcard'];
	$hospname = gethospname($row['pcucode']);
	$mposition = getuserposition($row['username']);
?>
<section class="content-header">
	<h1>
	ข้อมูลส่วนตัว
   	</h1>
		<ol class="breadcrumb">
			<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
			<li class="active">profile</li>
		</ol>
</section>
<section class="content">
	<div class="row">
		<div class="col-md-12">
			<!-- Primary box -->
			<div class="box box-solid box-primary">
				<div class="box-body">
					<div class="bs-example bs-example-tabs">
						<ul id="myTab" class="nav nav-tabs" role="tablist">
      						<li class="active"><a href="#home" role="tab" data-toggle="tab">ข้อมูลทั่วไป</a></li>
      						<li><a href="#profile" role="tab" data-toggle="tab">แก้ไขข้อมูล</a></li>
						</ul>
    <div id="myTabContent" class="tab-content">
      <div class="tab-pane fade in active" id="home">
        <div class="row">
        			<div class="col-sm-4">
                    <center><p><center><a href="#" data-toggle="modal" data-target="#myModalavatar"><img src="<?=$mavatar?>" alt="" class="img-thumbnail"></a></center></p>
                    <p><a href="#" role='button' class='btn btn-primary' onclick="javascript:window.open('../main/frm_avatar.php','data','top=40,left=50,width=800,height=600');">แก้ไขรูปประจำตัว</a></center></p>
					</div>
                    <div class="col-sm-8">
                   <br>
				<table class="table">
                <tr><td>
							 <label for="name">Username :</label>
                             </td><td>
							 <?=$mname;?>
				</td></tr>
				<tr><td>
							 <label for="name">Password :</label>
                             </td><td>
							 *******
				</td></tr>
                <tr><td>
							 <label for="name">ชื่อ - สกุล :</label>
                             </td><td>
							 <?=$musername;?>
				</td></tr>
                <tr><td>
							 <label for="name">ตำแหน่ง :</label>
                             </td><td>
							<?=$mposition;?>
				</td></tr>
                <tr><td>
							 <label for="name">เลขที่บัตรประชาชน :</label>
                             </td><td>
							<?=$midcard;?>
				</td></tr>
                <tr><td>
							 <label for="name">สถานที่ทำงาน :</label>
                             </td><td>
							<?=$hospname;?>
				</td></tr><tr><td></td><td></td></tr>
                </table>
                </div>
                </div>
      </div>
      <div class="tab-pane fade" id="profile">
<form>
  <table class="table" cellspacing=5 cellpadding=0 width=450>
    <tr>
      <td align="center" colspan="2"></td>
    </tr>
    <tr>
      <td align="right">Username:</td>
      <td><input name="username" class="form-control" type="text" id="username" value="<?=$mname;?>" disabled> <span id="msg1"></span></td>
    </tr>        
    <tr>
      <td align="right">Password:</td>
      <td><input name="password" class="form-control" placeholder="password" type="password" id="password" size="25" value="<?=$mpassword?>" required></td>
    </tr> 
    <tr>  
    <tr>
      <td align="right">คำนำหน้าชื่อ:</td>
      <td>
      <?php
    $txt = "";
	$txt .= "<select name='prename' class='form-control' id='prename'><option value='$mtitle'>$mtitlename</option>";
	$sql = "SELECT * from ctitle";
	$result=mysql_query($sql,$link);
	while($row=mysql_fetch_array($result)) {
		$txt .= "<option value='$row[titlecode]'>$row[titlename]</option>";
	}	  
	$txt .= "</select>";
	echo $txt;
	?>
    </td>
    </tr>
    <tr>
      <td align="right">ชื่อ :</td>
      <td>
      <input name="fname" class="form-control" type="text" id="fname" size="25" value="<?=$mfname;?>" required></td>
    </tr>
    <tr>
      <td align="right">สกุล:</td>
      <td>
      <input name="lname" class="form-control" type="text" id="lname" size="25" value="<?=$mlname;?>" required></td>
    </tr>
    <tr><td align="right" valign="top">ตำแหน่ง:</td>
    <td>
	<input name="level" class="form-control" placeholder="ตำแหน่ง" type="text" id="level" size="25" value="<?=$mposition;?>" required>
    </td></tr>    
    <tr>
      <td align="right">เลขที่บัตรประชาชน :</td>
      <td><input type="text" placeholder="เลขที่บัตรประชาชน" class="form-control" name="idcard" id="idcard" size="13" value="<?=$midcard;?>" required></td>
    </tr>
    <tr><td align="right" valign="top">สถานที่ทำงาน:</td>
    <td><input name="txtlevel" class="form-control" type="text" id="txtlevel" size="25" value="<?=$hospname;?>" disabled>
    </td></tr>
    
    <tr>
      <td>&nbsp;</td>
      <td><table><tr><td><button type="button" name="btnSubmit" id="btnSubmit" class="btn btn-success">ปรับปรุงข้อมูล</button></td><td><div id="result"></div></td></tr></table></td>
    </tr>
  </table>
</form>
      </div>
    </div>
  					</div><!-- /example-tabs -->
				</div><!-- /.box-body -->
			</div><!-- /.box -->
        </div><!-- /.col-md -->
	</div><!-- /.row -->
</section>

<script src="../js/jquery-2.0.2.min.js"></script>
<script>
$.fn.enterKey = function (fnc) {
    return this.each(function () {
        $(this).keypress(function (ev) {
            var keycode = (ev.keyCode ? ev.keyCode : ev.which);
            if (keycode == '13') {
                fnc.call(this, ev);
            }
        })
    })
}
$(document).ready(function(){
	$("#btnSubmit").click(function(){
		$("#result").html("<img src='../img/ajax-loader.gif' alt='Loading...'/>");
			$.get("../main/execute.php", { 
			action: "updateuserinfo", 
			password: $("#password").val(), 
			prename: $("#prename").val(),
			fname: $("#fname").val(),
			lname: $("#lname").val(),
			level: $("#level").val(),
			idcard: $("#idcard").val(),}, 
				function(result){
					$("#result").html(result);
				}
			);

		});
	});

</script>

<!-- Modal -->
<div class="modal fade" id="myModalavatar" tabindex="-1" role="dialog" aria-labelledby="myModalLabelavatar" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalavatar">รูปประจำตัว</h4>
      </div>
      <div class="modal-body">
        <center><img src="<?=$mavatar?>" alt="" class="img-rounded" width="80%" height="80%"></center>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" onclick="javascript:window.open('../main/frm_avatar.php','data','top=40,left=50,width=800,height=600');">แก้ไขรูปประจำตัว</button>
      </div>
    </div>
  </div>
</div>

