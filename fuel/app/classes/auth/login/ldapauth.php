<?php

class Auth_Login_Ldapauth extends \Auth\Auth_Login_Driver
{
	/**
	 * Load the config
	 */
	public static function _init()
	{
		Config::load('ldapauth', true, true, true);

		// TODO: kontroller LDAP-støtte på serveren

		// setup the remember-me session object if needed
		if (\Config::get('ldapauth.remember_me.enabled', false))
		{
			static::$remember_me = \Session::forge(array(
				'driver' => 'cookie',
				'cookie' => array(
					'cookie_name' => \Config::get('ldapauth.remember_me.cookie_name', 'bsrmcookie'),
				),
				'encrypt_cookie' => true,
				'expire_on_close' => false,
				'expiration_time' => \Config::get('ldapauth.remember_me.expiration', 86400 * 31),
			));
		}
	}

	

	// LDAP-functions - TODO: move to other file later??
	protected $ldapconn;
	protected $ldap_bound_as;
	protected function ldap_connect()
	{
		if ($this->ldapconn) return true;
		$this->ldapconn = ldap_connect(\Config::get('ldapauth.ldap_server'));
		if (!$this->ldapconn)
		{
			// TODO: error handling
			return false;
		}

		ldap_set_option($this->ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
		ldap_set_option($this->ldapconn, LDAP_OPT_REFERRALS, 0);

		if (!ldap_start_tls($this->ldapconn))
		{
			// TODO: error handling
			return false;
		}

		return true;
	}
	protected function ldap_test_bind($user, $pass)
	{
		$this->ldap_connect(); // TODO: error checking

		$user_dn = $this->ldap_get_user_dn($user);

		if (ldap_bind($this->ldapconn, $user_dn, $pass))
		{
			$this->ldap_bound_as = $user_dn;
			return true;
		}

		return false;
	}
	protected function ldap_get_user_dn($username)
	{
		return str_replace("USERNAME", static::ldap_escaped_string($username), \Config::get("ldapauth.ldap_bind_dn"));
	}
	protected function ldap_get_uid($user)
	{
		// TODO: error checking

		if (!$this->ldap_connect()) return null;

		// get correct user field
		$field = \Config::get("ldapauth.ldap_user_field");
		$r = ldap_search($this->get_ldap(), \Config::get("ldapauth.ldap_user_dn"), "(".static::ldap_escaped_string($field)."=".static::ldap_escaped_string($user).")", array($field));
		$e = ldap_get_entries($this->get_ldap(), $r);

		if ($e['count'] == 0) return null;
		return $e[0][$field][0];
	}
	protected function ldap_get_user_details($user)
	{
		// TODO: error checking

		if (!$this->ldap_connect()) return false;

		// get fields
		$fields = array_values(\Config::get("ldapauth.ldap_fields"));

		$entry = @ldap_read($this->ldapconn, $this->ldap_get_user_dn($user), 'objectClass=*', $fields);
		$info = @ldap_get_entries($this->ldapconn, $entry);

		if ($info['count'] > 0)
		{
			return $info[0];
		}

		return false;
	}

	/**
	 * Returns a string which has the chars *, (, ), \ & NUL escaped to LDAP compliant
	 * syntax as per RFC 2254
	 * Thanks and credit to Iain Colledge for the research and function.
	 * (from MediaWiki LdapAuthentication-extension)
	 *
	 * @param string $string
	 * @return string
	 * @access private
	 */
	protected static function ldap_escaped_string($string)
	{
		// Make the string LDAP compliant by escaping *, (, ) , \ & NUL
		return str_replace(
			array( "\\", "(", ")", "*", "\x00" ),
			array( "\\5c", "\\28", "\\29", "\\2a", "\\00" ),
			$string
			);
	}


	/*public function get_groups() {
		$user = $this->get_user();
		if (!$user) return array();

		$result = ldap_search($this->get_ldap(), "ou=Groups,dc=blindern-studenterhjem,dc=no", "(memberUid=$user)", array("cn"));
		$e = ldap_get_entries($this->get_ldap(), $result);

		$groups = array();
		for ($i = 0; $i < $e['count']; $i++) {
			$groups[] = $e[$i]['cn'][0];
		}

		return $groups;
	}*/


	/**
	 * @var  Database_Result  when login succeeded
	 */
	protected $user = null;

	/**
	 * @var  array  value for guest login
	 */
	protected static $guest_login = array(
		'id' => 0,
		'username' => 'guest',
		'group' => array(),
		'login_hash' => false,
		'email' => false
	);

	/**
	 * @var  array  SimpleAuth class config
	 */
	protected $config = array(
		'drivers' => array('group' => array('Ldapgroup')),
		'additional_fields' => array("realname", "phone"),
	);

	/**
	 * Check for login
	 *
	 * @return  bool
	 */
	protected function perform_check()
	{
		$username = \Session::get("username");
		$login_hash = \Session::get("login_hash");

		if (!empty($username) && !empty($login_hash))
		{
			if (is_null($this->user) || ($this->user['username'] != $username && $this->user != static::$guest_login))
			{
				// fetch user details
				$this->user = $this->get_user_details($username); // TODO
			}

			// TODO: check if pass is changed sine login
			return true;
		}

		// remember me
		elseif (static::$remember_me && $user_id = static::$remember_me->get('user_id', null))
		{
			return $this->force_login($user_id);
		}

		// no valid login when still here, ensure empty session and optionally set guest_login
		$this->user = \Config::get('ldapauth.guest_login', true) ? static::$guest_login : false;
		\Session::delete('username');
		\Session::delete('login_hash');

		return false;
	}

	/**
	 * Check the user exists
	 *
	 * @return  bool
	 */
	public function validate_user($username_or_email = '', $password = '')
	{
		if ($this->ldap_test_bind($username_or_email, $password))
		{
			return $this->get_user_details($username_or_email);
		}

		return false;
	}

	/**
	 * Fetch user details
	 */
	public function get_user_details($username)
	{
		$result = $this->ldap_get_user_details($username);
		if ($result)
		{
			$fields = \Config::get("ldapauth.ldap_fields");
			$data = array();
			foreach ($fields as $name => $alias)
			{
				$data[$name] = isset($result[$alias]) ? $result[$alias][0] : null;
			}

			return $data;
		}

		return false;
	}

	/**
	 * Login user
	 *
	 * @param   string
	 * @param   string
	 * @return  bool
	 */
	public function login($username_or_email = '', $password = '')
	{
		if (!($this->user = $this->validate_user($username_or_email, $password)))
		{
			\Session::delete("username");
			\Session::delete("login_hash");
			return false;
		}

		// register so Auth::logout() can find us
		Auth::_register_verified($this);

		\Session::set('username', $this->user['username']);
		\Session::set('login_hash', $this->create_login_hash());
		\Session::instance()->rotate();
		return true;
	}

	/**
	 * Force login user
	 *
	 * @param   string
	 * @return  bool
	 */
	public function force_login($user_id = '')
	{
		if (empty($user_id))
		{
			return false;
		}

		$this->user = $this->get_user_details($user_id);
		if ($this->user === false)
		{
			$this->user = \Config::get('ldapauth.guest_login', true) ? static::$guest_login : false;
			\Session::delete('username');
			\Session::delete('login_hash');
			return false;
		}

		\Session::set('username', $this->user['username']);
		\Session::set('login_hash', $this->create_login_hash());
		return true;
	}

	/**
	 * Logout user
	 *
	 * @return  bool
	 */
	public function logout()
	{
		$this->user = \Config::get('simpleauth.guest_login', true) ? static::$guest_login : false;
		\Session::delete('username');
		\Session::delete('login_hash');
		return true;
	}

	/**
	 * Create new user
	 *
	 * @param   string
	 * @param   string
	 * @param   string  must contain valid email address
	 * @param   int     group id
	 * @param   Array
	 * @return  bool
	 */
	public function create_user($username, $password, $email, $group = 1, Array $profile_fields = array())
	{
		// TODO: not implemented
		return false;
	}

	/**
	 * Update a user's properties
	 * Note: Username cannot be updated, to update password the old password must be passed as old_password
	 *
	 * @param   Array  properties to be updated including profile fields
	 * @param   string
	 * @return  bool
	 */
	public function update_user($values, $username = null)
	{
		// TODO: not implemented
		return false;
	}

	/**
	 * Change a user's password
	 *
	 * @param   string
	 * @param   string
	 * @param   string  username or null for current user
	 * @return  bool
	 */
	public function change_password($old_password, $new_password, $username = null)
	{
		// TODO: not implemented
		return false;
	}

	/**
	 * Generates new random password, sets it for the given username and returns the new password.
	 * To be used for resetting a user's forgotten password, should be emailed afterwards.
	 *
	 * @param   string  $username
	 * @return  string
	 */
	public function reset_password($username)
	{
		// TODO: not implemented
		return false;
	}

	/**
	 * Deletes a given user
	 *
	 * @param   string
	 * @return  bool
	 */
	public function delete_user($username)
	{
		// TODO: not implemented
		return false;
	}

	/**
	 * Creates a temporary hash that will validate the current login
	 *
	 * @return  string
	 */
	public function create_login_hash()
	{
		if (empty($this->user))
		{
			throw new \SimpleUserUpdateException('User not logged in, can\'t create login hash.', 10);
		}

		$last_login = \Date::forge()->get_timestamp();
		$login_hash = sha1(\Config::get('ldapauth.login_hash_salt').$this->user['username'].$last_login);

		/*\DB::update(\Config::get('simpleauth.table_name'))
			->set(array('last_login' => $last_login, 'login_hash' => $login_hash))
			->where('username', '=', $this->user['username'])
			->execute(\Config::get('simpleauth.db_connection'));*/

		$this->user['login_hash'] = $login_hash;

		return $login_hash;
	}

	/**
	 * Get the user's ID
	 *
	 * @return  Array  containing this driver's ID & the user's ID
	 */
	public function get_user_id()
	{
		if (empty($this->user))
		{
			return false;
		}

		return array($this->id, (int) $this->user['id']);
	}

	/**
	 * Get the user's groups
	 *
	 * @return  Array  containing the group driver ID & the user's group ID
	 */
	public function get_groups()
	{
		if (empty($this->user))
		{
			return false;
		}

		if (!$this->ldap_connect())
		{
			return false;
		}

		$r = ldap_search($this->ldapconn, "ou=Groups,dc=blindern-studenterhjem,dc=no", "(&(memberUid=".$this->ldap_escaped_string($this->user['username']).")(objectclass=posixGroup))", array("dn", "cn", "description", "gidNumber"));
		$e = ldap_get_entries($this->ldapconn, $r);

		$groups = array();
		$groups_sort = array();
		for ($i = 0; $i < $e['count']; $i++)
		{
			$groups_sort[] = strtolower($e[$i]['cn'][0]);
			$groups[] = array("Ldapgroup", $e[$i]['cn'][0]);
		}

		array_multisort($groups_sort, $groups);

		return $groups;
	}

	public function get_realname()
	{
		return $this->get("realname");
	}

	public function get_phone()
	{
		return $this->get("phone");
	}

	/**
	 * Getter for user data
	 *
	 * @param  string  name of the user field to return
	 * @param  mixed  value to return if the field requested does not exist
	 *
	 * @return  mixed
	 */
	public function get($field, $default = null)
	{
		if (isset($this->user[$field]))
		{
			return $this->user[$field];
		}

		return $default;
	}

	/**
	 * Get the user's emailaddress
	 *
	 * @return  string
	 */
	public function get_email()
	{
		return $this->get("email", false);
	}

	/**
	 * Get the user's screen name
	 *
	 * @return  string
	 */
	public function get_screen_name()
	{
		return $this->get("username", false);
	}

	/**
	 * Extension of base driver method to default to user group instead of user id
	 */
	/*public function has_access($condition, $driver = null, $user = null)
	{
		
	}*/

	/**
	 * Extension of base driver because this supports a guest login when switched on
	 */
	/*public function guest_login()
	{
		
	}*/
}

// end of file simpleauth.php
