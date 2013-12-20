<?php
return array(
	'_root_'  => 'bs/index',  // The default route
	'_404_'   => 'bs/404',    // The main 404 route
	
	'hello(/:name)?' => array('welcome/hello', 'name' => 'hello'),

	'userlist' => 'bs/userlist',
	'login' => 'auth/login',
	'logout' => 'auth/logout',


	'userdetails' => 'bs/userdetails',
	'printer/siste' => 'bs/printersiste',
	'printer/fakturere' => 'bs/printerfakturere',

	'kalender' => 'kalender/index',
	'kalender/ical' => 'kalender/ical',
);