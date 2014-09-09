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
function getData(pid,id,date_start){
				var labresulttext = document.getElementById('labresulttext').value;
				var url = "insert_visit_ultra.php?pid="+pid+"&ov="+id+"&date_start="+date_start+"&labresulttext="+labresulttext;
				window.open(url,'data','top=120,left=250,width=500,height=300');
}
function cursor(){
 document.labresulttext.focus();
}
</script>
<body>
<center>
<?php
if($_GET[ov] == 1){
$pid = $_GET[pid];
$txt = "<br>";
$txt .= "ผลการตรวจข้อความ<label for='labresulttext'></label>
  <input type='text' name='labresulttext' id='labresulttext' />";
$txt .="<input type='button' value='ตกลง' onclick='getData(".$pid.",1,".$_GET[date_start].");'>";
echo $txt;
}
?>
</center>
<?php
}
else{
		header("Location: login.php");
		}
		?>
</body>
</html>