<?php

// workflow.php - emulates user activity on server
//
// Vers. 1.0 , YP 03/23/2016
//
//
// Run:	workflow.php for usage
//

//
// Copyright © 2016 Neo Code Software Ltd
// Artisanal FileMaker & PHP hosting and development since 2002
// 1-888-748-0668 http://store.neocodesoftware.com
//
// History:
// 1.0, YP 03/21/2016 - Initial release
//

include_once('config.php');			// configuration and common stuff
include_once('class.WorkFlowThread.php'); 	//  class to send requests in threads
include_once('Snoopy.class.php');
include_once('workflow_data.php');	// $TEST_WORKFLOW is defined here
									// Globals
define('LOG_FILE',$CONFIG['VAR_DIR'].'workflow.log');
define('WEB_LOG_FILE',$CONFIG['VAR_DIR'].'workflow_web.log');
define('ERR_LOG_FILE',$CONFIG['VAR_DIR'].'workflow_err.log');

$options = array();
$workflow = array();

									// Local functions
function printHelpAndExit() {
  print "Usage: workflow.php --threads=X [--repeat=Y]  [--workflow=WORKFLOW]\n".
  		"                    [--log=(FILE|CONSOLE|NULL)] [--weblog=(FILE|CONSOLE|NULL)] [--errlog=(FILE|CONSOLE|NULL)]\n".
  		"\n".
  		"  X - number of simultaneous requests to your server\n".
  		"  Y - number of repetitions\n".
  		"  WORKFLOW - coma separated list of actions defined in workflow_data.php\n".
  		"    for example: login,page_open,page_post,page_open\n".
  		"    by default WORKFLOW defined in config.ini will be used\n".
  		"  log - where to send app output, default: FILE. Logs will be saved in ".LOG_FILE." \n".
	    "  weblog - where to send web pages output, default: FILE. Logs will be saved in ".WEB_LOG_FILE."\n".
	    "  errlog - where to send web pages errors, default: FILE. Logs will be saved in ".ERR_LOG_FILE."\n".
	    "    CONSOLE - logs are send in the console\n".
	    "    NULL - no logs\n";
  exit;
}
									// Start here
$ckStart = new CheckStart($CONFIG['VAR_DIR'].'workflow.lock');
if(!$ckStart->canStart()) {			// Check if script already running. Doesn't allow customer to send multiple restart requests
  printLogAndDie("Script is already running.");
}
									// Get input parameters
$options = getopt("",array("threads:","repeat::","workflow::","log::","weblog::","errlog::"));
									// Check input parameters
if (!array_key_exists('threads',$options)) {
  printHelpAndExit();
}
if(!array_key_exists('repeat',$options)) {
  $options['repeat'] = 1;
}
foreach (array('log','weblog','errlog') as $l) {	// Define default log settings
  $options[$l] = array_key_exists($l,$options) ? $options[$l] : 'FILE';
}
									// Define workflow
if (array_key_exists('workflow',$options) && $options['workflow']) {
  foreach (explode(',',$options['workflow']) as $str) {
    if ($s = trim($str)) {
	  $workflow[] = $s;
	}
  }
}
else {
  $workflow = $CONFIG['WORKFLOW'];
}
									// Overwrite default log defined in config.php
if ($options['log'] == 'CONSOLE' || $options['log'] == 'NULL') {
  $LOG = new LOG($options['log']);
}
else {
  $LOG = new LOG(LOG_FILE);
}
									// Define weblog
if ($options['weblog'] == 'CONSOLE' || $options['weblog'] == 'NULL') {
  $WEB_LOG = new LOG($options['weblog']);
}
else {
  $WEB_LOG = new LOG(WEB_LOG_FILE);
}

									// Define web errors log
if ($options['errlog'] == 'CONSOLE' || $options['errlog'] == 'NULL') {
  $ERR_LOG = new LOG($options['errlog']);
}
else {
  $ERR_LOG = new LOG(ERR_LOG_FILE);
}

$LOG->message("start ".$options['threads']." threads".(array_key_exists('repeat',$options) ? ", ".$options['repeat']." repetitions" : ""));

for ($j=0;$j<$options['repeat'];$j++) {
  $threads = array();
  for ($i=0;$i<$options['threads'];$i++) {
    $threads[] = new WorkFlowThread($CONFIG,array('LOG' => $LOG, 'LOGRES' => $WEB_LOG, 'LOGERR' => $ERR_LOG),
	  								$workflow, $TEST_WORKFLOW);
  }
  foreach ($threads as $thread) {		// Run all requests
    $thread->start();
  }
  foreach ($threads as $thread) {		// Wait for all threads to complete
    $thread->join();
  }
}

$LOG->message("done");

exit;

?>