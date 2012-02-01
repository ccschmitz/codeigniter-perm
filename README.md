> Is that a perm?

A simple access control library for CodeIgniter. Here's a quick rundown of the features:

* Use group and/or role-based controls
* Validate single or multiple required roles
* Validate hierarchical or optional roles
* Automatic database configuration

## Usage

You can set group or role-based controls.

### Groups

You can use the name of the group or the group's ID

	$this->perm->in_group(3);

or:

	$this->perm->in_group('admin');

### Roles

You can validate a single role by using the name of the role in the `has_role` method:

	$this->perm->has_role('users_create');

You can also pass multiple required roles as an array:

	$required_roles = array('admin', 'users');
	$this->perm->has_roles($required_roles);

> **Note:** You can use `has_role()` and `has_roles()` interchangeably. The `has_roles()` method maps to `has_role()`.

If you want to pass a hierarchy of roles, you can do that by separating each role with a color (`:`). If a user has any of the roles in the hierarchy, the method will return true.

	$this->perm->has_role('admin:users:users_create');