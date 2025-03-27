<?php

namespace OpenAIWrapper\Frontend;

use OpenAIWrapper\Helpers\SettingsHelper;

class Shortcode {
    public function __construct() {
        add_shortcode('openai-wrapper', [$this, 'render_chat_interface']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function render_chat_interface(): string {
        if (!SettingsHelper::is_configured()) {
            return '<div class="openai-wrapper-error">' . 
                   esc_html__('OpenAI Wrapper is not properly configured. Please check the settings.', 'openai-wrapper') . 
                   '</div>';
        }

        wp_enqueue_style('openai-wrapper');
        wp_enqueue_script('openai-wrapper');

        ob_start();
        ?>
        <div class="openai-wrapper-chat" data-nonce="<?php echo wp_create_nonce('openai_wrapper_chat'); ?>">
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

        wp_register_script(
            'openai-wrapper',
            OPENAI_WRAPPER_PLUGIN_URL . 'assets/scripts.js',
            ['jquery'],
            OPENAI_WRAPPER_VERSION,
            true
        );

        wp_localize_script('openai-wrapper', 'openAIWrapper', [
            'ajaxUrl' => rest_url('openai-wrapper/v1'),
            'nonce' => wp_create_nonce('wp_rest'),
        ]);
    }
} 