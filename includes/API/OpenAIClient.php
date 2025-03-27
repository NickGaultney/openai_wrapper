<?php

namespace OpenAIWrapper\API;

class OpenAIClient {
    private string $api_key;
    private string $model_type;
    private string $assistant_id;
    private string $api_base = 'https://api.openai.com/v1';
    private int $timeout = 60; // Increased timeout to 60 seconds
    private int $max_retries = 3;

    public function __construct(string $api_key, string $model_type, string $assistant_id) {
        $this->api_key = $api_key;
        $this->model_type = $model_type;
        $this->assistant_id = $assistant_id;
    }

    public function process_chat(string $message, ?string $thread_id = null): array {
        if (empty($thread_id)) {
            $thread_id = $this->create_thread();
        }

        $this->add_message($thread_id, $message);
        $run_id = $this->create_run($thread_id);
        $response = $this->wait_for_response($thread_id, $run_id);

        return [
            'thread_id' => $thread_id,
            'response' => $response,
        ];
    }

    private function create_thread(): string {
        $response = $this->make_request_with_retry('threads', [], 'POST');
        return $response['id'];
    }

    private function add_message(string $thread_id, string $message): array {
        return $this->make_request_with_retry("threads/{$thread_id}/messages", [
            'role' => 'user',
            'content' => $message,
        ], 'POST');
    }

    private function create_run(string $thread_id): string {
        $response = $this->make_request_with_retry("threads/{$thread_id}/runs", [
            'assistant_id' => $this->assistant_id,
        ], 'POST');
        return $response['id'];
    }

    private function wait_for_response(string $thread_id, string $run_id): string {
        $start_time = time();
        $max_wait_time = 120; // Maximum wait time of 2 minutes

        do {
            if (time() - $start_time > $max_wait_time) {
                throw new \Exception("Response timeout after {$max_wait_time} seconds");
            }

            sleep(1);
            $run_status = $this->make_request_with_retry("threads/{$thread_id}/runs/{$run_id}");
            $status = $run_status['status'];

            if ($status === 'failed' || $status === 'cancelled' || $status === 'expired') {
                throw new \Exception("Run failed with status: {$status}");
            }

        } while ($status === 'queued' || $status === 'in_progress');

        if ($status === 'completed') {
            $messages = $this->make_request_with_retry("threads/{$thread_id}/messages")['data'];
            return $messages[0]['content'][0]['text']['value'];
        }

        throw new \Exception("Unexpected run status: {$status}");
    }

    private function make_request_with_retry(string $endpoint, array $data = [], string $method = 'GET'): array {
        $attempts = 0;
        $last_error = null;

        while ($attempts < $this->max_retries) {
            try {
                return $this->make_request($endpoint, $data, $method);
            } catch (\Exception $e) {
                $last_error = $e;
                $attempts++;
                
                // Only retry on timeout errors
                if (!str_contains($e->getMessage(), 'timed out') && !str_contains($e->getMessage(), 'timeout')) {
                    throw $e;
                }

                if ($attempts < $this->max_retries) {
                    sleep(1); // Wait before retrying
                }
            }
        }

        throw new \Exception(
            "Failed after {$this->max_retries} attempts. Last error: " . $last_error->getMessage()
        );
    }

    private function make_request(string $endpoint, array $data = [], string $method = 'GET'): array {
        $url = "{$this->api_base}/{$endpoint}";
        
        $args = [
            'headers' => [
                'Authorization' => "Bearer {$this->api_key}",
                'Content-Type' => 'application/json',
                'OpenAI-Beta' => 'assistants=v1',
            ],
            'timeout' => $this->timeout,
            'method' => $method,
        ];

        if (!empty($data)) {
            $args['body'] = json_encode($data);
        }

        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            throw new \Exception($response->get_error_message());
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $body = json_decode(wp_remote_retrieve_body($response), true);

        if ($response_code >= 400) {
            $error_message = $body['error']['message'] ?? 'Unknown error occurred';
            throw new \Exception("API Error ({$response_code}): {$error_message}");
        }

        return $body;
    }
} 