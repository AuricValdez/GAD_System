# Narrative Forms Module

This module enables users to create and manage narrative reports for PPAS forms.

## Setup Instructions

### 1. Database Tables

The narrative forms module requires two database tables:

1. `ppas_forms` - This table stores PPAS form data
2. `narrative_forms` - This table stores the narrative form data associated with PPAS forms

To set up these tables, follow these steps:

#### Setting up the PPAS Forms Table

If your database does not already have a `ppas_forms` table:

1. Navigate to the `narrative` directory
2. Locate the file `ppas_forms_table.sql`
3. Execute this SQL script in your database (via phpMyAdmin or another database tool)

#### Setting up the Narrative Forms Table

1. Navigate to the `narrative` directory
2. Locate the file `narrative_table.sql`
3. Execute this SQL script in your database (via phpMyAdmin or another database tool)

### 2. Database Connection

Ensure that your database connection settings in `includes/db_connection.php` are correct:

```php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'gad_db'); // Must match your actual database name
define('DB_USER', 'root');   // Must match your database username
define('DB_PASS', '');       // Must match your database password
```

## Troubleshooting

If you encounter issues with the narrative forms module, follow these steps:

### 1. Check Database Connection

Run the `test_database.php` script in the `narrative` directory to verify your database connection and table setup:

```
http://your-site-url/narrative/test_database.php
```

This tool will help you identify issues with:
- Database connection
- Missing tables
- Missing data

### 2. Check for Errors

If you encounter a 500 Internal Server Error:

1. Check your web server error logs
2. Ensure PHP error reporting is enabled in your PHP files:

```php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
```

3. Look for `debug_logs.txt` file in the `narrative` directory that may contain additional error information

### 3. Common Issues and Solutions

#### No PPAS Forms Showing

If the dropdown doesn't show any PPAS forms:

1. Verify that the `ppas_forms` table exists and has data
2. Run the `test_database.php` script to check
3. If needed, run the SQL from `ppas_forms_table.sql` to populate sample data

#### Cannot Save Narrative Forms

If you cannot save narrative forms:

1. Verify that the `narrative_forms` table exists
2. Check that your user has proper MySQL permissions
3. Ensure your forms have the required fields (PPAS ID, etc.)

#### Cannot View or Delete Narratives

If you cannot view or delete narratives:

1. Check that your user has the correct permissions
2. Verify that the narrative ID is valid
3. Ensure your database connection is working properly

## Files in This Module

- `narrative.php` - Main form interface
- `get_ppas_forms.php` - Fetches PPAS forms for the dropdown
- `get_ppas_details.php` - Fetches details of a selected PPAS form
- `check_narrative.php` - Checks if a narrative exists for a PPAS form
- `save_narrative.php` - Saves/updates narrative forms
- `delete_narrative.php` - Deletes narrative forms
- `view_narrative.php` - Displays a printable narrative report
- `test_database.php` - Tool for diagnosing database issues
- `ppas_forms_table.sql` - SQL script to create/populate the PPAS forms table
- `narrative_table.sql` - SQL script to create the narrative forms table

## Support

For additional help, please contact your system administrator or the developer of this system. 