<?php

namespace Itb\Gigachat;

use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Uri;
use Itb\Gigachat\Enum\Method;
use Itb\Gigachat\Exceptions\ClientException;
use Itb\Gigachat\Exceptions\ClientResultEmptyException;

class Client extends HttpClient
{
    protected mixed $data = null;
    protected $result;

    public function __construct(?array $options = null)
    {
        parent::__construct($options);
        //$this->disableSslVerification();
    }

    public function setData(mixed $data): static
    {
        $this->data = $data;
        return $this;
    }

    /**
     * добавляет в массив data ключ и значение, если data был не массивом, то станет пустым массивом
     */
    public function addData(string $name, string $value): static
    {
        if(!is_array($this->data)){
            $this->data = [];
        }
        $this->data[$name] = $value;
        return $this;
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    /**
     * @throws Exception;
     */
    public function request(Method $method, Uri $uri): static
    {
        $result = match ($method) {
            Method::GET => $this->get($uri),
            Method::POST => $this->post($uri),
            default => null
        };
        $this->setResult($result);
        $this->handleResult();
        return $this;
    }

    /**
     * @param Uri|string $url
     * @return mixed
     */
    public function post($url, $postData = null, $multipart = false)
    {
        if ($url instanceof Uri) {
            $url = $url->getLocator();
        }
        return parent::post($url, $this->data ? $this->data : $postData, $multipart);
    }

    /**
     * @param Uri|string $url
     * @return mixed
     */
    public function get($url)
    {
        if ($url instanceof Uri) {
            if (is_array($this->data)) {
                $url->addParams($this->data);
            }
            $url = $url->getLocator();
        }
        return parent::get($url);
    }

    protected function handleResult(): void
    {
        if (!$this->isSuccess()) throw new ClientException('HTTP Request Failed');
        if (!$this->result) throw new ClientResultEmptyException('Request result is null');
    }

    protected function setResult(mixed $result): static
    {
        $this->result = $result;
        return $this;
    }

    public function getResult(): mixed
    {
        return $this->result;
    }

    public function isSuccess(): bool
    {
        $status = $this->getStatus();
        return $status > 0 && $status < 300;
    }
}
