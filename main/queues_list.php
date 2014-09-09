<?php
session_start();
set_time_limit(0);
if($_SESSION[username]){
include("../includes/conndb.php"); 
include("../includes/config.inc.php");
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="../ico/favicon.ico">

    <title><?php echo $titleweb; ?></title>

    <!-- Bootstrap core CSS -->
    <link href="../css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="../css/style.css" rel="stylesheet">

    <!-- Just for debugging purposes. Don't actually copy this line! -->
    <!--[if lt IE 9]><script src="../../assets/js/ie8-responsive-file-warning.js"></script><![endif]-->

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

    <script src="../js/bootstrap.min.js"></script>
</head>

<body>
<?php 
$data = $_GET[data];
if($data == ''){$flax = "";}else{$flax = " and visit.flagservice = '$data'";}
$sql = "SELECT person.pcucodeperson,person.pid,person.prename,person.fname,person.lname,person.birth,visit.symptoms,visit.visitno,FLOOR(datediff(now(),person.birth)/365.25) as age FROM person inner join visit on person.pcucodeperson = visit.pcucodeperson and person.pid = visit.pid INNER JOIN cflagservice ON visit.flagservice = cflagservice.servicecode where visit.flagservice not in ('02','03','04','05','06','07','99','-H') and visit.visitdate = CURDATE() $flax";

$result = mysql_query($sql);
$servicename = getservice($data);
$txt = "<div class='box box-solid box-success'>
                                <div class='box-header'>
                                   <h3 class='box-title'>รายชื่อผู้ที่รอคิวบริการวันที่</h3>
                                    <div class='box-tools pull-right'>
                                        <button class='btn btn-success btn-sm' data-widget='collapse'><i class='fa fa-minus'></i></button>
                                        <button class='btn btn-success btn-sm' data-widget='remove'><i class='fa fa-times'></i></button>
                                    </div>
                                </div>
                                <div class='box-body'>
                                    <center><b>คิวรอรับบริการที่จุด :
							";
$txt .= "$servicename</b></center><div class='table-responsive'><table class='table table-hover' width='100%' border='0' cellspacing='1' cellpadding='1'>
  <tr class='active'>
    <th width='3%' scope='col'><div align='center'>ที่</div></th>
    <th width='5%' scope='col'><div align='center'>ลำดับที่</div></th>
	<th width='5%' scope='col'><div align='center'>HN</div></th>
	<th width='6%' scope='col'><div align='center'>คำนำหน้า</div></th>
    <th width='10%' scope='col'><div align='center'>ชื่อ</div></th>
    <th width='10%' scope='col'><div align='center'>สกุล</div></th>
	<th width='9%' scope='col'><div align='center'>วันเกิด</div></th>
	<th width='5%' scope='col'><div align='center'>อายุ</div></th>
	<th width='20%' scope='col'><div align='center'>อาการเบื้อต้น</div></th>
	<th width='10%' scope='col'><div align='center'>เรียกคิว</div></th>
	<th width='5%' scope='col'><div align='center'></div></th>
  </tr>";
while($row=mysql_fetch_array($result)) {
	$flname = $row[fname]."  ".$row[lname];
	$prename = getTitle($row[prename]);
	$birth = retDatets($row[birth]);
++$i;
	if(($i%2) == 1){$cr = " class='info'";}else{$cr = "";}
$txt .="  <tr $cr>
    <td><div align='center'>$i</div></td>
    <td><div align='center'>&nbsp;$row[visitno]</div></td>
	<td><div align='center'>&nbsp;$row[pid]</div></td>
	<td><div align='center'>&nbsp;$prename</div></td>
    <td><div align='center'>&nbsp;$row[fname]</div></td>
    <td><div align='center'>&nbsp;$row[lname]</div></td>
    <td><div align='center'>&nbsp;$birth</div></td>
	<td><div align='center'>&nbsp;$row[age]</div></td>
	<td><div align='center'>&nbsp;$row[symptoms]</div></td>
	<td><div align='center'>&nbsp;<a href='#' onclick=getsound(\"'$flname','$servicename'\");><i id='btn1' name='btn1' class='fa fa-volume-up'>zz</i></a></div></td>
	<input name='flname' type='hidden' id='flname' value='$flname' /><input name='servicename' type='hidden' id='servicename' value='$servicename' />
	<td><div align='center'></div></td>
  </tr>";
}
$txt .= "</table><p div align='right' class='text-danger'>ข้อมูลระหว่างวันที่  $strx ถึง $stox </p></div><br></div><!-- /.box-body -->
                            </div><!-- /.box -->";  
echo $txt;
?>

<?php
}
else{
		header("Location: index.php");
		}
		?>
        
</body>
</html>