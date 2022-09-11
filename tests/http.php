<?php

//Register the function
array_push($tests,'apetest_http');

//Define the function
function apetest_http($dbConnection,$checkid,$data)
{
    if ($data['domain'] != '')
        $endpoint = $data['domain'];
    elseif ($data['ipaddress'] != '')
        $endpoint = $data['ipaddress'];

    $result = '';
    
    if ($endpoint != '')
    {
        $fp = fsockopen($endpoint, 80, $errno, $errstr, 2);

        
	
        if ($fp == FALSE)
        {
            $result = "Port not open";
        } 
        //Port open
        else 
        {
            $result = get_headers('http://' . $endpoint . '/', true);

            $result = json_encode($result);
        }
    }
    else
    {
        $result = 0;
    }

    insert_result($dbConnection,$checkid,$data['epid'],'apetest_http',$result,0);
}

//Define the alert trigger

?>