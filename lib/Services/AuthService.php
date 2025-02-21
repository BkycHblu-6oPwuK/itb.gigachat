<?php
namespace Itb\Gigachat\Services;

use Itb\Core\Helpers\WebHelper;

class AuthService extends ApiService
{
    public function getAccessToken() : ?string
    {
        static $result = null;
        if($result === null){
            $result = $this->post('/api/v2/oauth', $this->getData(), $this->getHeaders())['access_token'];
        }
        return $result;
    }

    private function getHeaders(): array
    {
        return [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Accept' => 'application/json',
            'RqUID' => WebHelper::getUuidV4(),
            'Authorization' => "Basic {$this->options->authorizationKey}",
        ];
    }

    private function getData(): array
    {
        return [
            'scope' => $this->options->scope,
        ];
    }
}