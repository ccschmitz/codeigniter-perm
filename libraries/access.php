<?php

class Access {

	protected $ci;

	// the users table
	protected $users_table = 'users';

	// use roles and/or groups
	protected $use_roles = TRUE;
	protected $use_groups = TRUE;

	// db config
	protected $user_roles_table = 'user_roles';
	protected $user_group_id_field = 'group_id';
	protected $user_id_field_in_roles_table = 'user_id';

	protected $roles_to_users_table = 'roles_to_users';

	protected $user_groups_table = 'user_groups';

	// session variables
	protected $user_id_session_key = 'user_id';
	protected $user_roles_session_key = 'user_roles';
	protected $user_group_session_key = 'user_group';
	protected $user_group_id_session_key = 'user_group_id';

	public function __construct()
	{
		$this->ci = get_instance();

		log_message('debug', 'Access class initialized');
	}
	
	/**
	 * Checks to see if a user is in a user group
	 *
	 * @return bool
	 **/
	public function in_group($required_group, $user_id = FALSE)
	{
		// check to see if the group is an integer (id) or string
		if (is_int($required_group))
		{
			$user_group = $this->ci->session->userdata($this->user_group_id_session_key);
		}
		else
		{
			$user_group = $this->ci->session->userdata($this->user_group_session_key);
		}

		// see if the user is in the group
		if ($required_group == $user_group)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Checks to see if a user has the specified role(s)
	 *
	 * Accepts a string as a single role or an array or required roles
	 * 
	 * @return bool
	 **/
	public function has_role($required_roles, $user_id = FALSE)
	{
		// grab the user roles form the session
		$user_roles = $this->ci->session->userdata($this->user_roles_session_key);

		// if an array of required roles is passed...
		if (is_array($required_roles))
		{
			// check to see if user has all required roles
			foreach ($required_roles as $role)
			{
				if ( ! in_array($role, $user_roles))
				{
					return FALSE;
				}
			}
		}
		elseif (is_string($required_roles)) // a single role is passed in 
		{
			if ( ! in_array($required_roles, $user_roles))
			{
				return FALSE;
			}
		}
		else // i don't know what you're doing...
		{
			show_error('You must pass a string or an array to the has_role method.');
		}

		return TRUE;
	}

	/**
	 * Sets up the user privileges in the session.
	 * 
	 * This method should usually be called upon a successful login attempt.
	 *
	 * @return null
	 **/
	public function setup_user_privileges()
	{
		$user_id = $this->ci->session->userdata($this->user_id_session_key);

		if ($this->use_roles)
		{
			// create the user roles table if it doesn't exist
			if ( ! $this->ci->db->table_exists($this->user_roles_table))
			{
				$this->configure_database();
			}

			// pull the roles from the database
			$user_roles = $this->ci->db
				->select($this->user_roles_table.'.name AS name')
				->where($this->user_id_field_in_roles_table, $user_id)
				->join($this->user_roles_table, $this->user_roles_table .'.id = '. $this->roles_to_users_table .'.user_role_id')
				->get($this->roles_to_users_table)
				->result();
			
			$user_privileges = array();
			foreach ($user_roles as $role)
			{
				array_push($user_privileges, $role->name);
			}

			if ($user_roles)
			{
				// set the roles in the session
				$this->ci->session->set_userdata($this->user_roles_session_key, $user_privileges);
			}
		}

		if ($this->use_groups)
		{
			// setup the user group in the session
			$user = $this->ci->db
				->select($this->users_table .'.*,'. $this->user_groups_table .'.name AS '. $this->user_group_session_key .','. $this->user_groups_table .'.id AS '. $this->user_group_id_session_key)
				->where($this->users_table.'.id', $user_id)
				->join($this->user_groups_table, $this->user_groups_table.'.id = '.$this->users_table.'.'.$this->user_group_id_field)
				->limit(1)
				->get($this->users_table)
				->row();
			
			if ($user)
			{
				$this->ci->session->set_userdata($this->user_group_session_key, $user->{$this->user_group_session_key});
				$this->ci->session->set_userdata($this->user_group_id_session_key, $user->{$this->user_group_id_session_key});
			}
		}
	}

	/**
	 * Sets up the database with the required fields/tables to use the library
	 *
	 * @return null
	 **/
	private function configure_database()
	{
		$this->ci->load->dbforge();

		// if user roles are used...
		if ($this->use_roles)
		{
			// setup the user_roles table
			$fields = array(
				'id' => array(
					'type' => 'INT', 
					'constraint' => 11,
					'unsigned' => TRUE,
					'auto_increment' => TRUE
				),
				'name' => array(
					'type' => 'INT'
				),
				'date_created' => array(
					'type' => 'DATETIME'
				),
				'date_modified' => array(
					'type' => 'DATETIME'
				)
			);
			$this->ci->dbforge->add_field($fields);
			$this->ci->dbforge->add_key('id', TRUE);
			$this->ci->dbforge->create_table($this->user_roles_table, TRUE);

			// setup the roles_to_users table
			$fields = array(
				'id' => array(
					'type' => 'INT', 
					'constraint' => 11,
					'unsigned' => TRUE,
					'auto_increment' => TRUE
				),
				'user_id' => array(
					'type' => 'INT'
				),
				'user_role_id' => array(
					'type' => 'INT'
				),
				'date_created' => array(
					'type' => 'DATETIME'
				),
				'date_modified' => array(
					'type' => 'DATETIME'
				)
			);
			$this->ci->dbforge->add_field($fields);
			$this->ci->dbforge->add_key('id', TRUE);
			$this->ci->dbforge->create_table($this->roles_to_users_table, TRUE);
		}

		// if user groups are being used...
		if ($this->use_groups)
		{
			// setup the user_groups table
			$fields = array(
				'id' => array(
					'type' => 'INT', 
					'constraint' => 11,
					'unsigned' => TRUE,
					'auto_increment' => TRUE
				),
				'name' => array(
					'type' => 'INT'
				),
				'date_created' => array(
					'type' => 'DATETIME'
				),
				'date_modified' => array(
					'type' => 'DATETIME'
				)
			);
			$this->ci->dbforge->add_field($fields);
			$this->ci->dbforge->add_key('id', TRUE);
			$this->ci->dbforge->create_table($this->user_groups_table, TRUE);

			// add the group_id field to the users table
			$user_group_id_field = array(
				$this->user_group_id_field => array(
					'type' => 'INT'
				)
			);
			$this->ci->dbforge->add_column($this->users_table, $user_group_id_field);
		}
	}
}