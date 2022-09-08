# APE TEST - Automated Public Endpoint Testing 

The trends in software development over the last few years have led to a situation where developers are building and deploying things at lightning speed. This is great for getting new products and tools out the door, but a nightmare for security teams trying to keep track of all of their public facing application endpoints. Maintaining an updated and accurate software asset catalog is nearly impossible in all but the most strictly organized development environments, and testing those endpoints on a regular basis can be an expensive and time consuming task.

The Automated Public Endpoint Testing tool -- APE TEST -- is designed to accomplish three goals:

1. Detect and catalog all public facing application endpoints (websites, APIs, etc) from OSINT sources and internal tools when available
2. Provide a framework for automating simple tests that can be performed against those endpoints to identify the highest risk items to investigate further
3. Log the results of those tests and provide the ability to alert based on defined criteria

## Installation Guide

In an ideal configuration, there are three components to this system that will need to be configured:

- A persistent MySQL database
- A server (container works too) running PHP, configured with the appropriate environment variables
- Some persistent mechanism for sending regular HTTP commands to the server (chron, etc)

### Step 1: Prepare the MySQL Database

This system has been tested and designed to work with a MySQL compatible database, however you can use whatever DB you'd like which is compatible with the PHP PDO database connection object system. The PDO connection string is located in the `dbconn.php` file and can be updated to use different database engines and character sets.

Once you have created your MySQL databse and added a new user to the DB, import the accompanying `init.db` file which has the appropriate data structures needed to support the system.

To "jump start" the process, you should add entries into the `endpoints` table for the highest level domain names commonly used by your organization (for example, if your systems are deployed on `*.example.com` domains, then add `example.com` to the database) setting the `rootdomain` field for the entries to `1` as this will trigger the OSINT discovery process for those domains. You can also add as many known domains to this database as you like.

You can add domains easily to the table using the following generic command:

```
INSERT INTO endpoints (epenabled,added,domain) VALUES (1,CURRENT_TIMESTAMP(),[ENDPOINT]);
```

### Step 2: Create the PHP Server

Now that the MySQL database is available, it is time to configure the PHP server. This system does not store any data locally and therefore can be optionally persistent, just make sure that the right environment variables are configured. 

The following environment variables are implemented in the system:
|Variable Key|Type|Description|
|---|---|---|
|DB_LOC|REQUIRED|Database location (IP or DNS name)|
|DB_NAME|REQUIRED|Database name|
|DB_UN|REQUIRED|Database username|
|DB_PW|REQUIRED|Plaintext password for DB user|
|accesskey|optional|If you set this variable, then every page will require a GET parameter of `key` with the associated value to operate.|

Ensure that your server can access the MySQL server and that the variables are properly configured.

### Create the CRON Job

There are two things that you will want to regularly call to make the system operate as intended:

- `ed.php`, the Endpoint Detection script, will need to be called once a day to grab any new domains from the OSINT sources
- `check.php`, the testing engine, will need to be called once every minute (or more, depending on the size of your environment) with a new process to automate the testing of each endpoint sequentially with all of the defined tests

### (Optional) Configure Internal Tooling

While the system will look for domains using OSINT sources, if there are internal resources that can identify new endpoints then they should be configured to send that information to APE TEST as well. Endpoints can be passed to APE TEST using either a GET or a POST request to `endpoint` on the ed.php script, which will automatically de-duplicate any domains and add them for review and processing.

Example:
```
https://example.com/ed.php?endpoint=test.com
```

## Writing Your Own Tests

Automated testing is a large part of why this system exists -- the idea is that you should be able to define things about your endpoints that you want to check, which would be concerning if found. Things like endpoints without authentication, or where a WAF is disabled, or where the domain name is misconfigured somehow.

Each test is defined in its own file in the `tests/` directory. When the `check.php` file runs, it automatically links and includes all files in that directory. Each test you write should be listed in its own file, with a unique function name. 

Here's a sample of what a test template should look like based on the included HTTP test:

```
<?php

//Register the function
array_push($tests,'apetest_http');

//Define the function
function apetest_http($dbConnection,$checkid,$data)
{
    //Test steps here 

    //Insert the results into the database
    insert_result($dbConnection,$checkid,$data['epid'],'apetest_http',$result,$alert);
}

//Define the alert trigger
//TODO
```

Let's talk about the components of this file for a moment.

The first thing that this file does is push the name of the function into an array called `$tests`. This array is used by the `check.php` file to loop through all available test functions for each endpoint. This is why havign a unique function name for each test is important. Note that all default tests included in this system use the prefix `apetest_`, and I recommend you use your own prefix as well in case others create new bundles of tests that are useful down the road.

Also improtant to note is that, because the script loops through all functions iteratively, the values passed to the function are always the same and in the same order. Therefore you should always declare your testing function with the same values:

```
function apetest_http($dbConnection,$checkid,$data)
```

The values passed to the function are as follows:

|Value|Detail|
|--|--|
|`$dbConnection`|The PDC database connection from the `dbconn.php` script. The same object is passed throughout the script to minimize open DB connections.|
|`$checkid`|This is a UUID value that ties all of the checks being performed on the endpoint together. The results are all stored in a single database, so this allows us to group the results and analyze them as a single view of the endpoint at a specific point in time.|
|`$data`|This is an array that is explained a bit more below.|

Within that `$data` array are three keys -- two semi-optional and one required.
- `epid`: The EndPoint ID, which is a unique identifier (primary key) within the database for that endpoint.
- `domain`: If the endpoint has a domain name associated with it, this is provided in this key. Typically domain based endpoints do not have an IP address listed, as it changes frequently.
- `ipaddress`: If the endpoint only has an IP address then that is provided in this key.

Within the function, you can define whatever tests you want to perform. Some common ideas include testing to see if a specific port is open, seeing if there is a redirect on port 80, if there is authentication on the endpoint, etc. Anything that can be defined in code can be tested.

After the tests are complete, the results can be stored in the database for review and analysis. This is accomplished using the `insert_result` function that is available. Note the following format for that function:

```
insert_result($dbConnection,$checkid,$data['epid'],'apetest_http',$result,$alert);
```

The following variables should be passed (in this order) when using this function:

|Value|Detail|
|--|--|
|`$dbConnection`|The PDC databse connection once again.|
|`$checkid`|The UUID that ties all the checks being performed on this endpoint together.|
|`$data['epid']`|The `epid` value from the data array that was originally passed to the function from the `check.php` file. This identifies the specific endpoint being tested.|
|`'apetest_http'`|This value should be the string for the test being performed so that it can be identified later.|
|`$result`|Whatever results you want to store. This can be a base64 encoded output of the HTTP response or anything else that will fit in a text field in a MySQL database.|
|`$alert`|Should this result trigger an alert? You should store a `1` for yes and a `0` for no.|