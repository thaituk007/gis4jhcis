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
 document.frm_old.date_end.focus();
}
</script>
<body  onload="cursor();">

<?php
$pname = getPersonName($_GET[pid]);
$txt = "<br><form id='frm_old' name='frm_old' method='get' action='insert_ancdeliver.php'>";  
$txt .= "<table><tr><td>บันทึกวันที่คลอดของ $pname   <input name='date_end' type='text' size='8' id='datepicker-th2' onkeypress='date_fbb(this);' onchange='getData();' value='$dayend'/>
	  </td><td><input name='pid' type='hidden' id='pid' value='$_GET[pid]' /></td><td><input name='pregno' type='hidden' id='pregno' value='$_GET[pregno]' /></td><td><input type='submit' name='Submit' value='บันทึก' /></td></tr></table></form>";
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