# WP-CLI Usage Patterns

## Overview

This document covers WP-CLI command patterns for WordPress automation, with a focus on `wp eval` and `wp eval-file` for executing PHP code with full WordPress context.

## wp eval-file Command

### Overview

The `wp eval-file` command loads and executes a PHP file within the WordPress environment.

**Official documentation:** https://developer.wordpress.org/cli/commands/eval-file/

### Syntax

```bash
wp eval-file <file> [<arg>...] [--skip-wordpress] [--use-include]
```

### Parameters

| Parameter | Description |
|-----------|-------------|
| `<file>` | The path to the PHP file to execute |
| `<arg>...` | One or more positional arguments to pass to the file (placed in `$args` variable) |
| `--skip-wordpress` | Load and execute file without loading WordPress |
| `--use-include` | Process the file via `include` instead of evaluating its contents |

### WordPress Environment Loading

By default (without `--skip-wordpress`), `wp eval-file` loads the complete WordPress environment, including:

1. WordPress core functions and classes
2. All active plugins
3. Current theme functions
4. Database connection
5. WordPress configuration (wp-config.php)

**Key finding:** File execution happens after WordPress has loaded entirely, which means you can use any utilities defined in WordPress, active plugins, or the current theme.

### GFAPI Access in wp eval-file

Yes, GFAPI (Gravity Forms API) is fully accessible when using `wp eval-file` because:

1. The full WordPress environment is loaded
2. All active plugins (including Gravity Forms) are initialized
3. The `plugins_loaded` action has already fired

**Verification:** Testing with `did_action('plugins_loaded')` confirms that active plugins are accessible when using `eval-file`.

### Global Variable Handling

Because code is executed within a method, global variables need to be explicitly globalized:

```php
<?php
// example-script.php

// Access WordPress globals
global $wpdb, $wp_version;

echo "WordPress Version: " . $wp_version . "\n";
echo "Database Prefix: " . $wpdb->prefix . "\n";
```

### Basic Usage Examples

#### Simple execution:
```bash
wp eval-file simple-command.php
```

#### Passing arguments:
```bash
wp eval-file work.php param1 param2
```

Arguments are available in the `$args` variable:
```php
<?php
// work.php
var_dump($args);  // ['param1', 'param2']
```

#### Skip WordPress loading:
```bash
wp eval-file work.php param1 param2 --skip-wordpress
```

#### Use include instead of eval:
```bash
wp eval-file script.php --use-include
```

## wp eval Command

### Overview

The `wp eval` command executes arbitrary PHP code inline.

**Official documentation:** https://developer.wordpress.org/cli/commands/eval/

### Syntax

```bash
wp eval <php-code> [--skip-wordpress]
```

### Usage Examples

```bash
# Get WordPress version
wp eval 'echo get_bloginfo("version");'

# Count published posts
wp eval 'echo wp_count_posts()->publish;'

# Get user by email
wp eval 'var_dump(get_user_by("email", "admin@example.com"));'
```

## GFAPI Usage with wp eval-file

### Example 1: Submit Form Entry

```php
<?php
// submit-form.php

// No need to include WordPress files - already loaded

$form_id = 1;
$input_values = array(
    'input_1' => 'John Doe',
    'input_2' => 'john@example.com',
    'input_3' => '555-1234',
);

$result = GFAPI::submit_form($form_id, $input_values);

if ($result['is_valid']) {
    echo "Entry created successfully. Entry ID: " . $result['entry_id'] . "\n";
} else {
    echo "Validation failed:\n";
    print_r($result['validation_messages']);
}
```

**Execute:**
```bash
wp eval-file submit-form.php
```

### Example 2: Process Multiple Entries from CSV

```php
<?php
// import-entries.php

// Access command line arguments
global $args;

$csv_file = isset($args[0]) ? $args[0] : 'entries.csv';
$form_id = isset($args[1]) ? (int)$args[1] : 1;

if (!file_exists($csv_file)) {
    echo "Error: CSV file not found: $csv_file\n";
    exit(1);
}

$handle = fopen($csv_file, 'r');
$headers = fgetcsv($handle);
$success_count = 0;
$error_count = 0;

while (($row = fgetcsv($handle)) !== FALSE) {
    $input_values = array();

    // Map CSV columns to form inputs
    foreach ($headers as $index => $field_id) {
        if (isset($row[$index])) {
            $input_values[$field_id] = $row[$index];
        }
    }

    $result = GFAPI::submit_form($form_id, $input_values);

    if ($result['is_valid']) {
        $success_count++;
        echo "✓ Entry {$result['entry_id']} created\n";
    } else {
        $error_count++;
        echo "✗ Entry failed: " . json_encode($result['validation_messages']) . "\n";
    }
}

fclose($handle);

echo "\nImport complete:\n";
echo "Success: $success_count\n";
echo "Errors: $error_count\n";
```

**Execute:**
```bash
wp eval-file import-entries.php entries.csv 1
```

### Example 3: Get Form Data

```php
<?php
// get-form-info.php

global $args;
$form_id = isset($args[0]) ? (int)$args[0] : 1;

$form = GFAPI::get_form($form_id);

if (is_wp_error($form)) {
    echo "Error: " . $form->get_error_message() . "\n";
    exit(1);
}

echo "Form ID: {$form['id']}\n";
echo "Form Title: {$form['title']}\n";
echo "Fields:\n";

foreach ($form['fields'] as $field) {
    echo "  - ID {$field->id}: {$field->label} (Type: {$field->type})\n";
}

// Get entry count
$search_criteria = array();
$entries = GFAPI::get_entries($form_id, $search_criteria);
echo "\nTotal Entries: " . count($entries) . "\n";
```

**Execute:**
```bash
wp eval-file get-form-info.php 1
```

### Example 4: Custom Notification Trigger

```php
<?php
// send-notifications.php

global $args;
$entry_id = isset($args[0]) ? (int)$args[0] : 0;
$event = isset($args[1]) ? $args[1] : 'form_submission';

if (!$entry_id) {
    echo "Error: Entry ID required\n";
    exit(1);
}

$entry = GFAPI::get_entry($entry_id);

if (is_wp_error($entry)) {
    echo "Error: " . $entry->get_error_message() . "\n";
    exit(1);
}

$form = GFAPI::get_form($entry['form_id']);

// Send notifications
GFAPI::send_notifications($form, $entry, $event);

echo "Notifications sent for entry {$entry_id} (event: {$event})\n";
```

**Execute:**
```bash
wp eval-file send-notifications.php 123 form_submission
```

## wp profile eval-file

For performance profiling, use `wp profile eval-file`:

```bash
wp profile eval-file import-entries.php entries.csv 1
```

This provides detailed timing information for WordPress hooks, plugins, and themes.

**Official documentation:** https://developer.wordpress.org/cli/commands/profile/eval-file/

## Best Practices

### 1. Error Handling

Always include error handling in eval-file scripts:

```php
<?php
// Always check for WP_Error instances
$result = GFAPI::submit_form($form_id, $input_values);

if (is_wp_error($result)) {
    echo "Error: " . $result->get_error_message() . "\n";
    exit(1);
}
```

### 2. Exit Codes

Use proper exit codes for script automation:

```php
<?php
if ($success) {
    exit(0);  // Success
} else {
    exit(1);  // Error
}
```

### 3. Output for Automation

Structure output for parsing:

```php
<?php
// JSON output for automation
$result = array(
    'success' => true,
    'entry_id' => 123,
    'message' => 'Entry created',
);

echo json_encode($result) . "\n";
```

### 4. Argument Validation

Validate command-line arguments:

```php
<?php
global $args;

if (count($args) < 2) {
    echo "Usage: wp eval-file script.php <arg1> <arg2>\n";
    exit(1);
}

$form_id = filter_var($args[0], FILTER_VALIDATE_INT);
if ($form_id === false) {
    echo "Error: Invalid form ID\n";
    exit(1);
}
```

### 5. Global Variables

Explicitly declare required globals:

```php
<?php
global $wpdb, $wp_version;

// Now $wpdb and $wp_version are accessible
$prefix = $wpdb->prefix;
```

## Automation Examples

### Cron Job with wp eval-file

```bash
#!/bin/bash
# daily-import.sh

RESULT=$(wp eval-file import-entries.php entries.csv 1 --path=/var/www/html 2>&1)
EXIT_CODE=$?

if [ $EXIT_CODE -eq 0 ]; then
    echo "Import successful: $RESULT"
else
    echo "Import failed: $RESULT"
    # Send alert email
    echo "$RESULT" | mail -s "Import Failed" admin@example.com
fi
```

### Docker Container Usage

```dockerfile
FROM wordpress:latest

# Install WP-CLI
RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar && \
    chmod +x wp-cli.phar && \
    mv wp-cli.phar /usr/local/bin/wp

# Copy script
COPY import-script.php /scripts/

# Run script on container start
CMD ["wp", "eval-file", "/scripts/import-script.php", "--allow-root"]
```

## Gotchas & Limitations

1. **Global variables require explicit declaration** - Don't forget to use `global $wpdb;`

2. **Script execution context** - Code runs within a method, not global scope

3. **Output buffering** - Some plugins may buffer output; use `flush()` if needed

4. **Memory limits** - For large operations, increase PHP memory:
   ```bash
   wp eval-file script.php --memory-limit=512M
   ```

5. **Execution timeout** - For long-running scripts, adjust PHP timeout:
   ```bash
   php -d max_execution_time=600 $(which wp) eval-file script.php
   ```

6. **Plugin compatibility** - Some plugins may not initialize properly in CLI context

7. **Path context** - Use `--path` flag to specify WordPress installation:
   ```bash
   wp eval-file script.php --path=/var/www/html
   ```

## Debugging wp eval-file Scripts

### Enable Debug Mode

```bash
wp eval-file script.php --debug
```

### Add Debug Output

```php
<?php
// Debug information
if (defined('WP_DEBUG') && WP_DEBUG) {
    error_log("Debug: Processing form ID {$form_id}");
}

WP_CLI::debug("Processing entry: " . print_r($input_values, true));
```

### Use WP_CLI Helper Functions

```php
<?php
// Display success message
WP_CLI::success("Import complete!");

// Display error and exit
WP_CLI::error("Form not found!");

// Display warning
WP_CLI::warning("Some entries failed validation");

// Display line
WP_CLI::line("Processing entry 1 of 100...");

// Progress bar
$progress = WP_CLI\Utils\make_progress_bar('Importing entries', 100);
for ($i = 0; $i < 100; $i++) {
    // Process entry
    $progress->tick();
}
$progress->finish();
```

## References

- [wp eval-file Documentation](https://developer.wordpress.org/cli/commands/eval-file/)
- [wp eval Documentation](https://developer.wordpress.org/cli/commands/eval/)
- [wp profile eval-file Documentation](https://developer.wordpress.org/cli/commands/profile/eval-file/)
- [WP-CLI Commands Cookbook](https://make.wordpress.org/cli/handbook/guides/commands-cookbook/)
- [WP-CLI GitHub Repository](https://github.com/wp-cli/eval-command)
