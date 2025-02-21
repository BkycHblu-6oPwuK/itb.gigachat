<?php

namespace Itb\Gigachat;

final class Options
{
    const MODULE_ID = 'itb.gigachat';

    private static $instance;

    public readonly string $authorizationKey;
    public readonly string $scope;
    public readonly string $baseUrl;
    public readonly bool $logErrors;

    private function __construct()
    {
        $options = \Bitrix\Main\Config\Option::getForModule(self::MODULE_ID);
        $this->authorizationKey = $options['authorization_key'];
        $this->scope = $options['scope'];
        $this->baseUrl = $options['base_api_url'];
        $this->logErrors = $options['log_errors'] && $options['log_errors'] == 'Y' ? true : false;
        if(!$this->authorizationKey || !$this->scope || !$this->baseUrl){
            throw new \Exception("Не заполнены обязательные настройки модуля (Ключ авторизации, Scope или Базовый адрес запроса)");
        }
    }

    public static function getInstance()
    {
        if(self::$instance === null){
            self::$instance = new self;
        }
        return self::$instance;
    }
}
