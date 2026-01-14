<?php

namespace App\Integrations\Linear;

use Illuminate\Support\Facades\Http;

class LinearClient
{
    public function isConfigured(): bool
    {
        return (bool) config('services.linear.api_key');
    }

    public function createIssue(array $payload): array
    {
        $apiKey = config('services.linear.api_key');

        $mutation = <<<'GQL'
mutation CreateIssue($input: IssueCreateInput!) {
  issueCreate(input: $input) {
    success
    issue {
      id
      url
      identifier
      title
    }
  }
}
GQL;

        $res = Http::withToken($apiKey)
            ->post('https://api.linear.app/graphql', [
                'query' => $mutation,
                'variables' => ['input' => $payload],
            ]);

        if (! $res->successful()) {
            return [
                'error' => true,
                'status' => $res->status(),
                'body' => $res->json(),
            ];
        }

        $data = $res->json('data.issueCreate.issue') ?? [];

        return [
            'id' => $data['id'] ?? null,
            'url' => $data['url'] ?? null,
            'identifier' => $data['identifier'] ?? null,
            'title' => $data['title'] ?? null,
        ];
    }
}
