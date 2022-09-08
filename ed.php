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
    //Grab all defined "root domains", the highest level domains which will be the starting point for enumeration
    $stmt = $dbConnection->query("SELECT * FROM endpoints WHERE rootdomain = 1")->fetchAll();

    foreach ($stmt as $row) 
    {
        if (($row['domain'] != '') AND (filter_var($row['domain'], FILTER_VALIDATE_DOMAIN,FILTER_FLAG_HOSTNAME)))
        {
            /*
            This appears to be a domain name. Search all available somain name enumeration tools.
            */

            //===== crt.sh query =====
            $tabledata = simplexml_load_string((file_get_contents("https://crt.sh/atom?q=" . $row['domain'])));
            $tabledata = json_decode(json_encode((array)$tabledata), TRUE);

            //Loop through all returned DNS names, strip out only the usable domains
            $looper = 0;

            while($tabledata['entry'][$looper]['summary'] != '')
            {
                
                //Chunk sanitization
                $chunk = $tabledata['entry'][$looper]['summary'];
                $breakpos = stripos($chunk,'<br><br>');
                
                if ($breakpos > 0)
                {
                    $chunk = trim(substr($chunk, 0, $breakpos));
                    $chunk = preg_replace("/\r|\n/", " ", $chunk);
                    
                    //Signal that there is at least one domain name to test
                    $test = 1;
                }
                else
                {
                    //There are no domain names to test
                    $test = 0;
                }
                
                //Test the domain names
                if ($test == 1)
                {
                    $domainloop = 0;
                    $tempdomains = explode(' ',$chunk);
                    
                    while ($tempdomains[$domainloop] != '')
                    {
                        
                        $tempholder = strtolower(trim($tempdomains[$domainloop]));

                        //If this is a FQDN and not a wildcard, test it directly
                        if ((filter_var($tempholder, FILTER_VALIDATE_DOMAIN,FILTER_FLAG_HOSTNAME)) AND (substr($tempholder,0,1) != "*"))
                        {
                            endpoint_check($dbConnection,$tempholder);
                        }

                        //If this is a wildcard FQDN, strip the star and test the result
                        elseif ((filter_var($tempholder, FILTER_VALIDATE_DOMAIN,FILTER_FLAG_HOSTNAME)) AND (substr($tempholder,0,2) == "*."))
                        {
                            $tempholder = substr($tempholder,2);
                            endpoint_check($dbConnection,$tempholder);
                        }
                        
                        $domainloop++;
                    }
                }
                
                $looper++;
            }

            //===== Next =====
        }
        
    }
}