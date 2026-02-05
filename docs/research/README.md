# Library Research Documentation

This directory contains implementation-critical documentation for external libraries and APIs used in the di-form-buddy project.

## Quick Reference

### Gravity Forms API (GFAPI)

**File:** [gravity-forms-api-patterns.md](./gravity-forms-api-patterns.md)

**Key Topics:**
- `GFAPI::submit_form()` - Full form submission with validation, notifications, hooks
- `GFAPI::add_entry()` - Direct entry creation without submission lifecycle
- Input values format for all field types (text, checkbox, radio, address, etc.)
- Captcha bypass for programmatic submissions
- Hook lifecycle and when `gform_after_submission` fires

**Quick Answer: submit_form() vs add_entry()**

| Feature | submit_form() | add_entry() |
|---------|---------------|-------------|
| Validation | ✅ Yes | ❌ No |
| Notifications | ✅ Yes | ❌ No |
| Hooks fired | ✅ Yes | ❌ No |
| Captcha validation | ✅ Yes (bypass needed for API) | ❌ No |

**Quick Answer: Does submit_form() fire gform_after_submission?**

✅ **Yes** - `submit_form()` fires the complete submission lifecycle including `gform_after_submission` hook.

**Quick Answer: Captcha Validation**

✅ **Yes** - Captcha fields are validated by default in `submit_form()`, but you must bypass them for programmatic submissions:

```php
add_filter('gform_field_validation', function($result, $value, $form, $field) {
    if ($field->type === 'captcha' && defined('REST_REQUEST') && REST_REQUEST) {
        $result['is_valid'] = true;
        $result['message'] = '';
    }
    return $result;
}, 10, 4);
```

### WordPress JWT Authentication

**File:** [wordpress-jwt-authentication.md](./wordpress-jwt-authentication.md)

**Key Topics:**
- Recommended lightweight JWT plugins
- Setup and configuration (wp-config.php, .htaccess)
- Token generation, validation, and refresh
- Protecting custom REST API endpoints
- Security best practices

**Quick Answer: Best JWT Plugin**

**Recommended:** JWT Auth by Useful Team (https://github.com/usefulteam/jwt-auth)

- Simple setup
- Refresh token support (30-day expiration)
- Access tokens expire after 10 minutes
- Device-based token rotation

**Quick Answer: Basic Setup**

1. Add to `.htaccess`:
   ```apache
   RewriteEngine on
   RewriteCond %{HTTP:Authorization} ^(.*)
   RewriteRule ^(.*) - [E=HTTP_AUTHORIZATION:%1]
   ```

2. Add to `wp-config.php`:
   ```php
   define('JWT_AUTH_SECRET_KEY', 'your-secret-key');
   define('JWT_AUTH_CORS_ENABLE', true);
   ```

3. Install plugin:
   ```bash
   wp plugin install jwt-authentication-for-wp-rest-api --activate
   ```

**Quick Answer: Protect REST API Endpoint**

```php
add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/protected', array(
        'methods'  => 'POST',
        'callback' => 'custom_callback',
        'permission_callback' => function() {
            return is_user_logged_in(); // JWT plugin handles authentication
        },
    ));
});
```

### WP-CLI Patterns

**File:** [wp-cli-patterns.md](./wp-cli-patterns.md)

**Key Topics:**
- `wp eval-file` command usage
- WordPress environment loading
- GFAPI access in CLI scripts
- Passing arguments to scripts
- Error handling and automation patterns

**Quick Answer: Can wp eval-file access GFAPI?**

✅ **Yes** - By default, `wp eval-file` loads the complete WordPress environment including all active plugins. GFAPI is fully accessible.

**Quick Answer: Basic Usage**

```bash
# Execute PHP script with full WordPress environment
wp eval-file script.php

# Pass arguments (available in $args variable)
wp eval-file script.php arg1 arg2

# Skip WordPress loading
wp eval-file script.php --skip-wordpress
```

**Quick Answer: GFAPI Example**

```php
<?php
// submit-form.php

$form_id = 1;
$input_values = array(
    'input_1' => 'John Doe',
    'input_2' => 'john@example.com',
);

$result = GFAPI::submit_form($form_id, $input_values);

if ($result['is_valid']) {
    echo "Entry created: {$result['entry_id']}\n";
    exit(0);
} else {
    echo "Validation failed\n";
    exit(1);
}
```

Execute:
```bash
wp eval-file submit-form.php
```

## Common Integration Patterns

### Pattern 1: REST API Endpoint with GFAPI Submission

```php
// Register custom endpoint
add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/submit-form', array(
        'methods'  => 'POST',
        'callback' => 'handle_form_submission',
        'permission_callback' => function() {
            return is_user_logged_in(); // JWT authentication
        },
    ));
});

// Bypass captcha for programmatic submissions
add_filter('gform_field_validation', function($result, $value, $form, $field) {
    if ($field->type === 'captcha' && defined('REST_REQUEST') && REST_REQUEST) {
        $result['is_valid'] = true;
        $result['message'] = '';
    }
    return $result;
}, 10, 4);

function handle_form_submission($request) {
    $params = $request->get_json_params();
    $form_id = $params['form_id'];
    $input_values = $params['input_values'];

    // Submit through GFAPI (triggers validation, notifications, hooks)
    $result = GFAPI::submit_form($form_id, $input_values);

    if ($result['is_valid']) {
        return array(
            'success' => true,
            'entry_id' => $result['entry_id'],
            'confirmation' => $result['confirmation_message'],
        );
    } else {
        return new WP_Error(
            'validation_failed',
            'Form validation failed',
            array(
                'status' => 400,
                'errors' => $result['validation_messages'],
            )
        );
    }
}
```

### Pattern 2: JWT-Protected Endpoint

```php
// Generate token
POST /wp-json/jwt-auth/v1/token
{
    "username": "user",
    "password": "pass"
}

// Response
{
    "success": true,
    "data": {
        "token": "eyJ0eXAiOiJKV1QiLCJhbGci...",
        "id": 1,
        "email": "user@example.com"
    }
}

// Use token in subsequent requests
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGci...
```

### Pattern 3: WP-CLI Batch Processing

```php
<?php
// batch-import.php

global $args;
$csv_file = $args[0];
$form_id = (int)$args[1];

$handle = fopen($csv_file, 'r');
$headers = fgetcsv($handle);

while (($row = fgetcsv($handle)) !== FALSE) {
    $input_values = array_combine($headers, $row);
    $result = GFAPI::submit_form($form_id, $input_values);

    if ($result['is_valid']) {
        WP_CLI::success("Entry {$result['entry_id']} created");
    } else {
        WP_CLI::warning("Entry failed validation");
    }
}

fclose($handle);
```

Execute:
```bash
wp eval-file batch-import.php data.csv 1
```

## Troubleshooting

### JWT Issues

**Problem:** Authorization header not passed
**Solution:** Add .htaccess rewrite rule (see JWT documentation)

**Problem:** Invalid token signature
**Solution:** Verify JWT_AUTH_SECRET_KEY is set in wp-config.php

**Problem:** Token expired immediately
**Solution:** Check server time synchronization; adjust expiration with `jwt_auth_expire` filter

### GFAPI Issues

**Problem:** Captcha validation failing in API submissions
**Solution:** Add `gform_field_validation` filter to bypass captcha (see Gravity Forms documentation)

**Problem:** Notifications not sending
**Solution:** Use `GFAPI::submit_form()` instead of `add_entry()` - only `submit_form()` triggers notifications

**Problem:** Hooks not firing
**Solution:** Use `GFAPI::submit_form()` - `add_entry()` bypasses the submission lifecycle

### WP-CLI Issues

**Problem:** GFAPI class not found
**Solution:** Ensure Gravity Forms plugin is active and not using `--skip-wordpress` flag

**Problem:** Global variables undefined
**Solution:** Explicitly declare globals: `global $wpdb;`

**Problem:** Script timeout on large operations
**Solution:** Increase PHP timeout: `php -d max_execution_time=600 $(which wp) eval-file script.php`

## Implementation Checklist

When implementing form submission via REST API:

- [ ] Install and configure JWT authentication plugin
- [ ] Configure wp-config.php with JWT secret key
- [ ] Add .htaccess rules for Authorization header
- [ ] Register custom REST API endpoint
- [ ] Add JWT permission_callback to endpoint
- [ ] Add gform_field_validation filter to bypass captcha
- [ ] Use GFAPI::submit_form() for full submission lifecycle
- [ ] Handle validation errors in response
- [ ] Test token generation and validation
- [ ] Test form submission with valid JWT token
- [ ] Verify notifications are sent
- [ ] Verify gform_after_submission hook fires

## References

### Official Documentation

- [Gravity Forms GFAPI Documentation](https://docs.gravityforms.com/api-functions/)
- [WordPress REST API Handbook](https://developer.wordpress.org/rest-api/)
- [WP-CLI Command Reference](https://developer.wordpress.org/cli/commands/)
- [JWT RFC 7519 Specification](https://tools.ietf.org/html/rfc7519)

### Plugin Repositories

- [JWT Auth by Useful Team](https://github.com/usefulteam/jwt-auth)
- [JWT Authentication for WP REST API](https://wordpress.org/plugins/jwt-authentication-for-wp-rest-api/)
- [Gravity Forms Hook Reference](https://gravitywiz.com/gravity-forms-hook-reference/)

## Last Updated

2026-02-03
