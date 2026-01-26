<?php
/**
 * ==============================================================================
 * DI AUTOMATION BYPASS - DIAGNOSTIC MODE
 * ==============================================================================
 * 1. Logs ALL headers to debug.log (to find why Auth is failing)
 * 2. Injects tokens into 'input_1337' AND 'g-recaptcha-response'
 * 3. Forces validation success early
 */

if ( ! defined( 'DI_AUTOMATION_SECRET_KEY' ) ) {
    define( 'DI_AUTOMATION_SECRET_KEY', 'test-automation-key-2026' );
}

/**
 * Diagnostic Logger
 */
function di_ab_log( $message ) {
    error_log( '[DI-Diagnostic] ' . $message );
}

/**
 * Authorization Check with Header Dump
 */
function di_ab_is_authorized() {
    static $is_auth = null;
    if ( $is_auth === null ) {
        // 1. DUMP HEADERS TO LOG (One time)
        if ( ! defined( 'DI_HEADER_DUMPED' ) ) {
            $headers = [];
            foreach ( $_SERVER as $key => $value ) {
                if ( strpos( $key, 'HTTP_' ) === 0 ) {
                    $headers[ $key ] = $value;
                }
            }
            di_ab_log( 'Incoming Headers: ' . json_encode( $headers ) );
            define( 'DI_HEADER_DUMPED', true );
        }

        $header_value = $_SERVER['HTTP_X_DI_TEST_AUTH'] ?? null;
        $is_auth = ( $header_value && $header_value === DI_AUTOMATION_SECRET_KEY );
        
        if ( $is_auth ) {
            di_ab_log( '✅ Authorization SUCCESS' );
        } else {
            di_ab_log( '❌ Authorization FAILED. Expected: ' . DI_AUTOMATION_SECRET_KEY . ' Got: ' . ( $header_value ? '*****' : 'NULL' ) );
        }
    }
    return $is_auth;
}

/**
 * 1. EARLY INIT: Mock Token Injection (Targeting input_1337)
 */
add_action( 'init', function() {
    if ( di_ab_is_authorized() ) {
        $mock_token = 'AUTOMATION_BYPASS_TOKEN_DIAGNOSTIC';
        
        // Inject into standard and custom fields
        $_REQUEST['g-recaptcha-response'] = $mock_token;
        $_POST['g-recaptcha-response']    = $mock_token;
        $_POST['input_1337']              = $mock_token; // Target specific hidden field
        $_REQUEST['input_1337']           = $mock_token;
        
        di_ab_log( '💉 Injected mock tokens into g-recaptcha-response and input_1337' );
    }
}, 1 );

/**
 * 2. VALIDATION OVERRIDE: Force Success
 */
add_filter( 'gform_validation', function( $result ) {
    if ( di_ab_is_authorized() ) {
        di_ab_log( '🛡️ Forcing gform_validation to TRUE' );
        $result['is_valid'] = true;
        
        // Clear any field errors
        foreach ( $result['form']['fields'] as &$field ) {
            $field->failed_validation = false;
            $field->validation_message = '';
        }
    }
    return $result;
}, 0 ); // Priority 0 (Run FIRST)

/**
 * 3. NETWORK MOCK (Same as before)
 */
add_filter( 'pre_http_request', function( $preempt, $args, $url ) {
    if ( di_ab_is_authorized() && strpos( $url, 'google.com/recaptcha' ) !== false ) {
        di_ab_log( '🛑 Intercepting Google API call' );
        return [
            'response' => [ 'code' => 200, 'message' => 'OK' ],
            'body'     => json_encode( [
                'success' => true,
                'score' => 0.9,
                'action' => 'submit',
                'hostname' => 'localhost'
            ] )
        ];
    }
    return $preempt;
}, 10, 3 );
