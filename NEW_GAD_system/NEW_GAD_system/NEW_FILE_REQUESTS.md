# NEW FILE REQUEST: update_ppas_form.php

## Description
A new endpoint is needed to update an existing PPAS entry in the `ppas_forms` table. This will be used for the edit mode in the PPAS form UI. The update must handle all fields, including JSON fields, and match the insert logic in `save_ppas_form.php`.

## Duplicate Functionality Search
- **ppas_form/**: No `update_ppas_form.php` or similar file exists. `save_ppas_form.php` only does inserts (checked SQL and logic).
- **ppas_form_temp/**: Has `get_ppas_entry.php` for fetching, but no update endpoint.
- **Other modules (gbp_forms, target_forms, etc.)**: Have their own update endpoints, but nothing for PPAS.
- **Frontend JS in ppas.php**: No update logic found, only save (insert) and delete.

## Request
Create `ppas_form/update_ppas_form.php` to update an existing PPAS entry by ID, using the same field structure as `save_ppas_form.php`. Must use the same validation and handle all JSON fields. 

# NEW FILE REQUEST: get_ppas_entry.php

## Description
A new endpoint is needed to fetch a single PPAS entry from the `ppas_forms` table by its ID. This is required for edit mode in the PPAS form UI, so all data (including JSON fields) can be loaded into the form for editing.

## Duplicate Functionality Search
- **ppas_form/**: No `get_ppas_entry.php` exists. Only `save_ppas_form.php` (insert) and `update_ppas_form.php` (update) are present.
- **ppas_form_temp/**: Has a `get_ppas_entry.php` but it is not in the main directory and may be for temp/draft entries.
- **Other modules (gbp_forms, target_forms, etc.)**: Have their own fetch endpoints, but nothing for PPAS in the main directory.
- **Frontend JS:** All fetches for single entry data expect this endpoint.

--- 