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
        $url = 'http://' . $endpoint . '/';
        
        while (($check > 0) AND ($check < 10))
        {
            $redirect = '';

            $temp = get_headers($url, true);
            
            if (is_array($temp))
            {
                $return = array_change_key_case($temp,CASE_LOWER);

                $redirect = $return['location'];

                if (is_array($redirect) AND ($return['location'][0] != ''))
                    $redirect = ($return['location'][0]);

                //See if this is redirecting to another page on the site
                if (($redirect != '') AND (substr_count(strtolower($redirect),strtolower($endpoint)) > 0))
                {
                    $url = $redirect;
                    $check++;
                }

                //If this is redirecting to a different domain, check what domain it is and whether that is a known auth domain
                elseif ($redirect != '')
                {
                    if (substr_count($redirect,'accounts.google.com') > 0)
                    {
                        $result = "Google Auth";
                        $alert = 0;
                    }
                    elseif (substr_count($redirect,'login.microsoftonline.com') > 0)
                    {
                        $result = "Microsoft Auth";
                        $alert = 0;
                    }
                    else
                    {
                        $result = "Redirect to " . $redirect;
                        $alert = 1;
                    }

                    $check = 0;
                }
                elseif ($return[0] != '')
                {
                    if (substr_count($return[0],'401') > 0)
                    {
                        $result = "401 Unauthorized";
                        $alert = 0;
                    }
                    elseif (substr_count($return[0],'200') > 0)
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
            else
            {
                $result = "ERROR: Cannot open stream - redirected " . $check . " times";

                if ($check > 0)
                    $result = $result . " The target SSL certificate may not be valid, or the site may not be responding.";


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