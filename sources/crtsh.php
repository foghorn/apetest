<?php
//Register the function
array_push($sources,'apetest_crtsh');

//Define the source test
function apetest_crtsh($dbConnection,$domain = '')
{

    //===== crt.sh query =====
    $tabledata = simplexml_load_string((file_get_contents("https://crt.sh/atom?q=" . $domain)));
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
}
?>