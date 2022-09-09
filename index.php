<?php


session_start();

//Destroy session if logging out
if ($_GET['logout'] == 1)
{
	$_SESSION['PERMIT'] = "";
	$_SESSION['TZ'] = "";
	$_SESSION['ADMIN'] = "";
	$_SESSION['USER'] = "";
	$_SESSION['ACCOUNT'] = "";
	$_SESSION['FIRSTLOGIN'] = "";
	$_SESSION['EMAIL'] = "";
	$_SESSION['LoginTimestamp'] = "";
  	$_SESSION['CSRFTOKEN'] = "";
	session_destroy();
	session_start();
}

//Grab DB information
require_once 'dbconn.php';
require_once 'functions.php';

//If there's a one time key being used present the user the ability to set their password.
if($_GET['key'] != "")
{
  //Destroy the current session if logged in
  if ($_SESSION['PERMIT'] != "")
	{
		$_SESSION['PERMIT'] = "";
		$_SESSION['TZ'] = "";
		$_SESSION['ADMIN'] = "";
		$_SESSION['USER'] = "";
		$_SESSION['FIRSTLOGIN'] = "";
		$_SESSION['EMAIL'] = "";
		$_SESSION['LoginTimestamp'] = "";
		$_SESSION['IDSSELECT'] = "";
		session_destroy();
		session_start();
	}

	//Sanitize the key
	$invitekey = inputSanitize($_GET['key']);

	//Grab the pre-entered user info and display it
	$stmt = $dbConnection->prepare('SELECT * FROM useraccounts  WHERE invitekey = :invitekey');
	$stmt->execute([ 'invitekey' => $invitekey ]);
	$row = $stmt->fetch();

	

    if ($row['email'] != "")
    {
        //Allow first login
        $_SESSION['USER'] = $row['userid'];
        $_SESSION['FIRSTLOGIN'] = 1;
		$_SESSION['EMAIL'] = $row['email'];

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
			<!-- Bootstrap Core CSS -->
			<link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

			<!-- Custom CSS -->
			<link href="css/login.css" rel="stylesheet">

			<!-- Custom Fonts -->
			<link href="vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

			<!-- Google Graphs API -->
			<script type="text/javascript" src="https://www.google.com/jsapi"></script>

			<title>Watchtower - First Time Login</title>
		</head>

		<body>

			<div class="container">
				<div class="row">
					<div class="col-md-5 col-md-offset-4">
						<div class="login-panel panel panel-default">
							<div class="panel-heading text-center">
								<h3 class="panel-title">Create Your Watchtower Account Password</h3>
							</div>
							<div class="panel-body">
								<form name="input" action="index.php" method="post" role="form">
									<fieldset>
										<div class="form-group">
											<input class="form-control" placeholder="E-mail" name="email" type="email" value="<?php echo $row['email'] ?>"><br>
											<input class="form-control" placeholder="Name" name="user" type="name" value="<?php echo $row['name'] ?>">
										</div>
										<div class="form-group" style="margin-bottom: 4px;">
											<input class="form-control" placeholder="Password" name="password" type="password" value=""><br>
											<input class="form-control" placeholder="Confirm Password" name="password2" type="password" value="">
										</div>
										<div class="text-right">

										</div>
										</div>

										<input type="submit" class="btn btn-lg btn-success btn-block btn-login"></input>
									</fieldset>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- jQuery -->
			<script src="vendor/jquery/jquery.min.js"></script>

			<!-- Bootstrap Core JavaScript -->
			<script src="vendor/bootstrap/js/bootstrap.min.js"></script>

		<div style="height:75px; width: 100%; "></div></body>

		</html>
		<?php
		//actionlog($dbConnection,$_SESSION['USER'],"First login token used for user.");
    }
	else
		echo "ERROR: No email associated to this key";
}
//IF logging in for the first time and setting the password, but the password doesn't match, prompt again.
elseif (($_SESSION['FIRSTLOGIN'] == 1) AND ($_POST['password'] != "") AND ($_POST['password'] != $_POST['password2']) AND ($_SESSION['USER'] != ""))
{
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
			<!-- Bootstrap Core CSS -->
			<link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

			<!-- Custom CSS -->
			<link href="css/login.css" rel="stylesheet">

			<!-- Custom Fonts -->
			<link href="vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

			<!-- Google Graphs API -->
			<script type="text/javascript" src="https://www.google.com/jsapi"></script>
			<title>Watchtower - First Time Login</title>
		</head>

		<body>

			<div class="container">
				<div class="row">
					<div class="col-md-5 col-md-offset-4">
						<div class="login-panel panel panel-default">
							<div class="panel-heading text-center">
								<h3 class="panel-title">Create Your Watchtower Account Password</h3>
							</div>
							<div class="panel-body">
								<form name="input" action="index.php" method="post" role="form">
									<fieldset>
										<div class="form-group">
											<input class="form-control" placeholder="E-mail" name="email" type="email" value="<?php echo $_SESSION['EMAIL'] ?>"><br>
											<input class="form-control" placeholder="Name" name="user" type="name" value="<?php echo $row['name'] ?>">
										</div>
										<div class="form-group" style="margin-bottom: 4px;">
											ERROR: Passwords did not match!<br>
											<input class="form-control" placeholder="Password" name="password" type="password" value=""><br>
											<input class="form-control" placeholder="Confirm Password" name="password2" type="password" value="">
										</div>
										<div class="text-right">

										</div>
										</div>

										<input type="submit" class="btn btn-lg btn-success btn-block btn-login"></input>
									</fieldset>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- jQuery -->
			<script src="vendor/jquery/jquery.min.js"></script>

			<!-- Bootstrap Core JavaScript -->
			<script src="vendor/bootstrap/js/bootstrap.min.js"></script>

		<div style="height:75px; width: 100%; "></div></body>

		</html>
		<?php
		//actionlog($dbConnection,$_SESSION['USER'],"First login - password mismatch when setting new password.");

}
//IF they didn't enter a password prompt again
elseif (($_SESSION['FIRSTLOGIN'] == 1) AND ($_POST['password'] == "") AND ($_SESSION['USER'] != ""))
{
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
			<!-- Bootstrap Core CSS -->
			<link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

			<!-- Custom CSS -->
			<link href="css/login.css" rel="stylesheet">

			<!-- Custom Fonts -->
			<link href="vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

			<!-- Google Graphs API -->
			<script type="text/javascript" src="https://www.google.com/jsapi"></script>
			<title>Watchtower - First Time Login</title>
		</head>

		<body>

			<div class="container">
				<div class="row">
					<div class="col-md-5 col-md-offset-4">
						<div class="login-panel panel panel-default">
							<div class="panel-heading text-center">
								<h3 class="panel-title">Create Your Watchtower Account Password</h3>
							</div>
							<div class="panel-body">
								<form name="input" action="index.php" method="post" role="form">
									<fieldset>
										<div class="form-group">
											<input class="form-control" placeholder="E-mail" name="email" type="email" value="<?php echo $_SESSION['EMAIL'] ?>"><br>
											<input class="form-control" placeholder="Name" name="user" type="name" value="<?php echo $row['name'] ?>">
										</div>
										<div class="form-group" style="margin-bottom: 4px;">
											ERROR: Please enter a password!<br>
											<input class="form-control" placeholder="Password" name="password" type="password" value=""><br>
											<input class="form-control" placeholder="Confirm Password" name="password2" type="password" value="">
										</div>
										<div class="text-right">

										</div>
										</div>

										<input type="submit" class="btn btn-lg btn-success btn-block btn-login"></input>
									</fieldset>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- jQuery -->
			<script src="vendor/jquery/jquery.min.js"></script>

			<!-- Bootstrap Core JavaScript -->
			<script src="vendor/bootstrap/js/bootstrap.min.js"></script>

		<div style="height:75px; width: 100%; "></div></body>

		</html>
		<?php
		//actionlog($dbConnection,$_SESSION['USER'],"First login - no password provided.");
}
//IF the passwords match AND it's a first time login set the password and continue
elseif (($_SESSION['FIRSTLOGIN'] == 1) AND ($_POST['password'] == $_POST['password2']) AND ($_SESSION['USER'] != ""))
{
    $_SESSION['PERMIT'] = 1;

	//Process first time login
	$ID = $_SESSION['EMAIL'];
    $PASS = passHash($_POST['password'],$ID);
	$_SESSION['LoginTimestamp'] = date("Y-m-d");

	$_SESSION['CSRFTOKEN'] = passHash(time(),$ID);

	$UNAME = inputSanitize($_POST['user']);

	$stmt = $dbConnection->prepare('UPDATE useraccounts SET name = :uname , pass = :pass , invitekey = NULL  WHERE userid = :userid');
    $stmt->execute([ 'userid' => $_SESSION['USER'], 'uname' => $UNAME, 'pass' => $PASS ]);

	redirectHeader();
	
	$_SESSION['FIRSTLOGIN'] = 0;
	//actionlog($dbConnection,$_SESSION['USER'],"User successfully logged in.");
	die();

}
//If this is their first login but all of the other cases don't apply then IDK what happened.
elseif (($_SESSION['FIRSTLOGIN'] == 1))
{
	Echo "ERROR: This is a terrible problem. Please alert support@iatu.io.";
}
//If they are logging in using an email and password, check it
elseif ($_POST['email'] != "" AND $_SESSION['PERMIT'] != 1)
{

    $ID = inputSanitize($_POST['email']);
    $PASS = passHash($_POST['password'],$ID);

	$stmt = $dbConnection->prepare('SELECT * FROM useraccounts  WHERE email = :email');
	$stmt->execute([ 'email' => $ID ]);
	$row = $stmt->fetch();

    if ($PASS != $row['pass'])
    {
		$_SESSION['LoginAttempts'] = $_SESSION['LoginAttempts'] + 1;
		
		if ((!ip_is_private(ipgrab())))
			blacklistUpdate(0);
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
			<!-- Bootstrap Core CSS -->
			<link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

			<!-- Custom CSS -->
			<link href="css/login.css" rel="stylesheet">

			<!-- Custom Fonts -->
			<link href="vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

			<!-- Google Graphs API -->
			<script type="text/javascript" src="https://www.google.com/jsapi"></script>
			<title>Watchtower - Login</title>
		</head>

		<body>

			<div class="container">
				<div class="row">
					<div class="col-md-5 col-md-offset-4">
						<div class="login-panel panel panel-default">
							<div class="panel-heading text-center">
								<h3 class="panel-title">Log Into Your Watchtower Account</h3>
							</div>
							<div class="panel-body">
								<form name="input" action="index.php" method="post" role="form">
									<fieldset>
										<div class="form-group">
										ERROR: Please try again.<br>
											<input class="form-control" placeholder="E-mail" name="email" type="email" value=""><br>
										</div>
										<div class="form-group" style="margin-bottom: 4px;">
											<input class="form-control" placeholder="Password" name="password" type="password" value=""><br>
										</div>
										<div class="text-right">
											<!--<a href="forgot-password.html" target="_blank" id="btn-forgot-password">Forgot Password</a>-->
										</div>
										</div>
										<!-- Change this to a button or input when using this as a form -->
										<input type="submit" class="btn btn-lg btn-success btn-block btn-login"></input>
									</fieldset>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- jQuery -->
			<script src="vendor/jquery/jquery.min.js"></script>

			<!-- Bootstrap Core JavaScript -->
			<script src="vendor/bootstrap/js/bootstrap.min.js"></script>

		<div style="height:75px; width: 100%; "></div></body>

		</html>
		<?php
		//actionlog($dbConnection,'',"Unsuccessful login attempt #" . $_SESSION['LoginAttempts'] . " - USER [" . $ID . "] PASS [" . $PASS . "]");

        //LOGIN FORM
        die;
    }
    else
    {
        $_SESSION['USER'] = $row['userid'];
		$_SESSION['ACCOUNT'] = $row['accountid'];
        $_SESSION['PERMIT'] = 1;
        $_SESSION['LoginAttempts'] = 0;
        $_SESSION['LoginTimestamp'] = date("Y-m-d");
		$_SESSION['TZ'] = $row['tzone'];
    	$_SESSION['CSRFTOKEN'] = passHash(time(),$ID);
        //echo "Login successful.<br><br>";
		//==Invoke index display==

		//actionlog($dbConnection,$_SESSION['USER'],"User successfully logged in");


		redirectHeader();
		die();
    }
}
elseif ($_SESSION['PERMIT'] == 1)
{
	//Ensure session isn't stale
	SessionCheck();

	redirectHeader();
	die();
}
else
{
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
			<!-- Bootstrap Core CSS -->
			<link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

			<!-- Custom CSS -->
			<link href="css/login.css" rel="stylesheet">

			<!-- Custom Fonts -->
			<link href="vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

			<!-- Google Graphs API -->
			<script type="text/javascript" src="https://www.google.com/jsapi"></script>
			<title>Watchtower - Login</title>
		</head>

		<body>

			<div class="container">
				<div class="row">
					<div class="col-md-5 col-md-offset-4">
						<div class="login-panel panel panel-default">
							<div class="panel-heading text-center">
								<h3 class="panel-title">Log Into Your Watchtower Account</h3>
							</div>
							<div class="panel-body">
								<form name="input" action="index.php" method="post" role="form">
									<fieldset>
										<div class="form-group">
											<input class="form-control" placeholder="E-mail" name="email" type="email" value=""><br>
										</div>
										<div class="form-group" style="margin-bottom: 4px;">
											<input class="form-control" placeholder="Password" name="password" type="password" value=""><br>
										</div>
										<div class="text-right">
											<!--<a href="forgot-password.html" target="_blank" id="btn-forgot-password">Forgot Password</a>-->
										</div>
										</div>
										<!-- Change this to a button or input when using this as a form -->
										<input type="submit" class="btn btn-lg btn-success btn-block btn-login"></input>
									</fieldset>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- jQuery -->
			<script src="vendor/jquery/jquery.min.js"></script>

			<!-- Bootstrap Core JavaScript -->
			<script src="vendor/bootstrap/js/bootstrap.min.js"></script>

		<div style="height:75px; width: 100%; "></div></body>

		</html>
		<?php
}

?>