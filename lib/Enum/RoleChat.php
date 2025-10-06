<?php
namespace Beeralex\Gigachat\Enum;

enum RoleChat : string
{
    /**
     * системный промпт, который задает роль модели, например, должна модель отвечать как академик или как школьник;
     */
    case SYSTEM = 'system';

    /**
     * ответ модели
     */
    case ASSISTANT = 'assistant';

    /**
     * сообщение пользователя
     */
    case USER = 'user';

    /**
     * сообщение с результатом работы пользовательской функции.
     */
    case FUNCTION = 'function ';
}