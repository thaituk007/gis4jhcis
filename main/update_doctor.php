            <section class="content-header">
                    <h1>
                        <?php echo $offname?>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                        <li class="active">updatedoctor</li>
                    </ol>
                </section>

                <!-- Main content -->
                <section class="content">
                <div class="row">
                        <div class="col-md-12">
                            <div class="box box-primary">
                                <div class="box-header">
                                    <i class="fa fa-edit"></i>
                                    <h3 class="box-title">กำหนดหมู่บ้านรับผิดชอบ</h3>
                                </div>
                                <div class="box-body pad table-responsive">

<?php
$txt .= "<div align = 'center'><br/><table><tr><td>เจ้าหน้าที่</td><td><select name='username' id='username' class='form-control'><option value=''>เลือก นสค.</option>";
	$sql = "select `user`.username, concat(c.titlename,user.fname,'  ',user.lname) as pname FROM `user` Inner Join ctitle c ON `user`.prename = c.titlecode WHERE `user`.markdelete IS NULL";
	$result=mysql_query($sql,$link);
	while($row=mysql_fetch_array($result)) {
		$txt .= "<option value='$row[username]'>$row[pname]</option>";
	}
	$txt .= "</select></td>";

$txt .= "<td>รับผิดชอบหมู่บ้าน :</td><td><select name='villcode' id='villcode' class='form-control'><option value=''>เลือกหมู่บ้าน</option>";
$sql = "SELECT villcode,villno,villname FROM village WHERE villno <> '0' ORDER BY villcode";
$result=mysql_query($sql,$link);
while($row=mysql_fetch_array($result)) {
	$txt .= "<option value='$row[villcode]'>$row[villno] $row[villname]</option>";
}	  
$txt .= "</select></td>
			<td></td><td><input type='button' class='btn btn-success' name='btn1' id='btn1' value='บันทึก' onclick='getData();'></td></tr></table></div>";
	$txt .= "<div id='update_doctor'></div>";
	echo $txt;
?>

<br>
<center><input type='button' class='btn btn-success' value='กำหนด นสค. ประชากรนอกเขต เป็นค่าว่าง( typearea = 4)' onclick='setnullnsk();'></center>
                                </div><!-- /.box -->
                            </div>
                        </div><!-- /.col -->
                    </div><!-- ./row -->
				</section>
<script type="text/javascript">
var xmlHttp;

function createXMLHttpRequest() {
    if (window.ActiveXObject) {
    xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
	 } 
	else if (window.XMLHttpRequest) {
 	 xmlHttp = new XMLHttpRequest();
	 }
}
function getData(){
		document.getElementById("update_doctor").innerHTML = "<center><img src='../img/loader3.gif'/></center>";
			var username = document.getElementById('username').value;
			var villcode = document.getElementById('villcode').value;
			tget = "username="+username+"&villcode="+villcode;
			createXMLHttpRequest();
            xmlHttp.open("get", "../execute/execute_updatedoctor.php?" + tget, true);
            xmlHttp.onreadystatechange = function () {
                if (xmlHttp.readyState == 4) {
                    if (xmlHttp.status == 200) {
                        document.getElementById("update_doctor").innerHTML = xmlHttp.responseText;
                    }
                }            
            };
            xmlHttp.send(null);
	}
function setnullnsk(){
		document.getElementById("update_doctor").innerHTML = "<center><img src='../img/loader3.gif'/></center>";
			createXMLHttpRequest();
            xmlHttp.open("get", "../execute/execute_updatedoctor.php?chk=0", true);
            xmlHttp.onreadystatechange = function () {
                if (xmlHttp.readyState == 4) {
                    if (xmlHttp.status == 200) {
                        document.getElementById("update_doctor").innerHTML = xmlHttp.responseText;
                    }
                }            
            };
            xmlHttp.send(null);
	}
</script>
    </body>
    
</html>