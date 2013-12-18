<?php

return array(
	'ldap_server' => 'ldap.blindern-studenterhjem.no',
	'ldap_encryption' => 'tls',
	'ldap_base_dn' => 'dc=blindern-studenterhjem,dc=no',
	'ldap_group_dn' => 'ou=Groups,dc=blindern-studenterhjem,dc=no',
	'ldap_user_dn' => 'ou=Users,dc=blindern-studenterhjem,dc=no',
	'ldap_bind_dn' => 'uid=USERNAME,ou=Users,dc=blindern-studenterhjem,dc=no',
	'ldap_user_field' => 'uid',
	'ldap_fields' => array(
		'id' => 'uidNumber',
		'username' => 'uid',
		'email' => 'mail',
		'realname' => 'cn',
		'phone' => 'mobile',
	),
	'remember_me' => array(
		'enabled' => true,
		'cookie_name' => 'bsrmcookie',
		'expiration' => 86400*31,
	),
	'guest_login' => false,
	'login_hash_salt' => require "auth_salt.php",

	/**
	 * Groups as id => array(name => <string>, roles => <array>)
	 */
	'groups' => array(
		/**
		 * Examples
		 * ---
		 *
		 * -1   => array('name' => 'Banned', 'roles' => array('banned')),
		 * 0    => array('name' => 'Guests', 'roles' => array()),
		 * 1    => array('name' => 'Users', 'roles' => array('user')),
		 * 50   => array('name' => 'Moderators', 'roles' => array('user', 'moderator')),
		 * 100  => array('name' => 'Administrators', 'roles' => array('user', 'moderator', 'admin')),
		 */
	),

	/**
	 * Roles as name => array(location => rights)
	 */
	'roles' => array(
		/**
		 * Examples
		 * ---
		 *
		 * Regular example with role "user" given create & read rights on "comments":
		 *   'user'  => array('comments' => array('create', 'read')),
		 * And similar additional rights for moderators:
		 *   'moderator'  => array('comments' => array('update', 'delete')),
		 *
		 * Wildcard # role (auto assigned to all groups):
		 *   '#'  => array('website' => array('read'))
		 *
		 * Global disallow by assigning false to a role:
		 *   'banned' => false,
		 *
		 * Global allow by assigning true to a role (use with care!):
		 *   'super' => true,
		 */
	),

);
