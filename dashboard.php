<?php

session_start();
require_once 'dbconn.php';
require_once 'functions.php';

//Check session
SessionCheck();

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="">
<meta name="author" content="">
<link rel="icon" href="favicon.ico">
<title>Watchtower - Gumshoe Dashboard</title>

<!-- jQuery -->
<script src="vendor/jquery/jquery.min.js"></script>

<!-- Bootstrap Core JavaScript -->
<script src="vendor/bootstrap/js/bootstrap.min.js"></script>


<!-- chartist.js -->
<script src="chartist/dist/chartist.min.js"></script>

<!-- Bootstrap Core CSS -->
<link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

<!-- Custom CSS -->
<link href="css/dashboard.css" rel="stylesheet">
<link href="css/structure.css" rel="stylesheet">

<!-- Chartist Charts CSS -->
<link rel="stylesheet" href="chartist/dist/chartist.min.css">

<!-- Custom Fonts -->
<link href="vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="https://www.google.com/jsapi"></script>


<style>
.chart {
  width: 100%;
  min-height: 450px;
}
/* Style the buttons that are used to open and close the accordion panel */
.accordion {
  background-color: #eee;
  color: #444;
  cursor: pointer;
  padding: 18px;
  width: 100%;
  text-align: left;
  border: none;
  outline: none;
  transition: 0.4s;
}

/* Add a background color to the button if it is clicked on (add the .active class with JS), and when you move the mouse over it (hover) */
.active, .accordion:hover {
  background-color: #ccc;
}

/* Style the accordion panel. Note: hidden by default */
.panel {
  padding: 0 18px;
  background-color: white;
  display: none;
  overflow: hidden;
}
.coolbutton {
    background-color: #eee;
    border: none;
    color: black;
    padding: 5px 5px;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    font-size: 16px;
    margin: 4px 2px;
    cursor: pointer;
}

.nicetable {
	border-collapse: collapse;
	border: 3px solid white;
	background-color: #eee;
}

#pretty td, #customers th {
    border: 1px solid #ddd;
    padding: 8px;
}

#pretty tr:nth-child(even){background-color: #f2f2f2;}

#pretty tr:hover {background-color: #ddd;}

#pretty th {
    padding-top: 12px;
    padding-bottom: 12px;
    text-align: left;
    background-color: #4CAF50;
    color: white;
}
</style>
</head>

<body>
<div id="wrapper">

  <!-- Navigation -->
  <?php  navHeader(); ?>
  <div class="container">
   <div id="page-wrapper">
   <div class="row">
   <div class="col-md-12 col-lg-12">
	<!-- START BODY-->

<?php

if ($_GET['page'] == '')
{
	?><h2 class="page-header">Scan Reports</h2><?php
	//Handle adding new endpoint
	if ($_POST['csrf'] == $_SESSION['CSRFTOKEN'])
	{
		$return = endpoint_check($dbConnection,$_POST['endpoint'],$_POST['scan']);

		if ($return != '')
			echo "Endpoint ID " . $return . " added.<br><br>";
	}

	//Setup for new endpoint
	$_SESSION['CSRFTOKEN'] = md5(time());
	?>
	<button class="accordion">Add a New Endpoint</button>
	<div class="panel">
			<p>
	<form action='dashboard.php' method='post'>
	<table border=0>
	<tr>
	<td><label for="endpoint">Endpoint address (domain or IP):</label></td><td><input type="text" id="endpoint" name="endpoint"></td>
	</tr><tr>
	<td>Scan for subdomains? </td>
	<td><input type="radio" id="scan" name="scan" value="1"><label for="1"> Yes</label><br><input type="radio" id="scan" name="scan" value="0" checked="checked"><label for="0"> No</label></td>
	</tr>
	</table>
	<input type="hidden" id="csrf" name="csrf" value=<?php echo '"' . $_SESSION['CSRFTOKEN'] . '"'; ?>>
	<input type="submit" value="Submit">
	</form><br>
	</p>
	</div>

	<?php

	//== Display endpoints and latest scan ==

	//Check and see if anything has an active alarm
	$stmt = $dbConnection->query("SELECT DISTINCT epid FROM endpoints WHERE epenabled = 1")->fetchAll();
	foreach ($stmt as $row) 
	{
		activealarms($dbConnection,0,$row['epid']);
	}

	?><h2>Active Endpoints</h2><?php
	
	?><h4>Endpoints with Alarms</h4><?php
	dashboardaccordion($dbConnection,"SELECT * FROM endpoints WHERE epenabled = 1 AND activealarm = 1");

	?><h4>Endpoints In Compliance</h4><?php
	dashboardaccordion($dbConnection,"SELECT * FROM endpoints WHERE epenabled = 1 AND activealarm = 0");

	?><h3>Inactive Endpoints</h3><?php
	dashboardaccordion($dbConnection,"SELECT * FROM endpoints WHERE epenabled = 0");
	
	
}

?>
	<!-- END BODY-->
	</div>
   </div>
  </div>
  </div>
</div>
<!--<div style="position: static; bottom: 0px; width: 100%; height: 50px; border-color: #243B5C; background: #243B5C;" ></div>-->
</div>

<div style="height:75px; width: 100%; "></div>
<script>
		var acc = document.getElementsByClassName("accordion");
	var i;

	for (i = 0; i < acc.length; i++) {
	acc[i].addEventListener("click", function() {
		/* Toggle between adding and removing the "active" class,
		to highlight the button that controls the panel */
		this.classList.toggle("active");

		/* Toggle between hiding and showing the active panel */
		var panel = this.nextElementSibling;
		if (panel.style.display === "block") {
		panel.style.display = "none";
		} else {
		panel.style.display = "block";
		}
	});
	}
	</script>
</body>