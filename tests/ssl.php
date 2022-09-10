<?php

//Register the function
array_push($tests,'apetest_ssl');

//Define the function
function apetest_ssl($dbConnection,$checkid,$data)
{
    if ($data['domain'] != '')
        $endpoint = $data['domain'];
    elseif ($data['ipaddress'] != '')
        $endpoint = $data['ipaddress'];
    
    if ($endpoint != '')
    {
        
        $url = 'https://' . $endpoint . '/';
        $orignal_parse = parse_url($url, PHP_URL_HOST);
        $get = stream_context_create(array("ssl" => array("capture_peer_cert" => TRUE)));
        $read = stream_socket_client("ssl://".$orignal_parse.":443", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $get);
        $cert = stream_context_get_params($read);
        $certinfo = openssl_x509_parse($cert['options']['ssl']['peer_certificate']);
        
	
        if (is_array($certinfo))
        {
            $testarray = array();

            //Check if cert is going to expire within the next month
            if ($certinfo['validTo_time_t'] < (time() + 2592000))
            {
                $result = "WARNING: Cert expiring within 30 days";
                $alarm = 1;
            }
            else
            {
                $result = "Cert valid until " . date("M d, Y",$certinfo['validTo_time_t']) . " for the following domains: "  . $certinfo['name'];
                $alarm = 0;
            }
        } 
        //Port open
        else 
        {
            $result = "Unable to fetch SSL information";
            $alarm = 0;
        }
    }
    else
    {
        $result = 0;
    }

    insert_result($dbConnection,$checkid,$data['epid'],'apetest_ssl',$result,$alarm);
}

//Define the alert trigger

?>