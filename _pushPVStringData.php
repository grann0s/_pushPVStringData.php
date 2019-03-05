<?php

/**********************************************************************************

PHP Script to extract data from a Fronius Inverter and push it to PVOutput account.
The script is designed to be run every five (5) minutes from a host computer which
can access the inverter and access the PVOutput website.

The script specifically extracts the voltage and current from each string, computes
the instantaneous power, and pushes the result to the EXTENDED VALUE fields at 
PVOutput. N.B. Your account *MUST* be in *DONATION MODE* to push the data.

This script is pushes data to v7, v8 and v9 which *MUST* be configured in your PVOutput
account. v7 and v8 are Watts. v9 is degrees C. I suggest plotting v9 on a separate vertical
axis.

This script was developed on an iMac running PHP Ver. 7.1.23

This script contains *NO* explicit error catching routines - yet. I strongly suggest that
you manually test the script *before* you running it as a cron job.

Uncomment the ->>>> file_get_contents(trim($pvOutputURL));	<<<<- at the end of the script
to actually push the data.

The script is designed to be run from cron at five-minute intervals. I am redirecting the
output for diagnostic purposes

The script was tested against a Fronius Symo 5.0-3-M Inverter with F/W 3.12.2-2

Use it at your peril. All useful feed-back will be appreciated.

**********************************************************************************/


// Define recursive function to extract nested values
function printValues($arr) {
    global $count;
    global $values;
    
    // Check input is an array
    if(!is_array($arr)){
        die("ERROR: Input is not an array");
    }
    
    /*
    Loop through array, if value is itself an array recursively call the
    function else add the value found to the output items array,
    and increment counter by 1 for each value found
    */
    foreach($arr as $key=>$value){
        if(is_array($value)){
            printValues($value);
        } else{
            $values[] = $value;
            $count++;
        }
    }
    
    // Return total count and values found in array
    return array('total' => $count, 'values' => $values);
}


// Prevent Script from Trying to Read Data BEFORE it is Written

sleep(15);

$dataManagerIP = "fronius";										// Inverter HOSTNAME or IP								

$pvOutputApiKEY = "PVOUTPUTAPIKEY";								// PVOutput API Key found in https://pvoutput.org/account.jsp					

$pvOutputSID = "PVOUTPUT_SID";									// PVOutput SystemID
																
$country = "Australia";											// /etc/localtime

$capitalCity ="Perth";			

date_default_timezone_set("$country/$capitalCity");

// Date for Fronius API
$date = date('Y-m-d', time());

// Date for PVOutput
$pushDate = date('Ymd', time());

$time = date('H:i', time());

// API Root - PVOutput
$pvOutputApiURL = "https://pvoutput.org/service/r2/addstatus.jsp?";

// API URL - Fronius
$inverterDataURL = "http://".$dataManagerIP."/solar_api/v1/GetArchiveData.cgi?Scope=System&StartDate=".$date."&EndDate=".$date."&Channel=Voltage_DC_String_1&Channel=Current_DC_String_1&Channel=Voltage_DC_String_2&Channel=Current_DC_String_2&Channel=Temperature_Powerstage";

$inverterJSON = file_get_contents($inverterDataURL);

$arr = json_decode($inverterJSON, true);

$result = printValues($arr);

$x = 0;

while ( $x < 86400 && gmdate("H:i", $x) <= $time )
	{
	$A1 = $arr["Body"]["Data"]["inverter/1"]["Data"]["Current_DC_String_1"]["Values"][$x];
	$A2 = $arr["Body"]["Data"]["inverter/1"]["Data"]["Current_DC_String_2"]["Values"][$x];
	$V1 = $arr["Body"]["Data"]["inverter/1"]["Data"]["Voltage_DC_String_1"]["Values"][$x];
	$V2 = $arr["Body"]["Data"]["inverter/1"]["Data"]["Voltage_DC_String_2"]["Values"][$x];
	$T1 = $arr["Body"]["Data"]["inverter/1"]["Data"]["Temperature_Powerstage"]["Values"][$x];
	$W1 = $V1 * $A1;
	$W2 = $V2 * $A2;
	
	$time_value = gmdate("H:i", $x);
	
	// echo gmdate("H:i", $x) . "," . $W1 . "," . $W2 . "," . $T1 . "\n";
                
	$x += 300;
	
	}

$pvOutputURL = $pvOutputApiURL
                . "key=" .  $pvOutputApiKEY
                . "&sid=" . $pvOutputSID
                . "&d=" .   $pushDate
                . "&t=" .   $time_value
                . "&v7=" .  $W1
                . "&v8=" .  $W2
                . "&v9=" . 	$T1;
                
echo $pvOutputURL . "\n";

// Uncomment the Following Line to Actually PUSH the Data.
// file_get_contents(trim($pvOutputURL));	

?>