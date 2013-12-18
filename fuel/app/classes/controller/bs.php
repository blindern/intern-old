<?php

class Controller_BS extends Controller_Template
{
	public function action_index()
	{
		/*$ldapconn = ldap_connect("ldap.blindern-studenterhjem.no");

		ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
		ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);

		if (!ldap_start_tls($ldapconn))
		{
			// TODO: error handling
		}

		$r = ldap_search($ldapconn, "ou=Groups,dc=blindern-studenterhjem,dc=no", "(&(memberUid=henrste)(objectclass=posixGroup))", array("dn", "cn", "description", "gidNumber"));
		$e = ldap_get_entries($ldapconn, $r);
		var_dump($e);
		die;*/

		/*var_dump(Auth::member("lpadmin"));
		die;*/

		$this->template->title = "Internverktøy";
		$this->template->content = View::forge('bs/index');
	}

	public function action_userlist()
	{
		if (!$this->require_access("beboer")) return;

		// hent brukerliste
		//$groups = array("beboer", "dugnadprinter", "ffvprinter", "ffhprinter", "fsprinter", "hytteprinter", "kollegietprinter", "ukaprinter", "lpadmin", "utflyttet", "velferd");
		//sort($groups);

		$ignore_groups = array("Domain Users", "Domain Admins");

		// koble til LDAP
		$ad = ldap_connect("ldap://ldap.blindern-studenterhjem.no") or die("Kunne ikke koble til LDAP-database");
		ldap_start_tls($ad);

		// hent ut info om alle brukere i systemet
		$r = ldap_search($ad, "ou=Users,dc=blindern-studenterhjem,dc=no", "(uid=*)", array("uid", "cn", "mail", "uidNumber"));
		$e = ldap_get_entries($ad, $r);

		$users = array();
		$users_names = array();
		//var_dump($e);die;
		for ($i = 0; $i < $e['count']; $i++) {
			$users[$e[$i]['uid'][0]] = array(
				"id" => $e[$i]['uidnumber'][0],
				"username" => $e[$i]['uid'][0],
				"realname" => $e[$i]['cn'][0],
				"mail" => isset($e[$i]['mail'][0]) ? $e[$i]['mail'][0] : null,
				"groups" => array()
			);
			$users_names[] = strtolower($e[$i]['cn'][0]);
		}

		// sorter etter navn
		array_multisort($users_names, $users);

		// hent alle grupper
		$r = ldap_search($ad, "ou=Groups,dc=blindern-studenterhjem,dc=no", "(objectClass=posixGroup)", array("cn", "gidNumber", "memberUid"));
		$e = ldap_get_entries($ad, $r);

		$groups = array();
		$users_not_found = array();
		for ($i = 0; $i < $e['count']; $i++)
		{
			$name = $e[$i]['cn'][0];

			if (in_array($name, $ignore_groups))
			{
				// skip this group
				continue;
			}

			$groups[$name] = $e[$i];

			if (!empty($e[$i]['memberuid']))
			{
				for ($j = 0; $j < $e[$i]['memberuid']['count']; $j++)
				{
					$uid = $e[$i]['memberuid'][$j];
					if (!isset($users[$uid]))
					{
						$users_not_found[] = array($name, $uid);
					}
					else
					{
						$users[$uid]['groups'][] = $name;
					}
				}
			}
		}

		$data = array(
			"users" => $users,
			"users_not_found" => $users_not_found,
			"groups" => $groups
		);

		$this->template->title = "Brukerliste";
		$this->template->content = View::forge('bs/userlist', $data);
	}

	public function action_userdetails()
	{
		if (!$this->require_access()) return;
		$this->template->title = "Brukerdetaljer";

		$data = array(
			"user" => \Auth::instance()->get_user_array(),
		);

		$this->template->content = View::forge('bs/userdetails', $data);
	}

	public function action_404()
	{
		$this->template->title = "404";
		$this->template->content = Response::forge(View::forge("bs/404"), 404);
	}

	public function action_printersiste()
	{
		if (!$this->require_access()) return;

		// hent siste utskrifter fra printserveren
		$last = @json_decode(@file_get_contents("https://p.blindern-studenterhjem.no/api.php?method=pykotalast"), true);

		$this->template->title = "Siste utskrifter";
		$this->template->content = View::forge("bs/printersiste", array("prints" => $last));
	}

	protected function require_access($group = null)
	{
		// no user?
		if (!Auth::check() || ($group !== null && !Auth::member($group)))
		{
			$this->template->content = View::forge("bs/kungruppe", array("gruppe" => $group ?: "innloggede brukere"));
			if (!isset($this->template->title)) $this->template->title = "Ingen tilgang";
			return false;
		}

		return true;
	}

	protected function action_printerfakturere()
	{
		if (!$this->require_access("lpadmin")) return;

		$from = "2013-04-01";
		$to = "2013-12-31";

		// hent data fra printserveren
		$data = @json_decode(@file_get_contents("https://p.blindern-studenterhjem.no/api.php?method=fakturere&from=$from&to=$to"), true);

		// fetch all usernames
		$users = array();
		foreach ($data['prints'] as $group)
		{
			foreach (array_keys($group) as $user)
			{
				$users[] = $user;
			}
		}
		$users = array_unique($users);

		// fetch names for users
		$names = array();
		foreach ($users as $user)
		{
			$names[$user] = Auth::get_user_details($user);
		}
		$data['names'] = $names;

		// sorter gruppene
		foreach ($data['prints'] as &$group)
		{
			$name_sort = array();
			foreach (array_keys($group) as $user)
			{
				$name_sort[] = strtolower(isset($names[$user]) ? $names[$user]['realname'] : $user);
			}

			array_multisort($name_sort, $group);
		}

		// koble til LDAP
		$ad = ldap_connect("ldap://ldap.blindern-studenterhjem.no") or die("Kunne ikke koble til LDAP-database");
		ldap_start_tls($ad);

		// hent alle medlemmer av beboer-, og utflyttet-gruppa
		$r = ldap_search($ad, "ou=Groups,dc=blindern-studenterhjem,dc=no", "(&(objectClass=posixGroup)(|(cn=beboer)(cn=utflyttet)))", array("cn", "gidNumber", "memberUid"));
		$e = ldap_get_entries($ad, $r);

		$grupper = array();
		for ($i = 0; $i < $e['count']; $i++)
		{
			if (!empty($e[$i]['memberuid']))
			{
				for ($j = 0; $j < $e[$i]['memberuid']['count']; $j++)
				{
					$grupper[$e[$i]['cn'][0]][] = $e[$i]['memberuid'][$j];
				}
			}
		}
		$data['grupper'] = $grupper;

		$this->template->title = "Utskriftsfakturering";
		$this->template->content = View::forge("bs/printerfakturere", $data);
	}
}