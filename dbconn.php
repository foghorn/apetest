<?php

//Test environment file
include 'testparams.php';


//Read in environment variables
if ($_SERVER['DB_LOC'] == '')
{
    $_SERVER['DB_LOC'] = getenv('DB_LOC');
    $_SERVER['DB_NAME'] = getenv('DB_NAME');
    $_SERVER['DB_UN'] = getenv('DB_UN');
    $_SERVER['DB_PW'] = getenv('DB_PW');
    $_SERVER['accesskey'] = getenv('accesskey');
}

//Start PDO connection to database
$dbConnection = new PDO('mysql:dbname=' . $_SERVER['DB_NAME'] . ';host=' . $_SERVER['DB_LOC'] . ';charset=utf8mb4', $_SERVER['DB_UN'], $_SERVER['DB_PW']);
$dbConnection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>