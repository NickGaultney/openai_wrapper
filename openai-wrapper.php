<?php
/**
 * Plugin Name: OpenAI Wrapper
 * Plugin URI: https://github.com/nickgaultney/openai-wrapper
 * Description: A WordPress plugin that provides a wrapper for the OpenAI API with a ChatGPT-like interface.
 * Version: 0.1.0
 * Author: Nick Gaultney
 * Author URI: https://github.com/nickgaultney
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: openai-wrapper
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('OPENAI_WRAPPER_VERSION', '0.1.0');
define('OPENAI_WRAPPER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('OPENAI_WRAPPER_PLUGIN_URL', plugin_dir_url(__FILE__));

// Autoload plugin classes
spl_autoload_register(function ($class) {
    $prefix = 'OpenAIWrapper\\';
    $base_dir = OPENAI_WRAPPER_PLUGIN_DIR . 'includes/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Initialize plugin
function openai_wrapper_init() {
    // Load text domain for internationalization
    load_plugin_textdomain('openai-wrapper', false, dirname(plugin_basename(__FILE__)) . '/languages');
    
    // Initialize plugin components
    new OpenAIWrapper\Admin\Settings();
    new OpenAIWrapper\Frontend\Shortcode();
    new OpenAIWrapper\API\RestEndpoints();
}
add_action('plugins_loaded', 'openai_wrapper_init');

// Activation hook
register_activation_hook(__FILE__, function() {
    // Add default settings
    if (!get_option('openai_wrapper_settings')) {
        add_option('openai_wrapper_settings', [
            'api_key' => '',
            'model_type' => 'gpt-4o',
            'assistant_id' => '',
        ]);
    }
});

// Deactivation hook
register_deactivation_hook(__FILE__, function() {
    // Cleanup if needed
}); 