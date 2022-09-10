<?php

//Register the function
array_push($tests,'apetest_concerning_ports');

//Define the function
function apetest_concerning_ports($dbConnection,$checkid,$data)
{
    if ($data['domain'] != '')
        $endpoint = $data['domain'];
    elseif ($data['ipaddress'] != '')
        $endpoint = $data['ipaddress'];
    
    if ($endpoint != '')
    {
        $port_array = array(20,21,23,25,37,42,49,53,119,123,161,194,433,601,902,903,1433,1434,1476,1723,2049,2375,2376,2377,3306);

        $looper = 0;
        $result = array();

        while ($port_array[$looper] != '')
        {
            $tempresult = '';

            $fp = fsockopen($endpoint, $port_array[$looper], $errno, $errstr, 2);
        
            if ($fp == FALSE)
            {
                $result = $result;
            } 
            else 
            {
                $tempresult = array("port" => $port_array[$looper], "status" => "open");

                array_push($result,$tempresult);

                fclose($fp);
            }

            $looper++;
        }

        if ($result == '')
        {
            $result = 'All concerning ports closed or non-responsive in 2 seconds';
            $alarm = 0;
        }
        else
        {
            $alarm = 1;
        }
        
        
	
    }
    else
    {
        $result = 0;
        $alarm = 0;
    }

    if (is_array($result))
    {
        $result = json_encode($result);
    }

    insert_result($dbConnection,$checkid,$data['epid'],'apetest_concerning_ports',$result,$alarm);
}

//Define the alert trigger

?>