<?php

namespace Itb\Gigachat\Services;

class ModelsService extends AuthService
{
    public function getModels()
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
}
