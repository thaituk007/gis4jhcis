<?php 
include("../includes/conndb.php"); 
include("../includes/config.inc.php");
$txt = '';
	$scmenu = $_GET[scmenu];
	$sql = "SELECT menugis.id, menugis.menugroup, menugis.menuname, menugis.menulink, menugis.detail, menugis.mark FROM menugis where menugis.menuname like '%$scmenu%' limit 5";

$txt .= "";
$result = mysql_query($sql);
while($row=mysql_fetch_array($result)) {
	$txt .= "<li class='treeview'><a href='$row[menulink]'><i class='fa fa-angle-double-right'></i> $row[menuname]</a></li>";
}
$txt .= "";		
echo $txt;
?>
