# Copilot Instructions for `lancamento_notas` (Moodle Local Plugin)

## Overview
This codebase is a Moodle local plugin for automating grade entry ("lançamento de notas aleatórias") for users. It is designed to update grades for specific grade items using a random value, triggered via a simple web form.

## Architecture & Key Files
- `index.php`: Main entry point. Renders a form for user ID input and triggers grade updates via `local_lancamento_notas_atualizar($userid)`.
- `lib.php`: Contains the core logic for updating grades. Uses Moodle's DB API and grade item classes. Pattern-matches grade item IDs using a regex.
- `version.php`: Plugin metadata (version, requirements, component name).
- `settings.php`: Present but currently empty.

## Grade Update Workflow
- User enters a Moodle user ID in the form on `index.php`.
- On submit, `local_lancamento_notas_atualizar($userid)` is called.
- The function finds grade items matching a regex (`(AE|RE)[0-9](A|R)[0-9]$|RF3R1`) and with `finalgrade IS NULL`.
- For each item, a random grade (0.0 to 5.9) is assigned using Moodle's grade API.
- Feedback is logged with a timestamp.

## Project-Specific Patterns
- All DB access uses Moodle's `$DB` global and parameterized SQL.
- Grade item selection uses a regex on `idnumber` and requires `itemtype = 'category'`.
- Only users with `moodle/site:config` capability can launch grades.
- All output uses Moodle's `$OUTPUT` object for notifications and page rendering.

## Developer Workflows
- No build or test scripts are present; deploy by copying files into Moodle's `local/` directory and visiting the plugin page.
- Debugging is typically done via Moodle's debugging tools and direct page access.
- To update grades, use the form in `index.php` and provide a valid user ID.

## Integration Points
- Relies on Moodle core APIs: DB (`$DB`), grade items (`\grade_item`), context, and output.
- No external dependencies beyond Moodle.

## Conventions
- All functions are prefixed with `local_lancamento_notas_`.
- Plugin metadata follows Moodle's standard in `version.php`.
- Code expects to run inside Moodle (checks `MOODLE_INTERNAL`).

## Example: Grade Update Logic
```php
function local_lancamento_notas_atualizar($userid) {
    // ...existing code...
    $gradeitem->update_final_grade(
        $userid,
        $nota_aleatoria,
        'local_lancamento_notas',
        $feedback,
        FORMAT_MOODLE,
        null,
        null,
        true
    );
    // ...existing code...
}
```

## How to Extend
- Add new grade item patterns by updating the `$regex` in `lib.php`.
- Add settings via `settings.php` if needed.
- Use Moodle's API for additional features (see Moodle docs).

---
**Feedback requested:** Please review for missing or unclear sections, especially around workflows or integration points. Suggest improvements or corrections for your team or future agents.
