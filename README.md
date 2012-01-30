A simple access control library for CodeIgniter.

* Group and/or role-based controls
* Automatic database configuration
* Zero configuration (or darn close to it)

## Usage

You can set group or role-based controls.

### Groups

You can use the name of the group or the group's ID

	$this->access->in_group(3);

or:

	```php
	$this->access->in_group('admin');
	```

### Roles

You can use hierarchical roles, an array of multiple required roles, or just the name of the role you want to check.

Verify a user has a single role with a string:

	// returns true if the user has the required role
	$this->access->has_roles('users');

Verify a user has multiple required roles with an array:

	$required_roles = array('admin', 'users', 'create');
	
	// returns true only if user has all roles in the array
	$this->access->has_role($required_roles);

Verify a user has one of the roles in a hierarchy of roles:

	// hierarchy is: admin => users => create
	$required_roles = 'admin:users:create';

	// returns true if a user has any of the roles in the hierarchy
	// starts at the top of the hierarchy and returns true as soon as it finds a required role
	$this->access->has_roles($required_roles);

> **Note:** You can use `has_role` and `has_roles` interchangeably. `has_roles` is just a convenience method.

## Database Configuration

User group will be stored in the `group_id` field of the `users` table. All roles will be stored in a separate table (`user_roles` by default).

## Methods

* `in_group('group_slug_or_id')` - Checks to see if a user is part of the specified group (`group_slug`).
* `has_role('role_slug_or_id')` - Checks to see if a user has the specified role (`role_slug`).
* `setup_user_roles()` - Used to setup the roles in the session for the user. Stores user groups (`user_group`) and roles (`user_roles`).
* `configure_database()` - Checks to see if the necessary database tables and fields are created and created them if they do not exist.