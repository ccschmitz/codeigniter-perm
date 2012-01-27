A simple access control library for CodeIgniter.

* Group and/or role-based controls
* Automatic database configuration
* Zero configuration (or darn close to it)

### Prerequisites

* Roles *must* be stored in the database in order to set them in the session on login.

## Database Configuration

User group will be stored in the `group_id` field of the `users` table. All roles will be stored in a separate table (`user_roles` by default).

## Methods

* `in_group('group_slug_or_id')` - Checks to see if a user is part of the specified group (`group_slug`).
* `has_role('role_slug_or_id')` - Checks to see if a user has the specified role (`role_slug`).
* `setup_user_roles()` - Used to setup the roles in the session for the user. Stores user groups (`user_group`) and roles (`user_roles`).
* `configure_database()` - Checks to see if the necessary database tables and fields are created and created them if they do not exist.