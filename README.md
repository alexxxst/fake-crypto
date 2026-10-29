# FakeCrypto PHP SDK

PHP SDK для работы с платежным API FakeCrypto: приём платежей, выплаты, баланс и кошельки.

Документация API: https://doc.cryptomus.com/

## Требования

* PHP **8.3** или выше
* PHP-расширение **curl**
* PHP-расширение **json**

## Установка

Пакет не публикуется на Packagist. Установка выполняется напрямую из Git-репозитория.

### Из VCS-репозитория

Подключите репозиторий в `composer.json` вашего проекта:

```bash
composer config repositories.fake-crypto git https://github.com/alexxxst/fake-crypto.git
```

Затем установите пакет:

```bash
composer require vdsina/fake-crypto:@dev
```

Репозиторий приватный, поэтому Composer должен иметь к нему доступ. При установке по HTTPS используется Git credential helper (например, настроенный через `gh auth login`). Если нужен SSH, замените URL на `git@github.com:alexxxst/fake-crypto.git` и убедитесь, что ключ GitHub добавлен в `~/.ssh/known_hosts`.

Для установки конкретной ветки или тега укажите версию явно:

```bash
composer require vdsina/fake-crypto:dev-main
# после создания тега:
composer require vdsina/fake-crypto:^1.0
```

Замените `dev-main` на имя нужной ветки.

### Локальная разработка

Если пакет лежит рядом с проектом, подключите его как path-репозиторий:

```bash
composer config repositories.fake-crypto path ../FakeCrypto
composer require vdsina/fake-crypto:@dev
```

## Быстрый старт

Для работы с платежами нужны `PAYMENT_KEY` и `MERCHANT_UUID`:

```php
<?php

use FakeCrypto\Api\Client;

const PAYMENT_KEY = 'uQ4LFWCBE3dT84uQnt7ycL7p9WcSwjkSPQaZbik3ChoWO0egw51f4EAaZQKmefhPP0F1cX8OpRcl2c3HexNedoR7FGEYGA1mTgMPI8lzKl7Ct2I43R6SSC3gVDS3rkGX';
const MERCHANT_UUID = 'c26b80a8-9549-4a66-bb53-774f12809249';

$payment = Client::payment(PAYMENT_KEY, MERCHANT_UUID);

$services = $payment->services();
```

Для выплат используется отдельный ключ:

```php
<?php

use FakeCrypto\Api\Client;

const PAYOUT_KEY = 'qseRhcxu6wsxhygfhyidwrrgryrrgefhPP0F1cNedoR7FGEYGA1mTgMPX8OpRcl2c3HexNedoR7FGEYGA1mTgMPI8lzKl7Ct2I43R6S1f4EAaZQKmefhSC3gVDS3rkGX';
const MERCHANT_UUID = 'c26b80a8-9549-4a66-bb53-774f12809249';

$payout = Client::payout(PAYOUT_KEY, MERCHANT_UUID);
```

### Свой адрес API

По умолчанию запросы отправляются на `https://api.cryptomus.com`. Адрес можно переопределить третьим аргументом — например, для тестового окружения:

```php
<?php

$payment = Client::payment(PAYMENT_KEY, MERCHANT_UUID, 'https://api.example.com');
```

## Выплаты

### Создание выплаты

```php
<?php

$data = [
    'amount' => '15',
    'currency' => 'USD',
    'network' => 'TRON',
    'order_id' => '555321',
    'address' => 'TXguLRFtrAFrEDA17WuPfrxB84jVzJcNNV',
    'is_subtract' => '1',
    'url_callback' => 'https://example.com/callback',
];

$result = $payout->create($data);
```

| Параметр | Тип | Описание |
|----------|-----|----------|
| `amount` | string | Сумма выплаты |
| `currency` | string | Валюта |
| `network` | string | Сеть |
| `order_id` | string | Идентификатор заказа в вашей системе |
| `address` | string | Адрес кошелька получателя |
| `is_subtract` | string | Откуда списывать комиссию: `1` — с баланса, `0` — из суммы |
| `url_callback` | string | URL для callback-уведомлений |

Пример ответа:

```json
{
  "uuid": "a7c0caec-a594-4aaa-b1c4-77d511857594",
  "amount": "3",
  "currency": "TRX",
  "network": "tron",
  "address": "TJ...",
  "txid": null,
  "status": "process",
  "is_final": false,
  "balance": 129
}
```

### Информация о выплате

Передайте `uuid` или `order_id`:

```php
<?php

$data = ['uuid' => 'a7c0caec-a594-4aaa-b1c4-77d511857594'];
// или
$data = ['order_id' => '12345'];

$result = $payout->info($data);
```

Пример ответа:

```json
{
  "uuid": "a7c0caec-a594-4aaa-b1c4-77d511857594",
  "amount": "3.00000000",
  "currency": "TRX",
  "network": "tron",
  "address": "TJZ...",
  "txid": null,
  "status": "process",
  "is_final": false,
  "balance": "129.00000000"
}
```

## Платежи

### Доступные сервисы

```php
<?php

$result = $payment->services();
```

Пример ответа:

```json
[
  {
    "network": "TRON",
    "currency": "TRX",
    "is_available": true,
    "limit": {
      "min_amount": "1.00000000",
      "max_amount": "10.00000000"
    },
    "commission": {
      "fee_amount": "0.00",
      "percent": "0.00"
    }
  }
]
```

### Создание платежа

```php
<?php

$data = [
    'amount' => '16',
    'currency' => 'USD',
    'network' => 'ETH',
    'order_id' => '555123',
    'url_return' => 'https://example.com/return',
    'url_callback' => 'https://example.com/callback',
    'is_payment_multiple' => false,
    'lifetime' => '7200',
    'to_currency' => 'ETH',
];

$result = $payment->create($data);
```

| Параметр | Тип | Описание |
|----------|-----|----------|
| `amount` | string | Сумма платежа |
| `currency` | string | Валюта платежа |
| `network` | string | Сеть |
| `order_id` | string | Идентификатор заказа в вашей системе |
| `url_return` | string | URL возврата после оплаты |
| `url_callback` | string | URL для callback-уведомлений |
| `is_payment_multiple` | bool | Разрешить доплату частями |
| `lifetime` | string | Время жизни платежа в секундах |
| `to_currency` | string | Валюта, в которую нужно конвертировать сумму |

Пример ответа:

```json
{
  "uuid": "8b03432e-385b-4670-8d06-064591096795",
  "amount": "16",
  "order_id": "test_19",
  "currency": "TRX",
  "comments": null,
  "network": "tron",
  "address": "TW...",
  "from": null,
  "txid": null,
  "payment_status": "check",
  "url": "https://pay.cryptomus.com/pay/8b03432e-385b-4670-8d06-064591096795",
  "expired_at": 1650980953,
  "status": "check",
  "is_final": false
}
```

### Информация о платеже

Передайте `uuid` или `order_id`:

```php
<?php

$data = ['uuid' => '8b03432e-385b-4670-8d06-064591096795'];
// или
$data = ['order_id' => '12345'];

$result = $payment->info($data);
```

Пример ответа:

```json
{
  "uuid": "8b03432e-385b-4670-8d06-064591096795",
  "order_id": "test_10",
  "amount": "16.00000000",
  "payment_amount": "0.000000",
  "currency": "TRX",
  "comments": null,
  "network": "tron",
  "address": "TW...",
  "from": null,
  "txid": null,
  "payment_status": "check",
  "url": "https://pay.cryptomus.com/pay/8b03432e-385b-4670-8d06-064591096795",
  "expired_at": 1650980953,
  "status": "paid",
  "is_final": true
}
```

### История платежей

```php
<?php

$page = 3;

$result = $payment->history($page);
```

Пример ответа:

```json
{
  "items": [
    {
      "uuid": "87094a43-5fe4-4629-b2fd-c37e8e2af76c",
      "order_id": "1650956609",
      "amount": "16.00000000",
      "payment_amount": "0.01200000",
      "currency": "ETH",
      "comments": null,
      "network": "eth",
      "address": "0x...",
      "from": "0x4...",
      "txid": null,
      "payment_status": "paid",
      "url": "https://pay.cryptomus.com/pay/87094a43-5fe4-4629-b2fd-c37e8e2af76c",
      "expired_at": 1650960209,
      "status": "paid",
      "is_final": true
    }
  ],
  "paginate": {
    "count": 2,
    "hasPages": false,
    "nextCursor": null,
    "previousCursor": null,
    "perPage": 15
  }
}
```

### Баланс

```php
<?php

$result = $payment->balance();
```

Пример ответа (сокращённо):

```json
[
  {
    "balance": {
      "merchant": [
        {
          "uuid": "abcdabcd-abcd-1234-1234-abcdabcd",
          "balance": "0.00000000",
          "currency_code": "ETH"
        }
      ],
      "user": [
        {
          "uuid": "abcdabcd-abcd-1234-1234-abcdabcd",
          "balance": "0.40000000",
          "currency_code": "BTC"
        }
      ]
    }
  }
]
```

### Повторная отправка уведомлений

```php
<?php

$data = ['uuid' => 'a7c0caec-a594-4aaa-b1c4-77d511857594'];
// или
$data = ['order_id' => '12345'];

$result = $payment->reSendNotifications($data);
```

При успешном выполнении метод возвращает `true`.

### Создание кошелька

```php
<?php

$data = [
    'network' => 'TRON',
    'currency' => 'USDT',
    'order_id' => '5535321',
    'url_callback' => 'https://example.com/callback',
];

$result = $payment->createWallet($data);
```

Пример ответа:

```json
{
  "uuid": "9f64a7ce-...",
  "order_id": "24",
  "currency": "USDT",
  "network": "tron",
  "address": "TK8..."
}
```

## Ошибки

Все методы могут выбрасывать `FakeCrypto\Api\RequestBuilderException`:

```php
<?php

use FakeCrypto\Api\Client;
use FakeCrypto\Api\RequestBuilderException;

$payment = Client::payment(PAYMENT_KEY, MERCHANT_UUID);

try {
    $result = $payment->services();
} catch (RequestBuilderException $exception) {
    echo 'Ошибка запроса: ' . $exception->getMessage() . PHP_EOL;
    echo 'Метод: ' . $exception->getMethod() . PHP_EOL;

    foreach ($exception->getErrors() as $field => $errors) {
        echo $field . ': ' . implode(', ', (array) $errors) . PHP_EOL;
    }

    echo 'HTTP-код: ' . $exception->getCode() . PHP_EOL;
}
```

`getErrors()` возвращает ошибки валидации, если они есть в ответе API. `getCode()` содержит HTTP-код ответа.

## Лицензия

MIT.
