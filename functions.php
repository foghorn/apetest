<?php

function insert_result($dbConnection,$checkid,$epid,$fname,$result,$alarm = 0)
{
    $stmt = $dbConnection->prepare("INSERT INTO ep_test_results (checkid,epid,name,output,alarm,checktime) VALUES (:checkid,:epid,:fname,:result,:alarm,CURRENT_TIMESTAMP())");
    $stmt->execute([ 'checkid' => $checkid, 'epid' => $epid, 'fname' => $fname, 'result' => $result, 'alarm' => $alarm ]);
}

function guidv4($data)
{
    assert(strlen($data) == 16);

    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}


function endpoint_check($dbConnection,$endpoint = '')
{
    //Domain name for endpoint
    if (($endpoint != '') AND (filter_var($endpoint, FILTER_VALIDATE_DOMAIN,FILTER_FLAG_HOSTNAME)))
    {
        //Set to lowercase
        $endpoint = strtolower($endpoint);

        //Check for duplicates in the DB
        $stmt = $dbConnection->prepare('SELECT epid FROM endpoints  WHERE domain = :endpoint');
        $stmt->execute([ 'endpoint' => $endpoint ]);
        $row = $stmt->fetch();

        //If no duplicate then insert the new record
        if (($row['epid'] >= 0) AND ($row['epid'] != ''))
        {
            $return = $row['epid'];
        }
        else
        {
            //Insert new endpoint
            $stmt = $dbConnection->prepare('INSERT INTO endpoints (epenabled,added,domain) VALUES (1,CURRENT_TIMESTAMP(),:endpoint)');
            $stmt->execute([ 'endpoint' => $endpoint ]);

            //Grab new endpoint's EPID
            $stmt = $dbConnection->prepare('SELECT epid FROM endpoints  WHERE domain = :endpoint');
            $stmt->execute([ 'endpoint' => $endpoint ]);
            $row = $stmt->fetch();
            $return = $row['epid'];
        }


        //Queue a new scan of the endpoint?
    }
    //IP address for endpoint
    elseif (($endpoint != '') AND (filter_var($endpoint, FILTER_VALIDATE_IP)))
    {
        //Check for duplicates in the DB
        $stmt = $dbConnection->prepare('SELECT epid FROM endpoints  WHERE ipaddress = :endpoint');
        $stmt->execute([ 'endpoint' => $endpoint ]);
        $row = $stmt->fetch();

        //If no duplicate then insert the new record
        if ($row['epid'] >= 0)
        {
            $return = $row['epid'];
        }
        else
        {
            //Insert new endpoint
            $stmt = $dbConnection->prepare('INSERT INTO endpoints (epenabled,added,ipaddress) VALUES (1,"' . date("Y-m-d") . '",:endpoint)');
            $stmt->execute([ 'endpoint' => $endpoint ]);

            //Grab new endpoint's EPID
            $stmt = $dbConnection->prepare('SELECT epid FROM endpoints  WHERE ipaddress = :endpoint');
            $stmt->execute([ 'endpoint' => $endpoint ]);
            $row = $stmt->fetch();

            $return = $row['epid'];

        }

        //Queue a new scan of the endpoint?
    }
    elseif ($endpoint != '')
    {
        //ERROR: Invalid domain or IP
    }

    return $return;
}

?>