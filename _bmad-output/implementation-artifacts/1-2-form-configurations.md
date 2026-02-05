# Story 1.2: Automated Form Config Generation

Status: done  <!-- Approved 2026-02-05 after Session 2 review -->

## Story

As an operator,
I want a single command that auto-generates JSON config files for ALL forms on any site,
so that I never have to manually map field IDs and can immediately test any form.

## Acceptance Criteria

1. `--generate-configs` flag queries GFAPI and generates config files for ALL forms on the site
2. Each config file includes: `form_id`, `form_name`, `fields` (with id, type, label, required), `required_fields`, `test_data`, `skipped_fields`
3. Test data auto-populated based on field type with `di.form.buddy@gmail.com` as email
4. Complex fields handled correctly: name (simple/normal), address (sub-inputs), checkbox, select, radio
5. Non-data fields skipped: `html`, `section`, `page`, `captcha`
6. File upload fields included in `fields` but excluded from `test_data` (noted in `skipped_fields`)
7. Output files named `{form_id_padded}-{slug}.json` in `configs/` directory
8. Generated configs load and submit successfully via existing `--config` flag

## Tasks / Subtasks

- [x] Task 1: Add `--inspect` flag to di-form-buddy.php — **Completed in Story 1.1**
  - [x] `--inspect` dumps field structure
  - [x] `--list` enumerates all forms
- [x] Task 2: Implement `--generate-configs` flag (AC: 1, 7)
  - [x] Add `--generate-configs` to CLI argument parsing
  - [x] Add optional `--output-dir` (default: `configs/`)
  - [x] Call `GFAPI::get_forms()` to enumerate all forms
  - [x] Create output directory if it doesn't exist
- [x] Task 3: Implement field extraction and mapping (AC: 2, 4, 5, 6)
  - [x] For each form, extract all fields via `GFAPI::get_form($id)`
  - [x] Map field properties: id, type, label, isRequired
  - [x] Handle sub-inputs for complex types (name, address, checkbox)
  - [x] Skip non-data fields (html, section, page)
  - [x] Track skipped fields with reason
- [x] Task 4: Implement test data generation (AC: 3, 4)
  - [x] Generate input name: `input_{id}` or `input_{id}_{subid}` for sub-inputs
  - [x] Field type → test value mapping (see Dev Notes)
  - [x] Handle choice-based fields (select, radio, checkbox) — use first choice value
  - [x] Handle sub-input fields (name normal/extended, address)
  - [x] Skip fileupload in test_data, note in skipped_fields
- [x] Task 5: Write config files with proper structure (AC: 2, 7)
  - [x] Build config JSON structure
  - [x] Generate slug from form title (lowercase, hyphenated)
  - [x] Pad form_id to 2 digits for filename
  - [x] Write with `JSON_PRETTY_PRINT`
  - [x] Report generated files to console
- [x] Task 6: Integration test (AC: 8)
  - [x] Run `--generate-configs` on pod46/albanytoyota.net
  - [x] Verify all 23 forms generate configs
  - [x] Test submission with 3+ generated configs (simple and complex forms)
  - [x] Verify entries created without spam flags
  - [x] Delete test entries after verification

## Review Follow-ups (AI)

- [x] [AI-Review][HIGH] Checkbox choice-to-input ID mapping breaks after choice #9 (skips 10/20, causing duplicate IDs) → test_data can be wrong for checkbox fields with many choices. **Fixed** (corrected checkbox input ID mapping). [di-form-buddy.php]
- [x] [AI-Review][MEDIUM] `--list`/`--inspect`/`--generate-configs` still require a secret, blocking non-submit workflows unnecessarily. **Fixed** (secret required only for submission). [di-form-buddy.php]
- [x] [AI-Review][MEDIUM] `json_encode()` errors are not checked; invalid UTF-8 can write empty/invalid config files without warning. **Fixed** (check json_encode + log error). [di-form-buddy.php]
- [x] [AI-Review][LOW] `slugify()` can produce empty slugs for non-alphanumeric titles, leading to filenames like `01-.json`. **Fixed** (fallback slug). [di-form-buddy.php]
- [x] [AI-Review][MEDIUM] Working tree contains unrelated non-app changes not documented in story File List; review transparency gap. **Fixed** (documented in File List/Change Log). [story]
- [x] [AI-Review][MEDIUM] M1: `list` field type handled in code but not documented in Dev Notes table — added to reference
- [x] [AI-Review][MEDIUM] M2: `consent` field type not explicitly handled — added handler returning "1"
- [x] [AI-Review][MEDIUM] M3: Checkbox test data only selects first choice — documented in Known Limitations
- [x] [AI-Review][MEDIUM] M4: `multiselect` handled in code but not documented in Dev Notes table — added to reference
- [x] [AI-Review][LOW] L1: `post_*` field types (post_title, post_content, etc.) not documented — added to reference + code handlers
- [x] [AI-Review][LOW] L2: `product` and pricing field types not documented — added to reference + code handlers
- [x] [AI-Review][LOW] L3: `coupon` field type not handled — added handler (empty string)
- [x] [AI-Review][LOW] L4: Entry count inconsistency — fixed "4 forms" to "5 forms"
- [x] [AI-Review][LOW] L5: Story claims file "modified" but git shows untracked — fixed to "new file"
- [x] [AI-Review][LOW] L6: `captcha` skip doesn't clarify reCAPTCHA plugin type names — added note

## Review Follow-ups (AI) — 2026-02-05

- [x] [AI-Review][CRITICAL] Checkbox choice-to-input ID mapping still incorrect for choices ≥19 (e.g., 19th maps to 20). **Fixed** (corrected skip formula to avoid IDs ending in 0). [di-form-buddy.php]
- [x] [AI-Review][HIGH] Story File List claimed tracked modifications, but git shows `di-form-buddy.php` and this story file as untracked; updated File List wording + Change Log. **Fixed** (documentation corrected). [story]
- [x] [AI-Review][HIGH] Story claimed working-tree change documentation, but large non-app changes remain undocumented; added explicit note to File List about excluded non-app files. **Fixed** (transparency note added). [story]
- [x] [AI-Review][MEDIUM] No regression tests for checkbox input-id mapping; added explicit test coverage for choices 1–25 and skip-10 rule. **Fixed** (new tests). [tests/test-di-form-buddy.php]
## Review Follow-ups (AI) — 2026-02-05 Session 2

- [x] [AI-Review][HIGH] H1: Test file `tests/test-di-form-buddy.php` not documented in story File List — **Fixed** (added to File List). [story]
- [x] [AI-Review][MEDIUM] M1: `di_form_buddy_slugify()` can produce duplicate slugs causing silent config overwrite — **Fixed** (added collision detection with `-id{N}` suffix). [di-form-buddy.php:920]
- [x] [AI-Review][MEDIUM] M2: `--inspect` mode generates HMAC unnecessarily — **Fixed** (reordered logic, inspect exits before HMAC). [di-form-buddy.php:1055]
- [x] [AI-Review][MEDIUM] M3: No test coverage for config generation support functions — **Fixed** (added 12 tests for slugify, validate_output_dir, constant). [tests/]
- [x] [AI-Review][MEDIUM] M4: Sprint status prematurely set to "done" before this review — **Fixed** (reverted to "review"). [sprint-status.yaml]
- [x] [AI-Review][LOW] L1: Test helper functions not prefixed per project rules — **Accepted** (test-only code exemption documented). [tests/]
- [x] [AI-Review][LOW] L2: Magic strings for defaults — **Fixed** (extracted all to constants: `DI_FORM_BUDDY_DEFAULT_*`). [di-form-buddy.php:19-26]
- [x] [AI-Review][LOW] L4: Hardcoded email not configurable — **Fixed** (added `DI_FORM_BUDDY_EMAIL` env var support via `di_form_buddy_get_email()`). [di-form-buddy.php, .env.example]
- [x] [AI-Review][LOW] L3: No path validation for `--output-dir` — **Fixed** (added `di_form_buddy_validate_output_dir()` rejecting traversal and system paths). [di-form-buddy.php:867]

## Dev Notes

### Test Email Configuration
- **Default:** `di.form.buddy@gmail.com` — confirmed working inbox
- **Configurable:** Set `DI_FORM_BUDDY_EMAIL` env var to override (e.g., for team-specific testing)
- All notifications are rerouted to this address — never sent to actual dealers

### Complete Field Type Reference (from albanytoyota.net + GF docs)

| Type | Data | Sub-Inputs | Choices | Test Data Strategy |
|------|------|------------|---------|-------------------|
| `text` | Yes | No | No | `"Test {Label}"` or inferred from label |
| `textarea` | Yes | No | No | `"Automated test via DI Form Buddy"` |
| `email` | Yes | No | No | `"di.form.buddy@gmail.com"` |
| `phone` | Yes | No | No | `"555-555-1234"` |
| `number` | Yes | No | No | Middle of rangeMin/rangeMax or `"50"` |
| `date` | Yes | No | No | Current date `YYYY-MM-DD` |
| `time` | Yes | No | No | `"10:30 am"` or `"14:30"` (24h) |
| `website` | Yes | No | No | `"https://example.com"` |
| `hidden` | Yes | No | No | `""` (empty — frontend JS populates) |
| `tp-referral` | Yes | No | No | `""` (DI tracking field) |
| `name` (simple) | Yes | No | No | `"Test"` or `"User"` based on label |
| `name` (normal) | Yes | Yes | No | `input_{id}_3`="Test", `input_{id}_6`="User" |
| `address` | Yes | Yes | No | See address mapping below |
| `select` | Yes | No | Yes | First choice `value` |
| `radio` | Yes | No | Yes | First choice `value` |
| `multiselect` | Yes | No | Yes | First choice `value` |
| `checkbox` | Yes | Yes | Yes | `input_{id}_1` = first choice value (see limitation below) |
| `consent` | Yes | No | No | `"1"` (checked/accepted) |
| `fileupload` | Yes | No | No | **SKIP** in test_data (note in skipped_fields) |
| `list` | Yes | No | No | **SKIP** in test_data (complex repeater, note in skipped_fields) |
| `html` | **No** | — | — | **SKIP** entirely (display only) |
| `section` | **No** | — | — | **SKIP** entirely (divider) |
| `page` | **No** | — | — | **SKIP** entirely (pagination) |
| `captcha` | **No** | — | — | **SKIP** entirely (GF built-in captcha) |
| `post_title` | Yes | No | No | `"Test Post Title"` |
| `post_content` | Yes | No | No | `"Automated test content via DI Form Buddy"` |
| `post_excerpt` | Yes | No | No | `"Test excerpt"` |
| `post_category` | Yes | No | Yes | First category value |
| `post_tags` | Yes | No | No | `"test, automated"` |
| `post_image` | Yes | No | No | **SKIP** in test_data (requires file) |
| `post_custom_field` | Yes | No | No | `"Test custom value"` |
| `product` | Yes | No | Yes | First product choice or `"Test Product"` |
| `quantity` | Yes | No | No | `"1"` |
| `option` | Yes | No | Yes | First option value |
| `shipping` | Yes | No | Yes | First shipping choice |
| `total` | **No** | — | — | **SKIP** (calculated field) |
| `coupon` | Yes | No | No | `""` (empty — requires valid coupon code) |
| `password` | Yes | No | No | `"TestPass123!"` |
| `creditcard` | Yes | Yes | No | **SKIP** in test_data (sensitive/PCI) |
| `dropbox` | Yes | No | No | **SKIP** in test_data (requires-file) |
| `image_choice` | Yes | No | Yes | First choice `value` |
| `multiple_choice` | Yes | No | Yes | First choice `value` |
| `price` | Yes | No | No | `"10.00"` |
| `singleproduct` | Yes | No | No | `"Test Product"` |
| `hiddenproduct` | Yes | No | No | `""` (hidden) |
| `singleshipping` | Yes | No | No | `"Standard"` |
| `calculation` | **No** | — | — | **SKIP** (calculated field) |

**Note on reCAPTCHA plugins:** The `gravity-forms-no-captcha-recaptcha` plugin uses field type `captcha` with `captchaType` property. The skip logic handles this via the `captcha` type check.

### Known Limitations

- **Checkbox fields:** Test data includes a value for each checkbox choice. If a form expects only a subset of choices, edit the generated config to remove extras.
- **List fields:** Complex repeater fields are included in `fields` array but excluded from `test_data` — requires manual config population if needed.
- **File uploads:** Included in `fields` array but excluded from `test_data` — cannot be auto-populated, requires manual upload.
- **Coupon fields:** Requires valid coupon code — left empty in test_data, add manually if needed.

### Input Naming Convention (GF Standard)
- Simple field: `input_{field_id}` (e.g., `input_1`)
- Sub-input field: `input_{field_id}_{sub_id}` with dot replaced by underscore
  - Example: field 53.1 → `input_53_1`
  - Example: field 1.3 → `input_1_3`

### Address Field Mapping
```
input_{id}_1 = "123 Test Street"    (Street Address)
input_{id}_2 = ""                    (Address Line 2 - optional)
input_{id}_3 = "Test City"          (City)
input_{id}_4 = "IL"                  (State)
input_{id}_5 = "60601"              (Zip)
input_{id}_6 = "United States"      (Country - if not hidden)
```

### Name Field Mapping (normal/extended format)
```
input_{id}_3 = "Test"    (First Name)
input_{id}_6 = "User"    (Last Name)
```
Note: Simple name format has no sub-inputs — use `input_{id}` directly.

### Output JSON Structure
```json
{
  "form_id": 1,
  "form_name": "Get Today's Price",
  "generated_at": "2026-02-04T12:00:00Z",
  "generated_from": "www.albanytoyota.net",
  "field_count": 14,
  "fields": [
    {"id": 1, "type": "name", "label": "First Name", "required": true, "input_name": "input_1"},
    {"id": "53.1", "type": "address", "label": "Street Address", "required": true, "input_name": "input_53_1", "parent_id": 53}
  ],
  "required_fields": [1, 4, 2, 3],
  "test_data": {
    "input_1": "Test",
    "input_4": "User",
    "input_2": "di.form.buddy@gmail.com"
  },
  "skipped_fields": [
    {"id": 99, "type": "html", "reason": "display-only"},
    {"id": 50, "type": "fileupload", "reason": "requires-file"}
  ]
}
```

### PHP 7.2 Constraints
- No arrow functions (`fn() =>`)
- No typed properties
- No `??=`
- Use `json_encode($data, JSON_PRETTY_PRINT)` for output
- Use `preg_replace('/[^a-z0-9]+/', '-', strtolower($title))` for slug

### Impact on Epic 2
- **Story 2.3 (All Form Configs) is now OBSOLETE** — absorbed into this story
- One command generates configs for any site, any number of forms

### References
- [Source: Story 1.1 — existing --inspect and --list implementation]
- [Source: docs/project-context.md — PHP 7.2, JSON-only constraints]
- [Source: GF field class — input naming: `input_` + str_replace('.', '_', $input_id)]
- [Live verification: pod46/albanytoyota.net — 23 forms, 17 field types]

## Dev Agent Record

### Agent Model Used

Claude Opus 4.5 (claude-opus-4-5-20251101)

### Debug Log References

- 2026-02-04: Full field type inventory from albanytoyota.net (17 types across 23 forms)
- 2026-02-04: Verified input naming convention from GF source (`input_{id}_{subid}`)
- 2026-02-04: Test submission entry 1596 confirmed `input_X` format works, deleted after

### Completion Notes List

- 2026-02-05: Implemented `--generate-configs` flag with complete GF field type mapping
- 2026-02-05: Added `di_form_buddy_infer_from_label()` for label-aware test data (zip, phone, etc.)
- 2026-02-05: Verified against GF platform source code for deterministic field handling
- 2026-02-05: Tested on albanytoyota.net: 23 forms generated, 5 forms submitted successfully (entries 1597-1601)
- 2026-02-05: All test entries deleted after verification

### Change Log

- 2026-02-04: Story rewritten — replaced manual config creation with automated `--generate-configs` feature
- 2026-02-04: Absorbs Story 2.3 (all form configs) — one command handles any site
- 2026-02-05: Implementation complete — all 6 tasks finished, integration tests passed
- 2026-02-04: **Code Review** — 10 issues found (4 medium, 6 low), all auto-fixed
- 2026-02-05: **Code Review** — fixed checkbox input ID mapping, relaxed secret requirement for non-submit modes, added JSON encode error handling, slug fallback, and documented unrelated working tree changes
- 2026-02-05: **Code Review** — corrected checkbox input ID mapping for >18 choices and clarified file tracking/transparency notes
- 2026-02-05: **Tests** — added regression tests for checkbox input-id mapping, field inclusion/exclusion, and field-type value coverage
- 2026-02-05: **Mapping** — aligned field-type handling to full GF Field Classes list (core + add-ons)
- 2026-02-05: **Code Review Session 2** — 8 issues found (1 high, 4 medium, 3 low), all auto-fixed: added test file to File List, slug collision detection, path validation, output dir constant, reordered inspect/HMAC logic, added 12 new tests
- 2026-02-05: **Configurable Values** — extracted all hardcoded test values to constants (`DI_FORM_BUDDY_DEFAULT_*`), added `DI_FORM_BUDDY_EMAIL` env var for configurable email, updated .env.example, added 8 new constant tests (now 164 total)

### File List

- di-form-buddy.php (untracked/new — main implementation)
- tests/test-di-form-buddy.php (untracked/new — comprehensive test suite, 164 assertions)
- .env.example (untracked/new — environment configuration template)
- _bmad-output/implementation-artifacts/1-2-form-configurations.md (untracked/new — story file with review follow-ups)
- NOTE: Non-app working-tree changes (`.codex/*`, `_bmad/*`, `docs/knowledge/*`) excluded as not application source code.

## Senior Developer Review (AI)

**Reviewer:** Claude Opus 4.5
**Date:** 2026-02-04 (Session 1), 2026-02-05 (Session 2)
**Outcome:** ✅ APPROVED (after fixes)

### Review Summary — Session 1

Initial review found 10 issues (4 MEDIUM, 6 LOW) related to undocumented field types and documentation inconsistencies. All issues were auto-fixed in the same session.

| ID | Severity | Issue | Resolution |
|----|----------|-------|------------|
| M1 | MEDIUM | `list` field type undocumented | Added to reference table, added code handler |
| M2 | MEDIUM | `consent` field type missing handler | Added explicit handler returning "1" |
| M3 | MEDIUM | Checkbox limitation undocumented | Added to Known Limitations section |
| M4 | MEDIUM | `multiselect` undocumented | Added to reference table |
| L1 | LOW | `post_*` fields undocumented | Added 7 post field types to reference + code |
| L2 | LOW | Pricing fields undocumented | Added product, quantity, option, shipping, total, coupon |
| L3 | LOW | `coupon` field missing handler | Added handler (empty string) |
| L4 | LOW | Entry count typo | Fixed "4 forms" → "5 forms" |
| L5 | LOW | File List said "modified" | Changed to "new file" |
| L6 | LOW | reCAPTCHA plugin type unclear | Added clarifying note |

### Review Summary — Session 2 (EXTREMELY THOROUGH)

Deep adversarial review found 8 additional issues (1 HIGH, 4 MEDIUM, 3 LOW) related to documentation, code safety, and test coverage. All issues auto-fixed.

| ID | Severity | Issue | Resolution |
|----|----------|-------|------------|
| H1 | HIGH | Test file not in File List | Added tests/ to File List |
| M1 | MEDIUM | Slug collision silent overwrite | Added collision detection with `-id{N}` suffix |
| M2 | MEDIUM | HMAC generated for --inspect | Reordered logic, inspect exits before HMAC |
| M3 | MEDIUM | No tests for config gen functions | Added 12 tests for slugify, validate_output_dir |
| M4 | MEDIUM | Sprint status prematurely "done" | Reverted to "review" during review |
| L1 | LOW | Test functions unprefixed | Documented as acceptable exception |
| L2 | LOW | Magic string for output dir | Extracted to constant |
| L3 | LOW | No path validation for --output-dir | Added di_form_buddy_validate_output_dir() |

### Final Verification

- All 8 Acceptance Criteria verified as implemented ✓
- All 6 Tasks verified as complete ✓
- Code follows PHP 7.2 constraints ✓
- No security issues found ✓
- 164/164 tests passing ✓
- Git vs Story File List aligned ✓
- All hardcoded values extracted to constants ✓
- Email configurable via env var ✓
