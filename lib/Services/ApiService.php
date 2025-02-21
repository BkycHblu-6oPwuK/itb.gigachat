<?php

namespace Itb\Gigachat\Services;

use Bitrix\Main\Web\Json;
use Bitrix\Main\Web\Uri;
use Itb\Core\Helpers\WebHelper;
use Itb\Gigachat\Client;
use Itb\Gigachat\Enum\Method;
use Itb\Gigachat\Exceptions\ClientException;
use Itb\Gigachat\Options;

abstract class ApiService
{
    protected readonly Client $client;
    protected readonly Options $options;

    /**
     * @param null|array $options для http клиента bitrix
     */
    public function __construct(?array $options = null)
    {
        $this->client = new Client($options);
        $this->options = Options::getInstance();
        $this->setHeaders($this->getDefaultHeaders());
        $this->setData($this->getDefaultData());
    }

    /**
     * @param string $endpoint относительно базавого url из настроект модуля
     * @param null|array $data ключ-значение
     * @param null|array $headers ключ-значение
     */
    protected function get(string $endpoint, ?array $data = null, ?array $headers = null): ?array
    {
        $uri = new Uri($this->options->baseUrl . $endpoint);
        if ($data) $this->setData($data);
        if ($headers) $this->setHeaders($headers);
        return $this->request(Method::GET, $uri);
    }

    /**
     * @param string $endpoint относительно базавого url из настроект модуля
     * @param null|array $data ключ-значение
     * @param null|array $headers ключ-значение
     */
    protected function post(string $endpoint, ?array $data = null, ?array $headers = null): ?array
    {
        $uri = new Uri($this->options->baseUrl . $endpoint);
        if ($data) $this->setData($data);
        if ($headers) $this->setHeaders($headers);
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
            $this->log($error);
        } catch (\Exception $e) {
            $this->log($e->getMessage());
        }
        return null;
    }

    protected function handleResult(mixed $result): array
    {
        try {
            return Json::decode($result);
        } catch (\Exception $e) {
            $this->log($e->getMessage());
        }
        return [];
    }

    protected function getDefaultHeaders(): array
    {
        return [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Accept' => 'application/json',
            'RqUID' => WebHelper::getUuidV4(),
            'Authorization' => "Basic {$this->options->authorizationKey}",
        ];
    }

    protected function getDefaultData(): array
    {
        return [
            'scope' => $this->options->scope,
        ];
    }

    protected function setHeaders(array $headers): void
    {
        foreach ($headers as $name => $value) {
            $this->client->setHeader($name, $value);
        }
    }

    protected function setData(array $data): void
    {
        foreach ($data as $name => $value) {
            $this->client->addData($name, $value);
        }
    }

    protected function log(string|array $data): void
    {
        if ($this->options->logErrors) {
            $filename = date('Y-m-d') . '.log';
            if (is_array($data)) {
                $log = date('H:i:s') . ' ' . print_r($data, true);
            } else {
                $log = date('H:i:s') . ' ' . $data;
            }
            file_put_contents(__DIR__ . "/../../logs/{$filename}", $log . PHP_EOL, FILE_APPEND);
        }
    }
}
