<?php

namespace Itb\Gigachat\Services;

use Bitrix\Main\Web\Json;
use Bitrix\Main\Web\Uri;
use Itb\Gigachat\Entity\Chat\Chat;
use Itb\Gigachat\Exceptions\ClientUnathorizedException;

/**
 * @link https://developers.sber.ru/docs/ru/gigachat/api/reference/rest/post-chat
 * Без работы с историей чата и потоковой генерации
 */
class ChatService extends AuthService
{
    private string $userPromt = '';
    private ?string $systemPromt = null;
    private 

    /**
     * @param string $userPromt сообщение пользователя
     * @param string $systemPromt системный промпт, который задает роль модели, например, должна модель отвечать как академик или как школьник
     */
    public function getChat(string $userPromt, ?string $systemPromt = null): Chat
    {
        $this->userPromt = $userPromt;
        $this->systemPromt = $systemPromt;
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
        if ($this->systemPromt) {
            $messages[] = [
                'role' => 'system',
                'content' => $this->systemPromt,
            ];
        }
        $messages[] = [
            'role' => 'user',
            'content' => $this->userPromt,
        ];

        return Json::encode([
            'model' => $this->getDefaultModel(),
            'messages' => $messages,
            "stream" => false,
            "repetition_penalty" => 1
        ]);
    }
}
