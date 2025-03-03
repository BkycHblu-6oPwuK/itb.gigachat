<?php

namespace Itb\Gigachat\Services;

use Bitrix\Main\Web\Uri;
use Itb\Gigachat\Entity\CacheSettings;
use Itb\Gigachat\Entity\Models\Models;
use Itb\Gigachat\Exceptions\ClientUnathorizedException;
use Psr\Log\LoggerInterface;

/**
 * @link https://developers.sber.ru/docs/ru/gigachat/api/reference/rest/get-models
 */
class ModelsService extends AuthService
{
    private CacheSettings $cacheSettings;

    public function __construct(?LoggerInterface $logger = null)
    {
        parent::__construct($logger);
        $this->cacheSettings = new CacheSettings(1800, 'gigachat_models', '/gigachat/models');
    }

    /**
     * @throws \RuntimeException
     * @throws \Exception
     */
    public function getModels() : Models
    {
        try {
            $result = $this->makeRequest();
        } catch (ClientUnathorizedException $e){
            $this->refreshToken();
            $result = $this->makeRequest();
        }
        if(empty($result['data'])){
            throw new \RuntimeException("Ошибка получения моделей");
        }
        return new Models($result);
    }

    private function makeRequest()
    {
        return $this->get(new Uri("{$this->options->baseGigaChatUrl}/api/v1/models"), null, $this->getHeaders(), $this->cacheSettings);
    }

    private function getHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->getAccessToken(),
        ];
    }
}
