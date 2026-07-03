<?php

// src/Service/EvaProjectService.php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class EvaProjectService
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $evaApiUrl,
        private string $evaBearerToken
    ) {}

    // Метод 1: Получение списка задач с фильтрацией (или без)
    public function getTasksList(string $query): array
    {
        $filters = [
            ["responsible", "IN", ["__G_CURRENT_USER"]],
            ["order_by", "=", ["-priority", "-orderno"]]
        ];

        if (!empty($query)) {
            $filters[] = ["name", "ilike", "%" . $query . "%"];
        }

        return $this->sendJsonRpcRequest($filters);
    }

    // Метод 2: Получение одной конкретной задачи по её коду или ID
    public function getSingleTask(string $taskId): array
    {
        // Ищем задачу по её уникальному коду (например: CRM-SUB-52) или id
        $field = str_contains($taskId, ':') ? 'id' : 'code';
        $filters = [
            [$field, "=", $taskId]
        ];

        return $this->sendJsonRpcRequest($filters);
    }

    // Внутренний хелпер для отправки POST JSON-RPC запроса
    private function sendJsonRpcRequest(array $filters): array
    {
        $payload = [
            "method" => "CmfTask.list",
            "jsonrpc" => "2.0",
            "no_meta" => true,
            "kwargs" => [
                "select" => ["id", "name", "code"],
                "filter" => $filters
            ],
            "id" => uniqid()
        ];

        try {
            $response = $this->httpClient->request('POST', $this->evaApiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->evaBearerToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]);

            return $response->toArray();
        } catch (\Exception $e) {
            return [
                'jsonrpc' => '2.0',
                'error' => ['code' => -32603, 'message' => $e->getMessage()],
                'result' => []
            ];
        }
    }
}
