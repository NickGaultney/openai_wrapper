<?php

namespace OpenAIWrapper\API;

class OpenAIClient {
    private string $api_key;
    private string $model_type;
    private string $assistant_id;
    private string $api_base = 'https://api.openai.com/v1';

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
        $response = $this->make_request('threads', [], 'POST');
        return $response['id'];
    }

    private function add_message(string $thread_id, string $message): array {
        return $this->make_request("threads/{$thread_id}/messages", [
            'role' => 'user',
            'content' => $message,
        ], 'POST');
    }

    private function create_run(string $thread_id): string {
        $response = $this->make_request("threads/{$thread_id}/runs", [
            'assistant_id' => $this->assistant_id,
        ], 'POST');
        return $response['id'];
    }

    private function wait_for_response(string $thread_id, string $run_id): string {
        do {
            sleep(1);
            $status = $this->make_request("threads/{$thread_id}/runs/{$run_id}")['status'];
        } while ($status === 'queued' || $status === 'in_progress');

        if ($status === 'completed') {
            $messages = $this->make_request("threads/{$thread_id}/messages")['data'];
            return $messages[0]['content'][0]['text']['value'];
        }

        throw new \Exception("Run failed with status: {$status}");
    }

    private function make_request(string $endpoint, array $data = [], string $method = 'GET'): array {
        $url = "{$this->api_base}/{$endpoint}";
        
        $args = [
            'headers' => [
                'Authorization' => "Bearer {$this->api_key}",
                'Content-Type' => 'application/json',
            ],
            'timeout' => 30,
        ];

        if (!empty($data)) {
            $args['body'] = json_encode($data);
        }

        $response = wp_remote_request($url, array_merge($args, ['method' => $method]));

        if (is_wp_error($response)) {
            throw new \Exception($response->get_error_message());
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (!empty($body['error'])) {
            throw new \Exception($body['error']['message']);
        }

        return $body;
    }
} 