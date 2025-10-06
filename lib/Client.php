<?php

namespace Beeralex\Gigachat;

use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Uri;
use Beeralex\Gigachat\Enum\Method;
use Beeralex\Gigachat\Exceptions\ClientException;
use Beeralex\Gigachat\Exceptions\ClientUnathorizedException;

class Client extends HttpClient
{
    protected mixed $postData = null;

    public function __construct(?array $options = null)
    {
        parent::__construct($options);
    }

    public function setPostData(mixed $data)
    {
        $this->postData = $data;
        return $this;
    }

    public function getPostData(): mixed
    {
        return $this->postData;
    }

    /**
     * @throws \Throwable;
     */
    public function request(Method $method, Uri $uri): static
    {
        match ($method) {
            Method::GET => $this->get($uri->getLocator()),
            Method::POST => $this->post($uri->getLocator(), $this->getPostData()),
            default => null
        };
        $this->handleResult();
        return $this;
    }

    protected function handleResult(): void
    {
        if ($this->getStatus() === 401) throw new ClientUnathorizedException('Client unathorized');
        if (!$this->isSuccess()) throw new ClientException('HTTP Request Failed');
    }

    public function isSuccess(): bool
    {
        $status = $this->getStatus();
        return $status > 0 && $status < 300;
    }
}
