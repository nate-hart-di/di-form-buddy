# Story 2.2: Health Check Mode

Status: done

## Story

As an operator,
I want to verify a site's form configuration without submitting a lead,
So that I can safely scan production sites at scale.

## Acceptance Criteria

1. **Given** the tool is run with `--health-check --form=1`
   **When** executed
   **Then** the tool:
   - Bootstraps WordPress
   - Verifies GFAPI available
   - Verifies form exists
   - Reports field count and required fields
   - Does NOT install bypass filters
   - Does NOT call `GFAPI::submit_form()`
   - Does NOT require `--secret`

2. **Human-readable output (default):**
   ```
   [DI-Form-Buddy] HEALTH CHECK: dealer.com / Form 1
   [DI-Form-Buddy] ✓ WordPress bootstrapped
   [DI-Form-Buddy] ✓ GFAPI available
   [DI-Form-Buddy] ✓ Form exists: "Get E-Price"
   [DI-Form-Buddy] ✓ 8 fields, 3 required
   [DI-Form-Buddy] HEALTH: PASS
   ```

3. **With `--output=json` (requires Story 2.1):**
   ```json
   {
     "mode": "health_check",
     "site": "dealer.com",
     "form_id": 1,
     "form_name": "Get E-Price",
     "status": "pass",
     "field_count": 8,
     "required_count": 3,
     "checks": {
       "wordpress": true,
       "gfapi": true,
       "form_exists": true
     }
   }
   ```

4. **When** any check fails
   **Then** status is `"fail"` and the failing check is `false`

5. Exit code 0 for pass, non-zero for fail (consistent with existing patterns)

## Tasks / Subtasks

- [x] Task 1: Add `--health-check` flag to CLI argument parser (AC: 1)
  - [x] 1.1: Add `'health-check'` to `$longopts` array in `di_form_buddy_parse_args()`
  - [x] 1.2: Parse flag: `$health_check = isset($opts['health-check']);`
  - [x] 1.3: Return `'health_check' => $health_check` in args array
  - [x] 1.4: Update secret-required check to skip health-check mode (like inspect/list)

- [x] Task 2: Implement health check execution function (AC: 1, 2, 3, 4)
  - [x] 2.1: Create `di_form_buddy_health_check($site, $form_id, $output_mode)` function
  - [x] 2.2: Build health check result array with all check statuses
  - [x] 2.3: Call `GFAPI::get_form()` to verify form exists and get metadata
  - [x] 2.4: Count total fields and required fields from form data
  - [x] 2.5: Return structured result array (for both text and JSON output)

- [x] Task 3: Integrate health check mode into main flow (AC: 1)
  - [x] 3.1: Add health-check handling in `di_form_buddy_main()` after inspect/list checks
  - [x] 3.2: Call health check function BEFORE bypass filter installation
  - [x] 3.3: Exit after health check (do not proceed to submission)

- [x] Task 4: Output formatting (AC: 2, 3, 5)
  - [x] 4.1: For text mode, use `di_form_buddy_log()` with checkmark formatting
  - [x] 4.2: For JSON mode (if Story 2.1 complete), output structured JSON to stdout
  - [x] 4.3: Return appropriate exit code (0 for pass, 1 for fail)

### Review Follow-ups (AI)

- [x] [AI-Review][High] Health-check failure paths must emit health-check structured output (bootstrap/GFAPI fail) instead of generic error payload. `di-form-buddy.php:1149`
- [x] [AI-Review][Medium] Avoid extra banner/bootstrap logs in health-check output to match AC example. `di-form-buddy.php:1345`
- [x] [AI-Review][Medium] Health-check tests should validate output formatting helpers (pass/fail) and field counting logic. `tests/test-di-form-buddy.php:1120`
- [x] [AI-Review][Medium] Add subprocess test for `--health-check --output=json` to verify valid JSON output. `tests/test-di-form-buddy.php`
- [x] [AI-Review][Medium] Use `di_form_buddy_get_input_type()` in field counting for consistent skip-type detection. `di-form-buddy.php:1155`
- [x] [AI-Review][Low] Remove unused MockGFAPI class from test file. `tests/test-di-form-buddy.php:1143`

## Dev Notes

### Architecture Compliance

- **PHP 7.2 REQUIRED**: No typed properties, no arrow functions, no `str_contains()`, no `??=`
- **Function naming**: All functions MUST be prefixed `di_form_buddy_`
- **Logging pattern**: Use `di_form_buddy_log($message)` for all output
- **No external dependencies**: Zero Composer packages, native PHP only

### Implementation Approach

This is a straightforward new execution mode that follows the existing `--inspect` and `--list` patterns:

1. **CLI parsing** - Already have pattern in `di_form_buddy_parse_args()` at lines 65-162
2. **Mode branching** - Add handler in `di_form_buddy_main()` similar to lines 1087-1122
3. **Form introspection** - Reuse `GFAPI::get_form()` pattern from inspect mode (line 1110)

### Code Location References

| Component | File | Lines | Pattern to Follow |
|-----------|------|-------|-------------------|
| CLI arg parsing | `di-form-buddy.php` | 65-162 | Add flag to `$longopts`, parse, return in array |
| Secret bypass | `di-form-buddy.php` | 96-101 | Add `!$health_check` to condition |
| Mode handling | `di-form-buddy.php` | 1087-1122 | Follow `--inspect` pattern exactly |
| Form loading | `di-form-buddy.php` | 1110-1121 | `GFAPI::get_form($args['form'])` |

### Key Implementation Details

1. **Field counting**: Iterate `$form['fields']` and count where `$field->isRequired` is truthy
2. **Skip non-data fields**: Exclude types in `di_form_buddy_get_skip_types()` (html, section, page, captcha, total, calculation)
3. **Exit handling**: Use `exit(0)` for pass, `exit(1)` for fail (matches existing patterns)

### Dependency on Story 2.1

- If Story 2.1 (JSON output) is NOT complete: Output text only, log message if `--output=json` requested
- If Story 2.1 IS complete: Support both `--output=text` (default) and `--output=json`

### Test Scenarios

1. Health check on valid form: Should pass with field counts
2. Health check on non-existent form: Should fail with `form_exists: false`
3. Health check without WordPress: Should fail with `wordpress: false`
4. Health check without GFAPI: Should fail with `gfapi: false`

### Project Structure Notes

- Single file script: All changes go in `di-form-buddy.php`
- No new files required for this story
- Config files (`configs/`) not affected

### References

- [Source: _bmad-output/planning-artifacts/epics.md#Story 2.2: Health Check Mode]
- [Source: docs/project-context.md#PHP 7.2 Compatibility]
- [Source: docs/project-context.md#Framework-Specific Rules]

## Dev Agent Record

### Agent Model Used

Claude Opus 4.5 (claude-opus-4-5-20251101)

### Debug Log References

- All implementation follows existing patterns from `--inspect` and `--list` modes
- Field counting logic reuses `di_form_buddy_get_skip_types()` to exclude display-only fields (html, section, page, captcha, total, calculation)

### Completion Notes List

- **Task 1 (CLI Parsing):** Added `--health-check` flag to `$longopts` array, parsed it, returned in args array, and updated secret/data-required checks to skip health-check mode
- **Task 2 (Health Check Function):** Created `di_form_buddy_health_check($site, $form_id, $output_mode)` that verifies bootstrap/GFAPI/form existence, counts data fields (excluding display-only types), and returns structured result for both text and JSON output
- **Task 3 (Main Integration):** Health-check handler runs before banner/standard bootstrap to avoid extra logs and ensure no submission logic runs during health check
- **Task 4 (Output Formatting):** Implemented text output helper with checkmark formatting matching AC 2 spec, JSON output matching AC 3 spec, and exit codes (0 for pass, 1 for fail) per AC 5
- **Tests Updated:** Added helper tests for health-check text output (pass/fail) and field counting helper

### File List

- `di-form-buddy.php` - Added health-check helpers, expanded health-check logic to cover bootstrap/GFAPI failures, and adjusted main flow to avoid extra logs in health-check mode
- `tests/test-di-form-buddy.php` - Added helper tests for health-check text output and field counting

### Change Log

- 2026-02-05: Implemented Story 2.2 Health Check Mode - all 4 tasks complete
- 2026-02-05: Post-review fixes for health-check failure output and text formatting; tests expanded for helpers
- 2026-02-05: Code review round 2 - Added subprocess JSON test, fixed inputType skip detection in field counting, removed dead MockGFAPI class
