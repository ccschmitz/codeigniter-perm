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

	public function __construct()
	{
		$this->ci = get_instance();

		log_message('debug', 'Access class initialized');
	}
	
	public static function in_group($user_id = FALSE)
	{
		
	}

	public function has_role($role, $user_id = FALSE)
	{
		
	}

	public function verify_resource()
	{
		
	}

	public function setup_user_privileges()
	{
		$user_id = $this->ci->session->userdata($this->user_id_session_key);

		if ($this->use_roles)
		{
			// pull the roles from the database
			$user_roles = $this->ci->db
				->where($this->user_id_field_in_roles_table, $user_id)
				->get($this->user_roles_table);

			if ($user_roles)
			{
				// set the roles in the session
				$this->ci->session->set_userdata($this->user_roles_session_key, $user_roles->result());
			}
			else
			{
				// if the user roles table exists...
				if ($this->ci->db->table_exists($this->user_roles_table))
				{
					return FALSE;
				}
				else // create the user roles table
				{
					$this->configure_database();
				}
			}

			if ($this->use_groups)
			{
				// setup the user group in the session
				$user_group = $this->ci->db
					->select($this->user_group_id_field .','. $this->user_groups_table .'.name AS'. $this->user_group_session_key)
					->where('id', $user_id)
					->limit(1)
					->join($this->user_groups_table, $this->user_groups_table.'.id = '.$this->users_table.'.'.$this->user_group_id_field)
					->get($this->users_table)
					->result();
				
				$this->ci->session->set_userdata($this->user_group_session_key, $user_group->{$this->user_group_session_key});
			}
		}
		
		// if unsuccessful, configure the database
	}

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
			$this->ci->dbforge->create_table($this->user_roles_table);

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
			$this->ci->dbforge->create_table($this->roles_to_users_table);
		}

		// if user groups are being used...
		if ($this->user_groups)
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
			$this->ci->dbforge->add_field();
			$this->ci->dbforge->create_table($this->user_groups_table);

			// add the group_id field to the users table
			$this->ci->dbforge->add_column(
				$this->users_table, array(
					$this->user_group_id_field => array('type' => 'INT')
				)
			);
		}
	}

	private function write_roles_to_config_file()
	{
		
	}
}