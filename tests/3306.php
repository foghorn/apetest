<?php

//Register the function
array_push($tests,'apetest_3306');

//Define the function
function apetest_3306($dbConnection,$checkid,$data)
{
    if ($data['domain'] != '')
        $endpoint = $data['domain'];
    elseif ($data['ipaddress'] != '')
        $endpoint = $data['ipaddress'];
    
    if ($endpoint != '')
    {
        $fp = fsockopen($endpoint, 3306, $errno, $errstr, 2);
	
        if ($fp == FALSE)
        {
        $result = 0;
        } 
        else 
        {
        $result = 1;
        fclose($fp);
        }
    }
    else
    {
        $result = 0;
    }

    insert_result($dbConnection,$checkid,$data['epid'],'apetest_3306',$result,0);
}

//Define the alert trigger

?>