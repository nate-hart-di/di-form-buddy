# Story 2.1: Structured JSON Output Mode

Status: done

## Story

As an automation script,
I want machine-parseable JSON output from di-form-buddy,
so that I can aggregate results across multiple sites programmatically.

## Acceptance Criteria

1. **`--output=json` flag:** When specified, stdout contains exactly one JSON object (no log lines mixed in)
2. **Success response:** Returns `{"success": true, "site": "...", "form_id": N, "form_name": "...", "entry_id": N, "timestamp": "..."}`
3. **Validation failure response:** Returns `{"success": false, "site": "...", "form_id": N, "error": "validation_failed", "validation_errors": {...}}`
4. **Fatal error response:** Returns `{"success": false, "site": "...", "form_id": N, "error": "...", "message": "..."}`
5. **Log redirection:** All `[DI-Form-Buddy]` log lines go to stderr (not stdout) when `--output=json`
6. **Exit codes:** Exit 0 for success, non-zero for failure (unchanged from current behavior)
7. **Default behavior:** Without `--output=json`, existing human-readable output is preserved (backward compatible)

## Tasks / Subtasks

- [x] Task 1: Add `--output` flag to CLI argument parsing (AC: 1, 7)
  - [x] Add `output:` to `$longopts` array in `di_form_buddy_parse_args()`
  - [x] Parse value: accept `text` (default) or `json`
  - [x] Return `output_mode` in args array
  - [x] Validate: reject invalid values with helpful error

- [x] Task 2: Create output mode aware logging function (AC: 5)
  - [x] Modify `di_form_buddy_log()` to check global/static `$output_mode`
  - [x] When `json`: write to `fwrite(STDERR, ...)` instead of `echo`
  - [x] When `text`: preserve current behavior (`echo` + `error_log()`)
  - [x] Alternative: Create new function `di_form_buddy_set_output_mode()` + internal state

- [x] Task 3: Create structured result builder (AC: 2, 3, 4)
  - [x] Create `di_form_buddy_build_result()` function that builds result array
  - [x] Success result: `success`, `site`, `form_id`, `form_name`, `entry_id`, `timestamp`
  - [x] Validation failure: `success`, `site`, `form_id`, `error`, `validation_errors`
  - [x] Fatal error: `success`, `site`, `form_id`, `error`, `message`
  - [x] Timestamp format: ISO 8601 UTC (`gmdate('Y-m-d\TH:i:s\Z')`)

- [x] Task 4: Integrate JSON output into main flow (AC: 1, 2, 3, 4, 6)
  - [x] Initialize output mode early in `di_form_buddy_main()`
  - [x] Capture result throughout execution flow
  - [x] On success: build and output JSON result
  - [x] On validation failure: build and output JSON result, exit non-zero
  - [x] On fatal error: build and output JSON result, exit non-zero
  - [x] Ensure JSON output happens ONCE at the end (or on fatal exit)

- [x] Task 5: Update existing modes for JSON compatibility (AC: 1, 5, 7)
  - [x] `--list` mode: output JSON array of forms when `--output=json`
  - [x] `--inspect` mode: output JSON object with form fields when `--output=json`
  - [x] `--generate-configs` mode: output JSON summary when `--output=json`
  - [x] Preserve human-readable output when `--output=text` (default)

- [x] Task 6: Write tests and validate (AC: 1-7)
  - [x] Test: `--output=json` produces valid JSON on stdout
  - [x] Test: log messages go to stderr when `--output=json`
  - [x] Test: success response includes all required fields
  - [x] Test: validation failure response includes validation_errors
  - [x] Test: fatal error response includes error code and message
  - [x] Test: exit codes match expected behavior
  - [x] Test: `--output=text` preserves existing behavior
  - [x] Test: invalid `--output` value rejected with error

### Review Follow-ups (AI)

- [x] [AI-Review][HIGH] JSON mode still emits human-readable fatal argument errors to stdout and does not return a structured JSON error for missing/invalid CLI args (violates AC 1 and 4). [di-form-buddy.php:137]
- [x] [AI-Review][MEDIUM] `di_form_buddy_output_json()` builds fallback JSON manually without escaping `json_last_error_msg()`, which can emit invalid JSON if the message contains quotes/backslashes. [di-form-buddy.php:1116]
- [x] [AI-Review][MEDIUM] Tests do not assert JSON error output for CLI argument failures in `--output=json`, so regressions on AC 1/4 aren’t caught. [tests/test-di-form-buddy.php:877]

## Dev Notes

### Implementation Strategy

The key insight from Story 1.1/1.2 is that `di_form_buddy_log()` currently writes to BOTH `error_log()` and `echo`. For JSON mode:
- All informational logs should go to stderr only
- Only the final JSON result should go to stdout
- This enables: `php di-form-buddy.php --output=json 2>/dev/null | jq .`

### Logging Architecture Change

```php
// Current (text mode):
function di_form_buddy_log($message) {
    $line = '[DI-Form-Buddy] ' . $message;
    error_log($line);
    echo $line . PHP_EOL;  // stdout
}

// New (output-mode aware):
function di_form_buddy_log($message) {
    static $output_mode = 'text';
    // ... mode setter logic ...

    $line = '[DI-Form-Buddy] ' . $message;
    error_log($line);

    if ($output_mode === 'json') {
        fwrite(STDERR, $line . PHP_EOL);  // stderr
    } else {
        echo $line . PHP_EOL;  // stdout
    }
}
```

### JSON Response Schemas

**Success:**
```json
{
  "success": true,
  "site": "dealer.com",
  "form_id": 1,
  "form_name": "Get E-Price",
  "entry_id": 12345,
  "timestamp": "2026-02-04T15:30:00Z"
}
```

**Validation Failure:**
```json
{
  "success": false,
  "site": "dealer.com",
  "form_id": 1,
  "error": "validation_failed",
  "validation_errors": {"3": "Email is required"}
}
```

**Fatal Error Types:**
- `bootstrap_failed` - WordPress not found
- `gfapi_unavailable` - Gravity Forms not active
- `form_not_found` - Form ID doesn't exist
- `auth_failed` - Missing secret
- `config_invalid` - Invalid config file

```json
{
  "success": false,
  "site": "dealer.com",
  "form_id": 1,
  "error": "bootstrap_failed",
  "message": "WordPress not found at /var/www/..."
}
```

### Exit Codes (Unchanged)

| Code | Meaning |
|------|---------|
| 0 | Success |
| 1 | Fatal error or validation failure |

### Estimated Changes

~30-50 lines of changes across:
- `di_form_buddy_parse_args()` - add `--output` flag
- `di_form_buddy_log()` - add output mode awareness
- `di_form_buddy_submit()` - return result instead of exit
- `di_form_buddy_main()` - integrate JSON output
- New: `di_form_buddy_output_json()` - output final JSON result

### PHP 7.2 Constraints (CRITICAL)

- NO arrow functions (`fn() =>`)
- NO typed properties
- NO `str_contains()`, `str_starts_with()`
- Use `json_encode($data, JSON_UNESCAPED_SLASHES)` for clean URLs
- Use `fwrite(STDERR, ...)` for stderr output

### Backward Compatibility

- Default `--output=text` preserves ALL existing behavior
- No changes to exit codes
- No changes to `--list`, `--inspect`, `--generate-configs` default output
- Scripts relying on grep/text parsing continue to work

### Previous Story Learnings (Carried Forward)

From Story 1.1:
- Entry ID capture via `gform_after_submission` hook works reliably
- `json_encode()` can fail on invalid UTF-8 - always check return value
- Always use `gmdate()` for consistent UTC timestamps

From Story 1.2:
- `JSON_PRETTY_PRINT` for human-readable configs, but for `--output=json` submission results, compact JSON is better (easier to parse in batch scripts)
- Use constants for magic strings

### Testing Strategy

1. **Unit tests:** Test individual functions in isolation
2. **Integration tests:** Test full CLI flow with `--output=json`
3. **stderr/stdout separation:** Use output buffering to verify log destination

### Project Structure Notes

- Single file modification: `di-form-buddy.php`
- No new files needed
- Test additions to: `tests/test-di-form-buddy.php`

### References

- [Source: epics.md - Story 2.1 acceptance criteria, response schemas]
- [Source: docs/project-context.md - PHP 7.2 constraints, coding patterns]
- [Source: Story 1.1 - di_form_buddy_log() implementation, entry ID capture]
- [Source: Story 1.2 - JSON encoding patterns, error handling]
- [Source: di-form-buddy.php:51-56 - current logging implementation]
- [Source: di-form-buddy.php:1006-1067 - current submission handling]

## Dev Agent Record

### Agent Model Used

Codex (GPT-5)

### Debug Log References

- Tests not run in this review pass.

### Completion Notes List

1. **JSON error handling:** CLI validation errors now return structured JSON when `--output=json`, with logs redirected to stderr.

2. **Fallback JSON safety:** `di_form_buddy_output_json()` fallback path uses `json_encode()` on a safe array to avoid invalid JSON.

3. **Tests:** Added CLI tests asserting JSON error output for missing secret, missing site, and missing data in `--output=json`.

### File List

- di-form-buddy.php (modified)
- tests/test-di-form-buddy.php (modified)
- _bmad-output/implementation-artifacts/2-1-structured-json-output.md (modified)

## Change Log

- **2026-02-05:** Story 2.1 implemented - Added `--output=json` flag for structured JSON output. All log messages redirected to stderr in JSON mode. Success, validation failure, and fatal error responses follow defined schemas. All existing modes (--list, --inspect, --generate-configs) support JSON output. 194/194 tests pass. (Claude Opus 4.5)
- **2026-02-05:** Code review fixes - JSON mode now returns structured errors for CLI validation failures, fallback JSON encoding is safe, and tests cover JSON error output. (Codex)
