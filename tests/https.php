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
            $out = "GET / HTTP/1.1\r\n";
            $out .= "Host: " . $endpoint . "\r\n";
            $out .= "Connection: Close\r\n\r\n";
            fwrite($fp, $out);

            while (!feof($fp)) {
                $result = $result . fgets($fp, 128);
            }
            fclose($fp);
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