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
    var $cmd = '';
    var $item = '';
    var $val = '';
   
    /** 
    * constructor
    */ 
    public function __construct($request)
    {
        $this->cmd = $request['cmd'];
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
            $response = $this->sendReadCmd(urlencode($this->parseReadCmd($item)));
            if($response !== '')
            {
                $res[$item] = $response;
            }
        }
        return $res;
    }
    
    public function readPlot()
    {
        $res = [];     
        
        if (count($this->item) > 0)
        {
            $response = $this->sendReadCmd(urlencode($this->parseReadPlotCmd($this->item[0], $this->val)));
            if($response !== '')
            {
                $resarr = array();
                $lines = explode("\n", rtrim($response, "\n"));
                for($i = 0; $i < count($lines); $i++)
                {
                    $items = explode(" ", $lines[$i]);
                    $item = [];
                    $date = DateTime::createFromFormat("Y-m-d_H:i:s", $items[0]);
                    if ($date){
                        array_push($item, $date->format('U') * 1000);
                        array_push($item, floatval($items[1]));
                        array_push($resarr, $item);                        
                    }
                }
                $res[$this->item[0]] = $resarr;
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
            $this->sendWriteCmd(urlencode($this->parseWriteCmd($this->item[0], $this->val)));
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
	
        switch ($this->cmd)
        {
            case 'read':
                $ret = $this->read();
                break;
            case 'write':
                $ret = $this->write();
                break;
            case 'plot':
                $ret = $this->readPlot();
        }       
                        
        return json_encode($ret);
    }
    
    private function sendWriteCmd($cmd)
    {
        $host = config_driver_address;
        $port = config_driver_port;

        $url = $host.":".$port."/fhem?cmd=".$cmd."&XHR=1";

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_exec($curl);
        curl_close($curl); 
    }
    
    private function sendReadCmd($cmd)
    {
        $host = config_driver_address;
        $port = config_driver_port;
        
        $url = $host.":".$port."/fhem?cmd=".$cmd."&XHR=1";
        
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
    
    private function parseReadPlotCmd($item, $val)
    {
        $parsedItem = str_replace('->', '#', explode('.', $item)[0]);
        $items = explode('#', $parsedItem);        
        return "get ".$items[0]." - - ".$val["tmin"]." ".$val["tmax"]." ".$items[1];
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
