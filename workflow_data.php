<?php

// workflow_data.php - data for workflow.php
//


$TEST_WORKFLOW = array (
  'login' => array (
	'URL' => 'http://tester:8080',
	'METHOD' => 'POST',
	'POST_DATA' => array ('action' => 'Login',
	  'username' => 'username',
	  'password' => 'password'),
  ),
  'page_open' => array (
	'URL' => 'http://tester:8080',
	'METHOD' => 'GET',
  ),
  'page_post' => array (
	'URL' => 'http://tester:8080',
	'METHOD' => 'POST',
	'POST_DATA' => array ('action' => 'Complete',
	  'doit' => 'yes'),
  ),
  'page_get' => array (
	'URL' => 'http://tester:8080',
	'METHOD' => 'GET',
  ),
);

?>