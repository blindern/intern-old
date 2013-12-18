<?php

return array(
	'driver' => 'Ldapauth',
	'verify_multiple_logins' => false,
	'salt' => require "auth_salt.php",
	'iterations' => 10000,
	'additional_fields' => array(),
);
