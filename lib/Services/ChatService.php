<?php

namespace Itb\Gigachat\Services;

use Bitrix\Main\Web\Json;

class ChatService extends AuthService
{
    public function getResultByPromt(string $promt)
    {
        $result = $this->post('/api/v1/models', null, $this->getHeaders())['access_token'];
        return $result;
    }

    private function getHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->getAccessToken(),
        ];
    }

    private function getData(): string
    {
        return Json::encode([
            'model' => 'GigaChat',
            'messages' => [
                'role' => 'user',
                'content' => 'promt',
            ],
            "stream" => false,
            "repetition_penalty" => 1
        ]);
    }
}
