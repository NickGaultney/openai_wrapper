<?php

namespace OpenAIWrapper\Helpers;

class SettingsHelper {
    private static string $option_name = 'openai_wrapper_settings';

    public static function get_api_key(): string {
        $options = get_option(self::$option_name);
        return $options['api_key'] ?? '';
    }

    public static function get_model_type(): string {
        $options = get_option(self::$option_name);
        return $options['model_type'] ?? 'gpt-4o';
    }

    public static function get_assistant_id(): string {
        $options = get_option(self::$option_name);
        return $options['assistant_id'] ?? '';
    }

    public static function is_configured(): bool {
        return !empty(self::get_api_key()) && !empty(self::get_assistant_id());
    }
} 