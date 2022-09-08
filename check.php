<?php
/* 
== Endpoint Checker ==

This script is intended to include all of the defined test cases in the 'tests' folder and run each test against all defined endpoints in the databse. 

There are three ways to operate this script: 
- 1: "Ratchet Mode" where the script will sort endpoints by the last time they were checked and check the oldest entry. Will only check tose that haven't been checked in 24 hours so it can be run continuously.
- 2: "Single Mode" where a single endpoint is provided and the script runs against that endpoint
- 3: "Blast Mode" where it will check everything in the database. WARNING: There is a high likelihood that this will casue the script to time out, and therefore will not successfully check all of the endpoints.

*/

//Grab DB information
require_once 'dbconn.php';

//IF there is an access key set, make sure it is passed as a GET parameter before continuing
if ($_SERVER['accesskey'] != '')
{
    if ($_GET['key'] != $_SERVER['accesskey'])
    {
        die();
    }
}

//Grab functions just in case
require_once 'functions.php';

//Set up an empty array for the domains to be tested
$domainarray = array();
$looper = 0;

//Check which version we are doing
//Function 1 (or default): Ratchet
if (($_GET['function'] == 1) OR ($_GET['function'] == ''))
{
    //Find the next oldest endpoint to test that is more than 24 hours old
    $stmt = $dbConnection->query("SELECT * FROM endpoints WHERE epenabled = 1 AND lastcheck < SUBDATE(CURRENT_DATE, 1) ORDER BY lastcheck ASC LIMIT 1");
    $row = $stmt->fetch();

    //Set as the only endpoint to be tested
    $domainarray[$looper]['epid'] = $row['epid'];
    $domainarray[$looper]['domain'] = $row['domain'];
    $domainarray[$looper]['ipaddress'] = $row['ipaddress'];

    $stmt = $dbConnection->prepare('UPDATE endpoints SET lastcheck = CURRENT_TIMESTAMP() WHERE epid = :epid');
    $stmt->execute([ 'epid' => $row['epid'] ]);

    $looper++;
}
//Function 2: Defined
elseif (($_GET['function'] == 2) AND ($_GET['endpoint'] != ''))
{
    //Validate the endpoint as a domain name
    if (filter_var($_GET['endpoint'], FILTER_VALIDATE_DOMAIN,FILTER_FLAG_HOSTNAME))
    {   
        $domainarray[$looper]['epid'] = endpoint_check($dbConnection,$_GET['endpoint']); 
        $domainarray[$looper]['domain'] = $_GET['endpoint'];
        $looper++;
    }
    //Validate the endpoint as an IP address
    elseif (filter_var($_GET['endpoint'], FILTER_VALIDATE_IP))
    {
        $domainarray[$looper]['epid'] = endpoint_check($dbConnection,$_GET['endpoint']); 
        $domainarray[$looper]['ipaddress'] = $_GET['endpoint'];
        $looper++;
    }
    else
    {
        //Not a valid endpoint
        die();
    }
    //Set as the only endpoint to be tested
}
//Function 3: Blast
elseif ($_GET['function'] == 3)
{
    //Grab all enabled domains
    $stmt = $dbConnection->query("SELECT * FROM endpoints WHERE epenabled = 1")->fetchAll();

    //Add them to the array
    foreach ($stmt as $row) 
    {
        $domainarray[$looper]['epid'] = $row['epid'];
        $domainarray[$looper]['domain'] = $row['domain'];
        $domainarray[$looper]['ipaddress'] = $row['ipaddress'];
        $looper++;
    }
    
}
else
{
    //Something went wrong, safest to just stop execution
    die();
}


//== Process a check on each identified endpoint ==

//Grab all defined tests
$tests = array();
foreach (glob("tests/*.php") as $filename) 
{
    include $filename;
}

//Create a UUID for the check
$checkid = guidv4(openssl_random_pseudo_bytes(16));

while ($looper > 0)
{
    $looper = $looper - 1;

    //Make sure that the identified endpoint is still valid, if a domain name
    if ($domainarray[$looper]['domain'] != '')
    {
        $dnscheck = dns_get_record($domainarray[$looper]['domain'],DNS_ALL);

        //If there is no returned host, then the hostname does not exist and should be skipped / disabled
        if ($dnscheck[0]['host'] == '')
        {
            $stmt = $dbConnection->prepare('UPDATE endpoints SET epenabled = 0 WHERE epid = :epid');
            $stmt->execute([ 'epid' => $domainarray[$looper]['epid'] ]);
        }
        //Otherwise, if the domain still exists, proceed
        else
        {
            //Process the endpoint for all defined tests
            $testloop = 0;

            while ($tests[$testloop] != '')
            {
                $tests[$testloop]($dbConnection,$checkid,$domainarray[$looper]);
                $testloop++;
            }
        }
    }
    //If not a domain name, then check the IP address or process the checks
    else
    {
        //Process the endpoint for all defined tests
        $testloop = 0;

        while ($tests[$testloop] != '')
        {
            $tests[$testloop]($dbConnection,$checkid,$domainarray[$looper]);
            $testloop++;
        }
    }
}


?>