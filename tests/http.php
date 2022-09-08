<?php

//Register the function
array_push($tests,'apetest_http');

//Define the function
function apetest_http($dbConnection,$checkid,$data)
{
    if ($data['doimain'] != '')
        $endpoint = $data['doimain'];
    elseif ($data['ipaddress'] != '')
        $endpoint = $data['ipaddress'];
    
    if ($endpoint != '')
    {
        $fp = fsockopen($endpoint, 80, $errno, $errstr, 2);
	
        //Port closed
        if ($fp == FALSE)
        {
        $result = 0;
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

            $result = base64_encode($result);
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