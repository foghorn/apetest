<?php

//Register the function
array_push($tests,'apetest_https_auth');

//Define the function
function apetest_https_auth($dbConnection,$checkid,$data)
{
    $alert = 0;
    
    if ($data['domain'] != '')
        $endpoint = $data['domain'];
    elseif ($data['ipaddress'] != '')
        $endpoint = $data['ipaddress'];
    
    if (($endpoint != '') AND fsockopen($endpoint, 443, $errno, $errstr, 2))
    {
        $check = 1;
        $result = '';
        $url = 'https://' . $endpoint . '/';
        
        while (($check > 0) AND ($check < 10))
        {
            $result = array_change_key_case(get_headers($url, true),CASE_LOWER);


            //See if this is redirecting to another page on the site
            if (($result['location'][0] != '') AND (substr_count(strtolower($result['location'][0]),strtolower($endpoint)) > 0))
            {
                $url = $result['location'][0];
                $check++;
            }

            //If this is redirecting to a different domain, check what domain it is and whether that is a known auth domain
            elseif ($result['location'][0] != '')
            {
                if (substr_count($result['location'][0],'accounts.google.com') > 0)
                {
                    $result = "Google Auth";
                    $alert = 0;
                }
                elseif (substr_count($result['location'][0],'login.microsoftonline.com') > 0)
                {
                    $result = "Microsoft Auth";
                    $alert = 0;
                }
                else
                {
                    $result = "Redirect to unknown third party";
                    $alert = 1;
                }

                $check = 0;
            }
            elseif ($result[0] != '')
            {
                if (substr_count($result[0],'401') > 0)
                {
                    $result = "401 Unauthorized";
                    $alert = 0;
                }
                else
                {
                    $result = "NO AUTH DETECTED";
                    $alert = 1;
                }
                $check = 0;
            }

            else
            {
                $result = "ERROR";
                $alert = 1;
                $check = 0;
            }

            
        }
    }
    elseif ($endpoint != '')
    {
        $result = "Port Not Open";
        $alert = 0;
    }
    else
    {
        $result = 0;
    }

    insert_result($dbConnection,$checkid,$data['epid'],'apetest_https_auth',$result,$alert);
}

//Define the alert trigger

?>