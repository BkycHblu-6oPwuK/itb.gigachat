<?php
namespace Itb\Gigachat\Services;

class AuthService extends ApiService
{
    public function getAccessToken()
    {
        static $result = null;
        if($result === null){
            $result = $this->post('/api/v2/oauth')['access_token'];
        }
        return $result;
    }
}