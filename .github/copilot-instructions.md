# Copilot Instructions for `lancamento_notas` (Moodle Local Plugin)

## Overview
This codebase is a Moodle local plugin for automating grade entry ("lançamento de notas aleatórias") for users. It is designed to update grades for specific grade items using a random value, triggered via a simple web form.

 # Copilot Instructions for `lancamento_notas` (Moodle Local Plugin)

 Overview
 - Small Moodle local plugin to create placeholder grade records and launch random grades for testing/homologation.

 Key files
 - `index.php`: UI; calls the helper to create placeholders and then updates grades.
 - `lib.php`: Core logic:
     - `local_lancamento_notas_ensure_grade_rows($userid, $maxperrun)` creates placeholder rows in `mdl_grade_grades` for `grade_items` matching the idnumber regex, only for courses where the user is enrolled.
     - `local_lancamento_notas_atualizar($userid, $maxperrun)` updates grades for records with `finalgrade IS NULL`.
 - `version.php`, `settings.php`: plugin metadata and future config.

 Important behaviors for agents
 - Placeholder creation is per-course: the helper finds courses where the user is enrolled and only creates rows for `grade_items` in those courses.
 - Regex used: `(AE|RE)[0-9](A|R)[0-9]$|RF3R1` (update in `lib.php` if needed).
 - The plugin processes in small batches (default 50) to avoid long-running requests; repeated runs are expected for large sets.
 - `session_write_close()` is called to avoid blocking other users during processing.
 - The helper checks `mdl_grade_grades` before creating a row to avoid duplicates.

 Debugging and testing
 - The plugin logs debug lines and returns counters (`created`, `skipped`, `courses_checked`, `debug`) which `index.php` shows as notifications.
 - To verify DB changes, run a query on `mdl_grade_grades` joined to `mdl_grade_items` filtered by `userid` and `courseid`.

 Developer notes
 - Use Moodle APIs: `$DB` for queries, `grade_item::fetch()` and `update_final_grade()` to create/update grades.
 - Avoid expensive queries: the helper uses `LIMIT` and per-run caps.
 - If you need to change batch size, modify the `$maxperrun` parameter in the calls from `index.php`.

 Deployment
 - Install by placing the folder in `local/` and visiting Administration → Notifications.

 Limitations
 - Intended for test environments only. It modifies grades and can overwrite real data.
 - Tested up to Moodle 5.1 in this repo.

 If something is unclear, run the plugin on a dev instance and copy the notification/debug output here.
    // ...existing code...
