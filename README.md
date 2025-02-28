# Интеграция с api sber gigachat

для работы обязателен модуль itb.core - https://git.itb-dev.ru/ITB-dev/itb.core
минимальная версия php 8.1

# Установка

1. Установить модуль itb.core
2. Установить этот модуль
3. Выполнить регистрацию в ЛК гигачат - https://developers.sber.ru/docs/ru/gigachat/quickstart/ind-create-project
4. Заполнить настройки модуля (Ключ авторизации и Scope)
5. После сохранения настроек станет доступна настройка с выбором модели, которая будет отвечать на вопросы в чате. Выберите модель в настройке - Модель формирующая ответ на сообщение

# Использование

Пользуйтесь классом ```Itb\Gigachat\Services\ChatService``` чтобы отправлять запросы к модели гигачат. Метод ```getChat``` принимает ваше ваш промт и системный (подробнее https://developers.sber.ru/docs/ru/gigachat/api/reference/rest/post-chat). Метод выкидывает исключения в случае ошибок. В случае когда все успешно возвращается объект ```Itb\Gigachat\Entity\Chat\Chat``` в котором хранится ответ модели.

Пример:
```php
$service = new \Itb\Gigachat\Services\ChatService();
$chat = $service->getChat(
    'GigaChat — это сервис, который умеет взаимодействовать с пользователем в формате диалога, писать код, создавать тексты и картинки по запросу пользователя.',
    'Ты профессиональный переводчик на английский язык. Переведи точно сообщение пользователя.');
/** choices массив ответов */
$message = $chat->choices->first()->message->content;
```

Доступные сервисы:

1. Itb\Gigachat\Services\ApiService - базовый класс для сервисов, в нем методы для отправки запросов к api.
2. Itb\Gigachat\Services\AuthService - Сервис осуществляющий получение (метод ```getAccessToken```) и обновление (метод ```refreshToken```) токена, токен кешируется на 30 минут. В случае ошибки 401 при использовании в других сервисах токена, используйте метод refreshToken и пробуйте выполнить запрос повторно. Остальные сервисы можно наследовать от него
3. Itb\Gigachat\Services\BalanceService - Сервис выполняет запрос за балансом ваших моделей (метод ```getBalance```), возвращает объект ```Itb\Gigachat\Entity\Balance\Balance```.
4. Itb\Gigachat\Services\ChatService
5. Itb\Gigachat\Services\EmbeddingService - сервис для генерации эмбеддинга (метод ```getEmbeddings```), возвращает объект ```Itb\Gigachat\Entity\Embedding\Embeddings```
6. Itb\Gigachat\Services\ModelsService - сервис для получения доступных моделей (метод ```getModels```), возвращает объект ```Itb\Gigachat\Entity\Models\Models```