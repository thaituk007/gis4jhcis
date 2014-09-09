<?php 
session_start();
if($_SESSION[username]){
include("includes/conndb.php"); 
include("includes/config.inc.php"); 
$dm = date("d/m");
$d = date("d");
$m = date("m");
$yx = date("Y");
$y = date("Y") + 543;
$daysdatestr = $d."/".$m."/".$y;
$daystart = "01/".$m."/".$y;
$daylast = $yx."/".$m;
$daylast2 = lastday($daylast);
$dayend = retDatets($daylast2);
$daysdatepick = $dm."/".$y;
$daledcy = date("Y");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $titleweb; ?></title>
<link href="css/style.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="js/functions.js"></script>
<script type="text/javascript" src="js/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="js/jquery-ui-1.8.1.offset.datepicker.min.js"></script>
<link type="text/css" href="css/ui-lightness/jquery-ui-1.8.1.custom.css" rel="stylesheet" />
<script type="text/javascript">
$(function(){
		  $("#datepicker-th1").datepicker({ dateFormat: 'dd/mm/yy', yearOffset: 543, defaultDate: '<?php echo $daysdatepick; ?>'});
		  $("#datepicker-th2").datepicker({ dateFormat: 'dd/mm/yy', yearOffset: 543, defaultDate: '<?php echo $daysdatepick; ?>'});
		});
function cursor(){
 document.frm_old.lot.focus();
}
function getData(pid,pregno){
			var url = "execute_ancdeliver2.php?pid="+pid+"&pregno="+pregno;
				window.open(url,'data','top=120,left=250,width=750,height=300');
}
</script>
<body  onload="cursor();">
<?php
$pid = $_GET[pid];
$sql = "SELECT pcucodeperson,pid,pregno,zero2null(lmp) as lmp,zero2null(edc) as edc,firstabnormal FROM visitancpregnancy where visitancpregnancy.pid = $pid";


$result = mysql_query($sql);
$txt = "<form id='frm_get' name='frm_get' method='get' action=''><p align=\'center\'><b>บันทึกการคลอด</b></p>";
$txt .= "<table width='99%' border='0' cellspacing='1' cellpadding='1' class='tbhl'>
  <tr>
    <th width='2%' scope='col'>ลำดับ</th>
	<th width='3%' scope='col'>HN</th>
    <th width='10%' scope='col'>ชื่อ - สกุล</th>
    <th width='5%' scope='col'>ครรภ์ที่</th>
    <th width='2%' scope='col'>LMP</th>
	<th width='7%' scope='col'>EDC</th>
	<th width='15%' scope='col'>คลอด</th>
  </tr>";
while($row=mysql_fetch_array($result)) {
	$pid = $row[pid];
	$cid = $row[cid];
	$pname = getPersonName($row[pid]);
	if($row[lmp] == ""){$lmp = '';}else{$lmp = retDatets($row[lmp]);}
	if($row[edc] == ""){$edc = '';}else{$edc = retDatets($row[edc]);}
++$i;
	if(($i%2) == 1){$cr = " class='altrow'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$i</div></td>
	<td><div align='right'>&nbsp;$row[pid]</div></td>
    <td><div align='left'>&nbsp;$pname</div></td>
    <td><div align='right'>&nbsp;$row[pregno]</div></td>
	<td><div align='center'>&nbsp;$lmp</div></td>
	<td><div align='center'>&nbsp;$edc</div></td>
	<td><div align='center'><input type='button' value='คลอด' onclick=getData(".$row[pid].",".$row[pregno].");></div></td>
  </tr>";
}
$txt .= "</table></form>";  
echo $txt;
?>
<?php
}
else{
		header("Location: login.php");
		}
		?>
</body>
</html>