<?php
/**
 * The development database settings. These get merged with the global settings.
 */

return array(
	/*'default' => array(
		'connection'  => array(
			'dsn'        => 'mysql:host=localhost;dbname=fuel_dev',
			'username'   => 'root',
			'password'   => 'root',
		),
	),*/

	'default' => array(
		'type' => 'pdo',
		'connection' => array(
			'dsn' => 'sqlite:/var/webdev/projects/blindern-studenterhjem.no/intern/database.sqlite',
			'username' => '',
			'password' => ''
		),
		'charset' => null
	)
);
