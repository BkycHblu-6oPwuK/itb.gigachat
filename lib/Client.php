<?php

namespace Itb\Gigachat;

use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Uri;
use Itb\Gigachat\Enum\Method;
use Itb\Gigachat\Exceptions\ClientException;
use Itb\Gigachat\Exceptions\ClientResultEmptyException;

class Client extends HttpClient
{
    protected ?array $getParams = null;
    protected mixed $postData = null;
    protected $result;

    public function __construct(?array $options = null)
    {
        parent::__construct($options);
    }

    /**
     * добавить параметры к get запросу
     */
    public function setGetParams(array $data): static
    {
        $this->getParams = $data;
        return $this;
    }

    /**
     * добавить параметр к параметрам get запроса
     */
    public function addGetParams(string $name, string $value): static
    {
        $this->getParams[$name] = $value;
        return $this;
    }

    /**
     * получить параметры get запроса
     */
    public function getParams(): ?array
    {
        return $this->getParams;
    }

    /**
     * данные для post запроса
     */
    public function setPostData(mixed $data): static
    {
        $this->postData = $data;
        return $this;
    }

    /**
     * данные post запроса
     */
    public function getPostData(): mixed
    {
        return $this->postData;
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
     * @param $postData игнорируется если до этого данные были установлены в postData
     * @return mixed
     */
    public function post($url, $postData = null, $multipart = false)
    {
        if ($url instanceof Uri) {
            $url = $url->getLocator();
        }
        if($thisPostData = $this->getPostData()){
            $postData = $thisPostData;
        }
        return parent::post($url, $postData, $multipart);
    }

    /**
     * @param Uri|string $url
     * @return mixed
     */
    public function get($url)
    {
        if ($url instanceof Uri) {
            if ($params = $this->getParams()) {
                $url->addParams($params);
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
