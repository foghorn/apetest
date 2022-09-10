<?php

//Register the function
array_push($tests,'apetest_https');

//Define the function
function apetest_https($dbConnection,$checkid,$data)
{
    if ($data['domain'] != '')
        $endpoint = $data['domain'];
    elseif ($data['ipaddress'] != '')
        $endpoint = $data['ipaddress'];
    
    if ($endpoint != '')
    {
        $fp = get_headers('https://' . $endpoint . '/', true);
	
        if (is_array($fp))
        {
            $result = json_encode($fp);
        } 
        //Port open
        else 
        {
            $result = "Unable to fetch headers";
        }
    }
    else
    {
        $result = 0;
    }

    insert_result($dbConnection,$checkid,$data['epid'],'apetest_https',$result,0);
}

//Define the alert trigger

?>