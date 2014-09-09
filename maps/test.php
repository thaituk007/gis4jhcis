<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="ico/favicon.ico">
    <title><?php echo $titleweb; ?></title>
    <!-- Bootstrap core CSS -->
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom styles for this template -->
   	<link href="../css/style.css" rel="stylesheet">
	<link href="../css/datepicker.css" rel="stylesheet">
    <link href="../css/datepicker3.css" rel="stylesheet">
    <!-- Just for debugging purposes. Don't actually copy this line! -->
    <!--[if lt IE 9]><script src="../../assets/js/ie8-responsive-file-warning.js"></script><![endif]-->

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>

  <body>
  <div class="container"><!-- Main component for a primary marketing message or call to action -->
   <div class="row">
   <input name='strdate' class='form-control' type='text' id='datepicker-th1' onkeypress='date_fbb(this);' value='$daystart'/>
    </div><!-- /row -->
</div> <!-- /container -->

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="../js/jquery-2.0.2.min.js"></script>
    <script src="../js/bootstrap.min.js"></script>
    <script src="../js/bootstrap-datepicker.js"></script>
    <script src="../js/bootstrap-datepicker.th.js"></script>
    <script>
	$(function(){
		  $("#datepicker-th1").datepicker({ format: "yyyy-mm-dd", language: "th", autoclose: true, todayHighlight: true, defaultDate: '<?php echo $daystart; ?>'});
		  $("#datepicker-th2").datepicker({ format: "yyyy-mm-dd", language: "th", autoclose: true, todayHighlight: true, defaultDate: '<?php echo $dayend; ?>'});
		});
	</script>