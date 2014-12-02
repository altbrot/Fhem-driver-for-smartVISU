<?php
/**
 * -----------------------------------------------------------------------------
 * @package     fhem proxy service
 * @author      Florian Keider
 * @copyright   2012
 * @license     GPL [http://www.gnu.de]
 * ----------------------------------------------------------------------------- 
 */


// get config-variables 
require_once '../lib/includes.php';
    
   
/** 
 * This class is an offline driver as a replacement for knx-bus
 */   
class driver_json
{
    var $item = '';
    var $val = '';
   
    /** 
    * constructor
    */ 
    public function __construct($request)
    {
        $this->item = explode(',', $request['item']);
	$this->val = $request['val'];
    }

    
    /** 
    * Read from bus
    */      
    public function read()
    {
        $res = Array();
        
        foreach ($this->item as $item)
        {
            $host = config_driver_address;
            $port = config_driver_port;
            
            $parsedItem = str_replace('->', '#', $item);
            $tokens = explode('#', $parsedItem);
            
            $cmd = "{ReadingsVal('".$tokens[0]."','".$tokens[1]."','')}";
            $url = $host.":".$port."/fhem?cmd=".urlencode($cmd)."&XHR=1";

            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $curl_response = curl_exec($curl);
            if ($curl_response === false) {
                $info = curl_getinfo($curl);
                curl_close($curl);
                //die('error occured during curl exec. Additioanl info: ' . var_export($info));
            }
            curl_close($curl);
            
            if($curl_response !== '')
                $res[$item] = $curl_response;
        }
        return $res;
    }
    
    
    /** 
    * Write to bus
    */      
    public function write()
    {
        $res = array();     
        
        return $res;
    }

    /** 
    * synchronize data from / to json service
    */ 
    public function sync()
    {        
        $ret = array();
	   
        // write if a value is given
        if ($this->val != '')
        {
            $ret = $this->write();        
        }
        else
        {
            $ret = $this->read();
        }
                        
        return json_encode($ret);
    }
}


// -----------------------------------------------------------------------------
// call the driver
// -----------------------------------------------------------------------------

$driver = new driver_json(array_merge($_GET, $_POST));
echo $driver->sync();

?>
