<?php

/*
== Endpoint Detector (ED) ==

Two functions:
- Regular running against common enumaeration mechanisms to find new domains
- POST from other tools to ingest new endpoints and domains for potential addition to the list for scanning
*/

require_once 'dbconn.php';


//IF there is an access key set, make sure it is passed as a GET parameter before continuing
if ($_SERVER['accesskey'] != '')
{
    if ($_GET['key'] != $_SERVER['accesskey'])
    {
        die();
    }
}

require_once 'functions.php';

//If this is a POST with a new endpoint to investigate, see if we need to add it to the list
if (($_GET['endpoint'] != '') OR ($_POST['endpoint'] != ''))
{
    if (($_GET['endpoint'] != '') AND ($_POST['endpoint'] != ''))
    {
        echo "ERROR: Only supply either one POST or one GET endpoint per call.";
        die();
    }
    elseif ($_GET['endpoint'] != '')
        $endpoint = $_GET['endpoint'];
    elseif ($_POST['endpoint'] != '')
        $endpoint = $_POST['endpoint'];

    endpoint_check($dbConnection,$endpoint);
}

//Otherwise, assume that this is just a normal scan and start pulling from other sources
else
{
    //Grab all available sources from the sources folder
    $sources = array();

    //Grab all defined tests
    foreach (glob("sources/*.php") as $filename) 
    {
        include $filename;
    }

    //Loop through all sources that DO NOT require a domain
    $testloop = 0;
    while ($sources[$testloop][1] == 0)
    {
        $sources[$testloop][0]($dbConnection,$row['domain']);
        $testloop++;
    }
    
    //Grab all defined "root domains", the highest level domains which will be the starting point for enumeration
    $stmt = $dbConnection->query("SELECT * FROM endpoints WHERE rootdomain = 1")->fetchAll();

    foreach ($stmt as $row) 
    {
        if (($row['domain'] != '') AND (filter_var($row['domain'], FILTER_VALIDATE_DOMAIN,FILTER_FLAG_HOSTNAME)))
        {
            /*
            This appears to be a domain name. Search all available somain name enumeration tools.
            */

            $testloop = 0;

            //Loop through all sources that require a domain
            while ($sources[$testloop][1] == 1)
            {
                $sources[$testloop][0]($dbConnection,$row['domain']);
                $testloop++;
            }

        }
        
    }
}