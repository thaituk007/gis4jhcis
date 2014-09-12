<?php
$host = "localhost:3333";
$user = "root";
$pwd = "123456";
$db = "jhcisdb5";
global $link;
$link = mysql_connect($host,$user,$pwd) or die ("Could not connect to MySQL");
mysql_query("SET NAMES UTF8",$link);
mysql_select_db($db,$link) or die ("Could not select $db database");	
?>