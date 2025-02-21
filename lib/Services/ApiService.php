<?php

namespace Itb\Gigachat\Services;

use Bitrix\Main\Web\Json;
use Bitrix\Main\Web\Uri;
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

    /**
     * @param null|array $options для http клиента bitrix
     */
    public function __construct(?Logger $logger = null)
    {
        $this->client = new Client();
        $this->options = Options::getInstance();
        $this->client->disableSslVerification();
        if(!$logger){
            $logger = new GigachatLogger;
        }
        $this->logger = $logger;
    }

    /**
     * @param string $endpoint относительно базавого url из настроект модуля
     * @param null|array $data ключ-значение
     * @param null|array $headers ключ-значение
     */
    protected function get(string $endpoint, ?array $data = null, ?array $headers = null): ?array
    {
        $uri = new Uri($this->options->baseUrl . $endpoint);
        if ($data) $this->client->setGetParams($data);
        if ($headers) $this->client->setHeaders($headers);
        return $this->request(Method::GET, $uri);
    }

    /**
     * @param string $endpoint относительно базавого url из настроект модуля
     * @param mixed $data
     * @param null|array $headers ключ-значение
     */
    protected function post(string $endpoint, mixed $data = null, ?array $headers = null): ?array
    {
        $uri = new Uri($this->options->baseUrl . $endpoint);
        if ($data) $this->client->setPostData($data);
        if ($headers) $this->client->setHeaders($headers);
        return $this->request(Method::POST, $uri);
    }

    protected function request(Method $method, Uri $uri): ?array
    {
        try {
            $result = $this->handleResult($this->client->request($method, $uri)->getResult());
            return $result;
        } catch (ClientException $e) {
            $error = $this->client->getError();
            if (empty($error)) {
                $error = $this->handleResult($this->client->getResult());
            }
            $error['exceptionMessage'] = $e->getMessage();
            $this->logger->log($error);
        } catch (\Exception $e) {
            $this->logger->log($e->getMessage());
        }
        return null;
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
