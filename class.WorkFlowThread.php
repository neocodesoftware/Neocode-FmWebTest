<?php
//
// class.WorkFlowThread.php - class to send requests in threads
//
// Vers. ALPHA, YP 03/22/2016
//
//
// History:
// ALPHA, YP 03/22/2016 - Initial release.
//

include_once('Snoopy.class.php'); 	// Snoopy class


class WorkFlowThread  extends Thread  {

  function __construct($cfg,$log,$actions2Run,$requests_config) {
	$this->CFG = $cfg;
	$this->LOG = $log['LOG'];
	$this->LOGRES = $log['LOGRES'];
	$this->LOGERR = $log['LOGERR'];
	$this->ToRun = $actions2Run;
	$this->REQUESTS_CONFIG = $requests_config;
  }

  //
  // run method code is executed in separate Thread
  //		$actions - list of requests to sent
  //
  public function run(){
	$snoopy = new Snoopy;
	foreach ($this->ToRun as $action) {
	  if (array_key_exists($action,$this->REQUESTS_CONFIG)) {
	    $time_start = microtime(true);
	    $err = $this->sendRequest ($snoopy,$action);
	    $time_end = microtime(true);
	    $this->LOG->message("$action: " . ($err ? "Error" : 'OK'). ". Exec time: ".round($time_end - $time_start,2)."sec");
	    if ($err) {
		  $this->LOGERR->message($this->REQUESTS_CONFIG[$action]['URL']." Error: ".$err);
	    }
	  }
	  else {
		$this->LOG->message("$action is not configired");
	  }
	}
  }

  //
  // sendRequest - send request HTTP and check the output
  // Call:	$err = sendRequest($snoopy,$request)
  // Where:	$err - error if any
  //			$snoopy - snoopy object
  //			$action - request to sent - key from $this->REQUESTS_CONFIG
  //			$this->REQUESTS_CONFIG[$action] - request data in format:
  //				 array ('URL' - url to send request,
  //					    'METHOD' - GET or POST,
  //						'POST_DATA' - post data for POST method)
  //
  public function sendRequest ($snoopy,$action) {
    $request = $this->REQUESTS_CONFIG[$action];
	if ($request['METHOD'] == 'POST') {
	  $res = $snoopy->submit($request['URL'],$request['POST_DATA']);
	}
	else {
	  $res = $snoopy->fetch($request['URL']);
	}
	if($res) {
	  //print_r($snoopy);
	  $this->LOGRES->message("\n\n".$request['URL']." Results:\n".$snoopy->results);
/*
	  if ($snoopy->status == '200') {		// Ok
	  										// Check for PHP errors
		if (preg_match('/PHP Fatal error: (.+)/s', $snoopy->results, $matches)) {
		  $this->LOGERR->message("\n".$request['URL']." PHP errors:\n".$matches[1]);
		}
		return '';
	  }
	  else {
		$this->LOGERR->message("\n".$request['URL']." Error:\n".$snoopy->results);
		return $snoopy->results;				// Return error
	  }
*/
	  										// Check for PHP errors
      if (preg_match('/PHP Fatal error: (.+)/s', $snoopy->results, $matches)) {
		$this->LOGERR->message("\n".$request['URL']." PHP errors:\n".$matches[1]);
	  }
      if ($snoopy->status == '200') {		// Ok
	    return '';
	  }
      else {
		return "Status: ".  $snoopy->status;
	  }	
	}
	else {
	  return "error fetching document: ".$snoopy->error;
	}
  }

}