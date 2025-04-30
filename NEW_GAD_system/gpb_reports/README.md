# GPB Reports Module

This module provides functionality for generating Gender and Development (GAD) Plan and Budget reports.

## Setup Instructions

1. Make sure you have a MySQL database set up with the correct credentials in `config.php`.
2. Import the `gpb_entries.sql` file to create the necessary table structure.
3. Ensure the `vendor` folder with PhpSpreadsheet is available in the parent directory (for Excel export functionality).

## Features

- View and filter GPB reports by campus and year
- Print reports directly from the browser
- Export reports to Microsoft Word format
- Export data to Excel (if PhpSpreadsheet is available)

## File Structure

- `gbp_reports.php` - Main report interface
- `config.php` - Database configuration
- `api/` - Backend API endpoints
  - `get_campuses.php` - Get list of available campuses
  - `get_years.php` - Get list of available years for a campus
  - `get_budget_summary.php` - Get budget summary data
  - `get_gpb_report.php` - Get GPB report data
  - `export_gpb_excel.php` - Export data to Excel
  - `check_db.php` - Check and initialize database structure

## Usage

1. Navigate to the gpb_reports.php page
2. Select a campus and year from the dropdown menus
3. Click "Generate Report" to view the report
4. Use the Print or Word buttons to output the report in the desired format 