<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Untitled Document</title>
</head>

<body>
<script src="../js/jquery.1.11.0.min.js"></script>
<script>
    var oldVersion = jQuery.noConflict();
</script>
<script src="../js/jquery-2.0.2.min.js"></script>
<script>
    var newVersion = jQuery.noConflict();
</script>

<div id="testDiv"></div>

<script>
     alert( oldVersion('#testDiv') );
     alert( newVersion('#testDiv') );
</script>
</body>
</html>