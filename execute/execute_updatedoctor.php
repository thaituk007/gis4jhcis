<?php
session_start();
include("../includes/conndb.php"); 
include("../includes/config.inc.php");
if($_GET[villcode]){
	$villcode = $_GET[villcode];
	$user = $_GET[username];
	$userperson = getusername($user);
	$sql = "UPDATE house SET house.usernamedoc = '$user' WHERE house.villcode ='$villcode'";
	$result=mysql_query($sql);
	$sql = "UPDATE house Inner Join person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode SET person.privatedoc = '$userperson' WHERE house.villcode ='$villcode'";
	$result=mysql_query($sql);
		echo "<div align='center' class='text text-success'>กำหนดหมู่บ้านรับผิดชอบเรียบร้อยแล้ว</div>";
$sql = "SELECT
CONCAT(convert(village.villno using utf8),' ',`village`.`villname`) AS address,
concat(ctitle.titlename,`user`.fname,'  ',`user`.lname) as pname
FROM
house
Inner Join `user` ON house.pcucode = `user`.pcucode AND house.usernamedoc = `user`.username
Inner Join village ON village.villcode = house.villcode AND village.pcucode = house.pcucode
Inner Join ctitle ON `user`.prename = ctitle.titlecode
group by address
order by villno
";

$result = mysql_query($sql);
$txt = '';
$txt .= "<p align='center'><br><b><center>แสดงรายชื่อเจ้าหน้าที่และหมู่บ้านรับผิดชอบ</b></p></center>";
$txt .= "<center><table class='table table-striped'>
  <tr>
    <th width='8%' scope='col'>ลำดับ</th>
    <th width='25%' scope='col'>ชื่อหมู่บ้าน</th>
    <th width='25%' scope='col'>เจ้าหน้าที่ผู้รับผิดชอบ</th>
    <th width='15%' scope='col'>หมายเหตุ</th>
  </tr>";
while($row=mysql_fetch_array($result)) {
++$i;
	if(($i%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$i</div></td>
    <td>&nbsp;$row[address]</td>
    <td>&nbsp;$row[pname]</td>
    <td>&nbsp;</td>
  </tr>";
}
$txt .= "</table></center>";  
echo $txt;
}elseif($_GET[chk] == 0){
	$markxx = $_GET[chk];
	$sql = "UPDATE house SET house.usernamedoc = null where right(house.villcode,2) = '00'";
	$result=mysql_query($sql);
	$sql = "UPDATE house Inner Join person ON house.pcucode = person.pcucodeperson AND house.hcode = person.hcode 
SET person.privatedoc = null WHERE person.typelive = '4'";
	$result=mysql_query($sql);
	echo "<div align='center' class='text text-success'>กำหนดหมู่บ้านรับผิดชอบเรียบร้อยแล้ว</div>";
$sql = "SELECT
CONCAT(convert(village.villno using utf8),' ',`village`.`villname`) AS address,
concat(ctitle.titlename,`user`.fname,'  ',`user`.lname) as pname
FROM
house
Inner Join `user` ON house.pcucode = `user`.pcucode AND house.usernamedoc = `user`.username
Inner Join village ON village.villcode = house.villcode AND village.pcucode = house.pcucode
Inner Join ctitle ON `user`.prename = ctitle.titlecode
group by address
order by villno
";

$result = mysql_query($sql);
$txt = '';
$txt .= "<p align='center'><br><b><center>แสดงรายชื่อเจ้าหน้าที่และหมู่บ้านรับผิดชอบ</b></p></center>";
$txt .= "<center><table class='table table-striped'>
  <tr>
    <th width='8%' scope='col'>ลำดับ</th>
    <th width='25%' scope='col'>ชื่อหมู่บ้าน</th>
    <th width='25%' scope='col'>เจ้าหน้าที่ผู้รับผิดชอบ</th>
    <th width='15%' scope='col'>หมายเหตุ</th>
  </tr>";
while($row=mysql_fetch_array($result)) {
++$i;
	if(($i%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$i</div></td>
    <td>&nbsp;$row[address]</td>
    <td>&nbsp;$row[pname]</td>
    <td>&nbsp;</td>
  </tr>";
}
$txt .= "</table></center>";  
echo $txt;
}else{
$sql = "SELECT
CONCAT(convert(village.villno using utf8),' ',`village`.`villname`) AS address,
concat(ctitle.titlename,`user`.fname,'  ',`user`.lname) as pname
FROM
house
Inner Join `user` ON house.pcucode = `user`.pcucode AND house.usernamedoc = `user`.username
Inner Join village ON village.villcode = house.villcode AND village.pcucode = house.pcucode
Inner Join ctitle ON `user`.prename = ctitle.titlecode
group by address
order by villno
";

$result = mysql_query($sql);
$txt = '';
$txt .= "<p align='center'><br><b><center>แสดงรายชื่อเจ้าหน้าที่และหมู่บ้านรับผิดชอบ</b></p></center>";
$txt .= "<center><table  class='table table-striped table-hover'>
  <tr>
    <th width='8%' scope='col'>ลำดับ</th>
    <th width='25%' scope='col'>ชื่อหมู่บ้าน</th>
    <th width='25%' scope='col'>เจ้าหน้าที่ผู้รับผิดชอบ</th>
    <th width='15%' scope='col'>หมายเหตุ</th>
  </tr>";
while($row=mysql_fetch_array($result)) {
++$i;
	if(($i%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$i</div></td>
    <td>&nbsp;$row[address]</td>
    <td>&nbsp;$row[pname]</td>
    <td>&nbsp;</td>
  </tr>";
}
$txt .= "</table></center>";  
echo $txt;
}
?>