# Интеграция с api sber gigachat

для работы обязателен модуль beeralex.core - https://git.beeralex-dev.ru/ITB-dev/beeralex.core
минимальная версия php 8.1

# Установка

добавьте в composer.json экстра опцию, чтобы композер поставил пакет в local/modules

```json
"extra": {
  "installer-paths": {
    "local/modules/{$name}/": ["type:bitrix-module"]
  }
}
```

```bash
composer require beeralex/beeralex.gigachat
```

1. Установить модуль beeralex.core
2. Установить этот модуль
3. Выполнить регистрацию в ЛК гигачат - https://developers.sber.ru/docs/ru/gigachat/quickstart/ind-create-project
4. Заполнить настройки модуля (Ключ авторизации и Scope)
5. После сохранения настроек станет доступна настройка с выбором модели, которая будет отвечать на вопросы в чате. Выберите модель в настройке - Модель формирующая ответ на сообщение

# Сертификат НУЦ минцифры

установите сертификаты по инструкции - https://developers.sber.ru/docs/ru/gigachat/certificates

после этого поставьте галочку в настройке модуля - Сертификат НУЦ Минцифры установлен на уровне системы

Когда эта настройка не установлена, то для всех запросов будет отключена проверка SSL.

# Использование

Пользуйтесь классом ```Beeralex\Gigachat\Services\ChatService``` чтобы отправлять запросы к модели гигачат. Метод ```getChat``` объект ```\Beeralex\Gigachat\Entity\Chat\ChatParam``` (подробнее https://developers.sber.ru/docs/ru/gigachat/api/reference/rest/post-chat). Метод выкидывает исключения в случае ошибок. В случае когда все успешно возвращается объект ```Beeralex\Gigachat\Entity\Chat\Chat``` в котором хранится ответ модели.

Пример:
```php
$service = new \Beeralex\Gigachat\Services\ChatService();
$chat = $service->getChat(new \Beeralex\Gigachat\Entity\Chat\ChatParam(
    new \Beeralex\Gigachat\Entity\Chat\MessagesParam('GigaChat — это сервис, который умеет взаимодействовать с пользователем в формате диалога, писать код, создавать тексты и картинки по запросу пользователя.', 'Ты профессиональный переводчик на английский язык. Переведи точно сообщение пользователя.')
));
/** choices массив ответов */
$message = $chat->choices->first()->message->content;
```

Доступные сервисы:

1. Beeralex\Gigachat\Services\ApiService - базовый класс для сервисов, в нем методы для отправки запросов к api.
2. Beeralex\Gigachat\Services\AuthService - Сервис осуществляющий получение (метод ```getAccessToken```) и обновление (метод ```refreshToken```) токена, токен кешируется на 30 минут. В случае ошибки 401 при использовании в других сервисах токена, используйте метод refreshToken и пробуйте выполнить запрос повторно. Остальные сервисы можно наследовать от него
3. Beeralex\Gigachat\Services\BalanceService - Сервис выполняет запрос за балансом ваших моделей (метод ```getBalance```), возвращает объект ```Beeralex\Gigachat\Entity\Balance\Balance```.
4. Beeralex\Gigachat\Services\ChatService
5. Beeralex\Gigachat\Services\EmbeddingService - сервис для генерации эмбеддинга (метод ```getEmbeddings```), возвращает объект ```Beeralex\Gigachat\Entity\Embedding\Embeddings```
6. Beeralex\Gigachat\Services\ModelsService - сервис для получения доступных моделей (метод ```getModels```), возвращает объект ```Beeralex\Gigachat\Entity\Models\Models```