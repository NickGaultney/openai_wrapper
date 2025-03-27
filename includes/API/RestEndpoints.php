<?php

namespace OpenAIWrapper\API;

use OpenAIWrapper\Helpers\SettingsHelper;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class RestEndpoints {
    private string $namespace = 'openai-wrapper/v1';

    public function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes(): void {
        register_rest_route($this->namespace, '/chat', [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => [$this, 'handle_chat_request'],
            'permission_callback' => [$this, 'check_permission'],
            'args' => [
                'message' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'thread_id' => [
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);
    }

    public function check_permission(): bool {
        return true; // Allow public access, but you might want to add restrictions
    }

    public function handle_chat_request(WP_REST_Request $request): WP_REST_Response|WP_Error {
        if (!SettingsHelper::get_api_key()) {
            return new WP_Error(
                'openai_wrapper_not_configured',
                __('OpenAI API Key is not configured.', 'openai-wrapper'),
                ['status' => 500]
            );
        }

        try {
            $message = $request->get_param('message');
            $thread_id = $request->get_param('thread_id');
            $assistant_id = $request->get_param('assistant_id');

            if (empty($assistant_id)) {
                return new WP_Error(
                    'missing_assistant_id',
                    __('Assistant ID is required.', 'openai-wrapper'),
                    ['status' => 400]
                );
            }
            
            // Initialize OpenAI client
            $openai = new OpenAIClient(
                SettingsHelper::get_api_key(),
                $assistant_id
            );

            // Process the chat request
            $response = $openai->process_chat($message, $thread_id);

            return new WP_REST_Response($response, 200);

        } catch (\Exception $e) {
            return new WP_Error(
                'openai_wrapper_error',
                $e->getMessage(),
                ['status' => 500]
            );
        }
    }
} 