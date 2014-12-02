Fhem-driver-for-smartVISU
=========================

This is based on http://www.smartvisu.de and http://fhem.de

This repository contains a driver used to connect smartVISU to fhem homer server.

Installation
------------

 - copy files to driver directory
 
 - add the following lines to make.php
 ```php
    compile("driver/io_fhem.js");
    echo "\n";
 ```
    
 - call http(s)://[smartVISU host url]/make.php to trigger minification
 
 - go to settings to select fhem driver and configure url and port of fhem server
   (e.g http://192.168.1.1 and 8083)
   
 - optionally activate realtime mode
 
Howto
-----
 - define a device in fhem
   (e.g. define sensor.office.temp OWTHERM 10.FFFFFFFFFFFF)
  
 - configure widget listener
    
   syntax: [devicename]->[readingsval]
  
   (e.g. ```{{ basic.float('sensor.office.temp', 'sensor.office.temp->temperature', '°C') }}``` )


