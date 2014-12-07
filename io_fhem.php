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
            $response = $this->sendReadCmd($item);
            if($response !== '')
            {
                $res[$item] = $response;
            }
        }
        return $res;
    }
    
    
    /** 
    * Write to bus
    */      
    public function write()
    {
        $res = array();     
        
        if (count($this->item) > 0)
        {
            $this->sendWriteCmd($this->item[0]);
            $res[$this->item[0]] = $this->val;            
        }
        
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
    
    private function sendWriteCmd($item)
    {
        $host = config_driver_address;
        $port = config_driver_port;

        $url = $host.":".$port."/fhem?cmd=".urlencode($this->parseWriteCmd($item, $this->val))."&XHR=1";

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_exec($curl);
        curl_close($curl); 
    }
    
    private function sendReadCmd($item)
    {
        $host = config_driver_address;
        $port = config_driver_port;

        $url = $host.":".$port."/fhem?cmd=".urlencode($this->parseReadCmd($item))."&XHR=1";

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $curl_response = curl_exec($curl);
        
        if ($curl_response === false) {
            //$info = curl_getinfo($curl);
            curl_close($curl);
            return '';            
        }
        else {
            curl_close($curl);            
            return $curl_response;
        }
    }
    
    private function parseReadCmd($item)
    {
        return "{ReadingsVal('".$item."','state','')}";
    }
    
    private function parseWriteCmd($item, $val)
    {
        return "set ".$item." ".$val;
    }
}


// -----------------------------------------------------------------------------
// call the driver
// -----------------------------------------------------------------------------

$driver = new driver_json(array_merge($_GET, $_POST));
echo $driver->sync();

?>
