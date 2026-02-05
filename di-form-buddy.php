#!/usr/bin/env php
<?php
/**
 * DI Form Buddy — POC Bootstrap Submit Script
 *
 * Bootstraps WordPress on a DI pod and submits a Gravity Forms entry
 * with HMAC authentication and reCAPTCHA bypass.
 *
 * Usage:
 *   php di-form-buddy.php --site=example.com --form=1 --secret=KEY --config=path/to/config.json
 *   php di-form-buddy.php --site=example.com --form=1 --secret=KEY --data='{"input_1":"val"}'
 *   php di-form-buddy.php --site=example.com --form=1 --secret=KEY --inspect
 *
 * PHP 7.2+ compatible. Zero external dependencies.
 */

// ─── Constants ──────────────────────────────────────────────────────────────

define('DI_FORM_BUDDY_DEFAULT_OUTPUT_DIR', 'configs');
define('DI_FORM_BUDDY_DEFAULT_EMAIL', 'di.form.buddy@gmail.com');
define('DI_FORM_BUDDY_DEFAULT_PHONE', '555-555-1234');
define('DI_FORM_BUDDY_DEFAULT_ZIP', '60601');
define('DI_FORM_BUDDY_DEFAULT_CITY', 'Chicago');
define('DI_FORM_BUDDY_DEFAULT_STATE', 'IL');
define('DI_FORM_BUDDY_DEFAULT_COUNTRY', 'United States');
define('DI_FORM_BUDDY_DEFAULT_STREET', '123 Test Street');

/**
 * Get the configured test email address.
 * Checks DI_FORM_BUDDY_EMAIL env var, falls back to default.
 *
 * @return string
 */
function di_form_buddy_get_email()
{
    $env_email = getenv('DI_FORM_BUDDY_EMAIL');
    if ($env_email !== false && $env_email !== '') {
        return $env_email;
    }
    return DI_FORM_BUDDY_DEFAULT_EMAIL;
}

// ─── Output Mode Management ─────────────────────────────────────────────────

/**
 * Set the global output mode for logging.
 * When 'json', logs go to stderr; when 'text', logs echo to stdout.
 *
 * @param string|null $mode 'text' or 'json'. Pass null to get current mode.
 * @return string Current output mode
 */
function di_form_buddy_set_output_mode($mode = null)
{
    static $output_mode = 'text';
    if ($mode !== null) {
        $output_mode = $mode;
    }
    return $output_mode;
}

// ─── Logging ────────────────────────────────────────────────────────────────

/**
 * Log a message to error_log and CLI output.
 * Output destination depends on output mode:
 * - 'text': echo to stdout (default)
 * - 'json': write to stderr (keeps stdout clean for JSON)
 *
 * @param string $message
 * @return void
 */
function di_form_buddy_log($message)
{
    $line = '[DI-Form-Buddy] ' . $message;
    error_log($line);

    $output_mode = di_form_buddy_set_output_mode();
    if ($output_mode === 'json') {
        fwrite(STDERR, $line . PHP_EOL);
    } else {
        echo $line . PHP_EOL;
    }
}

/**
 * Exit with a fatal error, honoring output mode.
 *
 * @param string $error_code Error code for JSON output
 * @param string $message    Human-readable message (without "FATAL:" prefix)
 * @param string $site       Site domain (may be empty)
 * @param int    $form_id    Form ID (0 if unknown)
 * @param string $output_mode 'text' or 'json'
 * @return void
 */
function di_form_buddy_exit_with_error($error_code, $message, $site = '', $form_id = 0, $output_mode = 'text')
{
    di_form_buddy_log('FATAL: ' . $message);
    if ($output_mode === 'json') {
        di_form_buddy_output_json(di_form_buddy_build_result($error_code, $site, $form_id, array(
            'message' => $message,
        )), 1);
    }
    exit(1);
}

// ─── Task 1: CLI Argument Parsing (AC: 2, 6, 10) ───────────────────────────

/**
 * Parse and validate CLI arguments.
 *
 * @return array Parsed arguments with keys: site, form, secret, config, data, inspect
 */
function di_form_buddy_parse_args()
{
    $longopts = array(
        'site:',
        'form:',
        'secret:',
        'config:',
        'data:',
        'inspect',
        'list',
        'generate-configs',
        'output-dir:',
        'output:',
        'health-check',
        'all',
    );

    $opts = getopt('', $longopts);

    $list = isset($opts['list']);
    $inspect = isset($opts['inspect']);
    $generate_configs = isset($opts['generate-configs']);
    $health_check = isset($opts['health-check']);
    $all = isset($opts['all']);
    $output_dir = isset($opts['output-dir']) ? $opts['output-dir'] : DI_FORM_BUDDY_DEFAULT_OUTPUT_DIR;

    // Parse --output flag: 'text' (default) or 'json'
    $output_mode = 'text';
    if (isset($opts['output'])) {
        $output_val = strtolower($opts['output']);
        if ($output_val === 'json') {
            $output_mode = 'json';
        } elseif ($output_val === 'text') {
            $output_mode = 'text';
        } else {
            di_form_buddy_log('FATAL: Invalid --output value "' . $opts['output'] . '". Valid values: text, json');
            exit(1);
        }
    }

    // Ensure output mode is set before any error logging
    di_form_buddy_set_output_mode($output_mode);

    $site_for_errors = isset($opts['site']) ? $opts['site'] : '';
    $form_for_errors = isset($opts['form']) ? (int) $opts['form'] : 0;

    // Resolve secret: --secret arg takes precedence, then env var
    $secret = isset($opts['secret']) ? $opts['secret'] : null;
    if ($secret === null || $secret === '') {
        $env_secret = getenv('DI_FORM_BUDDY_SECRET');
        if ($env_secret !== false && $env_secret !== '') {
            $secret = $env_secret;
        }
    }

    // AC 10: Refuse to run if no secret available (submission-only)
    // Health check, list, inspect, and generate-configs modes don't require secret
    if (!$list && !$inspect && !$generate_configs && !$health_check) {
        if ($secret === null || $secret === '') {
            di_form_buddy_exit_with_error('auth_failed', 'No secret provided. Use --secret=KEY or set DI_FORM_BUDDY_SECRET env var.', $site_for_errors, $form_for_errors, $output_mode);
        }
    }

    // Validate required args
    if (!isset($opts['site']) || $opts['site'] === '') {
        di_form_buddy_exit_with_error('config_invalid', '--site is required (e.g. --site=example.com)', $site_for_errors, $form_for_errors, $output_mode);
    }

    // --generate-configs, --list, and --health-check --all don't require --form
    if (!$list && !$generate_configs && !$all && (!isset($opts['form']) || $opts['form'] === '')) {
        di_form_buddy_exit_with_error('config_invalid', '--form is required (e.g. --form=1). Use --list to discover forms.', $site_for_errors, $form_for_errors, $output_mode);
    }

    // AC 6: Input values from --config (JSON file) or --data (inline JSON)
    $data = null;
    if (isset($opts['config'])) {
        $config_path = $opts['config'];
        if (!file_exists($config_path)) {
            di_form_buddy_exit_with_error('config_invalid', 'Config file not found: ' . $config_path, $site_for_errors, $form_for_errors, $output_mode);
        }
        $raw = file_get_contents($config_path);
        $data = json_decode($raw, true);
        if ($data === null) {
            di_form_buddy_exit_with_error('config_invalid', 'Invalid JSON in config file: ' . $config_path, $site_for_errors, $form_for_errors, $output_mode);
        }
        // Support both structured configs (with test_data) and legacy flat format
        if (isset($data['test_data']) && is_array($data['test_data'])) {
            di_form_buddy_log('Loaded structured config: ' . $config_path . ' (form: ' . (isset($data['form_name']) ? $data['form_name'] : 'unknown') . ')');
            $data = $data['test_data'];
        } else {
            di_form_buddy_log('Loaded input values from config: ' . $config_path);
        }
    } elseif (isset($opts['data'])) {
        $data = json_decode($opts['data'], true);
        if ($data === null) {
            di_form_buddy_exit_with_error('config_invalid', 'Invalid inline JSON in --data', $site_for_errors, $form_for_errors, $output_mode);
        }
        di_form_buddy_log('Loaded input values from --data inline JSON');
    }

    // --inspect, --list, --generate-configs, and --health-check modes don't require data
    if (!$inspect && !$list && !$generate_configs && !$health_check && $data === null) {
        di_form_buddy_exit_with_error('config_invalid', 'Either --config=path.json or --data=\'{"input_1":"val"}\' is required (unless using --inspect, --generate-configs, or --health-check)', $site_for_errors, $form_for_errors, $output_mode);
    }

    return array(
        'site'             => $opts['site'],
        'form'             => isset($opts['form']) ? (int) $opts['form'] : 0,
        'secret'           => $secret,
        'config'           => isset($opts['config']) ? $opts['config'] : null,
        'data'             => $data,
        'inspect'          => $inspect,
        'list'             => $list,
        'generate_configs' => $generate_configs,
        'output_dir'       => $output_dir,
        'output_mode'      => $output_mode,
        'health_check'     => $health_check,
        'all'              => $all,
    );
}

// ─── Task 2: WordPress Bootstrap (AC: 1, 11) ───────────────────────────────

/**
 * Bootstrap WordPress and verify Gravity Forms is available.
 *
 * @param string $site Domain name
 * @return void
 */
function di_form_buddy_bootstrap_wp($site)
{
    // AC 1: Build path from site domain
    $wp_load = '/var/www/domains/' . $site . '/dealer-inspire/wp/wp-load.php';

    if (!file_exists($wp_load)) {
        di_form_buddy_log('FATAL: WordPress not found at: ' . $wp_load);
        exit(1);
    }

    di_form_buddy_log('Bootstrapping WordPress: ' . $wp_load);
    require_once $wp_load;

    // AC 11: Verify GFAPI class available
    if (!class_exists('GFAPI')) {
        di_form_buddy_log('FATAL: GFAPI class not available. Is Gravity Forms active on this site?');
        exit(1);
    }

    di_form_buddy_log('WordPress bootstrapped. GFAPI available.');
}

// ─── Task 3: HMAC Auth Validation (AC: 2) ───────────────────────────────────

/**
 * Generate HMAC signature and log the auth event.
 *
 * @param int    $form_id
 * @param string $secret
 * @return array Auth payload with keys: timestamp, nonce, signature, payload
 */
function di_form_buddy_generate_hmac($form_id, $secret)
{
    $timestamp = time();
    $nonce = bin2hex(random_bytes(16));
    $payload = $timestamp . ':' . $form_id . ':' . $nonce;
    $signature = hash_hmac('sha256', $payload, $secret);

    di_form_buddy_log('Auth: HMAC generated for form ' . $form_id . ' at ' . $timestamp);

    return array(
        'timestamp' => $timestamp,
        'nonce'     => $nonce,
        'signature' => $signature,
        'payload'   => $payload,
    );
}

// ─── Task 4: reCAPTCHA Bypass Shim (AC: 3, 4, 5, 9) ────────────────────────

/**
 * Install reCAPTCHA bypass filters.
 *
 * Returns an array of filter references for cleanup.
 *
 * @return array{recaptcha_filter: Closure, spam_filter: Closure, notification_filter: Closure}
 */
function di_form_buddy_install_bypass_filters()
{
    // AC 3: Set synthetic reCAPTCHA token
    $_REQUEST['recaptcha_response'] = 'di-form-buddy-bypass-token';
    di_form_buddy_log('Bypass: Set $_REQUEST[recaptcha_response]');

    // AC 4: Mock Google reCAPTCHA verify endpoint
    $recaptcha_filter = function ($preempt, $args, $url) {
        if (strpos($url, 'google.com/recaptcha') !== false) {
            di_form_buddy_log('Bypass: Intercepted reCAPTCHA verify request');
            return array(
                'response' => array('code' => 200, 'message' => 'OK'),
                'body'     => json_encode(array(
                    'success'  => true,
                    'score'    => 0.9,
                    'action'   => 'submit',
                    'hostname' => 'localhost',
                )),
            );
        }
        return $preempt;
    };
    add_filter('pre_http_request', $recaptcha_filter, 10, 3);
    di_form_buddy_log('Bypass: Installed pre_http_request filter for reCAPTCHA mock');

    // AC 5: Force entry not spam — runs AFTER both NoCaptchaReCaptcha (pri 10) and DISpamCheck (pri 11)
    $spam_filter = function () {
        di_form_buddy_log('Bypass: gform_entry_is_spam returning false');
        return false;
    };
    add_filter('gform_entry_is_spam', $spam_filter, 999);
    di_form_buddy_log('Bypass: Installed gform_entry_is_spam filter at priority 999');

    // Disable DI's content-based spam checker (checks name format, blacklists, etc.)
    $di_spam_filter_toggle = function () {
        di_form_buddy_log('Bypass: DI gform spam filter deactivated');
        return false;
    };
    add_filter('di_gform_spam_filter_active', $di_spam_filter_toggle, 1);
    di_form_buddy_log('Bypass: Disabled di_gform_spam_filter_active');

    // Clean spam_reason meta after submission — NoCaptchaReCaptchaPublic writes this
    // on gform_after_submission even when entry isn't blocked
    $spam_meta_cleaner = function ($entry) {
        if (function_exists('gform_update_meta')) {
            gform_update_meta($entry['id'], 'spam_reason', '');
            gform_update_meta($entry['id'], 'recaptcha_verified', true);
            di_form_buddy_log('Bypass: Cleared spam_reason and set recaptcha_verified=true on entry ' . $entry['id']);
        }
    };
    add_action('gform_after_submission', $spam_meta_cleaner, 9999, 1);
    di_form_buddy_log('Bypass: Installed spam_reason meta cleaner (gform_after_submission pri 9999)');

    // SAFETY: Reroute ALL notifications to test inbox — never email the dealer
    $safe_email = di_form_buddy_get_email();
    $notification_filter = function ($notification) use ($safe_email) {
        $original_to = isset($notification['to']) ? $notification['to'] : '(none)';
        $notification['to'] = $safe_email;
        // Also override cc/bcc if present
        if (isset($notification['cc'])) {
            $notification['cc'] = '';
        }
        if (isset($notification['bcc'])) {
            $notification['bcc'] = '';
        }
        di_form_buddy_log('Notify: Rerouted "' . $original_to . '" -> ' . $safe_email);
        return $notification;
    };
    add_filter('gform_notification', $notification_filter, 1, 1);
    di_form_buddy_log('Safety: ALL notifications rerouted to ' . $safe_email);

    return array(
        'recaptcha_filter'      => $recaptcha_filter,
        'spam_filter'           => $spam_filter,
        'di_spam_filter_toggle' => $di_spam_filter_toggle,
        'spam_meta_cleaner'     => $spam_meta_cleaner,
        'notification_filter'   => $notification_filter,
    );
}

/**
 * Remove bypass filters (AC 9: cleanup in finally block).
 *
 * @param array $filters Filter references from di_form_buddy_install_bypass_filters()
 * @return void
 */
function di_form_buddy_remove_bypass_filters($filters)
{
    remove_filter('pre_http_request', $filters['recaptcha_filter'], 10);
    remove_filter('gform_entry_is_spam', $filters['spam_filter'], 999);
    remove_filter('di_gform_spam_filter_active', $filters['di_spam_filter_toggle'], 1);
    remove_action('gform_after_submission', $filters['spam_meta_cleaner'], 9999);
    remove_filter('gform_notification', $filters['notification_filter'], 1);
    unset($_REQUEST['recaptcha_response']);
    di_form_buddy_log('Cleanup: All bypass filters removed (including notification reroute)');
}

// ─── Config Generation: Field Type Detection ────────────────────────────────

/**
 * Get the list of non-data field types that should be skipped entirely.
 *
 * @return array
 */
function di_form_buddy_get_skip_types()
{
    return array('html', 'section', 'page', 'captcha', 'total', 'calculation');
}

/**
 * Check if a name field uses simple format (no sub-inputs).
 *
 * @param object $field GF field object
 * @return bool
 */
function di_form_buddy_is_simple_name_field($field)
{
    // Simple name format has no inputs array or empty inputs
    if (!isset($field->inputs) || empty($field->inputs)) {
        return true;
    }
    // If nameFormat is 'simple', it's simple
    if (isset($field->nameFormat) && $field->nameFormat === 'simple') {
        return true;
    }
    return false;
}

/**
 * Check if a field has sub-inputs.
 *
 * @param object $field GF field object
 * @return bool
 */
function di_form_buddy_has_sub_inputs($field)
{
    return isset($field->inputs) && is_array($field->inputs) && count($field->inputs) > 0;
}

/**
 * Check if a field has choices (select, radio, checkbox).
 *
 * @param object $field GF field object
 * @return bool
 */
function di_form_buddy_has_choices($field)
{
    return isset($field->choices) && is_array($field->choices) && count($field->choices) > 0;
}

/**
 * Get the effective input type for a field.
 * Uses get_input_type() if available, then inputType, then type.
 *
 * @param object $field GF field object
 * @return string
 */
function di_form_buddy_get_input_type($field)
{
    if (is_object($field)) {
        if (method_exists($field, 'get_input_type')) {
            $input_type = $field->get_input_type();
            if (is_string($input_type) && $input_type !== '') {
                return $input_type;
            }
        }
        if (isset($field->inputType) && is_string($field->inputType) && $field->inputType !== '') {
            return $field->inputType;
        }
        if (isset($field->type) && is_string($field->type) && $field->type !== '') {
            return $field->type;
        }
    }
    return '';
}

/**
 * Add a field entry to the fields array.
 *
 * @param array  $fields
 * @param mixed  $id
 * @param string $type
 * @param string $label
 * @param bool   $required
 * @param string $input_name
 * @param mixed  $parent_id
 * @return void
 */
function di_form_buddy_add_field_entry(&$fields, $id, $type, $label, $required, $input_name, $parent_id = null)
{
    $entry = array(
        'id'         => $id,
        'type'       => $type,
        'label'      => $label,
        'required'   => $required,
        'input_name' => $input_name,
    );
    if ($parent_id !== null) {
        $entry['parent_id'] = $parent_id;
    }
    $fields[] = $entry;
}

// ─── Config Generation: Test Data Generation ─────────────────────────────────

/**
 * Infer test value from label for generic text fields.
 * Handles common field naming patterns that have validation expectations.
 *
 * @param string $label Field label
 * @return string|null Inferred test value or null if no pattern matched
 */
function di_form_buddy_infer_from_label($label)
{
    $label_lower = strtolower($label);

    // Zip/Postal code patterns - MUST be numeric 5-digit
    if (strpos($label_lower, 'zip') !== false || strpos($label_lower, 'postal') !== false) {
        return DI_FORM_BUDDY_DEFAULT_ZIP;
    }

    // Phone patterns - MUST match /^\D?(\d{3})\D?\D?(\d{3})\D?(\d{4})$/
    if (strpos($label_lower, 'phone') !== false || strpos($label_lower, 'tel') !== false ||
        strpos($label_lower, 'mobile') !== false || strpos($label_lower, 'cell') !== false ||
        strpos($label_lower, 'fax') !== false) {
        return DI_FORM_BUDDY_DEFAULT_PHONE;
    }

    // Email patterns
    if (strpos($label_lower, 'email') !== false || strpos($label_lower, 'e-mail') !== false) {
        return di_form_buddy_get_email();
    }

    // URL/Website patterns - MUST include protocol
    if (strpos($label_lower, 'url') !== false || strpos($label_lower, 'website') !== false ||
        strpos($label_lower, 'web site') !== false || strpos($label_lower, 'link') !== false) {
        return 'https://example.com';
    }

    // Name patterns
    if (strpos($label_lower, 'first name') !== false || $label_lower === 'first') {
        return 'Test';
    }
    if (strpos($label_lower, 'last name') !== false || $label_lower === 'last') {
        return 'User';
    }
    if (strpos($label_lower, 'middle name') !== false || $label_lower === 'middle') {
        return 'M';
    }
    if (strpos($label_lower, 'full name') !== false || $label_lower === 'name') {
        return 'Test User';
    }

    // Address component patterns
    if (strpos($label_lower, 'city') !== false) {
        return DI_FORM_BUDDY_DEFAULT_CITY;
    }
    if (strpos($label_lower, 'state') !== false || strpos($label_lower, 'province') !== false) {
        return DI_FORM_BUDDY_DEFAULT_STATE;
    }
    if (strpos($label_lower, 'country') !== false) {
        return DI_FORM_BUDDY_DEFAULT_COUNTRY;
    }
    if (strpos($label_lower, 'street') !== false || strpos($label_lower, 'address') !== false) {
        return DI_FORM_BUDDY_DEFAULT_STREET;
    }

    // Year patterns (often validated as 4-digit number)
    if (strpos($label_lower, 'year') !== false) {
        return '2026';
    }

    // VIN patterns (17 chars alphanumeric)
    if (strpos($label_lower, 'vin') !== false) {
        return '1HGBH41JXMN109186';
    }

    // Stock number patterns
    if (strpos($label_lower, 'stock') !== false) {
        return 'STK12345';
    }

    // No pattern matched
    return null;
}

/**
 * Generate test value for a field based on its type.
 * COMPLETE implementation verified against GF platform source.
 *
 * @param object      $field GF field object
 * @param string|null $input_id Sub-input ID if applicable (e.g., "1.3")
 * @param string|null $sub_label Sub-input label if applicable
 * @return string|null Test value or null if should skip
 */
function di_form_buddy_generate_test_value($field, $input_id = null, $sub_label = null)
{
    $type = isset($field->type) ? $field->type : '';
    $input_type = di_form_buddy_get_input_type($field);
    $effective_type = $input_type !== '' ? $input_type : $type;
    $label = $sub_label !== null ? $sub_label : (isset($field->label) ? $field->label : '');

    // === SKIP TYPES (return null to exclude from test_data) ===
    // Note: fileupload, list, post_image are handled in di_form_buddy_build_config() before reaching here
    // This is a safety fallback in case this function is called directly
    if ($effective_type === 'fileupload' || $effective_type === 'list' || $effective_type === 'post_image' ||
        $effective_type === 'dropbox' || $effective_type === 'creditcard' || $effective_type === 'captcha' ||
        $effective_type === 'total' || $effective_type === 'calculation') {
        return null;
    }

    // === CHOICE-BASED FIELDS (select, radio, checkbox, multiselect) ===
    if (di_form_buddy_has_choices($field)) {
        if ($type === 'checkbox' && $input_id !== null) {
            // For checkbox, find the choice matching this input
            // Checkbox inputs skip IDs ending in 0 (e.g., 1.1, 1.2, ..., 1.9, 1.11, 1.12...)
            foreach ($field->choices as $idx => $choice) {
                $choice_num = $idx + 1;
                // GF checkbox inputs skip IDs ending in 0: 1..9, 11..19, 21..29, ...
                // Add one for each completed block of 9 choices to skip the 10s.
                $choice_num += (int) floor(($choice_num - 1) / 9);
                $choice_input_id = $field->id . '.' . $choice_num;
                if ((string) $choice_input_id === (string) $input_id) {
                    return isset($choice['value']) && $choice['value'] !== '' ? $choice['value'] : $choice['text'];
                }
            }
            // Default to first choice
            $first = $field->choices[0];
            return isset($first['value']) && $first['value'] !== '' ? $first['value'] : $first['text'];
        }
        // select/radio/multiselect: first choice value
        $first = $field->choices[0];
        return isset($first['value']) && $first['value'] !== '' ? $first['value'] : $first['text'];
    }

    // === EXPLICIT FIELD TYPES ===
    switch ($effective_type) {
        // Email: validated by GFCommon::is_valid_email()
        case 'email':
            return di_form_buddy_get_email();

        // Phone: validated by /^\D?(\d{3})\D?\D?(\d{3})\D?(\d{4})$/ if phoneFormat='standard'
        case 'phone':
            return DI_FORM_BUDDY_DEFAULT_PHONE;

        // Number: validated by GFCommon::is_numeric() + range check
        case 'number':
            // Respect rangeMin/rangeMax if set
            $min = isset($field->rangeMin) && is_numeric($field->rangeMin) ? (int) $field->rangeMin : 0;
            $max = isset($field->rangeMax) && is_numeric($field->rangeMax) ? (int) $field->rangeMax : 100;
            // Pick middle of range or min+1
            $val = $min + max(1, (int) (($max - $min) / 2));
            return (string) $val;

        // Date: validated by checkdate() - use ISO 8601 format (yyyy-mm-dd) as safest
        case 'date':
            return date('Y-m-d');

        // Time: submitted as array [hour, minute, am/pm] or string "HH:MM am"
        case 'time':
            // For time fields, return format based on timeFormat setting
            $is_24h = isset($field->timeFormat) && $field->timeFormat === '24';
            if ($is_24h) {
                return '14:30';
            }
            return '10:30 am';

        // Website: validated by GFCommon::is_valid_url() - MUST have protocol
        case 'website':
            return 'https://example.com';

        // Hidden fields - empty, populated by frontend JS
        case 'hidden':
        case 'tp-referral':
        case 'hiddenproduct':
            return '';

        // Textarea
        case 'textarea':
            return 'Automated test via DI Form Buddy';

        // Name field with sub-inputs
        case 'name':
            if ($input_id !== null) {
                // Standard GF name sub-inputs: .2=prefix, .3=first, .4=middle, .6=last, .8=suffix
                $sub_id = substr((string) $input_id, strpos((string) $input_id, '.') + 1);
                switch ($sub_id) {
                    case '2': return 'Mr.';      // Prefix
                    case '3': return 'Test';    // First Name
                    case '4': return 'M';       // Middle Name
                    case '6': return 'User';    // Last Name
                    case '8': return 'Jr.';     // Suffix
                    default:  return 'Test';
                }
            }
            // Simple name field (nameFormat='simple') - check label
            $label_lower = strtolower($label);
            if (strpos($label_lower, 'first') !== false) {
                return 'Test';
            }
            if (strpos($label_lower, 'last') !== false) {
                return 'User';
            }
            return 'Test User';

        // Address field with sub-inputs
        case 'address':
            if ($input_id !== null) {
                // Standard GF address sub-inputs: .1=street, .2=street2, .3=city, .4=state, .5=zip, .6=country
                $sub_id = substr((string) $input_id, strpos((string) $input_id, '.') + 1);
                switch ($sub_id) {
                    case '1': return DI_FORM_BUDDY_DEFAULT_STREET;   // Street Address
                    case '2': return '';                              // Address Line 2 (optional)
                    case '3': return DI_FORM_BUDDY_DEFAULT_CITY;     // City
                    case '4': return DI_FORM_BUDDY_DEFAULT_STATE;    // State
                    case '5': return DI_FORM_BUDDY_DEFAULT_ZIP;      // Zip
                    case '6': return DI_FORM_BUDDY_DEFAULT_COUNTRY;  // Country
                    default:  return '';
                }
            }
            return DI_FORM_BUDDY_DEFAULT_STREET;

        // Consent field (GDPR checkbox) - value is "1" when checked
        case 'consent':
            return '1';

        // Password field
        case 'password':
            return 'TestPass123!';

        // Post creation fields
        case 'post_title':
            return 'Test Post Title';

        case 'post_content':
            return 'Automated test content via DI Form Buddy';

        case 'post_excerpt':
            return 'Test excerpt';

        case 'post_tags':
            return 'test, automated';

        case 'post_custom_field':
            return 'Test custom value';

        // Pricing fields
        case 'price':
            return '10.00';

        case 'product':
        case 'singleproduct':
            return 'Test Product';

        case 'quantity':
            return '1';

        case 'shipping':
        case 'singleshipping':
            return 'Standard';

        case 'option':
            return 'Option 1';

        case 'coupon':
            return ''; // Requires valid coupon code - leave empty

        // Text field and all other types - use label inference
        case 'text':
        default:
            // First try to infer from label
            $inferred = di_form_buddy_infer_from_label($label);
            if ($inferred !== null) {
                return $inferred;
            }
            // Default: generic test value
            return 'Test ' . $label;
    }
}

/**
 * Convert a GF input ID to the input_X_Y format.
 *
 * @param string|int $input_id The field or sub-input ID (e.g., 1, "1.3")
 * @return string Input name (e.g., "input_1", "input_1_3")
 */
function di_form_buddy_input_name($input_id)
{
    return 'input_' . str_replace('.', '_', (string) $input_id);
}

// ─── Config Generation: Main Generator ───────────────────────────────────────

/**
 * Extract fields from a form and build the config structure.
 *
 * @param array  $form GF form array
 * @param string $site Site domain
 * @return array Config structure
 */
function di_form_buddy_build_config($form, $site)
{
    $skip_types = di_form_buddy_get_skip_types();
    $fields = array();
    $required_fields = array();
    $test_data = array();
    $skipped_fields = array();

    foreach ($form['fields'] as $field) {
        $type = isset($field->type) ? $field->type : '';
        $id = $field->id;
        $label = isset($field->label) ? $field->label : '';
        $is_required = !empty($field->isRequired);
        $input_type = di_form_buddy_get_input_type($field);

        // Skip non-data fields entirely
        if (in_array($type, $skip_types, true) || in_array($input_type, $skip_types, true)) {
            $reason = 'display-only';
            if ($type === 'total' || $type === 'calculation' || $input_type === 'total' || $input_type === 'calculation') {
                $reason = 'calculated';
            }
            $skipped_fields[] = array(
                'id'     => $id,
                'type'   => $type,
                'reason' => $reason,
            );
            continue;
        }

        // Skip fileupload/post_image/dropbox from test_data but include in fields
        if ($type === 'fileupload' || $type === 'post_image' || $type === 'dropbox') {
            $input_name = di_form_buddy_input_name($id);
            di_form_buddy_add_field_entry($fields, $id, $type, $label, $is_required, $input_name);
            if ($is_required) {
                $required_fields[] = $id;
            }
            $skipped_fields[] = array(
                'id'     => $id,
                'type'   => $type,
                'reason' => 'requires-file',
            );
            continue;
        }

        // Credit card fields: include in fields but skip test_data (sensitive/PCI)
        if ($type === 'creditcard' || $input_type === 'creditcard') {
            if (di_form_buddy_has_sub_inputs($field)) {
                foreach ($field->inputs as $input) {
                    if (isset($input['isHidden']) && $input['isHidden']) {
                        continue;
                    }
                    $sub_id = $input['id'];
                    $sub_label = isset($input['label']) ? $input['label'] : $label;
                    $input_name = di_form_buddy_input_name($sub_id);
                    di_form_buddy_add_field_entry($fields, $sub_id, $type, $sub_label, $is_required, $input_name, $id);
                }
            } else {
                $input_name = di_form_buddy_input_name($id);
                di_form_buddy_add_field_entry($fields, $id, $type, $label, $is_required, $input_name);
            }
            if ($is_required) {
                $required_fields[] = $id;
            }
            $skipped_fields[] = array(
                'id'     => $id,
                'type'   => $type,
                'reason' => 'sensitive',
            );
            continue;
        }

        // Skip list (repeater) from test_data but include in fields
        if ($type === 'list') {
            $input_name = di_form_buddy_input_name($id);
            di_form_buddy_add_field_entry($fields, $id, $type, $label, $is_required, $input_name);
            if ($is_required) {
                $required_fields[] = $id;
            }
            $skipped_fields[] = array(
                'id'     => $id,
                'type'   => $type,
                'reason' => 'complex-repeater',
            );
            continue;
        }

        // Handle fields with sub-inputs (name normal/extended, address, checkbox)
        if (di_form_buddy_has_sub_inputs($field)) {
            foreach ($field->inputs as $input) {
                // Skip hidden sub-inputs
                if (isset($input['isHidden']) && $input['isHidden']) {
                    continue;
                }

                $sub_id = $input['id'];
                $sub_label = isset($input['label']) ? $input['label'] : $label;
                $input_name = di_form_buddy_input_name($sub_id);

                di_form_buddy_add_field_entry($fields, $sub_id, $type, $sub_label, $is_required, $input_name, $id);

                $test_value = di_form_buddy_generate_test_value($field, $sub_id, $sub_label);
                if ($test_value !== null) {
                    $test_data[$input_name] = $test_value;
                }
            }

            if ($is_required) {
                $required_fields[] = $id;
            }
            continue;
        }

        // Simple field (no sub-inputs)
        $input_name = di_form_buddy_input_name($id);
        di_form_buddy_add_field_entry($fields, $id, $type, $label, $is_required, $input_name);

        if ($is_required) {
            $required_fields[] = $id;
        }

        $test_value = di_form_buddy_generate_test_value($field, null, null);
        if ($test_value !== null) {
            $test_data[$input_name] = $test_value;
        }
    }

    return array(
        'form_id'         => (int) $form['id'],
        'form_name'       => $form['title'],
        'generated_at'    => gmdate('Y-m-d\TH:i:s\Z'),
        'generated_from'  => $site,
        'field_count'     => count($fields),
        'fields'          => $fields,
        'required_fields' => $required_fields,
        'test_data'       => $test_data,
        'skipped_fields'  => $skipped_fields,
    );
}

/**
 * Generate a slug from form title.
 *
 * @param string $title Form title
 * @return string Slug (lowercase, hyphenated)
 */
function di_form_buddy_slugify($title)
{
    $slug = strtolower($title);
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    $slug = trim($slug, '-');
    return $slug;
}

/**
 * Validate output directory path for safety.
 *
 * @param string $output_dir Output directory path
 * @return bool True if safe, false if suspicious
 */
function di_form_buddy_validate_output_dir($output_dir)
{
    // Reject empty paths
    if ($output_dir === '' || $output_dir === null) {
        return false;
    }

    // Reject paths that try to escape (../)
    if (strpos($output_dir, '..') !== false) {
        return false;
    }

    // Reject absolute paths to sensitive system directories
    $dangerous_prefixes = array('/etc', '/var', '/usr', '/bin', '/sbin', '/root', '/home');
    foreach ($dangerous_prefixes as $prefix) {
        if (strpos($output_dir, $prefix) === 0) {
            return false;
        }
    }

    return true;
}

/**
 * Generate config files for all forms on the site.
 *
 * @param string $site       Site domain
 * @param string $output_dir Output directory for config files
 * @return void
 */
function di_form_buddy_generate_configs($site, $output_dir)
{
    // Validate output directory path
    if (!di_form_buddy_validate_output_dir($output_dir)) {
        di_form_buddy_log('FATAL: Invalid or unsafe output directory: ' . $output_dir);
        exit(1);
    }

    // Get all forms
    $forms = \GFAPI::get_forms();

    if (empty($forms)) {
        di_form_buddy_log('GENERATE: No forms found on this site.');
        exit(0);
    }

    di_form_buddy_log('GENERATE: Found ' . count($forms) . ' form(s). Generating configs...');

    // Create output directory if it doesn't exist
    if (!is_dir($output_dir)) {
        if (!mkdir($output_dir, 0755, true)) {
            di_form_buddy_log('FATAL: Could not create output directory: ' . $output_dir);
            exit(1);
        }
        di_form_buddy_log('GENERATE: Created output directory: ' . $output_dir);
    }

    $generated = array();
    $used_filenames = array();

    foreach ($forms as $form) {
        $form_id = (int) $form['id'];

        // Get full form data with fields
        $full_form = \GFAPI::get_form($form_id);
        if (!$full_form || is_wp_error($full_form)) {
            di_form_buddy_log('  WARNING: Could not load form ' . $form_id . ', skipping');
            continue;
        }

        // Build config
        $config = di_form_buddy_build_config($full_form, $site);

        // Generate filename: {form_id_padded}-{slug}.json with collision detection
        $padded_id = str_pad((string) $form_id, 2, '0', STR_PAD_LEFT);
        $slug = di_form_buddy_slugify($full_form['title']);
        if ($slug === '') {
            $slug = 'form-' . $form_id;
        }
        $base_filename = $padded_id . '-' . $slug;
        $filename = $base_filename . '.json';

        // Handle collision: append form_id if slug already used
        if (isset($used_filenames[$filename])) {
            $filename = $base_filename . '-id' . $form_id . '.json';
            di_form_buddy_log('  NOTE: Slug collision detected, using ' . $filename);
        }
        $used_filenames[$filename] = true;

        $filepath = $output_dir . '/' . $filename;

        // Write config file
        $json = json_encode($config, JSON_PRETTY_PRINT);
        if ($json === false) {
            di_form_buddy_log('  WARNING: Failed to encode JSON for form ' . $form_id . ': ' . json_last_error_msg());
            continue;
        }
        if (file_put_contents($filepath, $json) === false) {
            di_form_buddy_log('  WARNING: Failed to write ' . $filepath);
            continue;
        }

        $generated[] = $filename;
        di_form_buddy_log('  ✓ ' . $filename . ' (' . count($config['fields']) . ' fields, ' . count($config['test_data']) . ' test values)');
    }

    di_form_buddy_log('GENERATE: Complete. ' . count($generated) . ' config file(s) written to ' . $output_dir . '/');
}

// ─── JSON Result Builder (Story 2.1) ─────────────────────────────────────────

/**
 * Build a structured result array for JSON output.
 *
 * @param string      $type    Result type: 'success', 'validation_failed', or error code
 * @param string      $site    Site domain
 * @param int         $form_id Form ID
 * @param array       $extra   Additional fields to merge (form_name, entry_id, validation_errors, message)
 * @return array Structured result
 */
function di_form_buddy_build_result($type, $site, $form_id, $extra = array())
{
    $result = array(
        'success' => ($type === 'success'),
        'site'    => $site,
        'form_id' => (int) $form_id,
    );

    if ($type === 'success') {
        // Success: include form_name, entry_id, timestamp
        if (isset($extra['form_name'])) {
            $result['form_name'] = $extra['form_name'];
        }
        if (isset($extra['entry_id'])) {
            $result['entry_id'] = (int) $extra['entry_id'];
        }
        $result['timestamp'] = gmdate('Y-m-d\TH:i:s\Z');
    } elseif ($type === 'validation_failed') {
        // Validation failure: include validation_errors
        $result['error'] = 'validation_failed';
        if (isset($extra['validation_errors'])) {
            $result['validation_errors'] = $extra['validation_errors'];
        }
    } else {
        // Fatal error: error code + message
        $result['error'] = $type;
        if (isset($extra['message'])) {
            $result['message'] = $extra['message'];
        }
    }

    return $result;
}

/**
 * Output JSON result to stdout and exit.
 *
 * @param array $result Result array from di_form_buddy_build_result()
 * @param int   $exit_code Exit code (0 for success, 1 for failure)
 * @return void
 */
function di_form_buddy_output_json($result, $exit_code = 0)
{
    $json = json_encode($result, JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        // Fallback if encoding fails
        $fallback = array(
            'success' => false,
            'error'   => 'json_encode_failed',
            'message' => json_last_error_msg(),
        );
        $json = json_encode($fallback, JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            $json = '{"success":false,"error":"json_encode_failed","message":"json_encode_failed"}';
        }
        $exit_code = 1;
    }
    echo $json . PHP_EOL;
    exit($exit_code);
}

// ─── Health Check Mode (Story 2.2) ───────────────────────────────────────────

/**
 * Count data fields and required fields for a form (excluding display-only types).
 *
 * @param array $form Gravity Forms form array
 * @return array{field_count:int, required_count:int}
 */
function di_form_buddy_count_form_fields($form)
{
    $skip_types = di_form_buddy_get_skip_types();
    $field_count = 0;
    $required_count = 0;

    if (isset($form['fields']) && is_array($form['fields'])) {
        foreach ($form['fields'] as $field) {
            $type = isset($field->type) ? $field->type : '';
            $input_type = di_form_buddy_get_input_type($field);
            // Skip if either type or inputType is a display-only type
            if (in_array($type, $skip_types, true) || in_array($input_type, $skip_types, true)) {
                continue;
            }
            $field_count++;
            if (!empty($field->isRequired)) {
                $required_count++;
            }
        }
    }

    return array(
        'field_count'    => $field_count,
        'required_count' => $required_count,
    );
}

/**
 * Emit health-check output in human-readable mode.
 *
 * @param array $result Health check result
 * @return void
 */
function di_form_buddy_output_health_check_text($result)
{
    di_form_buddy_log('HEALTH CHECK: ' . $result['site'] . ' / Form ' . $result['form_id']);

    di_form_buddy_log(($result['checks']['wordpress'] ? '✓' : '✗') . ' WordPress bootstrapped');
    di_form_buddy_log(($result['checks']['gfapi'] ? '✓' : '✗') . ' GFAPI available');

    if ($result['checks']['form_exists']) {
        di_form_buddy_log('✓ Form exists: "' . $result['form_name'] . '"');
        di_form_buddy_log('✓ ' . $result['field_count'] . ' fields, ' . $result['required_count'] . ' required');
    } else {
        di_form_buddy_log('✗ Form does not exist');
    }

    di_form_buddy_log('HEALTH: ' . strtoupper($result['status']));
}

/**
 * Execute health check on a form without submitting.
 *
 * Verifies WordPress bootstrap, GFAPI availability, and form existence.
 * Returns structured result for both text and JSON output modes.
 *
 * @param string $site       Site domain
 * @param int    $form_id    Form ID to check
 * @param string $output_mode 'text' or 'json'
 * @return array Health check result
 */
function di_form_buddy_health_check($site, $form_id, $output_mode)
{
    $checks = array(
        'wordpress'   => false,
        'gfapi'       => false,
        'form_exists' => false,
    );

    $form_name = null;
    $field_count = 0;
    $required_count = 0;

    // Bootstrap WordPress (no extra logging in health-check mode)
    $wp_load = '/var/www/domains/' . $site . '/dealer-inspire/wp/wp-load.php';
    if (file_exists($wp_load)) {
        $checks['wordpress'] = true;
        require_once $wp_load;

        if (class_exists('GFAPI')) {
            $checks['gfapi'] = true;

            $form = \GFAPI::get_form($form_id);
            if ($form && !is_wp_error($form)) {
                $checks['form_exists'] = true;
                $form_name = isset($form['title']) ? $form['title'] : '';

                $counts = di_form_buddy_count_form_fields($form);
                $field_count = $counts['field_count'];
                $required_count = $counts['required_count'];
            }
        }
    }

    $status = ($checks['wordpress'] && $checks['gfapi'] && $checks['form_exists']) ? 'pass' : 'fail';

    $result = array(
        'mode'           => 'health_check',
        'site'           => $site,
        'form_id'        => (int) $form_id,
        'form_name'      => $form_name,
        'status'         => $status,
        'field_count'    => $field_count,
        'required_count' => $required_count,
        'checks'         => $checks,
    );

    if ($output_mode === 'text') {
        di_form_buddy_output_health_check_text($result);
    }

    return $result;
}

// ─── Health Check All Mode (Story 2.3) ───────────────────────────────────────

/**
 * Execute health check on all forms without submitting.
 *
 * Iterates all forms via GFAPI::get_forms() and checks each using the
 * single-form health check function. Provides aggregate status and summary.
 *
 * @param string $site        Site domain
 * @param string $output_mode 'text' or 'json'
 * @return array Health check all result
 */
function di_form_buddy_health_check_all($site, $output_mode)
{
    $forms = \GFAPI::get_forms();

    if (empty($forms)) {
        if ($output_mode === 'text') {
            di_form_buddy_log('HEALTH CHECK ALL: ' . $site);
            di_form_buddy_log('No forms found on this site.');
        }
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
        // Defensive check: skip malformed form entries missing 'id'
        if (!isset($form['id'])) {
            continue;
        }
        $form_id = (int) $form['id'];

        // Call existing Story 2.2 function with 'json' mode to suppress its text output.
        // We handle our own text output per-form in --all mode for consistent formatting.
        $hc_result = di_form_buddy_health_check($site, $form_id, 'json');
        $results[] = $hc_result;

        if ($hc_result['status'] === 'pass') {
            $forms_passed++;
        }

        // Text output per form
        if ($output_mode === 'text') {
            $status_str = ($hc_result['status'] === 'pass') ? 'PASS' : 'FAIL';
            if ($hc_result['status'] === 'pass') {
                $detail = sprintf('(%d fields, %d required)', $hc_result['field_count'], $hc_result['required_count']);
            } else {
                $detail = '(form check failed)';
            }
            di_form_buddy_log(sprintf('  [%d] "%s" — %s %s',
                $form_id,
                $hc_result['form_name'] !== null ? $hc_result['form_name'] : 'Unknown',
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

// ─── Task 5: GFAPI Submission + Response Handling (AC: 6, 7, 8) ─────────────

/**
 * Submit form via GFAPI and handle the response.
 *
 * @param int    $form_id
 * @param array  $input_values
 * @param string $site Site domain for result building
 * @param string $output_mode 'text' or 'json'
 * @return array|null Result array for JSON mode, null for text mode (exits on failure)
 */
function di_form_buddy_submit($form_id, $input_values, $site = '', $output_mode = 'text')
{
    // AC 11: Verify form exists
    $form = \GFAPI::get_form($form_id);
    if (!$form || is_wp_error($form)) {
        di_form_buddy_log('FATAL: Form ' . $form_id . ' does not exist or could not be loaded.');
        if ($output_mode === 'json') {
            return di_form_buddy_build_result('form_not_found', $site, $form_id, array(
                'message' => 'Form ' . $form_id . ' does not exist or could not be loaded',
            ));
        }
        exit(1);
    }
    $form_name = isset($form['title']) ? $form['title'] : '';
    di_form_buddy_log('Form ' . $form_id . ' loaded: "' . $form_name . '"');

    // Capture entry_id via gform_after_submission hook
    $captured_entry_id = null;
    $capture_hook = function ($entry) use (&$captured_entry_id) {
        $captured_entry_id = $entry['id'];
    };
    add_action('gform_after_submission', $capture_hook, 10, 1);

    di_form_buddy_log('Submitting form ' . $form_id . ' with ' . count($input_values) . ' input value(s)...');

    // AC 6: Submit via GFAPI
    $result = \GFAPI::submit_form($form_id, $input_values);

    remove_action('gform_after_submission', $capture_hook, 10);

    // AC 7: Handle response types
    if (is_wp_error($result)) {
        /** @var \WP_Error $result */
        $error_msg = $result->get_error_message();
        di_form_buddy_log('ERROR: WP_Error — ' . $error_msg);
        if ($output_mode === 'json') {
            return di_form_buddy_build_result('wp_error', $site, $form_id, array(
                'message' => $error_msg,
            ));
        }
        exit(1);
    }

    if (is_array($result) && isset($result['is_valid']) && $result['is_valid'] === false) {
        di_form_buddy_log('VALIDATION FAILED:');
        $validation_errors = array();

        if (isset($result['validation_messages'])) {
            foreach ($result['validation_messages'] as $field_id => $message) {
                di_form_buddy_log('  Field ' . $field_id . ': ' . $message);
                $validation_errors[(string) $field_id] = $message;
            }
        }
        // Also check page-level validation
        if (isset($result['form']['fields'])) {
            foreach ($result['form']['fields'] as $field) {
                if (!empty($field->failed_validation)) {
                    di_form_buddy_log('  Field ' . $field->id . ' (' . $field->label . '): ' . $field->validation_message);
                    if (!isset($validation_errors[(string) $field->id])) {
                        $validation_errors[(string) $field->id] = $field->validation_message;
                    }
                }
            }
        }

        if ($output_mode === 'json') {
            return di_form_buddy_build_result('validation_failed', $site, $form_id, array(
                'validation_errors' => $validation_errors,
            ));
        }
        exit(1);
    }

    // Success
    $entry_id = $captured_entry_id;
    if ($entry_id === null && is_array($result) && isset($result['entry_id'])) {
        $entry_id = $result['entry_id'];
    }

    if ($entry_id !== null) {
        di_form_buddy_log('SUCCESS: Entry created — entry_id=' . $entry_id);
    } else {
        di_form_buddy_log('SUCCESS: Form submitted (entry_id not captured in hook)');
        di_form_buddy_log('Result: ' . print_r($result, true));
    }

    // Return success result for JSON mode
    if ($output_mode === 'json') {
        return di_form_buddy_build_result('success', $site, $form_id, array(
            'form_name' => $form_name,
            'entry_id'  => $entry_id,
        ));
    }

    return null;
}

// ─── Main ───────────────────────────────────────────────────────────────────

/**
 * Main entry point.
 *
 * @return void
 */
function di_form_buddy_main()
{
    // Task 1: Parse CLI args (before logging banner so we can set output mode)
    $args = di_form_buddy_parse_args();

    // Initialize output mode early (AC: 5 - log redirection)
    $output_mode = $args['output_mode'];
    di_form_buddy_set_output_mode($output_mode);

    // Handle --health-check --all mode (Story 2.3) - check all forms
    if ($args['health_check'] && $args['all']) {
        $result = di_form_buddy_health_check_all($args['site'], $output_mode);

        if ($output_mode === 'json') {
            $exit_code = ($result['status'] === 'pass') ? 0 : 1;
            di_form_buddy_output_json($result, $exit_code);
        }

        exit($result['status'] === 'pass' ? 0 : 1);
    }

    // Handle --health-check mode (single form) - Story 2.2
    if ($args['health_check']) {
        $hc_result = di_form_buddy_health_check($args['site'], $args['form'], $output_mode);

        if ($output_mode === 'json') {
            $exit_code = ($hc_result['status'] === 'pass') ? 0 : 1;
            di_form_buddy_output_json($hc_result, $exit_code);
        }

        exit($hc_result['status'] === 'pass' ? 0 : 1);
    }

    di_form_buddy_log('=== DI Form Buddy v0.1.0 ===');

    // Task 2: Bootstrap WordPress (with JSON error handling)
    di_form_buddy_bootstrap_wp_with_result($args['site'], $args['form'], $output_mode);

    // Handle --list mode: enumerate all forms on the site, then exit
    if ($args['list']) {
        $forms = \GFAPI::get_forms();
        if (empty($forms)) {
            if ($output_mode === 'json') {
                di_form_buddy_output_json(array(), 0);
            }
            di_form_buddy_log('LIST: No forms found on this site.');
            exit(0);
        }

        if ($output_mode === 'json') {
            // JSON output: array of form objects
            $json_forms = array();
            foreach ($forms as $form) {
                $entry_count = \GFAPI::count_entries($form['id']);
                $json_forms[] = array(
                    'id'          => (int) $form['id'],
                    'title'       => $form['title'],
                    'field_count' => count($form['fields']),
                    'entry_count' => (int) $entry_count,
                );
            }
            di_form_buddy_output_json($json_forms, 0);
        }

        di_form_buddy_log('LIST: Found ' . count($forms) . ' form(s):');
        foreach ($forms as $form) {
            $entry_count = \GFAPI::count_entries($form['id']);
            di_form_buddy_log('  [ID ' . $form['id'] . '] "' . $form['title'] . '" — ' . count($form['fields']) . ' fields, ' . $entry_count . ' entries');
        }
        di_form_buddy_log('LIST complete. Use --form=ID --inspect for field details.');
        exit(0);
    }

    // Handle --generate-configs mode: generate config files for all forms
    if ($args['generate_configs']) {
        di_form_buddy_generate_configs_with_result($args['site'], $args['output_dir'], $output_mode);
        exit(0);
    }

    // Handle --inspect mode: just verify bootstrap + form exists, then exit (no HMAC needed)
    if ($args['inspect']) {
        $form = \GFAPI::get_form($args['form']);
        if (!$form || is_wp_error($form)) {
            if ($output_mode === 'json') {
                di_form_buddy_output_json(di_form_buddy_build_result(
                    'form_not_found',
                    $args['site'],
                    $args['form'],
                    array('message' => 'Form ' . $args['form'] . ' does not exist')
                ), 1);
            }
            di_form_buddy_log('INSPECT: Form ' . $args['form'] . ' does NOT exist.');
            exit(1);
        }

        if ($output_mode === 'json') {
            // JSON output: form details with fields
            $json_fields = array();
            foreach ($form['fields'] as $field) {
                $json_fields[] = array(
                    'id'       => $field->id,
                    'type'     => $field->type,
                    'label'    => $field->label,
                    'required' => !empty($field->isRequired),
                );
            }
            di_form_buddy_output_json(array(
                'form_id'     => (int) $form['id'],
                'form_name'   => $form['title'],
                'field_count' => count($form['fields']),
                'fields'      => $json_fields,
            ), 0);
        }

        di_form_buddy_log('INSPECT: Form ' . $args['form'] . ' exists — "' . $form['title'] . '"');
        di_form_buddy_log('INSPECT: ' . count($form['fields']) . ' field(s)');
        foreach ($form['fields'] as $field) {
            di_form_buddy_log('  Field ' . $field->id . ' [' . $field->type . ']: ' . $field->label);
        }
        di_form_buddy_log('INSPECT complete. No submission made.');
        exit(0);
    }

    // Task 3: Generate HMAC auth (only for submission mode)
    $auth = di_form_buddy_generate_hmac($args['form'], $args['secret']);
    di_form_buddy_log('Auth payload: ' . $auth['payload']);
    di_form_buddy_log('Auth signature: ' . $auth['signature']);

    // Task 4 + 5: Install bypass, submit, cleanup
    $filters = di_form_buddy_install_bypass_filters();
    $submit_result = null;

    try {
        // Task 5: Submit form
        $submit_result = di_form_buddy_submit($args['form'], $args['data'], $args['site'], $output_mode);
    } finally {
        // AC 9: Cleanup in finally block
        di_form_buddy_remove_bypass_filters($filters);
    }

    // Output JSON result if in JSON mode
    if ($output_mode === 'json' && $submit_result !== null) {
        $exit_code = $submit_result['success'] ? 0 : 1;
        di_form_buddy_output_json($submit_result, $exit_code);
    }

    di_form_buddy_log('=== Done ===');
}

/**
 * Bootstrap WordPress with JSON error handling.
 *
 * @param string $site Site domain
 * @param int    $form_id Form ID (for error result)
 * @param string $output_mode 'text' or 'json'
 * @return void
 */
function di_form_buddy_bootstrap_wp_with_result($site, $form_id, $output_mode)
{
    // AC 1: Build path from site domain
    $wp_load = '/var/www/domains/' . $site . '/dealer-inspire/wp/wp-load.php';

    if (!file_exists($wp_load)) {
        di_form_buddy_log('FATAL: WordPress not found at: ' . $wp_load);
        if ($output_mode === 'json') {
            di_form_buddy_output_json(di_form_buddy_build_result(
                'bootstrap_failed',
                $site,
                $form_id,
                array('message' => 'WordPress not found at ' . $wp_load)
            ), 1);
        }
        exit(1);
    }

    di_form_buddy_log('Bootstrapping WordPress: ' . $wp_load);
    require_once $wp_load;

    // AC 11: Verify GFAPI class available
    if (!class_exists('GFAPI')) {
        di_form_buddy_log('FATAL: GFAPI class not available. Is Gravity Forms active on this site?');
        if ($output_mode === 'json') {
            di_form_buddy_output_json(di_form_buddy_build_result(
                'gfapi_unavailable',
                $site,
                $form_id,
                array('message' => 'GFAPI class not available. Is Gravity Forms active on this site?')
            ), 1);
        }
        exit(1);
    }

    di_form_buddy_log('WordPress bootstrapped. GFAPI available.');
}

/**
 * Generate configs with JSON result support.
 *
 * @param string $site Site domain
 * @param string $output_dir Output directory
 * @param string $output_mode 'text' or 'json'
 * @return void
 */
function di_form_buddy_generate_configs_with_result($site, $output_dir, $output_mode)
{
    // Validate output directory path
    if (!di_form_buddy_validate_output_dir($output_dir)) {
        di_form_buddy_log('FATAL: Invalid or unsafe output directory: ' . $output_dir);
        if ($output_mode === 'json') {
            di_form_buddy_output_json(array(
                'success' => false,
                'error'   => 'config_invalid',
                'message' => 'Invalid or unsafe output directory: ' . $output_dir,
            ), 1);
        }
        exit(1);
    }

    // Get all forms
    $forms = \GFAPI::get_forms();

    if (empty($forms)) {
        if ($output_mode === 'json') {
            di_form_buddy_output_json(array(
                'success'   => true,
                'generated' => array(),
                'count'     => 0,
            ), 0);
        }
        di_form_buddy_log('GENERATE: No forms found on this site.');
        exit(0);
    }

    di_form_buddy_log('GENERATE: Found ' . count($forms) . ' form(s). Generating configs...');

    // Create output directory if it doesn't exist
    if (!is_dir($output_dir)) {
        if (!mkdir($output_dir, 0755, true)) {
            di_form_buddy_log('FATAL: Could not create output directory: ' . $output_dir);
            if ($output_mode === 'json') {
                di_form_buddy_output_json(array(
                    'success' => false,
                    'error'   => 'directory_create_failed',
                    'message' => 'Could not create output directory: ' . $output_dir,
                ), 1);
            }
            exit(1);
        }
        di_form_buddy_log('GENERATE: Created output directory: ' . $output_dir);
    }

    $generated = array();
    $used_filenames = array();

    foreach ($forms as $form) {
        $form_id = (int) $form['id'];

        // Get full form data with fields
        $full_form = \GFAPI::get_form($form_id);
        if (!$full_form || is_wp_error($full_form)) {
            di_form_buddy_log('  WARNING: Could not load form ' . $form_id . ', skipping');
            continue;
        }

        // Build config
        $config = di_form_buddy_build_config($full_form, $site);

        // Generate filename: {form_id_padded}-{slug}.json with collision detection
        $padded_id = str_pad((string) $form_id, 2, '0', STR_PAD_LEFT);
        $slug = di_form_buddy_slugify($full_form['title']);
        if ($slug === '') {
            $slug = 'form-' . $form_id;
        }
        $base_filename = $padded_id . '-' . $slug;
        $filename = $base_filename . '.json';

        // Handle collision: append form_id if slug already used
        if (isset($used_filenames[$filename])) {
            $filename = $base_filename . '-id' . $form_id . '.json';
            di_form_buddy_log('  NOTE: Slug collision detected, using ' . $filename);
        }
        $used_filenames[$filename] = true;

        $filepath = $output_dir . '/' . $filename;

        // Write config file
        $json = json_encode($config, JSON_PRETTY_PRINT);
        if ($json === false) {
            di_form_buddy_log('  WARNING: Failed to encode JSON for form ' . $form_id . ': ' . json_last_error_msg());
            continue;
        }
        if (file_put_contents($filepath, $json) === false) {
            di_form_buddy_log('  WARNING: Failed to write ' . $filepath);
            continue;
        }

        $generated[] = array(
            'filename'    => $filename,
            'form_id'     => $form_id,
            'form_name'   => $full_form['title'],
            'field_count' => count($config['fields']),
            'test_values' => count($config['test_data']),
        );
        di_form_buddy_log('  ✓ ' . $filename . ' (' . count($config['fields']) . ' fields, ' . count($config['test_data']) . ' test values)');
    }

    if ($output_mode === 'json') {
        di_form_buddy_output_json(array(
            'success'    => true,
            'output_dir' => $output_dir,
            'generated'  => $generated,
            'count'      => count($generated),
        ), 0);
    }

    di_form_buddy_log('GENERATE: Complete. ' . count($generated) . ' config file(s) written to ' . $output_dir . '/');
}

// Only run main when executed directly (not when included by tests)
if (php_sapi_name() === 'cli' && realpath($argv[0]) === realpath(__FILE__)) {
    di_form_buddy_main();
}
