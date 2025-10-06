<?php

namespace Beeralex\Gigachat\Services;

use Bitrix\Main\Web\Uri;
use Beeralex\Core\Dto\CacheSettingsDto;
use Beeralex\Gigachat\Entity\Models\Models;
use Beeralex\Gigachat\Exceptions\ClientUnathorizedException;
use Psr\Log\LoggerInterface;

/**
 * @link https://developers.sber.ru/docs/ru/gigachat/api/reference/rest/get-models
 */
class ModelsService extends AuthService
{
    private CacheSettingsDto $cacheSettings;

    public function __construct(?LoggerInterface $logger = null)
    {
        parent::__construct($logger);
        $this->cacheSettings = new CacheSettingsDto(1800, 'gigachat_models', '/gigachat/models');
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
