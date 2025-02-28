<?php

namespace Itb\Gigachat\Services;

use Bitrix\Main\Data\Cache;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Web\Uri;
use Itb\Gigachat\CacheSettings;
use Itb\Gigachat\Client;
use Itb\Gigachat\Contracts\Logger;
use Itb\Gigachat\Enum\Method;
use Itb\Gigachat\Exceptions\ClientException;
use Itb\Gigachat\Logger as GigachatLogger;
use Itb\Gigachat\Options;

abstract class ApiService
{
    protected readonly Client $client;
    protected readonly Options $options;
    protected readonly Logger $logger;
    protected ?Cache $cache = null;

    /**
     * @param null|array $options для http клиента bitrix
     */
    public function __construct(?Logger $logger = null)
    {
        $this->client = new Client();
        $this->options = Options::getInstance();
        $this->client->disableSslVerification();
        if (!$logger) {
            $logger = new GigachatLogger;
        }
        $this->logger = $logger;
    }

    /**
     * @param Uri $uri адрес запроса
     * @param null|array $data ключ-значение
     * @param null|array $headers ключ-значение
     */
    protected function get(Uri $uri, ?array $data = null, ?array $headers = null, ?CacheSettings $cacheSettings = null): array
    {
        if ($data) $uri->addParams($data);
        if ($headers) $this->client->setHeaders($headers);
        return $this->request(Method::GET, $uri, $cacheSettings);
    }

    /**
     * @param Uri $uri адрес запроса
     * @param mixed $data
     * @param null|array $headers ключ-значение
     */
    protected function post(Uri $uri, mixed $data = null, ?array $headers = null, ?CacheSettings $cacheSettings = null): array
    {
        $this->client->setPostData($data);
        if ($headers) $this->client->setHeaders($headers);
        return $this->request(Method::POST, $uri, $cacheSettings);
    }

    private function request(Method $method, Uri $uri, ?CacheSettings $cacheSettings = null): array
    {
        try {
            if (!$cacheSettings) {
                $cacheSettings = new CacheSettings;
            }
            if ($cacheSettings->time > 0) {
                if(!$this->cache){
                    $this->cache = Cache::createInstance();
                }
                if ($this->cache->initCache(1700, $cacheSettings->key, $cacheSettings->dir)) {
                    return $this->cache->getVars();
                } elseif ($this->cache->startDataCache()) {
                    $result = $this->handleResult($this->client->request($method, $uri)->getResult());
                    if (empty($result)) {
                        $this->cache->abortDataCache();
                        throw new \RuntimeException('Ошибка получения данных при запросе к API');
                    }
                    $this->cache->endDataCache($result);

                    $this->logger->log($uri->getLocator() . ', статус - ' . $this->client->getStatus());

                    return $result;
                }
            }

            $result = $this->handleResult($this->client->request($method, $uri)->getResult());
            if (empty($result)) {
                throw new \RuntimeException('Ошибка получения данных при запросе к api');
            }

            $this->logger->log($uri->getLocator() . ', статус - ' . $this->client->getStatus());

            return $result;
        } catch (ClientException $e) {
            $error = $this->client->getError();
            $result = $this->handleResult($this->client->getResult());
            if (!empty($result)) {
                $error = [
                    'http_error' => $error,
                    'api_error' => $result,
                ];
            }
            $error['exceptionMessage'] = $e->getMessage();
            $error['status'] = $this->client->getStatus();
            $this->logger->log($error);
            throw $e;
        } catch (\Throwable $e) {
            $this->logger->log($e->getMessage());
            throw $e;
        }
    }

    protected function handleResult(mixed $result): array
    {
        try {
            return Json::decode($result);
        } catch (\Exception $e) {
            $this->logger->log($e->getMessage());
        }
        return [];
    }
}
