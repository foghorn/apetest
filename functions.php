<?php

function insert_result($dbConnection,$checkid,$epid,$fname,$result,$alarm = 0)
{
    $stmt = $dbConnection->prepare("INSERT INTO ep_test_results (checkid,epid,name,output,alarm,checktime) VALUES (:checkid,:epid,:fname,:result,:alarm,CURRENT_TIMESTAMP())");
    $stmt->execute([ 'checkid' => $checkid, 'epid' => $epid, 'fname' => $fname, 'result' => $result, 'alarm' => $alarm ]);
}

function guidv4($data)
{
    assert(strlen($data) == 16);

    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

function inputSanitize($input)
{
	$input = str_replace("\"","",$input);
	$input = str_replace("'","",$input);
	$input = filter_var($input,FILTER_SANITIZE_STRING);
	$input = ltrim(rtrim($input));
	$input = addslashes($input);
	
	return $input;
}

function passHash($PASS,$username)
{
    $PASS = $PASS . md5($PASS) .$username;
    $PASS = sha1($PASS);
    return $PASS;
}

function redirectHeader($domain = '')
{
	if ($domain == '')
    {
        $domain = $_SERVER['SERVER_NAME'];
    }
    
	if (strtolower($domain) == 'wt.iatu.io')
	{
		header('Location: https://wt.iatu.io/dashboard.php', true, 303);
	}
    elseif (strtolower($domain) == 'nickleghorn.com')
	{
		header('Location: https://nickleghorn.com/dctest/dashboard.php', true, 303);
	}
    elseif( isset($_SERVER['HTTPS'] ) ) 
	{
		header('Location: https://' . $domain . '/dashboard.php', true, 303);
	}
	else
	{
		header('Location: http://' . $domain . '/dashboard.php', true, 303);
	}
}

function SessionCheck($domain = '')
{
    if ($domain == '')
    {
        $domain = $_SERVER['SERVER_NAME'];
    }
    
    if ($_SESSION['PERMIT'] != 1)
    {
        header('Location: https://' . $domain . '/', true, 303);
		die();
    }
    elseif ($_SESSION['LoginTimestamp'] != date("Y-m-d"))
    {
        $_SESSION['PERMIT'] = 0;
		$_SESSION['USER'] = 0;
		$_SESSION['ACCOUNT'] = 0;
		$_SESSION['FIRSTLOGIN'] = 0;
		$_SESSION['EMAIL'] = "";
		$_SESSION['LoginTimestamp'] = 0;
		session_destroy();
        header('Location: https://' . $domain . '/', true, 303);
		die();
    }
}

function navHeader()
{
	?>
	<nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
		<a class="navbar-brand" href="dashboard.php"><img src="images/iatu_white.png" height="40" alt="Watchtower"></a>
	   <ul class="nav navbar-top-links navbar-right">
			<li class="dropdown"> <a class="dropdown-toggle" data-toggle="dropdown" href="#">
			  <div id="dd-profile"></div>
			  <i class="fa fa-caret-down"></i> </a>
			  <ul class="dropdown-menu dropdown-user">
			    <li><a href="dashboard.php">Home</a> </li>
				<li class="divider"></li>
				<!--<li><a href="settings.php">Account Settings</a> </li>
				<li><a href="ticket.php">Support Tickets</a> </li>-->
				<li class="divider"></li>
				<li><a href="index.php?logout=1"> Logout</a> </li>
			  </ul>
			</li>
		  </ul>
	  </nav>
	<?php
}

function announcementDisplay($title,$text,$link)
{
	?>
	 <div class="row">
        <div class="col-md-12 col-lg-12">
          <div class="panel panel-default">
            <div class="panel-heading"> <?php echo $title; ?> </div>
            <div class="panel-divider"></div>
            <div class="panel-body">
			<?php echo nl2br($text); ?>
            </div>
            <div class="panel-footer text-right">
			<?php echo $link; ?>
            </div>
          </div>
        </div>
      </div>
	<?
}

function activealarms($dbConnection,$checkid = 0,$epid = 0)
{
    //If no check ID provided, find the latest one
    if (($checkid == 0) AND ($epid != ''))
    {
        $stmt = $dbConnection->query("SELECT DISTINCT checkid, checktime FROM `ep_test_results` WHERE epid = " . $epid . " ORDER BY checktime LIMIT 1");
        $row = $stmt->fetch();

        $checkid = $row['checkid'];
    }

    $stmt = $dbConnection->query("SELECT MAX(alarm),epid FROM ep_test_results WHERE checkid = '" . $checkid . "'");
    $row = $stmt->fetch();


    $stmt = $dbConnection->prepare('UPDATE endpoints SET activealarm = :alarm WHERE epid = :epid');
    $stmt->execute([ 'epid' => $row['epid'], "alarm" => $row['MAX(alarm)'] ]);
}

function dashboardaccordion($dbConnection,$query)
{
    $stmt = $dbConnection->query($query)->fetchAll();

	foreach ($stmt as $row) 
	{
		if ($row['domain'] != '')
			$endpointname = $row['domain'];
		elseif ($row['ipaddress'] != '')
			$endpointname = $row['ipaddress'];
		else
			$endpointname = $row['epid'];
		
		?>
		<br>
		<button class="accordion"><?php echo $endpointname; ?></button>
		<div class="panel">
			<p>
		<table border=1>
					<tr>
						<td>Endpoint ID</td><td><?php echo $row['epid']; ?></td>
					</tr><tr>
						<td>Domain</td><td><?php echo $row['domain']; ?></td>
					</tr><tr>
						<td>IP Address</td><td><?php echo $row['ipaddress']; ?></td>
					</tr><tr>
						<td>Added</td><td><?php echo $row['added']; ?></td>
					</tr><tr>
						<td>Last Scanned</td><td><?php echo $row['lastcheck']; ?></td>
					</tr><tr>
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
					$stmt2 = $dbConnection->query("SELECT * FROM ep_test_results WHERE epid = " . $row['epid'] . " AND checkid = (SELECT DISTINCT checkid FROM ep_test_results WHERE epid = " . $row['epid'] . " ORDER BY checktime DESC LIMIT 1)")->fetchAll();

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
				</p>

		</div>

		<?php
	}
}

function endpoint_check($dbConnection,$endpoint = '',$rootdomain = 0)
{
    //Domain name for endpoint
    if (($endpoint != '') AND (filter_var($endpoint, FILTER_VALIDATE_DOMAIN,FILTER_FLAG_HOSTNAME)))
    {
        //Set to lowercase
        $endpoint = strtolower($endpoint);

        //Check for duplicates in the DB
        $stmt = $dbConnection->prepare('SELECT epid FROM endpoints  WHERE domain = :endpoint');
        $stmt->execute([ 'endpoint' => $endpoint ]);
        $row = $stmt->fetch();

        //If no duplicate then insert the new record
        if (($row['epid'] >= 0) AND ($row['epid'] != ''))
        {
            $return = $row['epid'];
        }
        else
        {
            //Insert new endpoint
            $stmt = $dbConnection->prepare('INSERT INTO endpoints (epenabled,added,domain,rootdomain) VALUES (1,CURRENT_TIMESTAMP(),:endpoint,:rootdomain)');
            $stmt->execute([ 'endpoint' => $endpoint, 'rootdomain' => $rootdomain ]);

            //Grab new endpoint's EPID
            $stmt = $dbConnection->prepare('SELECT epid FROM endpoints  WHERE domain = :endpoint');
            $stmt->execute([ 'endpoint' => $endpoint ]);
            $row = $stmt->fetch();
            $return = $row['epid'];
        }


        //Queue a new scan of the endpoint?
    }
    //IP address for endpoint
    elseif (($endpoint != '') AND (filter_var($endpoint, FILTER_VALIDATE_IP)))
    {
        //Check for duplicates in the DB
        $stmt = $dbConnection->prepare('SELECT epid FROM endpoints  WHERE ipaddress = :endpoint');
        $stmt->execute([ 'endpoint' => $endpoint ]);
        $row = $stmt->fetch();

        //If no duplicate then insert the new record
        if ($row['epid'] >= 0)
        {
            $return = $row['epid'];
        }
        else
        {
            //Insert new endpoint
            $stmt = $dbConnection->prepare('INSERT INTO endpoints (epenabled,added,ipaddress) VALUES (1,"' . date("Y-m-d") . '",:endpoint)');
            $stmt->execute([ 'endpoint' => $endpoint ]);

            //Grab new endpoint's EPID
            $stmt = $dbConnection->prepare('SELECT epid FROM endpoints  WHERE ipaddress = :endpoint');
            $stmt->execute([ 'endpoint' => $endpoint ]);
            $row = $stmt->fetch();

            $return = $row['epid'];

        }

        //Queue a new scan of the endpoint?
    }
    elseif ($endpoint != '')
    {
        //ERROR: Invalid domain or IP
    }

    return $return;
}

?>