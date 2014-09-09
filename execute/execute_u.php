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
</script>
<body>
<center>
<?php
$txt = "<form id='frm_old' name='frm_old' method='get' action='insert_diag_u.php'>";  
$txt .= "<table><tr><td>เลื่อกช่วงวันที่ของข้อมูล <input name='date_start' type='text' size='8' id='datepicker-th1' onkeypress='date_fbb(this);' onchange='getData();' value='$daystart'/>-
      <input name='date_end' type='text' size='8' id='datepicker-th2' onkeypress='date_fbb(this);' onchange='getData();' value='$dayend'/>
	  </td></tr><tr><td width='33%'><input type='submit' name='Submit' value='ตกลง รัน sql นี้' /></td></tr></table></form>";
echo $txt;
echo "ชื่อยาสมุนไพรต้องมีคำอย่างน้อยดังต่อไปนี้    &quot;ขมิ้น&quot;  	เพื่อเพิ่มรหัสวินิจฉัยเป็น  U66.70<br />
ชื่อยาสมุนไพรต้องมีคำอย่างน้อยดังต่อไปนี้    &quot;พยายอ&quot;  	เพื่อเพิ่มรหัสวินิจฉัยเป็น  U65.7<br />
ชื่อยาสมุนไพรต้องมีคำอย่างน้อยดังต่อไปนี้    &quot;ฟ้าทะลายโจร&quot; 	เพื่อเพิ่มรหัสวินิจฉัยเป็น  U65.30<br />
ชื่อยาสมุนไพรต้องมีคำอย่างน้อยดังต่อไปนี้    &quot;มะแว้ง&quot; 	เพื่อเพิ่มรหัสวินิจฉัยเป็น  U64.3<br />
ชื่อยาสมุนไพรต้องมีคำอย่างน้อยดังต่อไปนี้    &quot;รางจืด&quot; 	เพื่อเพิ่มรหัสวินิจฉัยเป็น  U75.6<br />
ชื่อยาสมุนไพรต้องมีคำอย่างน้อยดังต่อไปนี้    &quot;ประสะไพร&quot; 	เพื่อเพิ่มรหัสวินิจฉัยเป็น  U50.8<br />
ชื่อยาสมุนไพรต้องมีคำอย่างน้อยดังต่อไปนี้    &quot;เพชรสังฆาต&quot; 	เพื่อเพิ่มรหัสวินิจฉัยเป็น  U68.0<br />
ชื่อยาสมุนไพรต้องมีคำอย่างน้อยดังต่อไปนี้    &quot;เถาวัลย์เปรียง&quot; 	เพื่อเพิ่มรหัสวินิจฉัยเป็น  U75.05<br />
ชื่อยาสมุนไพรต้องมีคำอย่างน้อยดังต่อไปนี้    &quot;ไพลจีซาล&quot; 	เพื่อเพิ่มรหัสวินิจฉัยเป็น  U75.08
<br><br>";
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