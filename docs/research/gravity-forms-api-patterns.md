# Gravity Forms API (GFAPI) Implementation Patterns

## Overview

This document contains implementation-critical documentation for the Gravity Forms PHP API (GFAPI), focusing on programmatic form submission and entry creation.

## GFAPI::submit_form()

### Method Signature

```php
public static function submit_form(
    $form_id,
    $input_values,
    $field_values = array(),
    $target_page = 0,
    $source_page = 1
)
```

### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| `$form_id` | integer | The ID of the form to submit |
| `$input_values` | array | Associative array with field input names as keys and their values |
| `$field_values` | array | Optional dynamic population parameters (default: empty array) |
| `$target_page` | integer | Optional page to load after validation passes (default: 0) |
| `$source_page` | integer | Optional current page number for multi-page forms (default: 1) |

### Return Value

Returns an associative array containing:

- **is_valid** (bool): Form validation result
- **is_spam** (bool): Whether entry was marked as spam
- **form** (Form Object): The processed form
- **validation_messages** (array): Field errors when validation fails
- **page_number** (integer): Current page for multi-page forms
- **confirmation_message** (string): Resume or confirmation text
- **confirmation_type** (string): Either "message" or "redirect"
- **confirmation_redirect** (string): URL for redirect confirmations
- **entry_id** (integer): Created entry ID on success
- **resume_token** (string): Token for save-and-continue feature

### What submit_form() Does

This method creates an entry by sending input values through the **complete form submission process**, including:

1. **Save and Continue** - Saving progress for the save and continue feature
2. **Validation** - Form field validation
3. **Anti-spam checks** - Honeypot, captcha, Akismet, etc.
4. **Entry creation** - Saving the entry to the database
5. **Add-on feeds** - Third-party integration processing
6. **Notifications** - Automated email sending
7. **Confirmations** - User-facing confirmation messages
8. **Hooks** - All filters and action hooks triggered by a regular form submission

### Hooks Fired

The `gform_after_submission` hook **is fired** by `submit_form()`. This hook executes at the conclusion of form submission processing, after:
- Form validation completes
- Notifications send
- Entry creation finalizes

### Captcha Validation

By default, `submit_form()` **validates captcha fields**. However, for programmatic submissions (like REST API endpoints), you'll need to bypass captcha validation since it requires user interaction.

**Bypass captcha validation:**

```php
add_filter('gform_field_validation', function($result, $value, $form, $field) {
    if ($field->type === 'captcha' && defined('REST_REQUEST') && REST_REQUEST) {
        $result['is_valid'] = true;
        $result['message'] = '';
    }
    return $result;
}, 10, 4);
```

### Input Values Format

#### Simple Fields

```php
// Single line text (field ID 1)
$input_values['input_1'] = 'Single line text';

// Paragraph text (field ID 5)
$input_values['input_5'] = 'A paragraph of text.';

// Email (field ID 3)
$input_values['input_3'] = 'user@example.com';

// Number (field ID 7)
$input_values['input_7'] = '42';
```

#### Multi-part Fields

**Name field (field ID 2):**
```php
$input_values['input_2_3'] = 'John';      // First name
$input_values['input_2_6'] = 'Doe';       // Last name
```

**Address field (field ID 4):**
```php
$input_values['input_4_1'] = '123 Main St';     // Street
$input_values['input_4_2'] = 'Apt 5';           // Address Line 2
$input_values['input_4_3'] = 'San Francisco';   // City
$input_values['input_4_4'] = 'CA';              // State
$input_values['input_4_5'] = '94102';           // Zip/Postal Code
$input_values['input_4_6'] = 'United States';   // Country
```

#### Checkbox Fields

Checkbox fields use decimal notation (e.g., `input_5.1`, `input_5.2`) for each choice:

```php
// Checkbox field ID 5 with multiple choices
$input_values['input_5.1'] = 'Choice 1';  // First checkbox checked
$input_values['input_5.2'] = 'Choice 2';  // Second checkbox checked
// Omit unchecked boxes or set to empty string
```

#### Radio Fields

Radio buttons use the field ID with a single value:

```php
// Radio field ID 6
$input_values['input_6'] = 'Selected option value';
```

#### Save and Continue

```php
// Enable save and continue
$input_values['gform_save'] = true;
// Returns a resume_token in the response
```

### Example Usage

```php
$form_id = 1;
$input_values = array(
    'input_1' => 'John Doe',
    'input_2' => 'john@example.com',
    'input_3' => '555-1234',
    'input_4.1' => 'Option A',
    'input_4.2' => 'Option B',
);

$result = GFAPI::submit_form($form_id, $input_values);

if ($result['is_valid']) {
    $entry_id = $result['entry_id'];
    $confirmation = $result['confirmation_message'];
    // Handle success
} else {
    $errors = $result['validation_messages'];
    // Handle validation errors
}
```

### Important Limitations

- `submit_form()` isn't designed to process multiple submissions during a single request
- Requires cleanup after each call when used in loops
- Captcha fields must be explicitly bypassed for programmatic submissions

## GFAPI::add_entry()

### Method Signature

```php
public static function add_entry( $entry )
```

### Parameters

- **$entry** (array): Must include a `form_id` property. The `id` property is ignored if provided. All other properties, metadata, and field values are optional.

### Return Value

Returns an integer (the entry ID) or a `WP_Error` instance on failure.

### What add_entry() Does NOT Do

This method **bypasses** the form submission lifecycle. It does **NOT** trigger:

1. **Validation** - Form field validation is skipped
2. **Add-on feeds** - Third-party integrations don't execute
3. **Notifications** - Automated emails are not sent
4. **Confirmations** - User-facing confirmation messages don't display
5. **Hooks** - Standard submission hooks are not fired

### When to Use add_entry()

Use `add_entry()` when you:
- Need to import data directly into the database
- Are creating entries in bulk
- Have already validated the data
- Don't need notifications or add-on processing

### Manual Notifications

If you use `add_entry()` and need notifications, manually trigger them:

```php
$entry_id = GFAPI::add_entry($entry);
GFAPI::send_notifications($form, $entry, 'form_submission');
```

## submit_form() vs add_entry() - Quick Comparison

| Feature | submit_form() | add_entry() |
|---------|---------------|-------------|
| Validation | Yes | No |
| Anti-spam checks | Yes | No |
| Notifications | Yes | No |
| Confirmations | Yes | No |
| Add-on feeds | Yes | No |
| Hooks fired | Yes | No |
| Use case | Full form submission | Direct entry creation |

## Best Practices

1. **Use submit_form() for user submissions** - When accepting data from external sources that should go through the full validation and notification pipeline.

2. **Use add_entry() for imports** - When importing pre-validated data or creating entries programmatically without needing notifications.

3. **Bypass captcha for API submissions** - Always add the `gform_field_validation` filter to bypass captcha when using `submit_form()` in API endpoints.

4. **Check is_valid in responses** - Always verify the `is_valid` property in the return array before assuming success.

5. **Handle spam entries** - Check for `is_spam` in the response and in the `gform_after_submission` hook.

## References

- [Submitting Forms with the GFAPI](https://docs.gravityforms.com/submitting-forms-with-the-gfapi/)
- [Creating Entries with the GFAPI](https://docs.gravityforms.com/creating-entries-with-the-gfapi/)
- [gform_after_submission Hook](https://docs.gravityforms.com/gform_after_submission/)
- [Class GFAPI Documentation](http://inlinedocs.gravityhelp.com/class-GFAPI.html)
- [Gravity Forms Hook Reference](https://gravitywiz.com/gravity-forms-hook-reference/)
