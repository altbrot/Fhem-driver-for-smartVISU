Fhem-driver-for-smartVISU
=========================

This is based on http://www.smartvisu.de and http://fhem.de

This repository contains a driver used to connect smartVISU to fhem homer server.

Installation
------------
 - install php curl (e.g. ```apt-get install php5-curl```)

 - copy files to driver directory
 
 - add the following lines to make.php
 ```php
    compile("driver/io_fhem.js");
    echo "\n";
 ```
    
 - call ```http(s)://[smartVISU host url]/make.php``` to trigger minification
 
 - go to settings to select fhem driver and configure url and port of fhem server
   (e.g ```http://192.168.1.1``` and ```8083```)
   
 - optionally activate realtime mode
 
Howto
-----
 - define a dummy device in fhem
   (e.g. ```define sensor.office.temp dummy```)
   (e.g. ```define mainroom.lamp.main```)

 - initially call set through ui to create a reading
  
 - configure widget listener
  
   (e.g. ```{{ basic.float('sensor.office.temp', 'sensor.office.temp', '°C') }}``` )
   (e.g. ```{{ basic.flip('mainroom.lamp.main', 'mainroom.lamp.main', 'on', 'off') }}``` )

 - create notify events to trigger sensor changes
 
   (e.g. ```define on_owthermsensor_changed OWX_10_XXXXXXXXXXXX:temperature set sensor.office.temp $EVENT```)

 - create notify events to trigger ui actions
 
   (e.g. ```define on_mainroom.lamp.main_changed notify mainroom.lamp.main {if($EVENT==1){fhem "set mylamp on"}else{fhem "set mylamp off"}}```)
