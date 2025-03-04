<?php

namespace Itb\Gigachat;

final class Options
{
    const MODULE_ID = 'itb.gigachat';

    private static $instance;

    public readonly string $authorizationKey;
    public readonly string $scope;
    public readonly string $baseOauthUrl;
    public readonly string $baseGigaChatUrl;
    public readonly string $defaultModel;
    public readonly bool $logsEnable;
    public readonly bool $certEnable;

    private function __construct()
    {
        $options = \Bitrix\Main\Config\Option::getForModule(self::MODULE_ID);
        $this->authorizationKey = $options['authorization_key'];
        $this->scope = $options['scope'];
        $this->baseOauthUrl = $options['base_oauth_url'];
        $this->baseGigaChatUrl = $options['base_gigachat_url'];
        $this->logsEnable = $options['logs_enable'] && $options['logs_enable'] == 'Y' ? true : false;
        $this->defaultModel = $options['gigachat_model'] ? $options['gigachat_model'] : '';
        $this->certEnable = $options['cert_enable'] && $options['cert_enable'] == 'Y' ? true : false;
        if(!$this->authorizationKey || !$this->scope || !$this->baseOauthUrl || !$this->baseGigaChatUrl){
            throw new \Exception("Не заполнены обязательные настройки модуля (Ключ авторизации, Scope, Базовый адрес запроса для получения токена или Базовый адрес запроса к GigaChat API)");
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
