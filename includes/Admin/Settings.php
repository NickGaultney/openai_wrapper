<?php

namespace OpenAIWrapper\Admin;

class Settings {
    private string $option_name = 'openai_wrapper_settings';
    private array $default_settings;

    public function __construct() {
        $this->default_settings = [
            'api_key' => '',
        ];

        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function add_settings_page(): void {
        add_options_page(
            __('OpenAI Wrapper Settings', 'openai-wrapper'),
            __('OpenAI Wrapper', 'openai-wrapper'),
            'manage_options',
            'openai-wrapper-settings',
            [$this, 'render_settings_page']
        );
    }

    public function register_settings(): void {
        register_setting(
            'openai_wrapper_settings',
            $this->option_name,
            [$this, 'sanitize_settings']
        );

        add_settings_section(
            'openai_wrapper_main',
            __('Main Settings', 'openai-wrapper'),
            [$this, 'render_section_description'],
            'openai-wrapper-settings'
        );

        // API Key field
        add_settings_field(
            'api_key',
            __('OpenAI API Key', 'openai-wrapper'),
            [$this, 'render_api_key_field'],
            'openai-wrapper-settings',
            'openai_wrapper_main'
        );
    }

    public function render_settings_page(): void {
        if (!current_user_can('manage_options')) {
            return;
        }

        if (isset($_GET['settings-updated'])) {
            add_settings_error(
                'openai_wrapper_messages',
                'openai_wrapper_message',
                __('Settings Saved', 'openai-wrapper'),
                'updated'
            );
        }

        settings_errors('openai_wrapper_messages');
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('openai_wrapper_settings');
                do_settings_sections('openai-wrapper-settings');
                submit_button('Save Settings');
                ?>
            </form>
        </div>
        <?php
    }

    public function render_section_description(): void {
        echo '<p>' . esc_html__('Configure your OpenAI API settings below.', 'openai-wrapper') . '</p>';
    }

    public function render_api_key_field(): void {
        $options = get_option($this->option_name);
        ?>
        <input type="password"
               id="api_key"
               name="<?php echo esc_attr($this->option_name . '[api_key]'); ?>"
               value="<?php echo esc_attr($options['api_key'] ?? ''); ?>"
               class="regular-text"
        />
        <p class="description">
            <?php esc_html_e('Enter your OpenAI API key', 'openai-wrapper'); ?>
        </p>
        <?php
    }

    public function sanitize_settings(array $input): array {
        $sanitized = [];

        if (isset($input['api_key'])) {
            $sanitized['api_key'] = sanitize_text_field($input['api_key']);
        }

        return $sanitized;
    }
} 