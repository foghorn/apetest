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

if ($_GET['ilid'] == '')
{
	?><h2 class="page-header">Ignore List</h2><?php

	//Handle adding new entry
	if (($_POST['csrf'] == $_SESSION['CSRFTOKEN']) AND ($_POST['update'] != 1) AND ($_POST['delete'] != 1))
	{
        $epid = inputSanitize($_POST['epid']);
        $checkname = inputSanitize($_POST['cname']);
        $checkval = inputSanitize($_POST['val']);
        
        $stmt = $dbConnection->prepare("INSERT INTO ignorelist (epid,checkname,checkval,added) VALUES (:epid,:checkname,:checkval,CURRENT_TIMESTAMP())");
        $stmt->execute([ 'epid' => $epid, 'checkname' => $checkname, 'checkval' => $checkval ]);

        echo "Added new ignore list entry!<br>";
	}
    elseif (($_POST['csrf'] == $_SESSION['CSRFTOKEN']) AND ($_POST['update'] == 1))
    {
        $ilid = inputSanitize($_POST['ilid']);
        $epid = inputSanitize($_POST['epid']);
        $checkname = inputSanitize($_POST['cname']);
        $checkval = inputSanitize($_POST['val']);
        
        $stmt = $dbConnection->prepare("UPDATE ignorelist SET checkname = :checkname , checkval = :checkval , epid = :epid WHERE ilid = :ilid");
        $stmt->execute([ 'checkname' => $checkname, 'checkval' => $checkval , 'epid' => $epid, 'ilid' => $ilid ]);

        echo "Updated ignore list entry!<br>";
    }
    elseif (($_POST['csrf'] == $_SESSION['CSRFTOKEN']) AND ($_POST['delete'] == 1))
    {
        $ilid = inputSanitize($_POST['ilid']);
        
        $stmt = $dbConnection->prepare("DELETE FROM ignorelist WHERE ilid = :ilid");
        $stmt->execute([ 'ilid' => $ilid ]);

        echo "Deleted ignore list entry!<br>";
    }

    ?>
    <h2>Ignore List</h2>
    <table border=1>
        <tr>
            <td>ID</td>
            <td>Endpoint</td>
            <td>Check Name</td>
            <td>Value (MD5)</td>
            <td>Date Added</td>
            <td>Edit</td>
        </tr>

        
    
    <?php

    $stmt = $dbConnection->query("SELECT * FROM ignorelist")->fetchAll();

    foreach ($stmt as $row) 
	{
        ?>
        <tr>
            <td><?php echo $row['ilid']; ?></td>
            <td><?php echo endpointdecode($dbConnection,$row['epid']); ?></td>
            <td><?php echo $row['checkname']; ?></td>
            <td><?php echo $row['checkval']; ?></td>
            <td><?php echo $row['added']; ?></td>
            <td><a href=<?php echo "'ilist.php?ilid=" . $row['ilid'] . "'" ?>>Edit</a></td>
        </tr>
        <?php
    }

    ?>
    </table>
    <?php

	
	
	
}
elseif ($_GET['ilid'] >= 0)
{
    $ilid = inputSanitize($_GET['ilid']);
    
    $stmt = $dbConnection->prepare('SELECT * FROM ignorelist  WHERE ilid = :ignoreid');
	$stmt->execute([ 'ignoreid' => $ilid ]);
	$row = $stmt->fetch();

    ?>
    <h2>Edit Ignore List Entry</h2>
    <a href="ilist.php"><< Go Back</a><br><br>

    <form action='ilist.php' method='post'>
    <input type='hidden' id='csrf' name='csrf' value=<?php echo '"' . $_SESSION['CSRFTOKEN'] . '"'; ?>>
    <input type='hidden' id='ilid' name='ilid' value=<?php echo '"' . $row['ilid'] . '"'; ?>>
    <input type='hidden' id='update' name='update' value='1'>
    <table border=1>
        <tr>
            <td>ID</td>
            <td><?php echo $row['ilid']; ?></td>
        </tr>
        <tr>
            <td>Endpoint</td>
            <td> 
            <?php 
            
            if ($row['epid'] == '*')
            {
                echo "Any";
                ?><input type='hidden' id='epid' name='epid' value=<?php echo '"' . $row['epid'] . '"'; ?>><?php
            }
            else
            {
                echo '<select name="epid" id="epid">';
                echo '<option value="' . $row['epid'] . '" selected>' . endpointdecode($dbConnection,$row['epid']) . '</option>';
                echo '<option value="*">Any</option>';
                echo '</select>';
            }
            ?></td>
        </tr>
        <tr>
            <td>Check Name</td>
            <td>
            <?php 
            
            if ($row['checkname'] == '*')
            {
                echo "Any";
                ?><input type='hidden' id='cname' name='cname' value=<?php echo '"' . $row['checkname'] . '"'; ?>><?php
            }
            else
            {
                echo '<select name="cname" id="cname">';
                echo '<option value="' . $row['checkname'] . '" selected>' . $row['checkname'] . '</option>';
                echo '<option value="*">Any</option>';
                echo '</select>';
            }
            ?></td>
        </tr>
        <tr>
            <td>Value (MD5)</td>
            <td>
            <?php 
            
            if ($row['checkval'] == '*')
            {
                echo "Any";
                ?><input type='hidden' id='val' name='val' value=<?php echo '"' . $row['checkval'] . '"'; ?>><?php
            }
            else
            {
                echo '<select name="val" id="val">';
                echo '<option value="' . $row['checkval'] . '" selected>' . $row['checkval'] . '</option>';
                echo '<option value="*">Any</option>';
                echo '</select>';
            }
            ?></td>
        </tr>

    </table>
    <input type='submit' value='Update'>
	</form>

    <h2>Delete Entry</h2>
    WARNING: This action cannot be undone.<br>
    <form action='ilist.php' method='post'>
    <input type='hidden' id='csrf' name='csrf' value=<?php echo '"' . $_SESSION['CSRFTOKEN'] . '"'; ?>>
    <input type='hidden' id='ilid' name='ilid' value=<?php echo '"' . $row['ilid'] . '"'; ?>>
    <input type='hidden' id='delete' name='delete' value='1'>
    <input type='submit' value='Delete'>
        </form>

    
    <?php
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