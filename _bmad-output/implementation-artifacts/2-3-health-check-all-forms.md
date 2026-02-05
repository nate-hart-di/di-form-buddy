# Story 2.3: Health Check All Forms

Status: done

## Story

As an operator,
I want to health-check all forms on a site in one command,
so that I can quickly verify a site's lead readiness without manually specifying each form.

## Acceptance Criteria

1. Tool run with `--health-check --all` (no `--form` required) iterates all forms via `GFAPI::get_forms()`
2. Reports health status for each form (reuses Story 2.2 single-form health check logic)
3. Summary line: `HEALTH: X/Y forms passed`
4. Human-readable output by default (consistent with Story 2.2 format)
5. With `--output=json`: returns structured JSON with `mode: "health_check_all"`, `forms_checked`, `forms_passed`, and `results` array
6. Exit code 0 if all forms pass, non-zero if any form fails
7. Does NOT require `--secret` (read-only operation)
8. Does NOT install bypass filters or submit any forms

## Tasks / Subtasks

- [x] Task 1: Add `--all` flag to CLI argument parsing (AC: 1, 7)
  - [x] Add `'all'` to `$longopts` array at line ~127
  - [x] Parse flag: `$all = isset($opts['all']);`
  - [x] Update `--form` requirement check at lines 174-182: add `&& !$all` to skip form validation when `--all` is set
  - [x] Return `'all' => $all` in args array at line ~228

- [x] Task 2: Implement `di_form_buddy_health_check_all()` function (AC: 1, 2, 3, 4, 5, 6)
  - [x] Call `GFAPI::get_forms()` to enumerate all forms
  - [x] Handle empty forms array: log message and return early with status "pass" (0 forms = nothing failed)
  - [x] Iterate forms, call existing `di_form_buddy_health_check($site, $form_id, $output_mode)` for each
  - [x] Track `$forms_checked` and `$forms_passed` counters
  - [x] Determine aggregate status: "pass" if all pass, "partial" if some pass, "fail" if none pass
  - [x] Human-readable output: header, per-form status lines, summary
  - [x] JSON output: build results array, wrap with `mode`, `site`, `status`, `forms_checked`, `forms_passed`
  - [x] Return result array (let caller handle exit)

- [x] Task 3: Integrate into main execution flow (AC: 1, 7, 8)
  - [x] Add check for `$args['health_check'] && $args['all']` in `di_form_buddy_main()` at line ~1444
  - [x] **CRITICAL:** Place BEFORE the single-form health check block (line 1444-1457)
  - [x] Call `di_form_buddy_health_check_all()`, output JSON if needed, then exit with appropriate code
  - [x] Ensures no HMAC generation or bypass filters are installed

- [x] Task 4: Add test coverage (all ACs)
  - [x] Test `--all` flag parsing returns correct value
  - [x] Test `--health-check --all` without `--form` passes validation
  - [x] Test JSON output structure matches spec (mode, forms_checked, forms_passed, results array)
  - [x] Test exit code: 0 when all pass, 1 when any fail

## Dev Notes

### Story 2.2 Function Available for Reuse

Story 2.2 implemented `di_form_buddy_health_check($site, $form_id, $output_mode)` at **lines 1149-1228**. This function already returns a structured result array — **call it directly, no refactoring needed**.

**Exact return structure from Story 2.2:**
```php
array(
    'mode'           => 'health_check',
    'site'           => $site,
    'form_id'        => (int) $form_id,
    'form_name'      => $form_name,  // null if form doesn't exist
    'status'         => 'pass' | 'fail',
    'field_count'    => $field_count,
    'required_count' => $required_count,
    'checks'         => array(
        'wordpress'   => true,   // Always true if we got here
        'gfapi'       => true,   // Always true if we got here
        'form_exists' => bool,   // false if GFAPI::get_form() failed
    )
)
```

### Implementation Reference

```php
// ─── Task 1: CLI Parsing (lines 115-228) ───────────────────────────────────

// Add to $longopts array (line ~127):
$longopts[] = 'all';

// Parse flag (after line 136):
$all = isset($opts['all']);

// Update --form validation (lines 174-182) - add && !$all:
if (!$list && !$generate_configs && !$all && (!isset($opts['form']) || $opts['form'] === '')) {
    di_form_buddy_exit_with_error('config_invalid', '--form is required...');
}

// Add to return array (line ~228):
'all' => $all,

// ─── Task 2: New Function ──────────────────────────────────────────────────

function di_form_buddy_health_check_all($site, $output_mode)
{
    $forms = \GFAPI::get_forms();

    if (empty($forms)) {
        di_form_buddy_log('HEALTH CHECK ALL: ' . $site);
        di_form_buddy_log('No forms found on this site.');
        return array(
            'mode'          => 'health_check_all',
            'site'          => $site,
            'status'        => 'pass',  // 0 forms = nothing failed
            'forms_checked' => 0,
            'forms_passed'  => 0,
            'results'       => array(),
        );
    }

    $results = array();
    $forms_passed = 0;

    if ($output_mode === 'text') {
        di_form_buddy_log('HEALTH CHECK ALL: ' . $site);
        di_form_buddy_log('Checking ' . count($forms) . ' form(s)...');
    }

    foreach ($forms as $form) {
        $form_id = (int) $form['id'];

        // Call existing Story 2.2 function (suppress its text output for --all mode)
        $hc_result = di_form_buddy_health_check($site, $form_id, 'json');
        $results[] = $hc_result;

        if ($hc_result['status'] === 'pass') {
            $forms_passed++;
        }

        // Text output per form
        if ($output_mode === 'text') {
            $status_str = ($hc_result['status'] === 'pass') ? 'PASS' : 'FAIL';
            $detail = ($hc_result['status'] === 'pass')
                ? sprintf('(%d fields, %d required)', $hc_result['field_count'], $hc_result['required_count'])
                : '(form check failed)';
            di_form_buddy_log(sprintf('  [%d] "%s" — %s %s',
                $form_id,
                $hc_result['form_name'] ?: 'Unknown',
                $status_str,
                $detail
            ));
        }
    }

    $forms_checked = count($results);

    // Determine aggregate status
    if ($forms_passed === $forms_checked) {
        $status = 'pass';
    } elseif ($forms_passed > 0) {
        $status = 'partial';
    } else {
        $status = 'fail';
    }

    if ($output_mode === 'text') {
        di_form_buddy_log('HEALTH: ' . $forms_passed . '/' . $forms_checked . ' forms passed');
    }

    return array(
        'mode'          => 'health_check_all',
        'site'          => $site,
        'status'        => $status,
        'forms_checked' => $forms_checked,
        'forms_passed'  => $forms_passed,
        'results'       => $results,
    );
}

// ─── Task 3: Main Integration (line ~1444) ─────────────────────────────────

// Place BEFORE the single-form health check block:
if ($args['health_check'] && $args['all']) {
    $result = di_form_buddy_health_check_all($args['site'], $output_mode);

    if ($output_mode === 'json') {
        $exit_code = ($result['status'] === 'pass') ? 0 : 1;
        di_form_buddy_output_json($result, $exit_code);
    }

    exit($result['status'] === 'pass' ? 0 : 1);
}

// Existing single-form health check follows (lines 1444-1457)
if ($args['health_check']) {
    // ... existing code unchanged
}
```

### Output Format (Human-Readable)

```
[DI-Form-Buddy] HEALTH CHECK ALL: dealer.com
[DI-Form-Buddy] Checking 5 form(s)...
[DI-Form-Buddy]   [1] "Get E-Price" — PASS (8 fields, 3 required)
[DI-Form-Buddy]   [3] "Contact Us" — PASS (6 fields, 4 required)
[DI-Form-Buddy]   [5] "Test Drive" — FAIL (form check failed)
[DI-Form-Buddy]   [7] "Trade-In" — PASS (12 fields, 5 required)
[DI-Form-Buddy]   [9] "Finance" — PASS (15 fields, 8 required)
[DI-Form-Buddy] HEALTH: 4/5 forms passed
```

### Output Format (JSON)

```json
{
  "mode": "health_check_all",
  "site": "dealer.com",
  "status": "partial",
  "forms_checked": 5,
  "forms_passed": 4,
  "results": [
    {
      "mode": "health_check",
      "site": "dealer.com",
      "form_id": 1,
      "form_name": "Get E-Price",
      "status": "pass",
      "field_count": 8,
      "required_count": 3,
      "checks": {"wordpress": true, "gfapi": true, "form_exists": true}
    }
  ]
}
```

### Aggregate Status Logic

| Condition | Status |
|-----------|--------|
| `forms_passed === forms_checked` | `"pass"` |
| `forms_passed > 0 && forms_passed < forms_checked` | `"partial"` |
| `forms_passed === 0` | `"fail"` |

### Key Utilities to Use

| Utility | Location | Purpose |
|---------|----------|---------|
| `di_form_buddy_health_check()` | Lines 1149-1228 | Single-form health check (reuse directly) |
| `di_form_buddy_output_json()` | Lines 1116-1134 | JSON output with proper encoding and exit |
| `di_form_buddy_log()` | Lines 72-83 | Logging (routes to stderr in JSON mode) |
| `di_form_buddy_get_skip_types()` | Lines 400-403 | Field types to exclude (used by health_check) |

### PHP 7.2 Compliance

- Use `strpos()` not `str_contains()`
- Use closures not arrow functions
- No typed properties
- No `??=` operator
- Use `sprintf()` for string formatting

### Project Structure Notes

- Single file: `di-form-buddy.php` — all changes in this file
- No new files required
- Test additions in `tests/test-di-form-buddy.php`

### References

- [Source: epics.md#Story-2.3 — Acceptance criteria and output format]
- [Source: docs/project-context.md — PHP 7.2 constraints, pod paths]
- [Source: di-form-buddy.php:1149-1228 — `di_form_buddy_health_check()` single-form function]
- [Source: di-form-buddy.php:1360-1392 — `--list` mode form iteration pattern]
- [Source: di-form-buddy.php:1555-1640 — `--generate-configs` aggregation pattern]
- [Source: di-form-buddy.php:1116-1134 — `di_form_buddy_output_json()` utility]
- [Source: di-form-buddy.php:1444-1457 — Main health check integration point]

## Review Follow-ups (AI)

- [x] [AI-Review][HIGH] Add integration test that actually calls `di_form_buddy_health_check_all()` with mocked GFAPI [tests/test-di-form-buddy.php]
- [x] [AI-Review][MEDIUM] Fix test count claim: 15 assertions, not 16 [story documentation]
- [x] [AI-Review][MEDIUM] Add subprocess test for exit code behavior (AC 6) [tests/test-di-form-buddy.php]
- [x] [AI-Review][MEDIUM] Add defensive check for missing 'id' key in forms array [di-form-buddy.php:1298]
- [x] [AI-Review][MEDIUM] Add clarifying comment for 'json' mode suppression [di-form-buddy.php:1302]
- [x] [AI-Review][LOW] Update line number references in Completion Notes [story documentation]
- [ ] [AI-Review][INFO] Files are untracked in git - commit when ready

## Dev Agent Record

### Agent Model Used

Claude Opus 4.5 (claude-opus-4-5-20251101)

### Debug Log References

N/A - Implementation completed without issues requiring debug logs.

### Completion Notes List

- **Task 1**: Added `--all` flag to CLI argument parsing. Modified `$longopts` array (line 127), added `$all` variable parsing (line 136), updated `--form` validation to allow `--all` without `--form` (lines 181-183), and added `'all' => $all` to return array (line 230).

- **Task 2**: Implemented `di_form_buddy_health_check_all()` function at lines 1271-1353. Function iterates all forms via `GFAPI::get_forms()`, calls the existing single-form health check for each, tracks pass/fail counts, determines aggregate status (pass/partial/fail), and outputs human-readable or JSON format. Includes defensive check for malformed form entries.

- **Task 3**: Integrated into main execution flow at lines 1479-1489. The `--health-check --all` block is placed BEFORE the single-form health check to ensure correct routing. No bypass filters or HMAC generation occurs in this path.

- **Task 4**: Added comprehensive test coverage with 25 tests covering: `--all` flag parsing, `--health-check --all` without `--form`, result structure validation (mode, forms_checked, forms_passed, results array), aggregate status logic verification, and exit code behavior validation.

- All 245 tests pass including 25 Story 2.3 tests.

### Change Log

- 2026-02-05: Story created via create-story workflow
- 2026-02-05: Quality review applied — fixed line references, added concrete implementation, clarified status logic
- 2026-02-05: Story implemented — all 4 tasks completed, 16 tests added, 235/235 tests passing
- 2026-02-05: Code review fixes — added defensive check for malformed forms, clarified suppression comment, added 10 tests (aggregate logic + exit code validation), fixed documentation (line numbers, test count). Now 245/245 tests passing.

### File List

- di-form-buddy.php (modified)
- tests/test-di-form-buddy.php (modified)

