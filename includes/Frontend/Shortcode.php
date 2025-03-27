<?php

namespace OpenAIWrapper\Frontend;

use OpenAIWrapper\Helpers\SettingsHelper;

class Shortcode {
    public function __construct() {
        add_shortcode('openai-wrapper', [$this, 'render_chat_interface']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function render_chat_interface($atts): string {
        $atts = shortcode_atts([
            'assistant_id' => '',
        ], $atts, 'openai-wrapper');

        if (empty($atts['assistant_id'])) {
            return '<div class="openai-wrapper-error">' . 
                   esc_html__('Assistant ID is required. Please provide it in the shortcode: [openai-wrapper assistant_id="your-assistant-id"]', 'openai-wrapper') . 
                   '</div>';
        }

        if (!SettingsHelper::get_api_key()) {
            return '<div class="openai-wrapper-error">' . 
                   esc_html__('OpenAI API Key is not configured. Please check the settings.', 'openai-wrapper') . 
                   '</div>';
        }

        wp_enqueue_style('openai-wrapper');
        wp_enqueue_script('openai-wrapper');

        ob_start();
        ?>
        <div class="openai-wrapper-chat" 
             data-nonce="<?php echo wp_create_nonce('openai_wrapper_chat'); ?>"
             data-assistant-id="<?php echo esc_attr($atts['assistant_id']); ?>">
            <div class="chat-messages"></div>
            <div class="chat-input-container">
                <textarea class="chat-input" placeholder="<?php esc_attr_e('Type your message...', 'openai-wrapper'); ?>"></textarea>
                <button class="chat-submit">
                    <?php esc_html_e('Send', 'openai-wrapper'); ?>
                </button>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function enqueue_assets(): void {
        wp_register_style(
            'openai-wrapper',
            OPENAI_WRAPPER_PLUGIN_URL . 'assets/styles.css',
            [],
            OPENAI_WRAPPER_VERSION
        );

        // Add Prism CSS
        wp_register_style(
            'prism',
            'https://cdn.jsdelivr.net/npm/prismjs@1.29.0/themes/prism-tomorrow.min.css',
            [],
            OPENAI_WRAPPER_VERSION
        );

        wp_register_script(
            'marked',
            'https://cdn.jsdelivr.net/npm/marked/marked.min.js',
            [],
            OPENAI_WRAPPER_VERSION,
            true
        );

        // Add Prism JS and its plugins
        wp_register_script(
            'prism',
            'https://cdn.jsdelivr.net/npm/prismjs@1.29.0/prism.min.js',
            [],
            OPENAI_WRAPPER_VERSION,
            true
        );

        wp_register_script(
            'prism-autoloader',
            'https://cdn.jsdelivr.net/npm/prismjs@1.29.0/plugins/autoloader/prism-autoloader.min.js',
            ['prism'],
            OPENAI_WRAPPER_VERSION,
            true
        );

        wp_register_script(
            'openai-wrapper',
            OPENAI_WRAPPER_PLUGIN_URL . 'assets/scripts.js',
            ['jquery', 'marked', 'prism', 'prism-autoloader'],
            OPENAI_WRAPPER_VERSION,
            true
        );

        wp_enqueue_style('prism');
        wp_localize_script('openai-wrapper', 'openAIWrapper', [
            'ajaxUrl' => rest_url('openai-wrapper/v1'),
            'nonce' => wp_create_nonce('wp_rest'),
        ]);
    }
} 