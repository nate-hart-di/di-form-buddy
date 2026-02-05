# Quick Answers - GFAPI & WordPress Integration

## GFAPI::submit_form()

### Method Signature

```php
public static function submit_form(
    $form_id,           // integer: Form ID
    $input_values,      // array: Input values (e.g., 'input_1' => 'value')
    $field_values = [], // array: Dynamic population params (optional)
    $target_page = 0,   // integer: Target page for multi-page forms (optional)
    $source_page = 1    // integer: Current page for multi-page forms (optional)
)
```

### What $input_values Looks Like

```php
$input_values = array(
    // Simple text field (field ID 1)
    'input_1' => 'Single line text',

    // Email field (field ID 3)
    'input_3' => 'user@example.com',

    // Name field with sub-inputs (field ID 2)
    'input_2_3' => 'John',    // First name
    'input_2_6' => 'Doe',     // Last name

    // Address field with sub-inputs (field ID 4)
    'input_4_1' => '123 Main St',     // Street
    'input_4_2' => 'Apt 5',           // Line 2
    'input_4_3' => 'San Francisco',   // City
    'input_4_4' => 'CA',              // State
    'input_4_5' => '94102',           // Zip
    'input_4_6' => 'United States',   // Country

    // Checkbox field (field ID 5) - use decimal notation
    'input_5.1' => 'Choice 1',  // First checkbox
    'input_5.2' => 'Choice 2',  // Second checkbox

    // Radio field (field ID 6)
    'input_6' => 'Selected option',

    // Optional: Enable save and continue
    'gform_save' => true,
);
```

### What Hooks Does It Fire?

`submit_form()` fires the **complete submission lifecycle**, including:

1. **Validation hooks:**
   - `gform_field_validation`
   - `gform_validation`

2. **Pre-submission hooks:**
   - `gform_pre_submission`

3. **Post-submission hooks:**
   - `gform_after_submission` ✅ **YES, THIS FIRES**
   - `gform_post_submission`

4. **Entry creation hooks:**
   - `gform_entry_created`
   - `gform_entry_post_save`

5. **Notification hooks:**
   - `gform_pre_send_email`
   - `gform_post_send_email`

### Does It Go Through Validation?

✅ **YES** - Full validation including:
- Required field validation
- Field-specific validation (email format, number range, etc.)
- Custom validation via `gform_field_validation` filter
- Conditional logic validation

### Does It Validate Captcha Fields?

✅ **YES** - Captcha fields ARE validated by default.

**GOTCHA:** For programmatic/API submissions, captcha validation will FAIL because there's no user interaction. You MUST bypass captcha:

```php
add_filter('gform_field_validation', function($result, $value, $form, $field) {
    if ($field->type === 'captcha' && defined('REST_REQUEST') && REST_REQUEST) {
        $result['is_valid'] = true;
        $result['message'] = '';
    }
    return $result;
}, 10, 4);
```

### Return Value

```php
array(
    'is_valid' => true,                    // bool: Validation result
    'is_spam' => false,                    // bool: Spam detection result
    'form' => [Form Object],               // Complete form data
    'validation_messages' => array(),      // Field errors (if is_valid = false)
    'page_number' => 0,                    // Current page (multi-page forms)
    'confirmation_message' => 'Thanks!',   // Confirmation text
    'confirmation_type' => 'message',      // 'message' or 'redirect'
    'confirmation_redirect' => '',         // URL (if type = 'redirect')
    'entry_id' => 123,                     // Created entry ID
    'resume_token' => 'abc123...',         // Token (if gform_save = true)
)
```

## GFAPI::submit_form() vs GFAPI::add_entry()

### Complete Comparison

| Feature | submit_form() | add_entry() |
|---------|---------------|-------------|
| **Validation** | ✅ Full validation | ❌ No validation |
| **Required fields** | ✅ Checked | ❌ Not checked |
| **Anti-spam** | ✅ Yes (honeypot, captcha, Akismet) | ❌ No |
| **Entry creation** | ✅ Yes | ✅ Yes |
| **Notifications** | ✅ Sent automatically | ❌ Must call manually |
| **Confirmations** | ✅ Generated | ❌ No |
| **Add-on feeds** | ✅ Processed | ❌ Not processed |
| **gform_after_submission** | ✅ Fires | ❌ Does not fire |
| **gform_pre_submission** | ✅ Fires | ❌ Does not fire |
| **All submission hooks** | ✅ Fire | ❌ Do not fire |
| **Save & continue** | ✅ Supported | ❌ Not supported |
| **Multi-page forms** | ✅ Supported | ❌ Not supported |
| **Use case** | User submissions via API | Bulk imports, data migration |

### Which One to Use?

**Use `submit_form()` when:**
- Accepting form data from external sources
- Need validation and error messages
- Want notifications sent automatically
- Need the full submission lifecycle (hooks, add-ons, etc.)
- Building a REST API endpoint for form submissions

**Use `add_entry()` when:**
- Importing pre-validated data
- Migrating entries from another system
- Creating test data
- Don't need notifications or add-on processing
- Performance is critical (bulk operations)

### If Using add_entry() and Need Notifications

```php
// Create entry
$entry_id = GFAPI::add_entry($entry);

// Manually send notifications
if (!is_wp_error($entry_id)) {
    $form = GFAPI::get_form($entry['form_id']);
    $entry = GFAPI::get_entry($entry_id);
    GFAPI::send_notifications($form, $entry, 'form_submission');
}
```

## WordPress REST API + JWT Authentication

### Standard Approach

The standard lightweight approach uses dedicated JWT plugins that handle token generation and validation.

### Recommended Plugin: JWT Auth by Useful Team

**GitHub:** https://github.com/usefulteam/jwt-auth

**Why this one:**
- Lightweight (no heavy dependencies)
- Simple setup
- Refresh token support
- Active maintenance
- Well-documented

### Setup Steps

#### 1. .htaccess Configuration

Add this to enable the Authorization header:

```apache
RewriteEngine on
RewriteCond %{HTTP:Authorization} ^(.*)
RewriteRule ^(.*) - [E=HTTP_AUTHORIZATION:%1]
```

For WPEngine hosting:
```apache
SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1
```

#### 2. wp-config.php Configuration

Add before "That's all, stop editing!":

```php
define('JWT_AUTH_SECRET_KEY', 'your-top-secret-key-here');
define('JWT_AUTH_CORS_ENABLE', true);
```

Generate secret key: https://api.wordpress.org/secret-key/1.1/salt/

#### 3. Install Plugin

```bash
wp plugin install jwt-authentication-for-wp-rest-api --activate
```

Or manually via WordPress admin: Plugins → Add New → Search "JWT Auth"

### JWT Endpoints

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/wp-json/jwt-auth/v1/token` | POST | Generate token |
| `/wp-json/jwt-auth/v1/token/validate` | POST | Validate token |
| `/wp-json/jwt-auth/v1/token/refresh` | POST | Refresh token |

### Generate Token

**Request:**
```bash
curl -X POST https://example.com/wp-json/jwt-auth/v1/token \
  -H "Content-Type: application/json" \
  -d '{
    "username": "admin",
    "password": "password123"
  }'
```

**Response:**
```json
{
  "success": true,
  "statusCode": 200,
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "id": 1,
    "email": "admin@example.com"
  }
}
```

### Use Token

Include token in Authorization header for all protected requests:

```bash
curl https://example.com/wp-json/custom/v1/endpoint \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
```

### Protect Custom Endpoint - Simple Method

The JWT plugin automatically handles authentication. Just check if user is logged in:

```php
add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/protected', array(
        'methods'  => 'POST',
        'callback' => 'protected_callback',
        'permission_callback' => function() {
            // JWT plugin handles token validation automatically
            return is_user_logged_in();
        },
    ));
});

function protected_callback($request) {
    $user_id = get_current_user_id();

    return array(
        'success' => true,
        'message' => 'Authenticated',
        'user_id' => $user_id,
    );
}
```

### Protect Custom Endpoint - Manual Validation

If you need more control:

```php
function validate_jwt_permission() {
    $auth_header = isset($_SERVER['HTTP_AUTHORIZATION'])
        ? $_SERVER['HTTP_AUTHORIZATION']
        : '';

    if (empty($auth_header)) {
        return new WP_Error(
            'no_auth_header',
            'Authorization header missing',
            array('status' => 401)
        );
    }

    // JWT plugin automatically validates the token
    // If token is valid, is_user_logged_in() returns true
    if (!is_user_logged_in()) {
        return new WP_Error(
            'invalid_token',
            'Invalid or expired token',
            array('status' => 401)
        );
    }

    return true;
}

add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/protected', array(
        'methods'  => 'POST',
        'callback' => 'protected_callback',
        'permission_callback' => 'validate_jwt_permission',
    ));
});
```

### Token Expiration

- **Access tokens:** 10 minutes (default)
- **Refresh tokens:** 30 days (sent as HTTP-only cookie)

To change access token expiration:
```php
add_filter('jwt_auth_expire', function($expire) {
    return time() + (HOUR_IN_SECONDS * 2); // 2 hours
});
```

### Alternative Lightweight Approach (No Plugin)

If you prefer no dependencies, use the Firebase JWT library:

```bash
composer require firebase/php-jwt
```

```php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Generate token
$issued_at = time();
$expiration = $issued_at + 3600; // 1 hour
$payload = array(
    'iss' => get_bloginfo('url'),
    'iat' => $issued_at,
    'exp' => $expiration,
    'data' => array(
        'user_id' => $user_id,
    ),
);

$token = JWT::encode($payload, JWT_AUTH_SECRET_KEY, 'HS256');

// Validate token
try {
    $decoded = JWT::decode($token, new Key(JWT_AUTH_SECRET_KEY, 'HS256'));
    $user_id = $decoded->data->user_id;
} catch (Exception $e) {
    // Invalid token
}
```

**Caveat:** This requires more manual implementation but gives you complete control.

## WP-CLI eval-file

### Can It Access GFAPI?

✅ **YES** - Full WordPress environment is loaded by default, including:
- WordPress core
- All active plugins (including Gravity Forms)
- Current theme
- Database connection

The `plugins_loaded` action has already fired, so GFAPI is fully available.

### Basic Usage

```bash
# Execute PHP file
wp eval-file script.php

# Pass arguments (available in $args variable)
wp eval-file script.php arg1 arg2

# Skip WordPress loading (GFAPI won't be available)
wp eval-file script.php --skip-wordpress
```

### Example Script

```php
<?php
// submit-form.php

// Global variables need explicit declaration
global $wpdb;

// Access command line arguments
global $args;
$form_id = isset($args[0]) ? (int)$args[0] : 1;

// GFAPI is automatically available (no require/include needed)
$input_values = array(
    'input_1' => 'Test Name',
    'input_2' => 'test@example.com',
);

$result = GFAPI::submit_form($form_id, $input_values);

if ($result['is_valid']) {
    echo "✓ Entry created successfully\n";
    echo "Entry ID: {$result['entry_id']}\n";
    exit(0);
} else {
    echo "✗ Validation failed\n";
    print_r($result['validation_messages']);
    exit(1);
}
```

**Execute:**
```bash
wp eval-file submit-form.php 1
```

### Global Variables

Because code executes within a method, globals must be declared:

```php
<?php
global $wpdb, $wp_version;

echo "WordPress version: $wp_version\n";
echo "Database prefix: {$wpdb->prefix}\n";
```

### Error Handling

Always include error handling:

```php
<?php
$result = GFAPI::submit_form($form_id, $input_values);

if (is_wp_error($result)) {
    echo "Error: " . $result->get_error_message() . "\n";
    exit(1);
}

if (!$result['is_valid']) {
    echo "Validation failed\n";
    exit(1);
}

echo "Success\n";
exit(0);
```

### WP-CLI Helper Functions

```php
<?php
// Success message (green)
WP_CLI::success("Entry created!");

// Error message (red) and exit
WP_CLI::error("Form not found!");

// Warning message (yellow)
WP_CLI::warning("Some entries failed");

// Regular line
WP_CLI::line("Processing...");

// Progress bar
$progress = WP_CLI\Utils\make_progress_bar('Importing', 100);
for ($i = 0; $i < 100; $i++) {
    // Process
    $progress->tick();
}
$progress->finish();
```

## Complete Integration Example

### REST API Endpoint with GFAPI + JWT

```php
<?php
// In your plugin or theme's functions.php

// 1. Register custom REST API endpoint
add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/submit-form', array(
        'methods'  => 'POST',
        'callback' => 'handle_form_submission',
        'permission_callback' => function() {
            // JWT plugin validates token automatically
            return is_user_logged_in();
        },
    ));
});

// 2. Bypass captcha for programmatic submissions
add_filter('gform_field_validation', function($result, $value, $form, $field) {
    if ($field->type === 'captcha' && defined('REST_REQUEST') && REST_REQUEST) {
        $result['is_valid'] = true;
        $result['message'] = '';
    }
    return $result;
}, 10, 4);

// 3. Handle form submission
function handle_form_submission($request) {
    $params = $request->get_json_params();

    // Validate required params
    if (empty($params['form_id']) || empty($params['input_values'])) {
        return new WP_Error(
            'missing_params',
            'form_id and input_values are required',
            array('status' => 400)
        );
    }

    $form_id = (int)$params['form_id'];
    $input_values = $params['input_values'];

    // Submit through GFAPI (full submission lifecycle)
    $result = GFAPI::submit_form($form_id, $input_values);

    // Handle WP_Error
    if (is_wp_error($result)) {
        return new WP_Error(
            'submission_error',
            $result->get_error_message(),
            array('status' => 500)
        );
    }

    // Handle validation errors
    if (!$result['is_valid']) {
        return new WP_Error(
            'validation_failed',
            'Form validation failed',
            array(
                'status' => 400,
                'errors' => $result['validation_messages'],
            )
        );
    }

    // Handle spam
    if ($result['is_spam']) {
        return new WP_Error(
            'spam_detected',
            'Submission marked as spam',
            array('status' => 400)
        );
    }

    // Success response
    return array(
        'success' => true,
        'entry_id' => $result['entry_id'],
        'confirmation' => array(
            'type' => $result['confirmation_type'],
            'message' => $result['confirmation_message'],
            'redirect' => $result['confirmation_redirect'],
        ),
    );
}
```

### Client Usage

```javascript
// 1. Get JWT token
const tokenResponse = await fetch('https://example.com/wp-json/jwt-auth/v1/token', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    username: 'user',
    password: 'pass'
  })
});

const { data: { token } } = await tokenResponse.json();

// 2. Submit form with JWT token
const submitResponse = await fetch('https://example.com/wp-json/custom/v1/submit-form', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${token}`
  },
  body: JSON.stringify({
    form_id: 1,
    input_values: {
      'input_1': 'John Doe',
      'input_2': 'john@example.com',
      'input_3': '555-1234'
    }
  })
});

const result = await submitResponse.json();
console.log('Entry created:', result.entry_id);
```

## Key Gotchas

### GFAPI Gotchas

1. **Captcha will fail** - Always bypass for programmatic submissions
2. **submit_form() not for loops** - Not designed for bulk operations
3. **Multi-page forms** - Need to set `$source_page` and `$target_page`
4. **Checkbox format** - Use decimal notation: `input_5.1`, not `input_5_1`

### JWT Gotchas

1. **Authorization header blocked** - Add .htaccess rules
2. **Secret key missing** - Returns 500 error, check wp-config.php
3. **CORS issues** - Enable with `JWT_AUTH_CORS_ENABLE`
4. **Token size** - JWT tokens can be large, consider payload size
5. **Can't revoke tokens** - Use short expiration times

### WP-CLI Gotchas

1. **Globals not available** - Must explicitly declare: `global $wpdb;`
2. **Not global scope** - Code runs within a method
3. **Skip WordPress flag** - GFAPI won't work with `--skip-wordpress`
4. **Memory limits** - Large operations may need: `--memory-limit=512M`

## Documentation Files

- **[gravity-forms-api-patterns.md](./gravity-forms-api-patterns.md)** - Complete GFAPI reference
- **[wordpress-jwt-authentication.md](./wordpress-jwt-authentication.md)** - JWT setup and usage
- **[wp-cli-patterns.md](./wp-cli-patterns.md)** - WP-CLI examples and patterns
- **[README.md](./README.md)** - Overview and integration patterns

---

**Last Updated:** 2026-02-03
