# _pushPVStringData.php

Simple PHP script to read data from a Fronius inverter and push it to PVOutput.

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

Version History
0.9 05MAR2019
