<?php

namespace Beeralex\Gigachat\Services;

use Bitrix\Main\Web\Uri;
use Beeralex\Gigachat\Entity\Balance\Balance;
use Beeralex\Gigachat\Exceptions\ClientUnathorizedException;

/**
 * @link https://developers.sber.ru/docs/ru/gigachat/api/reference/rest/get-balance
 */
class BalanceService extends AuthService
{
    /**
     * @throws \RuntimeException
     * @throws \Exception
     */
    public function getBalance() : Balance
    {
        try {
            $result = $this->makeRequest();
        } catch (ClientUnathorizedException $e){
            $this->refreshToken();
            $result = $this->makeRequest();
        }
        if(empty($result['balance'])){
            throw new \RuntimeException("Ошибка получения баланса");
        }
        return new Balance($result);
    }

    private function makeRequest()
    {
        return $this->get(new Uri("{$this->options->baseGigaChatUrl}/api/v1/balance"), null, $this->getHeaders());
    }

    private function getHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->getAccessToken(),
        ];
    }
}
