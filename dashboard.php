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

	//== Display endpoints and latest scan ==

	//Get endpoints
	$stmt = $dbConnection->query("SELECT * FROM endpoints WHERE epenabled = 1")->fetchAll();

	foreach ($stmt as $row) 
	{
		if ($row['domain'] != '')
			$endpointname = $row['domain'];
		elseif ($row['ipaddress'] != '')
			$endpointname = $row['ipaddress'];
		else
			$endpointname = $row['epid'];
		
		?>
		<button class="accordion"><?php echo $endpointname; ?></button>
		<div class="panel">
			<!--<p>-->
				<table border=1>
					<tr>
						<td>Endpoint ID</td><td><?php echo $row['epid']; ?></td>
						<td>Domain</td><td><?php echo $row['domain']; ?></td>
						<td>IP Address</td><td><?php echo $row['ipaddress']; ?></td>
						<td>Added</td><td><?php echo $row['added']; ?></td>
						<td>Last Scanned</td><td><?php echo $row['lastcheck']; ?></td>
						<td>Root Domain?</td><td><?php echo $row['rootdomain']; ?></td>
					</tr>
				</table>
				<br><br>
				Last Scan Results:
				<table border=1>
					<tr>
						<td>Test Name and Timestamp</td><td>Output</td><td>Alarm</td>
					</tr>
				<?php
					$stmt2 = $dbConnection->query("SELECT * FROM ep_test_results WHERE epid = :epid ");
					$stmt2->execute([ 'epid' => $row['epid'] ]);

					foreach ($stmt2 as $row2) 
					{
						echo "<tr>";
						
						echo "<td>";
						echo $row2['name'] . "<br>" . $row2['checktime'];
						echo "</td>";

						echo "<td>";
						echo $row2['output'];
						echo "</td>";

						echo "<td>";
						echo $row2['alarm'];
						echo "</td>";

						echo "</tr>";
					}
				?>
				</table>

			<!--</p>-->
		</div>

		<?php
	}
	
	
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
</body>