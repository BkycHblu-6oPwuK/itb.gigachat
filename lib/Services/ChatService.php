<?php

namespace Beeralex\Gigachat\Services;

use Bitrix\Main\Web\Json;
use Bitrix\Main\Web\Uri;
use Beeralex\Gigachat\Entity\Chat\Chat;
use Beeralex\Gigachat\Entity\Chat\ChatParam;
use Beeralex\Gigachat\Exceptions\ClientUnathorizedException;

/**
 * @link https://developers.sber.ru/docs/ru/gigachat/api/reference/rest/post-chat
 * Без работы с историей чата и потоковой генерации
 */
class ChatService extends AuthService
{
    private ChatParam $params;

    /**
     * @param string $userPromt сообщение пользователя
     * @param string $systemPromt системный промпт, который задает роль модели, например, должна модель отвечать как академик или как школьник
     */
    public function getChat(ChatParam $params): Chat
    {
        $this->params = $params;
        try {
            $result = $this->makeRequest();
        } catch (ClientUnathorizedException $e) {
            $this->refreshToken();
            $result = $this->makeRequest();
        }
        return new Chat($result);
    }

    private function makeRequest()
    {
        return $this->post(new Uri("{$this->options->baseGigaChatUrl}/api/v1/chat/completions"), $this->getData(), $this->getHeaders());
    }

    private function getHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->getAccessToken(),
            //'X-Session-ID' => '' для сохранения истории
        ];
    }

    private function getDefaultModel(): string
    {
        $model = $this->options->defaultModel;
        if (!$model) {
            $model = (new ModelsService)->getModels()->first()->id;
        }
        return $model;
    }

    private function getData(): string
    {
        $messages = [];
        $params = [
            'model' => $this->getDefaultModel(),
            'messages' => $messages,
            'stream' => $this->params->stream,
            'repetition_penalty' => $this->params->repetitionPenalty,
        ];
        if($this->params->temperature !== null){
            $params['temperature'] = $this->params->temperature;
        }
        if($this->params->topP !== null){
            $params['top_p'] = $this->params->topP;
        }
        if($this->params->maxTokens !== null){
            $params['max_tokens'] = $this->params->maxTokens;
        }
        foreach($this->params->messages->getMessages() as $message){
            $messages[] = [
                'role' => $message->role->value,
                'content' => $message->content,
            ];
        }
        $params['messages'] = $messages;
        return Json::encode($params);
    }
}
